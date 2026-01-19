<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\Inventory;
use App\Models\Customer;
use App\Models\OrderItem;
use App\Models\Category;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LeysDashboardService
{
    /**
     * Get overall sales metrics with caching
     */
    public function getSummaryMetrics($startDate = null, $endDate = null)
    {
        $cacheKey = "dashboard_summary_" . ($startDate ?: 'all') . "_" . ($endDate ?: 'all');
        
        return Cache::remember($cacheKey, 300, function () use ($startDate, $endDate) {
            $query = Order::whereIn('status', ['confirmed', 'processing', 'shipped', 'delivered']);
            
            if ($startDate && $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }
            
            $orders = $query->get();
            
            $totalSales = $orders->sum('total_amount');
            $orderCount = $orders->count();
            $avgOrderValue = $orderCount > 0 ? $totalSales / $orderCount : 0;
            
            // Calculate inventory turnover rate
            $inventoryTurnover = $this->calculateInventoryTurnover($startDate, $endDate);
            
            return [
                'total_sales' => $totalSales,
                'order_count' => $orderCount,
                'average_order_value' => round($avgOrderValue, 2),
                'inventory_turnover_rate' => round($inventoryTurnover, 2),
                'date_range' => [
                    'start' => $startDate,
                    'end' => $endDate
                ]
            ];
        });
    }
    
    /**
     * Get sales performance data with date filtering
     */
    public function getSalesPerformance($period = 'month')
{
    $cacheKey = "sales_performance_{$period}";
    
    return Cache::remember($cacheKey, 180, function () use ($period) {
        $endDate = Carbon::now();
        
        switch ($period) {
            case 'today':
                $startDate = Carbon::today();
                break;
            case 'week':
                $startDate = Carbon::now()->subWeek();
                break;
            case 'month':
                $startDate = Carbon::now()->subMonth();
                break;
            case 'quarter':
                $startDate = Carbon::now()->subQuarter();
                break;
            case 'year':
                $startDate = Carbon::now()->subYear();
                break;
            default:
                $startDate = Carbon::now()->subMonth();
        }
        
        // Get all orders in the date range
        $orders = Order::whereBetween('created_at', [$startDate, $endDate])
            ->whereIn('status', ['confirmed', 'processing', 'shipped', 'delivered'])
            ->get();
        
        // Group orders by date in PHP (database-agnostic)
        $groupedData = $orders->groupBy(function ($order) use ($period) {
            $date = Carbon::parse($order->created_at);
            
            switch ($period) {
                case 'today':
                    return $date->format('H:00'); // Group by hour
                case 'week':
                case 'month':
                    return $date->format('Y-m-d'); // Group by day
                case 'quarter':
                case 'year':
                    return $date->format('Y-m'); // Group by month
                default:
                    return $date->format('Y-m-d');
            }
        })->map(function ($orders, $periodKey) {
            return [
                'period' => $periodKey,
                'order_count' => $orders->count(),
                'total_sales' => $orders->sum('total_amount'),
                'avg_order_value' => $orders->avg('total_amount')
            ];
        })->sortBy('period')->values();
        
        return [
            'period' => $period,
            'data' => $groupedData,
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString()
        ];
    });
}
    
    /**
     * Get category-wise inventory summary
     */
    public function getInventoryStatus()
    {
        $cacheKey = 'inventory_status_summary';
        
        return Cache::remember($cacheKey, 600, function () {
            // Fixed: Include all non-aggregated columns in GROUP BY
            $results = DB::table('inventories')
                ->join('products', 'inventories.product_id', '=', 'products.id')
                ->join('categories', 'products.category_id', '=', 'categories.id')
                ->select(
                    'categories.id as category_id',
                    'categories.name as category_name',
                    DB::raw('COUNT(DISTINCT inventories.product_id) as product_count'),
                    DB::raw('SUM(inventories.quantity) as total_quantity'),
                    DB::raw('SUM(inventories.reserved_quantity) as total_reserved'),
                    DB::raw('SUM(inventories.quantity - inventories.reserved_quantity) as total_available'),
                    DB::raw('AVG(products.price) as avg_price'),
                    DB::raw('SUM((inventories.quantity - inventories.reserved_quantity) * products.price) as total_available_value'),
                    DB::raw('SUM(inventories.quantity * products.price) as total_value')
                )
                ->groupBy('categories.id', 'categories.name')
                ->orderBy('categories.name')
                ->get();
            
            return $results->groupBy('category_name');
        });
    }
    
    /**
     * Get top 5 selling products
     */
    public function getTopProducts($limit = 5)
    {
        $cacheKey = "top_products_{$limit}";
        
        return Cache::remember($cacheKey, 300, function () use ($limit) {
            return OrderItem::with('product')
                ->select(
                    'product_id',
                    DB::raw('SUM(quantity) as total_sold'),
                    DB::raw('SUM(quantity * unit_price) as total_revenue')
                )
                ->whereHas('order', function ($query) {
                    $query->whereIn('status', ['confirmed', 'processing', 'shipped', 'delivered']);
                })
                ->groupBy('product_id')
                ->orderByDesc('total_sold')
                ->limit($limit)
                ->get()
                ->map(function ($item) {
                    return [
                        'product' => $item->product,
                        'total_sold' => $item->total_sold,
                        'total_revenue' => $item->total_revenue
                    ];
                });
        });
    }
    
    /**
     * Calculate inventory turnover rate
     * Formula: Cost of Goods Sold / Average Inventory Value
     */
    private function calculateInventoryTurnover($startDate = null, $endDate = null)
    {
        // Get Cost of Goods Sold (using cost_price from products)
        $cogsQuery = OrderItem::join('products', 'order_items.product_id', '=', 'products.id')
            ->whereHas('order', function ($q) use ($startDate, $endDate) {
                $q->whereIn('status', ['confirmed', 'processing', 'shipped', 'delivered']);
                
                if ($startDate && $endDate) {
                    $q->whereBetween('orders.created_at', [$startDate, $endDate]);
                }
            });
        
        // Use cost_price if available, otherwise use unit_price
        $cogs = $cogsQuery->sum(DB::raw('order_items.quantity * COALESCE(products.cost_price, order_items.unit_price)'));
        
        // Get average inventory value (average quantity * average cost)
        $avgQuantity = Inventory::avg('quantity');
        $avgCost = Product::avg(DB::raw('COALESCE(cost_price, price)'));
        $avgInventoryValue = $avgQuantity * $avgCost;
        
        return $avgInventoryValue > 0 ? $cogs / $avgInventoryValue : 0;
    }
}
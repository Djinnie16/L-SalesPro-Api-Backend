<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Inventory;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class LeysProductService
{
    public function getProductsWithFilters(array $filters): LengthAwarePaginator
    {
        $query = Product::with(['inventories.warehouse'])
            ->withSum('inventories', 'quantity')
            ->withSum('inventories', 'available_quantity');
        
        // Category filter
        if (isset($filters['category'])) {
            $query->where('category', $filters['category']);
        }
        
        // Subcategory filter
        if (isset($filters['subcategory'])) {
            $query->where('subcategory', $filters['subcategory']);
        }
        
        // Price range filter
        if (isset($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }
        if (isset($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }
        
        // Stock status filter
        if (isset($filters['stock_status'])) {
            if ($filters['stock_status'] === 'in_stock') {
                $query->whereHas('inventories', function ($q) {
                    $q->where('available_quantity', '>', 0);
                });
            } elseif ($filters['stock_status'] === 'out_of_stock') {
                $query->whereDoesntHave('inventories', function ($q) {
                    $q->where('available_quantity', '>', 0);
                });
            } elseif ($filters['stock_status'] === 'low_stock') {
                $query->whereHas('inventories', function ($q) {
                    $q->whereColumn('available_quantity', '<=', 'reorder_level')
                      ->where('available_quantity', '>', 0);
                });
            }
        }
        
        // Warehouse filter
        if (isset($filters['warehouse_id'])) {
            $query->whereHas('inventories', function ($q) use ($filters) {
                $q->where('warehouse_id', $filters['warehouse_id']);
            });
        }
        
        // Full-text search
        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%")
                  ->orWhere('sku', 'LIKE', "%{$search}%");
            });
        }
        
        // Sorting
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        
        // Handle special sort cases
        if ($sortBy === 'total_stock') {
            $query->orderBy('inventories_sum_quantity', $sortOrder);
        } elseif ($sortBy === 'available_stock') {
            $query->orderBy('inventories_sum_available_quantity', $sortOrder);
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }
        
        return $query->paginate($filters['per_page'] ?? 15);
    }
    
    public function createProduct(array $data): Product
    {
        return DB::transaction(function () use ($data) {
            $product = Product::create($data);
            
            // Create inventory entries for all warehouses
            $warehouses = \App\Models\Warehouse::all();
            foreach ($warehouses as $warehouse) {
                Inventory::create([
                    'product_id' => $product->id,
                    'warehouse_id' => $warehouse->id,
                    'quantity' => 0,
                    'reserved_quantity' => 0,
                    'average_cost' => $data['cost_price'] ?? null
                ]);
            }
            
            return $product->load('inventories.warehouse');
        });
    }
    
    public function updateProduct(Product $product, array $data): bool
    {
        return $product->update($data);
    }
    
    public function getLowStockProducts(array $filters = []): LengthAwarePaginator
    {
        $query = Product::whereHas('inventories', function ($q) {
            $q->whereColumn('available_quantity', '<=', 'reorder_level')
              ->where('available_quantity', '>', 0);
        })->with(['inventories' => function ($q) {
            $q->whereColumn('available_quantity', '<=', 'reorder_level')
              ->with('warehouse');
        }]);
        
        // Filter by warehouse
        if (isset($filters['warehouse_id'])) {
            $query->whereHas('inventories', function ($q) use ($filters) {
                $q->where('warehouse_id', $filters['warehouse_id'])
                  ->whereColumn('available_quantity', '<=', 'reorder_level');
            });
        }
        
        return $query->paginate($filters['per_page'] ?? 20);
    }
}
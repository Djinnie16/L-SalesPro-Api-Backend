<?php

namespace App\Console\Commands;

use App\Models\Inventory;
use App\Models\User;
use App\Services\LeysNotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckLowStock extends Command
{
    protected $signature = 'leys:check-low-stock';
    protected $description = 'Check for low stock products and send notifications';

    public function handle(LeysNotificationService $notificationService): void
    {
        $this->info('Checking for low stock products...');

        // Find all inventory items below reorder level
        $lowStockItems = Inventory::whereColumn('available_quantity', '<=', 'reorder_level')
            ->with(['product', 'warehouse'])
            ->get();

        $count = 0;
        
        foreach ($lowStockItems as $inventory) {
            // Get sales managers to notify
            $managers = User::where('role', 'Sales Manager')->get();
            
            foreach ($managers as $manager) {
                $notificationService->sendLowStockAlert($manager, [
                    'product_name' => $inventory->product->name,
                    'sku' => $inventory->product->sku,
                    'current_stock' => $inventory->available_quantity,
                    'reorder_level' => $inventory->reorder_level,
                    'warehouse' => $inventory->warehouse->name,
                    'warehouse_code' => $inventory->warehouse->code,
                ]);
                
                $count++;
            }
        }

        $this->info("Sent {$count} low stock notifications.");
        
        // Use Log facade instead of \Log
        Log::info('Low stock check completed', [
            'low_stock_items_count' => $lowStockItems->count(),
            'notifications_sent' => $count,
            'timestamp' => now()
        ]);
    }
}
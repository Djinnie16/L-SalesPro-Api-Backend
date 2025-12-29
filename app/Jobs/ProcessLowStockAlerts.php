<?php

namespace App\Jobs;

use App\Models\Inventory;
use App\Notifications\LowStockNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;

class ProcessLowStockAlerts implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        // Get low stock items
        $lowStockItems = Inventory::whereColumn('available_quantity', '<=', 'reorder_level')
            ->where('available_quantity', '>', 0)
            ->with(['product', 'warehouse'])
            ->get();
        
        foreach ($lowStockItems as $inventory) {
            // Send notification to warehouse manager
            Notification::route('mail', $inventory->warehouse->manager_email)
                ->notify(new LowStockNotification($inventory));
            
            // Also send to inventory managers (you can add more recipients)
            $additionalRecipients = ['inventory@leysco.co.ke', 'procurement@leysco.co.ke'];
            foreach ($additionalRecipients as $recipient) {
                Notification::route('mail', $recipient)
                    ->notify(new LowStockNotification($inventory));
            }
            
            // Mark as alerted (optional - prevent duplicate alerts)
            $inventory->update(['low_stock_alerted_at' => now()]);
        }
    }
}
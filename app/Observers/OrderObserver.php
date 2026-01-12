<?php

namespace App\Observers;

use App\Models\Order;
use Illuminate\Support\Facades\Cache;

class OrderObserver
{
    public function saved(Order $order)
    {
        // Clear dashboard caches when order changes
        Cache::forget('dashboard_summary_all_all');
        Cache::forget('sales_performance_month');
        Cache::forget('sales_performance_week');
        // Add other cache keys as needed
    }
}
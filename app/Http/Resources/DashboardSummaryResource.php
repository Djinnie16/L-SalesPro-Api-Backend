<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DashboardSummaryResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'total_sales' => [
                'amount' => $this['total_sales'],
                'formatted' => \App\Helpers\LeysHelpers::formatCurrency($this['total_sales'])
            ],
            'order_count' => $this['order_count'],
            'average_order_value' => [
                'amount' => $this['average_order_value'],
                'formatted' => \App\Helpers\LeysHelpers::formatCurrency($this['average_order_value'])
            ],
            'inventory_turnover_rate' => $this['inventory_turnover_rate'],
            'date_range' => $this['date_range'],
            'timestamp' => now()->toDateTimeString()
        ];
    }
}
<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SalesPerformanceResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'period' => $this['period'],
            'data' => $this['data']->map(function ($item) {
                return [
                    'period' => $item->period,
                    'order_count' => $item->order_count,
                    'total_sales' => [
                        'amount' => $item->total_sales,
                        'formatted' => \App\Helpers\LeysHelpers::formatCurrency($item->total_sales)
                    ],
                    'average_order_value' => [
                        'amount' => $item->avg_order_value,
                        'formatted' => \App\Helpers\LeysHelpers::formatCurrency($item->avg_order_value)
                    ]
                ];
            }),
            'date_range' => [
                'start' => $this['start_date'],
                'end' => $this['end_date']
            ],
            'timestamp' => now()->toDateTimeString()
        ];
    }
}
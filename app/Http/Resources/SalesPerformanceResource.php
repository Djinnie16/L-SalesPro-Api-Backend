<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Helpers\LeysHelpers;

class SalesPerformanceResource extends JsonResource
{
    public function toArray($request)
    {
        // Get the data array from the service
        $data = $this->resource['data'] ?? collect();
        
        return [
            'period' => $this->resource['period'] ?? null,
            'data' => $data->map(function ($item) {
                // $item is now an ARRAY, not an object
                return [
                    'period' => $item['period'] ?? null,
                    'order_count' => $item['order_count'] ?? 0,
                    'total_sales' => [
                        'amount' => $item['total_sales'] ?? 0,
                        'formatted' => LeysHelpers::formatCurrency($item['total_sales'] ?? 0)
                    ],
                    'average_order_value' => [
                        'amount' => $item['avg_order_value'] ?? 0, // Note: key is 'avg_order_value' not 'average_order_value'
                        'formatted' => LeysHelpers::formatCurrency($item['avg_order_value'] ?? 0)
                    ]
                ];
            }),
            'date_range' => [
                'start' => $this->resource['start_date'] ?? null,
                'end' => $this->resource['end_date'] ?? null
            ],
            'timestamp' => now()->toDateTimeString()
        ];
    }
}
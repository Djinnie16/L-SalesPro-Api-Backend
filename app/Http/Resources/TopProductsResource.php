<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TopProductsResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'products' => $this->map(function ($item) {
                return [
                    'product' => [
                        'id' => $item['product']->id ?? null,
                        'sku' => $item['product']->sku ?? null,
                        'name' => $item['product']->name ?? null,
                        'category' => $item['product']->category->name ?? null,
                        'price' => $item['product']->price ?? 0,
                        'formatted_price' => \App\Helpers\LeysHelpers::formatCurrency($item['product']->price ?? 0)
                    ],
                    'sales_data' => [
                        'total_sold' => $item['total_sold'] ?? 0,
                        'total_revenue' => $item['total_revenue'] ?? 0,
                        'formatted_revenue' => \App\Helpers\LeysHelpers::formatCurrency($item['total_revenue'] ?? 0),
                        'average_price' => $item['total_sold'] > 0 ? round($item['total_revenue'] / $item['total_sold'], 2) : 0
                    ]
                ];
            }),
            'metadata' => [
                'total_count' => $this->count(),
                'limit' => $request->query('limit', 5),
                'timestamp' => now()->toDateTimeString()
            ]
        ];
    }
}
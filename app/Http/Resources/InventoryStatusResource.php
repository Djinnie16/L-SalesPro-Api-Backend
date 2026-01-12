<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class InventoryStatusResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'categories' => $this->map(function ($category) {
                return [
                    'category_name' => $category->first()->category_name ?? 'Uncategorized',
                    'total_products' => $category->count(),
                    'total_quantity' => $category->sum('total_quantity'),
                    'total_value' => $category->sum('total_value'),
                    'formatted_total_value' => \App\Helpers\LeysHelpers::formatCurrency($category->sum('total_value')),
                    'items' => InventoryResource::collection($category->take(10)) // Limit items for response
                ];
            })->values(),
            'summary' => [
                'total_categories' => $this->count(),
                'total_products' => $this->sum(function ($category) {
                    return $category->count();
                }),
                'total_quantity' => $this->sum(function ($category) {
                    return $category->sum('total_quantity');
                }),
                'total_inventory_value' => $this->sum(function ($category) {
                    return $category->sum('total_value');
                })
            ]
        ];
    }
}
<?php

namespace App\Http\Resources;

use App\Helpers\LeysHelpers;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'sku' => $this->sku,
            'name' => $this->name,
            'category' => $this->category,
            'subcategory' => $this->subcategory,
            'description' => $this->description,
            'price' => (float) $this->price,
            'formatted_price' => LeyscoHelpers::formatCurrency($this->price),
            'cost_price' => $this->cost_price ? (float) $this->cost_price : null,
            'tax_rate' => $this->tax_rate ? (float) $this->tax_rate : null,
            'unit' => $this->unit,
            'packaging' => $this->packaging,
            'min_order_quantity' => $this->min_order_quantity,
            'reorder_level' => $this->reorder_level,
            'specifications' => $this->specifications ?? [],
            'is_active' => (bool) $this->is_active,
            'total_stock' => $this->total_stock ?? $this->inventories()->sum('quantity'),
            'available_stock' => $this->available_stock ?? $this->inventories()->sum('available_quantity'),
            'inventories' => InventoryResource::collection($this->whenLoaded('inventories')),
            'low_stock' => $this->available_stock <= $this->reorder_level,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->when($this->deleted_at, $this->deleted_at)
        ];
    }
}
<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class InventoryResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'warehouse_id' => $this->warehouse_id,
            'warehouse_name' => $this->warehouse->name ?? null,
            'warehouse_code' => $this->warehouse->code ?? null,
            'quantity' => $this->quantity,
            'reserved_quantity' => $this->reserved_quantity,
            'available_quantity' => $this->available_quantity,
            'average_cost' => $this->average_cost,
            'last_restocked_at' => $this->last_restocked_at,
            'is_low_stock' => $this->available_quantity <= ($this->reorder_level ?? 10),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
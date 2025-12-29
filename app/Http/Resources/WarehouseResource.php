<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class WarehouseResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'type' => $this->type,
            'address' => $this->address,
            'manager_email' => $this->manager_email,
            'phone' => $this->phone,
            'capacity' => $this->capacity,
            'used_capacity' => $this->used_capacity,
            'available_capacity' => $this->available_capacity,
            'utilization_rate' => $this->capacity > 0 
                ? round(($this->used_capacity / $this->capacity) * 100, 2) 
                : 0,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'is_active' => $this->is_active,
            'inventory_count' => $this->inventories_count ?? $this->inventories()->count(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
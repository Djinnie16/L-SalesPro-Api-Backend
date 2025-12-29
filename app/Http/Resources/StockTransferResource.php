<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StockTransferResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'transfer_number' => $this->transfer_number,
            'product' => new ProductResource($this->whenLoaded('product')),
            'from_warehouse' => new WarehouseResource($this->whenLoaded('fromWarehouse')),
            'to_warehouse' => new WarehouseResource($this->whenLoaded('toWarehouse')),
            'quantity' => $this->quantity,
            'status' => $this->status,
            'reason' => $this->reason,
            'notes' => $this->notes,
            'initiated_by' => $this->initiated_by,
            'approved_by' => $this->approved_by,
            'approved_at' => $this->approved_at,
            'completed_at' => $this->completed_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
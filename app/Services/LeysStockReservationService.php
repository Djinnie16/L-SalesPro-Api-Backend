<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Inventory;
use App\Models\StockReservation;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LeysStockReservationService
{
    public function reserveStockForOrder(int $orderId, array $items): array
    {
        $results = [];
        
        DB::transaction(function () use ($orderId, $items, &$results) {
            foreach ($items as $item) {
                $productId = $item['product_id'];
                $quantity = $item['quantity'];
                
                // Find warehouse with available stock
                $warehouse = $this->findWarehouseWithStock($productId, $quantity);
                
                if (!$warehouse) {
                    throw new \Exception("Insufficient stock for product ID: {$productId}");
                }
                
                // Reserve stock
                $reservation = StockReservation::create([
                    'order_id' => $orderId,
                    'product_id' => $productId,
                    'warehouse_id' => $warehouse->id,
                    'quantity' => $quantity,
                    'status' => 'reserved',
                    'expires_at' => Carbon::now()->addMinutes(30) // 30 min timeout
                ]);
                
                // Update inventory reserved quantity
                $inventory = Inventory::where([
                    'product_id' => $productId,
                    'warehouse_id' => $warehouse->id
                ])->first();
                
                $inventory->reserve($quantity);
                
                $results[] = [
                    'product_id' => $productId,
                    'warehouse_id' => $warehouse->id,
                    'quantity' => $quantity,
                    'reservation_id' => $reservation->id,
                    'expires_at' => $reservation->expires_at
                ];
            }
        });
        
        return $results;
    }
    
    public function releaseReservedStock(int $reservationId): bool
    {
        return DB::transaction(function () use ($reservationId) {
            $reservation = StockReservation::find($reservationId);
            
            if (!$reservation || $reservation->status !== 'reserved') {
                return false;
            }
            
            // Update inventory
            $inventory = Inventory::where([
                'product_id' => $reservation->product_id,
                'warehouse_id' => $reservation->warehouse_id
            ])->first();
            
            if ($inventory) {
                $inventory->release($reservation->quantity);
            }
            
            // Update reservation status
            $reservation->release();
            
            return true;
        });
    }
    
    public function consumeReservedStock(int $reservationId): bool
    {
        return DB::transaction(function () use ($reservationId) {
            $reservation = StockReservation::find($reservationId);
            
            if (!$reservation || $reservation->status !== 'reserved') {
                return false;
            }
            
            // Update inventory (deduct actual quantity)
            $inventory = Inventory::where([
                'product_id' => $reservation->product_id,
                'warehouse_id' => $reservation->warehouse_id
            ])->first();
            
            if ($inventory) {
                $inventory->decrement('quantity', $reservation->quantity);
                $inventory->decrement('reserved_quantity', $reservation->quantity);
            }
            
            // Update reservation status
            $reservation->consume();
            
            return true;
        });
    }
    
    public function checkAndReleaseExpiredReservations(): int
    {
        $expired = StockReservation::where('status', 'reserved')
            ->where('expires_at', '<=', Carbon::now())
            ->get();
        
        $releasedCount = 0;
        
        foreach ($expired as $reservation) {
            if ($this->releaseReservedStock($reservation->id)) {
                $releasedCount++;
            }
        }
        
        return $releasedCount;
    }
    
    private function findWarehouseWithStock(int $productId, int $quantity): ?Warehouse
    {
        $inventory = Inventory::where('product_id', $productId)
            ->where('available_quantity', '>=', $quantity)
            ->with('warehouse')
            ->orderBy('available_quantity', 'desc')
            ->first();
        
        return $inventory ? $inventory->warehouse : null;
    }
}
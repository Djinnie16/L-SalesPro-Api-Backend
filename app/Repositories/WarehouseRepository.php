<?php

namespace App\Repositories;

use App\Models\Warehouse;
use App\Models\Inventory;
use App\Models\StockTransfer;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class WarehouseRepository implements WarehouseRepositoryInterface
{
    public function getAllWarehouses(array $filters = []): LengthAwarePaginator
    {
        $query = Warehouse::with('inventories.product')
            ->withCount('inventories');

        // Apply filters
        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN));
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('code', 'LIKE', "%{$search}%")
                  ->orWhere('name', 'LIKE', "%{$search}%")
                  ->orWhere('address', 'LIKE', "%{$search}%");
            });
        }

        // Apply sorting
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        return $query->paginate($filters['per_page'] ?? 15);
    }

    public function getWarehouseById(int $warehouseId): ?Warehouse
    {
        return Warehouse::with([
            'inventories.product', 
            'incomingTransfers', 
            'outgoingTransfers'
        ])->find($warehouseId);
    }

    public function createWarehouse(array $warehouseDetails): Warehouse
    {
        return Warehouse::create($warehouseDetails);
    }

    public function updateWarehouse(int $warehouseId, array $newDetails): bool
    {
        $warehouse = Warehouse::find($warehouseId);
        
        if (!$warehouse) {
            return false;
        }

        return $warehouse->update($newDetails);
    }

    public function deleteWarehouse(int $warehouseId): bool
    {
        $warehouse = Warehouse::find($warehouseId);
        
        if (!$warehouse) {
            return false;
        }

        return $warehouse->delete();
    }

    public function getWarehouseInventory(int $warehouseId, array $filters = []): LengthAwarePaginator
    {
        $query = Inventory::with('product')
            ->where('warehouse_id', $warehouseId);

        // Apply filters
        if (isset($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }

        if (isset($filters['low_stock']) && $filters['low_stock']) {
            $query->whereRaw('(quantity - reserved_quantity) <= reorder_level');
        }

        if (isset($filters['out_of_stock']) && $filters['out_of_stock']) {
            $query->where('quantity', '<=', 0);
        }

        // Apply sorting
        $sortBy = $filters['sort_by'] ?? 'product_id';
        $sortOrder = $filters['sort_order'] ?? 'asc';
        $query->orderBy($sortBy, $sortOrder);

        return $query->paginate($filters['per_page'] ?? 20);
    }

    public function getWarehouseCapacityMetrics(int $warehouseId): array
    {
        $warehouse = Warehouse::with('inventories')->find($warehouseId);
        
        if (!$warehouse) {
            return [];
        }

        $totalQuantity = $warehouse->inventories->sum('quantity');
        $capacity = $warehouse->capacity;
        $utilizationRate = $capacity > 0 ? ($totalQuantity / $capacity) * 100 : 0;

        return [
            'total_capacity' => $capacity,
            'used_capacity' => $totalQuantity,
            'available_capacity' => max(0, $capacity - $totalQuantity),
            'utilization_rate' => round($utilizationRate, 2),
            'is_over_capacity' => $totalQuantity > $capacity
        ];
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Warehouse extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'type',
        'address',
        'manager_email',
        'phone',
        'capacity',
        'latitude',
        'longitude',
        'is_active'
    ];

    protected $casts = [
        'capacity' => 'integer',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'is_active' => 'boolean'
    ];

    /**
     * Relationship with inventory items
     */
    public function inventories()
    {
        return $this->hasMany(Inventory::class);
    }

    /**
     * Relationship with stock transfers (as source)
     */
    public function outgoingTransfers()
    {
        return $this->hasMany(StockTransfer::class, 'from_warehouse_id');
    }

    /**
     * Relationship with stock transfers (as destination)
     */
    public function incomingTransfers()
    {
        return $this->hasMany(StockTransfer::class, 'to_warehouse_id');
    }

    /**
     * Scope for active warehouses
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Calculate current used capacity
     */
    public function getUsedCapacityAttribute()
    {
        return $this->inventories()->sum('quantity');
    }

    /**
     * Calculate available capacity
     */
    public function getAvailableCapacityAttribute()
    {
        return max(0, $this->capacity - $this->used_capacity);
    }

    /**
     * Check if warehouse has capacity for additional stock
     */
    public function hasCapacity($quantity): bool
    {
        return $this->available_capacity >= $quantity;
    }

    /**
     * Get stock reservations for this warehouse
     */
    public function stockReservations()
    {
        return $this->hasMany(StockReservation::class);
    }
}
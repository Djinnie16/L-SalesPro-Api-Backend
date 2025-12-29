<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'sku',
        'name',
        'category_id',
        'subcategory_id',
        'description',
        'price',
        'cost_price',
        'tax_rate',
        'unit',
        'packaging',
        'min_order_quantity',
        'reorder_level',
        'is_active',
        'specifications',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'specifications' => 'array',
        'is_active' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function subcategory()
    {
        return $this->belongsTo(Category::class, 'subcategory_id');
    }

    public function inventory()
    {
        return $this->hasMany(Inventory::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function stockReservations()
    {
        return $this->hasMany(StockReservation::class);
    }

    public function getTotalStockAttribute()
    {
        return $this->inventory->sum('quantity');
    }

    public function getTotalReservedAttribute()
    {
        return $this->inventory->sum('reserved_quantity');
    }

    public function getTotalAvailableAttribute()
    {
        return $this->total_stock - $this->total_reserved;
    }

    public function isLowStock(): bool
    {
        return $this->total_available <= $this->reorder_level;
    }
}
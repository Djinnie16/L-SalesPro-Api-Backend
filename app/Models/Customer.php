<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'type',
        'category',
        'contact_person',
        'phone',
        'email',
        'tax_id',
        'payment_terms',
        'credit_limit',
        'current_balance',
        'latitude',
        'longitude',
        'address',
        'territory',
        'is_active',
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'is_active' => 'boolean',
    ];

    protected $appends = ['available_credit'];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function getAvailableCreditAttribute()
    {
        return $this->credit_limit - $this->current_balance;
    }

    public function canPlaceOrder(float $amount): bool
    {
        return $this->available_credit >= $amount;
    }

    public function updateBalance(float $amount): void
    {
        $this->increment('current_balance', $amount);
    }
}
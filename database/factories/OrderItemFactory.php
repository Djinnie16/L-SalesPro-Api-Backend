<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderItemFactory extends Factory
{
    public function definition(): array
    {
        $quantity = $this->faker->numberBetween(1, 20);
        $unitPrice = $this->faker->randomFloat(2, 10, 500);
        $taxRate = 16.0;
        $taxAmount = ($quantity * $unitPrice) * ($taxRate / 100);
        $totalPrice = ($quantity * $unitPrice) + $taxAmount;
        
        return [
            'order_id' => Order::factory(),
            'product_id' => Product::factory(),
            'warehouse_id' => Warehouse::factory(),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'tax_rate' => $taxRate,
            'tax_amount' => $taxAmount,
            'discount_amount' => 0,
            'discount_type' => null,
            'discount_value' => null,
            'total_price' => $totalPrice,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    public function definition(): array
    {
        $subtotal = $this->faker->randomFloat(2, 100, 5000);
        $taxAmount = $subtotal * 0.16;
        $shippingCost = $this->faker->randomFloat(2, 0, 100);
        $totalAmount = $subtotal + $taxAmount + $shippingCost;
        
        return [
            'order_number' => 'ORD-' . $this->faker->unique()->regexify('[A-Z0-9]{8}'),
            'customer_id' => Customer::factory(),
            'user_id' => User::factory(),
            'status' => $this->faker->randomElement(['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled']),
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'discount_amount' => 0,
            'shipping_cost' => $shippingCost,
            'total_amount' => $totalAmount,
            'discount_type' => null,
            'discount_value' => null,
            'notes' => $this->faker->optional()->sentence(),
            'confirmed_at' => null,
            'shipped_at' => null,
            'delivered_at' => null,
            'cancelled_at' => null,
            'cancelled_by' => null,
            'cancellation_reason' => null,
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'updated_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }

    public function delivered()
    {
        return $this->state([
            'status' => 'delivered',
            'delivered_at' => now(),
        ]);
    }
}
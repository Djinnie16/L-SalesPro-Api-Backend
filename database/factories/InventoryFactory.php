<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

class InventoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'warehouse_id' => Warehouse::factory(),
            'quantity' => $this->faker->numberBetween(0, 1000),
            'reserved_quantity' => $this->faker->numberBetween(0, 100),
            'min_stock_level' => $this->faker->numberBetween(10, 50),
            'max_stock_level' => $this->faker->numberBetween(500, 2000),
            'location' => $this->faker->optional()->regexify('[A-Z]{2}-\d{3}'),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
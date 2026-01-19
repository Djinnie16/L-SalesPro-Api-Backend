<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->words(2, true),
            'description' => $this->faker->optional()->paragraph(),
            'sku' => $this->faker->unique()->regexify('[A-Z0-9]{8}'),
            'category_id' => Category::factory(),
            'price' => $this->faker->randomFloat(2, 10, 1000),
            'cost_price' => $this->faker->randomFloat(2, 5, 500),
            'stock_quantity' => $this->faker->numberBetween(0, 1000),
            'min_stock_level' => $this->faker->numberBetween(10, 50),
            'unit' => $this->faker->randomElement(['piece', 'kg', 'liter', 'box']),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'sku' => $this->faker->unique()->regexify('[A-Z0-9]{8}'),
            'name' => $this->faker->unique()->words(2, true),
            'category_id' => Category::factory(),
            'subcategory_id' => null,
            'description' => $this->faker->optional()->paragraph(),
            'price' => $this->faker->randomFloat(2, 10, 1000),
            'cost_price' => $this->faker->randomFloat(2, 5, 500),
            'tax_rate' => 16.0,
            'unit' => $this->faker->randomElement(['piece', 'kg', 'liter', 'box']),
            'packaging' => $this->faker->randomElement(['box', 'carton', 'pack', 'bundle']),
            'min_order_quantity' => 1,
            'reorder_level' => 10,
            'is_active' => true,
            'specifications' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
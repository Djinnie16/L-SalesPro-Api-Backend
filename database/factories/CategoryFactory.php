<?php

namespace Database\Factories;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    public function definition(): array
    {
        $name = $this->faker->unique()->word();
        
        return [
            'name' => $name,
            'slug' => Str::slug($name) . '-' . Str::random(4),
            'type' => 'product',
            'description' => $this->faker->optional()->sentence(),
            'order' => $this->faker->numberBetween(0, 100),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class WarehouseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => $this->faker->unique()->regexify('[A-Z]{3}\d{3}'), // Unique code like ABC123
            'name' => $this->faker->unique()->company() . ' Warehouse',
            'type' => $this->faker->randomElement(['Main', 'Regional', 'Outlet']),
            'address' => $this->faker->address(),
            'manager_email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'capacity' => $this->faker->numberBetween(1000, 10000),
            'latitude' => $this->faker->latitude(),
            'longitude' => $this->faker->longitude(),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
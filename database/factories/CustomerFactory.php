<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Customer;

class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition()
    {
        return [
            'code' => $this->faker->unique()->bothify('CUST###'), // optional
            'name' => $this->faker->company(),
            'type' => $this->faker->randomElement(['Garage', 'Dealership', 'Individual', 'Corporate']),
            'category' => $this->faker->randomElement(['A+', 'A', 'B', 'C']),
            'contact_person' => $this->faker->name(),
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->unique()->safeEmail(),
            'tax_id' => $this->faker->optional()->bothify('P#########T'),
            'payment_terms' => $this->faker->numberBetween(0, 60),
            'credit_limit' => $this->faker->randomFloat(2, 0, 100000),
            'current_balance' => $this->faker->randomFloat(2, 0, 50000),
            'latitude' => $this->faker->optional()->latitude(),
            'longitude' => $this->faker->optional()->longitude(),
            'address' => $this->faker->address(),
            'territory' => $this->faker->optional()->city(),
            'is_active' => true,
        ];
    }
}

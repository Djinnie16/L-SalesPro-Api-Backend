<?php

namespace Database\Factories;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class NotificationFactory extends Factory
{
    protected $model = Notification::class;

    public function definition(): array
    {
        $types = [
            Notification::TYPE_ORDER_CONFIRMATION,
            Notification::TYPE_LOW_STOCK,
            Notification::TYPE_SYSTEM_ANNOUNCEMENT,
            Notification::TYPE_CREDIT_LIMIT_WARNING,
        ];

        return [
            'user_id' => User::factory(),
            'type' => $this->faker->randomElement($types),
            'title' => $this->faker->sentence,
            'message' => $this->faker->paragraph,
            'data' => ['sample_data' => $this->faker->word],
            'read_at' => $this->faker->optional()->dateTime(),
        ];
    }

    public function unread(): static
    {
        return $this->state(fn (array $attributes) => [
            'read_at' => null,
        ]);
    }

    public function read(): static
    {
        return $this->state(fn (array $attributes) => [
            'read_at' => now(),
        ]);
    }

    public function type(string $type): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => $type,
        ]);
    }
}
<?php

namespace Database\Factories;

use App\Enums\OrderStatusEnum;
use App\Enums\OrderTypeEnum;
use App\Models\Order;
use App\Models\Worker;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $createdAt = fake()->dateTimeBetween('-6 months', 'now');

        return [
            'worker_id' => Worker::factory(),
            'type' => fake()->randomElement(OrderTypeEnum::cases()),
            'status' => OrderStatusEnum::Pending,
            'amount' => fake()->randomFloat(2, 150, 5000),
            'due_at' => fake()->dateTimeInInterval($createdAt, '+14 days'),
            'completed_at' => null,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatusEnum::Completed,
            'completed_at' => fake()->dateTimeBetween($attributes['created_at'], 'now'),
        ]);
    }

    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatusEnum::InProgress,
            'completed_at' => null,
        ]);
    }

    public function delayed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatusEnum::Delayed,
            'due_at' => fake()->dateTimeBetween($attributes['created_at'], 'now'),
            'completed_at' => null,
        ]);
    }
}

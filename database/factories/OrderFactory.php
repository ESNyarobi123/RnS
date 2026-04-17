<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\Business;
use App\Models\Order;
use App\Models\User;
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
        $subtotal = fake()->randomFloat(2, 5000, 100000);

        return [
            'business_id' => Business::factory(),
            'worker_id' => null,
            'order_number' => null,
            'customer_name' => fake()->name(),
            'customer_phone' => fake()->phoneNumber(),
            'subtotal' => $subtotal,
            'tax' => 0,
            'total' => $subtotal,
            'status' => OrderStatus::Pending,
            'notes' => null,
            'completed_at' => null,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::Completed,
            'completed_at' => now(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::Cancelled,
        ]);
    }
}

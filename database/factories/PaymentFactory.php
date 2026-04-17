<?php

namespace Database\Factories;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Business;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'business_id' => Business::factory(),
            'order_id' => null,
            'amount' => fake()->randomFloat(2, 5000, 100000),
            'method' => fake()->randomElement(PaymentMethod::cases()),
            'status' => PaymentStatus::Completed,
            'reference' => fake()->uuid(),
            'paid_at' => now(),
            'provider' => null,
            'provider_order_id' => null,
            'customer_phone' => '255'.fake()->numerify('#########'),
            'customer_name' => fake()->name(),
            'metadata' => null,
        ];
    }
}

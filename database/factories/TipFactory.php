<?php

namespace Database\Factories;

use App\Models\Tip;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tip>
 */
class TipFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'business_id' => \App\Models\Business::factory(),
            'worker_id' => \App\Models\User::factory()->worker(),
            'payment_id' => null,
            'amount' => fake()->randomFloat(2, 1000, 50000),
            'customer_phone' => '255'.fake()->numerify('#########'),
            'customer_name' => fake()->name(),
            'source' => 'whatsapp',
        ];
    }
}

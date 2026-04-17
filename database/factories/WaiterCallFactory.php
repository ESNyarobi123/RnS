<?php

namespace Database\Factories;

use App\Models\WaiterCall;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WaiterCall>
 */
class WaiterCallFactory extends Factory
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
            'service_table_id' => null,
            'customer_phone' => '255'.fake()->numerify('#########'),
            'customer_name' => fake()->name(),
            'status' => 'pending',
        ];
    }
}

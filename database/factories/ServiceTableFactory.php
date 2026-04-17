<?php

namespace Database\Factories;

use App\Models\ServiceTable;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ServiceTable>
 */
class ServiceTableFactory extends Factory
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
            'name' => 'Table '.fake()->numberBetween(1, 50),
            'sort_order' => fake()->numberBetween(0, 20),
            'is_active' => true,
        ];
    }
}

<?php

namespace Database\Factories;

use App\Enums\StockStatus;
use App\Models\Business;
use App\Models\Stock;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Stock>
 */
class StockFactory extends Factory
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
            'name' => fake()->words(2, true),
            'description' => fake()->sentence(),
            'quantity' => fake()->numberBetween(0, 100),
            'unit_price' => fake()->randomFloat(2, 500, 10000),
            'reorder_level' => 5,
            'status' => StockStatus::InStock,
        ];
    }
}

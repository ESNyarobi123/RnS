<?php

namespace Database\Factories;

use App\Models\Business;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
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
            'category_id' => Category::factory(),
            'name' => fake()->words(2, true),
            'description' => fake()->sentence(),
            'price' => fake()->randomFloat(2, 1000, 50000),
            'image' => null,
            'duration_minutes' => null,
            'stock_quantity' => fake()->numberBetween(0, 100),
            'is_active' => true,
        ];
    }

    public function withDuration(): static
    {
        return $this->state(fn (array $attributes) => [
            'duration_minutes' => fake()->randomElement([15, 30, 45, 60, 90, 120]),
        ]);
    }
}

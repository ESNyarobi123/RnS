<?php

namespace Database\Factories;

use App\Enums\BusinessStatus;
use App\Enums\BusinessType;
use App\Models\Business;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Business>
 */
class BusinessFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->manager(),
            'name' => fake()->company(),
            'type' => fake()->randomElement(BusinessType::cases()),
            'description' => fake()->sentence(),
            'address' => fake()->address(),
            'phone' => fake()->phoneNumber(),
            'logo' => null,
            'status' => BusinessStatus::Active,
        ];
    }

    public function restaurant(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => BusinessType::Restaurant,
        ]);
    }

    public function salon(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => BusinessType::Salon,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => BusinessStatus::Inactive,
        ]);
    }
}

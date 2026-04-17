<?php

namespace Database\Factories;

use App\Enums\LinkType;
use App\Models\Business;
use App\Models\BusinessWorker;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BusinessWorker>
 */
class BusinessWorkerFactory extends Factory
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
            'worker_id' => User::factory()->worker(),
            'link_type' => LinkType::Permanent,
            'linked_at' => now(),
            'unlinked_at' => null,
            'expires_at' => null,
            'is_active' => true,
        ];
    }

    public function temporary(int $days = 30): static
    {
        return $this->state(fn (array $attributes) => [
            'link_type' => LinkType::Temporary,
            'expires_at' => now()->addDays($days),
        ]);
    }

    public function unlinked(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
            'unlinked_at' => now(),
        ]);
    }
}

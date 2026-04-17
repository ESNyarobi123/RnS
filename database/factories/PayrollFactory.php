<?php

namespace Database\Factories;

use App\Enums\PayrollStatus;
use App\Models\Business;
use App\Models\Payroll;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payroll>
 */
class PayrollFactory extends Factory
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
            'amount' => fake()->randomFloat(2, 50000, 500000),
            'period_start' => now()->startOfMonth(),
            'period_end' => now()->endOfMonth(),
            'status' => PayrollStatus::Pending,
            'paid_at' => null,
            'notes' => null,
        ];
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PayrollStatus::Paid,
            'paid_at' => now(),
        ]);
    }
}

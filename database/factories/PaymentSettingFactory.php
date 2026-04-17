<?php

namespace Database\Factories;

use App\Models\Business;
use App\Models\PaymentSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PaymentSetting>
 */
class PaymentSettingFactory extends Factory
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
            'provider' => fake()->randomElement(['mpesa', 'tigo_pesa', 'airtel_money', 'stripe']),
            'api_key' => fake()->uuid(),
            'api_secret' => fake()->uuid(),
            'config' => null,
            'is_active' => false,
        ];
    }
}

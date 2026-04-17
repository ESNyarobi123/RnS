<?php

namespace Database\Factories;

use App\Models\BotSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BotSetting>
 */
class BotSettingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'phone_number' => '255'.fake()->numerify('#########'),
            'secret_key' => fake()->regexify('[A-Za-z0-9]{64}'),
            'is_active' => true,
        ];
    }
}

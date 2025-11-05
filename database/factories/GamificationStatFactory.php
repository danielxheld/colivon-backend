<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GamificationStat>
 */
class GamificationStatFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'household_id' => \App\Models\Household::factory(),
            'total_xp' => fake()->numberBetween(0, 5000),
            'level' => fake()->numberBetween(1, 10),
            'current_streak' => fake()->numberBetween(0, 30),
            'longest_streak' => fake()->numberBetween(0, 50),
            'total_chores_completed' => fake()->numberBetween(0, 100),
            'current_month_xp' => fake()->numberBetween(0, 500),
            'current_month_chores' => fake()->numberBetween(0, 20),
            'title' => 'Neuling',
        ];
    }
}

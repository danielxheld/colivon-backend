<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Chore>
 */
class ChoreFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'household_id' => \App\Models\Household::factory(),
            'created_by' => \App\Models\User::factory(),
            'title' => fake()->sentence(3),
            'description' => fake()->sentence(),
            'recurrence_type' => fake()->randomElement(['daily', 'weekly', 'biweekly', 'monthly', 'once']),
            'difficulty_points' => fake()->numberBetween(1, 5),
            'estimated_duration' => fake()->numberBetween(10, 120),
            'requires_photo' => false,
            'is_active' => true,
            'assignment_mode' => 'manual',
        ];
    }
}

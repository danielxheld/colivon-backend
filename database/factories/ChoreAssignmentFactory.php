<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ChoreAssignment>
 */
class ChoreAssignmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'chore_id' => \App\Models\Chore::factory(),
            'user_id' => \App\Models\User::factory(),
            'assigned_at' => now(),
            'due_date' => fake()->dateTimeBetween('now', '+1 month'),
            'status' => 'pending',
            'assigned_by' => 'manual',
        ];
    }
}

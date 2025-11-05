<?php

namespace Database\Factories;

use App\Models\Household;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class HouseholdFactory extends Factory
{
    protected $model = Household::class;

    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true) . ' WG',
            'description' => fake()->sentence(),
            'invite_code' => strtoupper(Str::random(8)),
            'owner_id' => User::factory(),
        ];
    }
}

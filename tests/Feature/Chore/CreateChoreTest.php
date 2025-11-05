<?php

namespace Tests\Feature\Chore;

use App\Models\Household;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CreateChoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_chore()
    {
        $user = User::factory()->create();
        $household = Household::factory()->create(['owner_id' => $user->id]);
        $household->users()->attach($user->id, ['role' => 'owner']);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/chores', [
            'household_id' => $household->id,
            'title' => 'Clean bathroom',
            'description' => 'Clean the bathroom thoroughly',
            'recurrence_type' => 'weekly',
            'difficulty_points' => 3,
            'assignment_mode' => 'manual',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('chore.title', 'Clean bathroom')
            ->assertJsonPath('chore.recurrence_type', 'weekly');

        $this->assertDatabaseHas('chores', [
            'household_id' => $household->id,
            'title' => 'Clean bathroom',
            'created_by' => $user->id,
        ]);
    }

    public function test_non_member_cannot_create_chore()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $household = Household::factory()->create(['owner_id' => $otherUser->id]);
        $household->users()->attach($otherUser->id, ['role' => 'owner']);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/chores', [
            'household_id' => $household->id,
            'title' => 'Clean bathroom',
            'recurrence_type' => 'weekly',
        ]);

        $response->assertStatus(403);
    }

    public function test_chore_requires_valid_data()
    {
        $user = User::factory()->create();
        $household = Household::factory()->create(['owner_id' => $user->id]);
        $household->users()->attach($user->id, ['role' => 'owner']);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/chores', [
            'household_id' => $household->id,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'recurrence_type']);
    }
}

<?php

namespace Tests\Feature\Chore;

use App\Models\Chore;
use App\Models\Household;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AssignChoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_assign_chore()
    {
        $user = User::factory()->create();
        $member = User::factory()->create();
        $household = Household::factory()->create(['owner_id' => $user->id]);
        $household->users()->attach([$user->id => ['role' => 'owner'], $member->id => ['role' => 'member']]);

        $chore = Chore::factory()->create([
            'household_id' => $household->id,
            'created_by' => $user->id,
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/chores/{$chore->id}/assign", [
            'user_id' => $member->id,
            'due_date' => now()->addDays(7)->toDateString(),
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('assignment.chore_id', $chore->id)
            ->assertJsonPath('assignment.user_id', $member->id);

        $this->assertDatabaseHas('chore_assignments', [
            'chore_id' => $chore->id,
            'user_id' => $member->id,
            'status' => 'pending',
        ]);
    }

    public function test_can_fetch_my_assignments()
    {
        $user = User::factory()->create();
        $household = Household::factory()->create(['owner_id' => $user->id]);
        $household->users()->attach($user->id, ['role' => 'owner']);

        $chore = Chore::factory()->create([
            'household_id' => $household->id,
            'created_by' => $user->id,
        ]);

        $assignment = \App\Models\ChoreAssignment::factory()->create([
            'chore_id' => $chore->id,
            'user_id' => $user->id,
            'due_date' => now()->addDays(3),
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/chore-assignments/my');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }
}

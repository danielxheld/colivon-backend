<?php

namespace Tests\Feature\Chore;

use App\Models\Chore;
use App\Models\Household;
use App\Models\User;
use App\Models\UserChorePreference;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RouletteTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_run_roulette_for_household(): void
    {
        $user = User::factory()->create();
        $household = Household::factory()->create();
        $household->users()->attach($user->id, ['role' => 'admin']);

        // Create chores with different assignment modes
        $rouletteChore = Chore::factory()->create([
            'household_id' => $household->id,
            'assignment_mode' => 'roulette',
            'is_active' => true,
        ]);

        $autoChore = Chore::factory()->create([
            'household_id' => $household->id,
            'assignment_mode' => 'auto',
            'is_active' => true,
        ]);

        $manualChore = Chore::factory()->create([
            'household_id' => $household->id,
            'assignment_mode' => 'manual',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)
            ->postJson('/api/chore-assignments/roulette', [
                'household_id' => $household->id,
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'assignments' => [
                    '*' => [
                        'id',
                        'chore_id',
                        'user_id',
                        'due_date',
                        'status',
                        'assigned_by',
                    ],
                ],
                'message',
            ]);

        // Should create assignments for roulette and auto chores only
        $this->assertDatabaseHas('chore_assignments', [
            'chore_id' => $rouletteChore->id,
            'assigned_by' => 'roulette',
        ]);

        $this->assertDatabaseHas('chore_assignments', [
            'chore_id' => $autoChore->id,
            'assigned_by' => 'auto',
        ]);

        // Manual chore should not have assignment
        $this->assertDatabaseMissing('chore_assignments', [
            'chore_id' => $manualChore->id,
        ]);
    }

    public function test_roulette_respects_user_preferences(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $household = Household::factory()->create();
        $household->users()->attach([$user1->id, $user2->id], ['role' => 'member']);

        $chore = Chore::factory()->create([
            'household_id' => $household->id,
            'assignment_mode' => 'roulette',
            'is_active' => true,
        ]);

        // User1 loves this chore (higher weight)
        UserChorePreference::create([
            'user_id' => $user1->id,
            'chore_id' => $chore->id,
            'preference' => 'love',
            'weight' => 2.0,
        ]);

        // User2 hates this chore (lower weight)
        UserChorePreference::create([
            'user_id' => $user2->id,
            'chore_id' => $chore->id,
            'preference' => 'hate',
            'weight' => 0.1,
        ]);

        // Run roulette multiple times and check distribution
        $user1Count = 0;
        $user2Count = 0;
        $iterations = 20;

        for ($i = 0; $i < $iterations; $i++) {
            $response = $this->actingAs($user1)
                ->postJson('/api/chore-assignments/roulette', [
                    'household_id' => $household->id,
                ]);

            $response->assertStatus(200);

            $assignment = $chore->assignments()->latest()->first();
            if ($assignment->user_id === $user1->id) {
                $user1Count++;
            } else {
                $user2Count++;
            }

            // Delete the assignment for next iteration
            $assignment->delete();
        }

        // User1 should get significantly more assignments due to higher preference
        // With weights 2.0 vs 0.1, user1 should get ~95% of assignments
        $this->assertGreaterThan($user2Count, $user1Count);
    }

    public function test_roulette_does_not_create_duplicate_pending_assignments(): void
    {
        $user = User::factory()->create();
        $household = Household::factory()->create();
        $household->users()->attach($user->id, ['role' => 'admin']);

        $chore = Chore::factory()->create([
            'household_id' => $household->id,
            'assignment_mode' => 'roulette',
            'is_active' => true,
        ]);

        // Run roulette once
        $response1 = $this->actingAs($user)
            ->postJson('/api/chore-assignments/roulette', [
                'household_id' => $household->id,
            ]);

        $response1->assertStatus(200);

        // Should have one assignment
        $this->assertEquals(1, $chore->assignments()->count());

        // Run roulette again
        $response2 = $this->actingAs($user)
            ->postJson('/api/chore-assignments/roulette', [
                'household_id' => $household->id,
            ]);

        $response2->assertStatus(200);

        // Should still have only one assignment (no duplicate created)
        $this->assertEquals(1, $chore->assignments()->count());
    }

    public function test_roulette_only_assigns_active_chores(): void
    {
        $user = User::factory()->create();
        $household = Household::factory()->create();
        $household->users()->attach($user->id, ['role' => 'admin']);

        $activeChore = Chore::factory()->create([
            'household_id' => $household->id,
            'assignment_mode' => 'roulette',
            'is_active' => true,
        ]);

        $inactiveChore = Chore::factory()->create([
            'household_id' => $household->id,
            'assignment_mode' => 'roulette',
            'is_active' => false,
        ]);

        $response = $this->actingAs($user)
            ->postJson('/api/chore-assignments/roulette', [
                'household_id' => $household->id,
            ]);

        $response->assertStatus(200);

        // Should assign active chore
        $this->assertDatabaseHas('chore_assignments', [
            'chore_id' => $activeChore->id,
        ]);

        // Should not assign inactive chore
        $this->assertDatabaseMissing('chore_assignments', [
            'chore_id' => $inactiveChore->id,
        ]);
    }

    public function test_roulette_requires_household_id(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/chore-assignments/roulette', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['household_id']);
    }

    public function test_roulette_requires_user_to_be_member_of_household(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $household = Household::factory()->create();
        $household->users()->attach($otherUser->id, ['role' => 'admin']);

        $response = $this->actingAs($user)
            ->postJson('/api/chore-assignments/roulette', [
                'household_id' => $household->id,
            ]);

        $response->assertStatus(403);
    }
}

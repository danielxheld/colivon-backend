<?php

namespace Tests\Feature\Chore;

use App\Models\Chore;
use App\Models\ChoreAssignment;
use App\Models\Household;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CompleteChoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_complete_assignment()
    {
        $user = User::factory()->create();
        $household = Household::factory()->create(['owner_id' => $user->id]);
        $household->users()->attach($user->id, ['role' => 'owner']);

        $chore = Chore::factory()->create([
            'household_id' => $household->id,
            'created_by' => $user->id,
            'difficulty_points' => 3,
        ]);

        $assignment = ChoreAssignment::factory()->create([
            'chore_id' => $chore->id,
            'user_id' => $user->id,
            'due_date' => now()->addDays(3),
            'status' => 'pending',
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/chore-assignments/{$assignment->id}/complete", [
            'notes' => 'All clean!',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['completion', 'assignment']);

        $this->assertDatabaseHas('chore_assignments', [
            'id' => $assignment->id,
            'status' => 'completed',
        ]);

        $this->assertDatabaseHas('chore_completions', [
            'chore_assignment_id' => $assignment->id,
            'completed_by' => $user->id,
        ]);
    }

    public function test_completing_chore_awards_xp()
    {
        $user = User::factory()->create();
        $household = Household::factory()->create(['owner_id' => $user->id]);
        $household->users()->attach($user->id, ['role' => 'owner']);

        $chore = Chore::factory()->create([
            'household_id' => $household->id,
            'created_by' => $user->id,
            'difficulty_points' => 5,
        ]);

        $assignment = ChoreAssignment::factory()->create([
            'chore_id' => $chore->id,
            'user_id' => $user->id,
            'due_date' => now()->addDays(1),
        ]);

        Sanctum::actingAs($user);

        $this->postJson("/api/chore-assignments/{$assignment->id}/complete");

        $this->assertDatabaseHas('gamification_stats', [
            'user_id' => $user->id,
            'household_id' => $household->id,
        ]);

        $stats = $user->gamificationStats()->first();
        $this->assertGreaterThan(0, $stats->total_xp);
        $this->assertEquals(1, $stats->total_chores_completed);
    }
}

<?php

namespace Tests\Feature\Household;

use App\Models\Household;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DeleteHouseholdTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_delete_household(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $household = Household::factory()->create(['owner_id' => $user->id]);
        $household->users()->attach($user->id, ['role' => 'owner']);

        $response = $this->deleteJson("/api/households/{$household->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Household deleted successfully.',
            ]);

        $this->assertDatabaseMissing('households', [
            'id' => $household->id,
        ]);

        // Verify user relationship is also removed
        $this->assertDatabaseMissing('household_user', [
            'household_id' => $household->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_non_owner_cannot_delete_household(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        Sanctum::actingAs($member);

        $household = Household::factory()->create(['owner_id' => $owner->id]);
        $household->users()->attach($owner->id, ['role' => 'owner']);
        $household->users()->attach($member->id, ['role' => 'member']);

        $response = $this->deleteJson("/api/households/{$household->id}");

        $response->assertStatus(403);

        $this->assertDatabaseHas('households', [
            'id' => $household->id,
        ]);
    }

    public function test_unauthenticated_user_cannot_delete_household(): void
    {
        $user = User::factory()->create();
        $household = Household::factory()->create(['owner_id' => $user->id]);
        $household->users()->attach($user->id, ['role' => 'owner']);

        $response = $this->deleteJson("/api/households/{$household->id}");

        $response->assertStatus(401);
    }
}

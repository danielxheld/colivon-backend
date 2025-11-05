<?php

namespace Tests\Feature\Household;

use App\Models\Household;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class LeaveHouseholdTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_can_leave_household(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        Sanctum::actingAs($member);

        $household = Household::factory()->create(['owner_id' => $owner->id]);
        $household->users()->attach($owner->id, ['role' => 'owner']);
        $household->users()->attach($member->id, ['role' => 'member']);

        $response = $this->postJson("/api/households/{$household->id}/leave");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Successfully left household.',
            ]);

        $this->assertDatabaseMissing('household_user', [
            'household_id' => $household->id,
            'user_id' => $member->id,
        ]);
    }

    public function test_owner_cannot_leave_household(): void
    {
        $owner = User::factory()->create();
        Sanctum::actingAs($owner);

        $household = Household::factory()->create(['owner_id' => $owner->id]);
        $household->users()->attach($owner->id, ['role' => 'owner']);

        $response = $this->postJson("/api/households/{$household->id}/leave");

        $response->assertStatus(400)
            ->assertJson([
                'message' => 'Owner cannot leave the household. Transfer ownership or delete the household instead.',
            ]);

        $this->assertDatabaseHas('household_user', [
            'household_id' => $household->id,
            'user_id' => $owner->id,
        ]);
    }

    public function test_non_member_cannot_leave_household(): void
    {
        $owner = User::factory()->create();
        $nonMember = User::factory()->create();
        Sanctum::actingAs($nonMember);

        $household = Household::factory()->create(['owner_id' => $owner->id]);
        $household->users()->attach($owner->id, ['role' => 'owner']);

        $response = $this->postJson("/api/households/{$household->id}/leave");

        $response->assertStatus(400)
            ->assertJson([
                'message' => 'You are not a member of this household.',
            ]);
    }

    public function test_unauthenticated_user_cannot_leave_household(): void
    {
        $user = User::factory()->create();
        $household = Household::factory()->create(['owner_id' => $user->id]);
        $household->users()->attach($user->id, ['role' => 'owner']);

        $response = $this->postJson("/api/households/{$household->id}/leave");

        $response->assertStatus(401);
    }
}

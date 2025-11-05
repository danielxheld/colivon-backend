<?php

namespace Tests\Feature\Household;

use App\Models\Household;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class JoinHouseholdTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_join_household_with_valid_invite_code(): void
    {
        $owner = User::factory()->create();
        $newMember = User::factory()->create();
        Sanctum::actingAs($newMember);

        $household = Household::factory()->create([
            'owner_id' => $owner->id,
            'invite_code' => 'ABC123',
        ]);
        $household->users()->attach($owner->id, ['role' => 'owner']);

        $response = $this->postJson('/api/households/join', [
            'invite_code' => 'ABC123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'household' => ['id', 'name', 'invite_code', 'owner', 'members'],
                'message',
            ])
            ->assertJson([
                'message' => 'Successfully joined household.',
            ]);

        $this->assertDatabaseHas('household_user', [
            'household_id' => $household->id,
            'user_id' => $newMember->id,
            'role' => 'member',
        ]);
    }

    public function test_user_cannot_join_household_with_invalid_invite_code(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/households/join', [
            'invite_code' => 'INVALID',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['invite_code']);
    }

    public function test_user_cannot_join_same_household_twice(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        Sanctum::actingAs($member);

        $household = Household::factory()->create([
            'owner_id' => $owner->id,
            'invite_code' => 'ABC123',
        ]);
        $household->users()->attach($owner->id, ['role' => 'owner']);
        $household->users()->attach($member->id, ['role' => 'member']);

        $response = $this->postJson('/api/households/join', [
            'invite_code' => 'ABC123',
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'message' => 'You are already a member of this household.',
            ]);
    }

    public function test_unauthenticated_user_cannot_join_household(): void
    {
        $response = $this->postJson('/api/households/join', [
            'invite_code' => 'ABC123',
        ]);

        $response->assertStatus(401);
    }
}

<?php

namespace Tests\Feature\Household;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CreateHouseholdTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_household(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/households', [
            'name' => 'Test Household',
            'description' => 'A test household',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'household' => [
                    'id',
                    'name',
                    'description',
                    'invite_code',
                    'owner_id',
                    'owner',
                    'members',
                ],
                'message',
            ]);

        $this->assertDatabaseHas('households', [
            'name' => 'Test Household',
            'description' => 'A test household',
            'owner_id' => $user->id,
        ]);

        // Verify user is attached as owner
        $this->assertDatabaseHas('household_user', [
            'user_id' => $user->id,
            'role' => 'owner',
        ]);
    }

    public function test_household_has_unique_invite_code(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response1 = $this->postJson('/api/households', [
            'name' => 'Household 1',
        ]);

        $response2 = $this->postJson('/api/households', [
            'name' => 'Household 2',
        ]);

        $inviteCode1 = $response1->json('household.invite_code');
        $inviteCode2 = $response2->json('household.invite_code');

        $this->assertNotEquals($inviteCode1, $inviteCode2);
    }

    public function test_user_cannot_create_household_without_name(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/households', [
            'description' => 'A test household',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_unauthenticated_user_cannot_create_household(): void
    {
        $response = $this->postJson('/api/households', [
            'name' => 'Test Household',
        ]);

        $response->assertStatus(401);
    }
}

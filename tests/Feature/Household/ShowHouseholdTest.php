<?php

namespace Tests\Feature\Household;

use App\Models\Household;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ShowHouseholdTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_can_view_household(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $household = Household::factory()->create(['owner_id' => $user->id]);
        $household->users()->attach($user->id, ['role' => 'owner']);

        $response = $this->getJson("/api/households/{$household->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'description',
                    'invite_code',
                    'owner_id',
                    'owner',
                    'members',
                ],
            ])
            ->assertJson([
                'data' => [
                    'id' => $household->id,
                    'name' => $household->name,
                ],
            ]);
    }

    public function test_non_member_cannot_view_household(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $otherUser = User::factory()->create();
        $household = Household::factory()->create(['owner_id' => $otherUser->id]);
        $household->users()->attach($otherUser->id, ['role' => 'owner']);

        $response = $this->getJson("/api/households/{$household->id}");

        $response->assertStatus(403);
    }

    public function test_unauthenticated_user_cannot_view_household(): void
    {
        $user = User::factory()->create();
        $household = Household::factory()->create(['owner_id' => $user->id]);
        $household->users()->attach($user->id, ['role' => 'owner']);

        $response = $this->getJson("/api/households/{$household->id}");

        $response->assertStatus(401);
    }
}

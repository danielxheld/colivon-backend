<?php

namespace Tests\Feature\Household;

use App\Models\Household;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ListHouseholdsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_list_their_households(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Create households for the user
        $household1 = Household::factory()->create(['owner_id' => $user->id]);
        $household1->users()->attach($user->id, ['role' => 'owner']);

        $household2 = Household::factory()->create(['owner_id' => $user->id]);
        $household2->users()->attach($user->id, ['role' => 'member']);

        // Create household for another user (should not be in response)
        $otherUser = User::factory()->create();
        $otherHousehold = Household::factory()->create(['owner_id' => $otherUser->id]);
        $otherHousehold->users()->attach($otherUser->id, ['role' => 'owner']);

        $response = $this->getJson('/api/households');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'description',
                        'invite_code',
                        'owner_id',
                        'members_count',
                    ],
                ],
            ]);

        // Verify the correct households are returned
        $householdIds = collect($response->json('data'))->pluck('id')->toArray();
        $this->assertContains($household1->id, $householdIds);
        $this->assertContains($household2->id, $householdIds);
        $this->assertNotContains($otherHousehold->id, $householdIds);
    }

    public function test_unauthenticated_user_cannot_list_households(): void
    {
        $response = $this->getJson('/api/households');

        $response->assertStatus(401);
    }

    public function test_empty_household_list_returns_empty_array(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/households');

        $response->assertStatus(200)
            ->assertJsonCount(0, 'data');
    }
}

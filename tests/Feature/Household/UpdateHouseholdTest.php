<?php

namespace Tests\Feature\Household;

use App\Models\Household;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UpdateHouseholdTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_can_update_household(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $household = Household::factory()->create(['owner_id' => $user->id]);
        $household->users()->attach($user->id, ['role' => 'owner']);

        $response = $this->putJson("/api/households/{$household->id}", [
            'name' => 'Updated Household Name',
            'description' => 'Updated description',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'household' => [
                    'name' => 'Updated Household Name',
                    'description' => 'Updated description',
                ],
                'message' => 'Household updated successfully.',
            ]);

        $this->assertDatabaseHas('households', [
            'id' => $household->id,
            'name' => 'Updated Household Name',
            'description' => 'Updated description',
        ]);
    }

    public function test_non_member_cannot_update_household(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $otherUser = User::factory()->create();
        $household = Household::factory()->create(['owner_id' => $otherUser->id]);
        $household->users()->attach($otherUser->id, ['role' => 'owner']);

        $response = $this->putJson("/api/households/{$household->id}", [
            'name' => 'Updated Name',
        ]);

        $response->assertStatus(403);
    }

    public function test_cannot_update_household_without_name(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $household = Household::factory()->create(['owner_id' => $user->id]);
        $household->users()->attach($user->id, ['role' => 'owner']);

        $response = $this->putJson("/api/households/{$household->id}", [
            'description' => 'Updated description',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }
}

<?php

namespace Tests\Feature\ShoppingList;

use App\Models\Household;
use App\Models\ShoppingList;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ListShoppingListsTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_can_list_public_and_own_shopping_lists(): void
    {
        $user = User::factory()->create();
        $otherMember = User::factory()->create();
        Sanctum::actingAs($user);

        $household = Household::factory()->create(['owner_id' => $user->id]);
        $household->users()->attach($user->id, ['role' => 'owner']);
        $household->users()->attach($otherMember->id, ['role' => 'member']);

        // User's private list
        $myPrivateList = ShoppingList::factory()->private()->create([
            'household_id' => $household->id,
            'user_id' => $user->id,
        ]);

        // User's public list
        $myPublicList = ShoppingList::factory()->public()->create([
            'household_id' => $household->id,
            'user_id' => $user->id,
        ]);

        // Other member's public list
        $othersPublicList = ShoppingList::factory()->public()->create([
            'household_id' => $household->id,
            'user_id' => $otherMember->id,
        ]);

        // Other member's private list (should NOT be visible)
        $othersPrivateList = ShoppingList::factory()->private()->create([
            'household_id' => $household->id,
            'user_id' => $otherMember->id,
        ]);

        $response = $this->getJson('/api/shopping-lists?household_id=' . $household->id);

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data'); // Only 3 lists visible

        $listIds = collect($response->json('data'))->pluck('id')->toArray();
        $this->assertContains($myPrivateList->id, $listIds);
        $this->assertContains($myPublicList->id, $listIds);
        $this->assertContains($othersPublicList->id, $listIds);
        $this->assertNotContains($othersPrivateList->id, $listIds);
    }

    public function test_non_member_cannot_list_household_shopping_lists(): void
    {
        $user = User::factory()->create();
        $owner = User::factory()->create();
        Sanctum::actingAs($user);

        $household = Household::factory()->create(['owner_id' => $owner->id]);
        $household->users()->attach($owner->id, ['role' => 'owner']);

        $response = $this->getJson('/api/shopping-lists?household_id=' . $household->id);

        $response->assertStatus(403);
    }

    public function test_unauthenticated_user_cannot_list_shopping_lists(): void
    {
        $household = Household::factory()->create();

        $response = $this->getJson('/api/shopping-lists?household_id=' . $household->id);

        $response->assertStatus(401);
    }
}

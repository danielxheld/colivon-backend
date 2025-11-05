<?php

namespace Tests\Feature\ShoppingListItem;

use App\Models\Household;
use App\Models\ShoppingList;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AddItemTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_can_add_item_to_public_list(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $household = Household::factory()->create(['owner_id' => $user->id]);
        $household->users()->attach($user->id, ['role' => 'owner']);

        $list = ShoppingList::factory()->public()->create([
            'household_id' => $household->id,
            'user_id' => $user->id,
        ]);

        $response = $this->postJson("/api/shopping-lists/{$list->id}/items", [
            'name' => 'Milk',
            'quantity' => '2',
            'unit' => 'L',
            'category' => '🥛 Milchprodukte',
            'price' => 2.99,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'item' => [
                    'name' => 'Milk',
                    'quantity' => '2',
                    'unit' => 'L',
                ],
            ]);

        $this->assertDatabaseHas('shopping_list_items', [
            'shopping_list_id' => $list->id,
            'name' => 'Milk',
        ]);
    }

    public function test_member_cannot_add_item_to_private_list_of_other_user(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        Sanctum::actingAs($member);

        $household = Household::factory()->create(['owner_id' => $owner->id]);
        $household->users()->attach($owner->id, ['role' => 'owner']);
        $household->users()->attach($member->id, ['role' => 'member']);

        $list = ShoppingList::factory()->private()->create([
            'household_id' => $household->id,
            'user_id' => $owner->id,
        ]);

        $response = $this->postJson("/api/shopping-lists/{$list->id}/items", [
            'name' => 'Milk',
        ]);

        $response->assertStatus(403);
    }
}

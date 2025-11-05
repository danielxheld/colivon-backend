<?php

namespace Tests\Feature\ShoppingListItem;

use App\Models\Household;
use App\Models\ShoppingList;
use App\Models\ShoppingListItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ToggleItemTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_can_toggle_item_completion(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $household = Household::factory()->create(['owner_id' => $user->id]);
        $household->users()->attach($user->id, ['role' => 'owner']);

        $list = ShoppingList::factory()->public()->create([
            'household_id' => $household->id,
            'user_id' => $user->id,
        ]);

        $item = ShoppingListItem::factory()->create([
            'shopping_list_id' => $list->id,
            'is_completed' => false,
        ]);

        $response = $this->postJson("/api/shopping-list-items/{$item->id}/toggle");

        $response->assertStatus(200);

        $this->assertDatabaseHas('shopping_list_items', [
            'id' => $item->id,
            'is_completed' => true,
        ]);
    }

    public function test_completing_recurring_item_creates_new_item(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $household = Household::factory()->create(['owner_id' => $user->id]);
        $household->users()->attach($user->id, ['role' => 'owner']);

        $list = ShoppingList::factory()->public()->create([
            'household_id' => $household->id,
            'user_id' => $user->id,
        ]);

        $item = ShoppingListItem::factory()->recurring()->create([
            'shopping_list_id' => $list->id,
            'name' => 'Milk',
            'is_completed' => false,
        ]);

        $this->postJson("/api/shopping-list-items/{$item->id}/toggle");

        // Should have original item (completed) + new uncompleted item
        $this->assertDatabaseCount('shopping_list_items', 2);

        $this->assertDatabaseHas('shopping_list_items', [
            'id' => $item->id,
            'is_completed' => true,
        ]);

        $this->assertDatabaseHas('shopping_list_items', [
            'shopping_list_id' => $list->id,
            'name' => 'Milk',
            'is_completed' => false,
        ]);
    }
}

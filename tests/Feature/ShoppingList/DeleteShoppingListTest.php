<?php

namespace Tests\Feature\ShoppingList;

use App\Models\Household;
use App\Models\ShoppingList;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DeleteShoppingListTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_delete_shopping_list(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $household = Household::factory()->create(['owner_id' => $user->id]);
        $household->users()->attach($user->id, ['role' => 'owner']);

        $list = ShoppingList::factory()->create([
            'household_id' => $household->id,
            'user_id' => $user->id,
        ]);

        $response = $this->deleteJson("/api/shopping-lists/{$list->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('shopping_lists', [
            'id' => $list->id,
        ]);
    }

    public function test_non_owner_cannot_delete_shopping_list(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        Sanctum::actingAs($otherUser);

        $household = Household::factory()->create(['owner_id' => $owner->id]);
        $household->users()->attach($owner->id, ['role' => 'owner']);
        $household->users()->attach($otherUser->id, ['role' => 'member']);

        $list = ShoppingList::factory()->create([
            'household_id' => $household->id,
            'user_id' => $owner->id,
        ]);

        $response = $this->deleteJson("/api/shopping-lists/{$list->id}");

        $response->assertStatus(403);
    }
}

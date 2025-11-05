<?php

namespace Tests\Feature\ShoppingList;

use App\Models\Household;
use App\Models\ShoppingList;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UpdateShoppingListTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_update_shopping_list(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $household = Household::factory()->create(['owner_id' => $user->id]);
        $household->users()->attach($user->id, ['role' => 'owner']);

        $list = ShoppingList::factory()->create([
            'household_id' => $household->id,
            'user_id' => $user->id,
        ]);

        $response = $this->putJson("/api/shopping-lists/{$list->id}", [
            'name' => 'Updated List Name',
            'is_public' => false,
            'store' => 'Edeka',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'shopping_list' => [
                    'id' => $list->id,
                    'name' => 'Updated List Name',
                    'is_public' => false,
                    'store' => 'Edeka',
                ],
            ]);

        $this->assertDatabaseHas('shopping_lists', [
            'id' => $list->id,
            'name' => 'Updated List Name',
            'store' => 'Edeka',
        ]);
    }

    public function test_non_owner_cannot_update_shopping_list(): void
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

        $response = $this->putJson("/api/shopping-lists/{$list->id}", [
            'name' => 'Updated Name',
        ]);

        $response->assertStatus(403);
    }
}

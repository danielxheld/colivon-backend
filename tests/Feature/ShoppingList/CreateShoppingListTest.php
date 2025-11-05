<?php

namespace Tests\Feature\ShoppingList;

use App\Models\Household;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CreateShoppingListTest extends TestCase
{
    use RefreshDatabase;

    public function test_household_member_can_create_shopping_list(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $household = Household::factory()->create(['owner_id' => $user->id]);
        $household->users()->attach($user->id, ['role' => 'owner']);

        $response = $this->postJson('/api/shopping-lists', [
            'household_id' => $household->id,
            'name' => 'Weekly Groceries',
            'is_public' => true,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'shopping_list' => [
                    'id',
                    'household_id',
                    'user_id',
                    'name',
                    'is_public',
                    'items',
                ],
                'message',
            ])
            ->assertJson([
                'shopping_list' => [
                    'name' => 'Weekly Groceries',
                    'is_public' => true,
                    'household_id' => $household->id,
                    'user_id' => $user->id,
                ],
            ]);

        $this->assertDatabaseHas('shopping_lists', [
            'household_id' => $household->id,
            'user_id' => $user->id,
            'name' => 'Weekly Groceries',
            'is_public' => true,
        ]);
    }

    public function test_non_member_cannot_create_shopping_list_for_household(): void
    {
        $user = User::factory()->create();
        $owner = User::factory()->create();
        Sanctum::actingAs($user);

        $household = Household::factory()->create(['owner_id' => $owner->id]);
        $household->users()->attach($owner->id, ['role' => 'owner']);

        $response = $this->postJson('/api/shopping-lists', [
            'household_id' => $household->id,
            'name' => 'Weekly Groceries',
            'is_public' => true,
        ]);

        $response->assertStatus(403);
    }

    public function test_cannot_create_shopping_list_without_name(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $household = Household::factory()->create(['owner_id' => $user->id]);
        $household->users()->attach($user->id, ['role' => 'owner']);

        $response = $this->postJson('/api/shopping-lists', [
            'household_id' => $household->id,
            'is_public' => true,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_unauthenticated_user_cannot_create_shopping_list(): void
    {
        $household = Household::factory()->create();

        $response = $this->postJson('/api/shopping-lists', [
            'household_id' => $household->id,
            'name' => 'Weekly Groceries',
        ]);

        $response->assertStatus(401);
    }
}

<?php

namespace Tests\Feature\FavoriteItem;

use App\Models\FavoriteItem;
use App\Models\Household;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ManageFavoritesTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_can_list_household_favorites(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $household = Household::factory()->create(['owner_id' => $user->id]);
        $household->users()->attach($user->id, ['role' => 'owner']);

        FavoriteItem::factory()->count(5)->create([
            'household_id' => $household->id,
        ]);

        $response = $this->getJson('/api/favorite-items?household_id=' . $household->id);

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data');
    }

    public function test_member_can_add_favorite(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $household = Household::factory()->create(['owner_id' => $user->id]);
        $household->users()->attach($user->id, ['role' => 'owner']);

        $response = $this->postJson('/api/favorite-items', [
            'household_id' => $household->id,
            'name' => 'Milk',
            'category' => '🥛 Milchprodukte',
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('favorite_items', [
            'household_id' => $household->id,
            'name' => 'Milk',
            'usage_count' => 1,
        ]);
    }

    public function test_adding_existing_favorite_increments_usage_count(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $household = Household::factory()->create(['owner_id' => $user->id]);
        $household->users()->attach($user->id, ['role' => 'owner']);

        $favorite = FavoriteItem::factory()->create([
            'household_id' => $household->id,
            'name' => 'Milk',
            'usage_count' => 5,
        ]);

        $this->postJson('/api/favorite-items', [
            'household_id' => $household->id,
            'name' => 'Milk',
        ]);

        $this->assertDatabaseHas('favorite_items', [
            'id' => $favorite->id,
            'usage_count' => 6,
        ]);
    }

    public function test_member_can_delete_favorite(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $household = Household::factory()->create(['owner_id' => $user->id]);
        $household->users()->attach($user->id, ['role' => 'owner']);

        $favorite = FavoriteItem::factory()->create([
            'household_id' => $household->id,
        ]);

        $response = $this->deleteJson("/api/favorite-items/{$favorite->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('favorite_items', [
            'id' => $favorite->id,
        ]);
    }
}

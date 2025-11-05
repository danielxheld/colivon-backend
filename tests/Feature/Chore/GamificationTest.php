<?php

namespace Tests\Feature\Chore;

use App\Models\GamificationStat;
use App\Models\Household;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class GamificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_fetch_their_stats()
    {
        $user = User::factory()->create();
        $household = Household::factory()->create(['owner_id' => $user->id]);
        $household->users()->attach($user->id, ['role' => 'owner']);

        GamificationStat::factory()->create([
            'user_id' => $user->id,
            'household_id' => $household->id,
            'total_xp' => 500,
            'level' => 2,
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson("/api/gamification/my-stats?household_id={$household->id}");

        $response->assertStatus(200)
            ->assertJsonPath('stats.total_xp', 500)
            ->assertJsonPath('stats.level', 2)
            ->assertJsonStructure(['stats', 'rank', 'total_members']);
    }

    public function test_monthly_leaderboard_shows_users()
    {
        $household = Household::factory()->create();
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $household->users()->attach([$user1->id, $user2->id]);

        GamificationStat::factory()->create([
            'user_id' => $user1->id,
            'household_id' => $household->id,
            'current_month_xp' => 200,
        ]);

        GamificationStat::factory()->create([
            'user_id' => $user2->id,
            'household_id' => $household->id,
            'current_month_xp' => 150,
        ]);

        Sanctum::actingAs($user1);

        $response = $this->getJson("/api/gamification/leaderboard/monthly?household_id={$household->id}");

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');

        $this->assertEquals(200, $response->json('data.0.current_month_xp'));
        $this->assertEquals(150, $response->json('data.1.current_month_xp'));
    }
}

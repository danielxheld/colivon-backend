<?php

namespace App\Services;

use App\Models\GamificationStat;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class GamificationService
{
    /**
     * Get or create gamification stats for a user in a household.
     */
    public function getOrCreateStats(User $user, int $householdId): GamificationStat
    {
        return GamificationStat::firstOrCreate(
            [
                'user_id' => $user->id,
                'household_id' => $householdId,
            ],
            [
                'total_xp' => 0,
                'level' => 1,
                'current_streak' => 0,
                'longest_streak' => 0,
                'total_chores_completed' => 0,
                'current_month_xp' => 0,
                'current_month_chores' => 0,
                'title' => 'Neuling',
            ]
        );
    }

    /**
     * Record a chore completion and update stats.
     */
    public function recordChoreCompletion(User $user, int $householdId, int $xpEarned): GamificationStat
    {
        $stats = $this->getOrCreateStats($user, $householdId);

        // Add XP (this also updates level and title)
        $stats->addXp($xpEarned);

        // Update chore counters
        $stats->total_chores_completed++;
        $stats->current_month_chores++;

        // Update streak
        $this->updateStreak($stats, $user);

        $stats->save();

        return $stats->fresh();
    }

    /**
     * Update user's streak.
     */
    protected function updateStreak(GamificationStat $stats, User $user): void
    {
        // Get last completion date (excluding today)
        $lastCompletion = $user->choreCompletions()
            ->where('completed_at', '<', now()->startOfDay())
            ->latest('completed_at')
            ->first();

        if (!$lastCompletion) {
            // First ever completion
            $stats->current_streak = 1;
        } else {
            $daysSinceLastCompletion = $lastCompletion->completed_at->diffInDays(now(), false);

            if ($daysSinceLastCompletion === 1) {
                // Consecutive day - increment streak
                $stats->current_streak++;
            } elseif ($daysSinceLastCompletion > 1) {
                // Streak broken - reset to 1
                $stats->current_streak = 1;
            }
            // If same day, don't change streak
        }

        // Update longest streak if current is higher
        if ($stats->current_streak > $stats->longest_streak) {
            $stats->longest_streak = $stats->current_streak;
        }
    }

    /**
     * Get monthly leaderboard for a household.
     */
    public function getMonthlyLeaderboard(int $householdId, int $limit = 10): Collection
    {
        return GamificationStat::monthlyLeaderboard($householdId)
            ->with('user')
            ->limit($limit)
            ->get();
    }

    /**
     * Get all-time leaderboard for a household.
     */
    public function getAllTimeLeaderboard(int $householdId, int $limit = 10): Collection
    {
        return GamificationStat::allTimeLeaderboard($householdId)
            ->with('user')
            ->limit($limit)
            ->get();
    }

    /**
     * Reset monthly stats for all users (run at start of each month).
     */
    public function resetMonthlyStats(): int
    {
        return GamificationStat::query()->update([
            'current_month_xp' => 0,
            'current_month_chores' => 0,
        ]);
    }

    /**
     * Check and break streaks for users who haven't completed chores.
     */
    public function checkStreaks(): int
    {
        $yesterday = now()->subDay()->startOfDay();
        $twoDaysAgo = now()->subDays(2)->startOfDay();

        $stats = GamificationStat::where('current_streak', '>', 0)->get();

        $brokenCount = 0;

        foreach ($stats as $stat) {
            $user = $stat->user;

            // Check if user completed any chore yesterday
            $completedYesterday = $user->choreCompletions()
                ->whereBetween('completed_at', [$yesterday, now()->startOfDay()])
                ->exists();

            if (!$completedYesterday) {
                // Streak broken
                $stat->current_streak = 0;
                $stat->save();
                $brokenCount++;
            }
        }

        return $brokenCount;
    }

    /**
     * Get user rank in household.
     */
    public function getUserRank(User $user, int $householdId, string $period = 'monthly'): array
    {
        $stats = $this->getOrCreateStats($user, $householdId);

        if ($period === 'monthly') {
            $rank = GamificationStat::where('household_id', $householdId)
                ->where('current_month_xp', '>', $stats->current_month_xp)
                ->count() + 1;

            $total = GamificationStat::where('household_id', $householdId)->count();
        } else {
            $rank = GamificationStat::where('household_id', $householdId)
                ->where('total_xp', '>', $stats->total_xp)
                ->count() + 1;

            $total = GamificationStat::where('household_id', $householdId)->count();
        }

        return [
            'rank' => $rank,
            'total' => $total,
            'stats' => $stats,
        ];
    }
}

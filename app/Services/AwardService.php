<?php

namespace App\Services;

use App\Models\Award;
use App\Models\User;
use App\Models\UserAward;
use Illuminate\Support\Collection;

class AwardService
{
    /**
     * Check and grant awards for a user in a household.
     */
    public function checkAndGrantAwards(User $user, int $householdId): Collection
    {
        $awards = Award::all();
        $newlyEarned = collect();

        foreach ($awards as $award) {
            // Check if user already has this award
            $hasAward = UserAward::where('user_id', $user->id)
                ->where('household_id', $householdId)
                ->where('award_id', $award->id)
                ->exists();

            if ($hasAward) {
                continue;
            }

            // Check if criteria is met
            if ($award->checkCriteria($user, $householdId)) {
                $userAward = $this->grantAward($user, $householdId, $award);
                $newlyEarned->push($userAward);
            }
        }

        return $newlyEarned;
    }

    /**
     * Grant an award to a user.
     */
    public function grantAward(User $user, int $householdId, Award $award): UserAward
    {
        return UserAward::create([
            'user_id' => $user->id,
            'household_id' => $householdId,
            'award_id' => $award->id,
            'earned_at' => now(),
        ]);
    }

    /**
     * Get all awards earned by a user in a household.
     */
    public function getUserAwards(User $user, int $householdId): Collection
    {
        return UserAward::where('user_id', $user->id)
            ->where('household_id', $householdId)
            ->with('award')
            ->orderBy('earned_at', 'desc')
            ->get();
    }

    /**
     * Get all available awards.
     */
    public function getAllAwards(): Collection
    {
        return Award::orderBy('category')->orderBy('rarity')->get();
    }

    /**
     * Get award progress for a user.
     */
    public function getAwardProgress(User $user, int $householdId, Award $award): array
    {
        $criteria = $award->criteria;
        $progress = [];

        // Check chore completions
        if (isset($criteria['chore_completions'])) {
            $completions = $user->choreAssignments()
                ->whereHas('chore', function ($q) use ($householdId, $criteria) {
                    $q->where('household_id', $householdId);
                    if (isset($criteria['chore_category'])) {
                        $q->where('category', $criteria['chore_category']);
                    }
                })
                ->where('status', 'completed')
                ->count();

            $progress['chore_completions'] = [
                'current' => $completions,
                'required' => $criteria['chore_completions'],
                'percentage' => min(100, ($completions / $criteria['chore_completions']) * 100),
            ];
        }

        // Check streak
        if (isset($criteria['streak'])) {
            $stats = $user->gamificationStats()
                ->where('household_id', $householdId)
                ->first();

            $current = $stats ? $stats->current_streak : 0;
            $progress['streak'] = [
                'current' => $current,
                'required' => $criteria['streak'],
                'percentage' => min(100, ($current / $criteria['streak']) * 100),
            ];
        }

        // Check level
        if (isset($criteria['level'])) {
            $stats = $user->gamificationStats()
                ->where('household_id', $householdId)
                ->first();

            $current = $stats ? $stats->level : 1;
            $progress['level'] = [
                'current' => $current,
                'required' => $criteria['level'],
                'percentage' => min(100, ($current / $criteria['level']) * 100),
            ];
        }

        // Check total XP
        if (isset($criteria['total_xp'])) {
            $stats = $user->gamificationStats()
                ->where('household_id', $householdId)
                ->first();

            $current = $stats ? $stats->total_xp : 0;
            $progress['total_xp'] = [
                'current' => $current,
                'required' => $criteria['total_xp'],
                'percentage' => min(100, ($current / $criteria['total_xp']) * 100),
            ];
        }

        return $progress;
    }
}

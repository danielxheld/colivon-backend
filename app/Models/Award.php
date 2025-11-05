<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Award extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'name',
        'description',
        'icon',
        'category',
        'rarity',
        'criteria',
    ];

    protected $casts = [
        'criteria' => 'array',
    ];

    /**
     * Get all user awards for this achievement.
     */
    public function userAwards(): HasMany
    {
        return $this->hasMany(UserAward::class);
    }

    /**
     * Check if criteria is met for a user.
     */
    public function checkCriteria(User $user, int $householdId): bool
    {
        $criteria = $this->criteria;

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

            if ($completions < $criteria['chore_completions']) {
                return false;
            }
        }

        // Check streak
        if (isset($criteria['streak'])) {
            $stats = $user->gamificationStats()
                ->where('household_id', $householdId)
                ->first();

            if (!$stats || $stats->current_streak < $criteria['streak']) {
                return false;
            }
        }

        // Check level
        if (isset($criteria['level'])) {
            $stats = $user->gamificationStats()
                ->where('household_id', $householdId)
                ->first();

            if (!$stats || $stats->level < $criteria['level']) {
                return false;
            }
        }

        // Check total XP
        if (isset($criteria['total_xp'])) {
            $stats = $user->gamificationStats()
                ->where('household_id', $householdId)
                ->first();

            if (!$stats || $stats->total_xp < $criteria['total_xp']) {
                return false;
            }
        }

        return true;
    }
}

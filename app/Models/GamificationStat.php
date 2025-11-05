<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GamificationStat extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'household_id',
        'total_xp',
        'level',
        'current_streak',
        'longest_streak',
        'total_chores_completed',
        'current_month_xp',
        'current_month_chores',
        'title',
    ];

    protected $casts = [
        'total_xp' => 'integer',
        'level' => 'integer',
        'current_streak' => 'integer',
        'longest_streak' => 'integer',
        'total_chores_completed' => 'integer',
        'current_month_xp' => 'integer',
        'current_month_chores' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }

    /**
     * Calculate level from total XP.
     * Level formula: sqrt(XP / 100)
     */
    public function calculateLevel(): int
    {
        return max(1, (int) floor(sqrt($this->total_xp / 100)));
    }

    /**
     * Get XP needed for next level.
     */
    public function xpForNextLevel(): int
    {
        $nextLevel = $this->level + 1;
        return ($nextLevel ** 2) * 100;
    }

    /**
     * Get progress to next level as percentage.
     */
    public function levelProgress(): float
    {
        $currentLevelXp = ($this->level ** 2) * 100;
        $nextLevelXp = $this->xpForNextLevel();
        $xpInCurrentLevel = $this->total_xp - $currentLevelXp;
        $xpNeededForLevel = $nextLevelXp - $currentLevelXp;

        return min(100, ($xpInCurrentLevel / $xpNeededForLevel) * 100);
    }

    /**
     * Update title based on stats.
     */
    public function updateTitle(): void
    {
        $title = match (true) {
            $this->level >= 50 => 'WG-Legende',
            $this->level >= 30 => 'Sauberkeits-Meister',
            $this->level >= 20 => 'Aufgaben-Profi',
            $this->level >= 15 => 'Müll-Ninja',
            $this->level >= 10 => 'Sauberkeits-Held',
            $this->level >= 5 => 'Fleißiges Bienchen',
            $this->total_chores_completed >= 1 => 'Anfänger',
            default => 'Neuling',
        };

        // Special titles for streaks
        if ($this->current_streak >= 30) {
            $title = 'Streak-Champion';
        } elseif ($this->current_streak >= 14) {
            $title = 'Konsistenz-König';
        }

        $this->title = $title;
        $this->save();
    }

    /**
     * Add XP and recalculate level and title.
     */
    public function addXp(int $xp): void
    {
        $this->total_xp += $xp;
        $this->current_month_xp += $xp;
        $this->level = $this->calculateLevel();
        $this->updateTitle();
        $this->save();
    }

    /**
     * Scope for monthly leaderboard.
     */
    public function scopeMonthlyLeaderboard($query, int $householdId)
    {
        return $query->where('household_id', $householdId)
            ->orderByDesc('current_month_xp')
            ->orderByDesc('current_month_chores');
    }

    /**
     * Scope for all-time leaderboard.
     */
    public function scopeAllTimeLeaderboard($query, int $householdId)
    {
        return $query->where('household_id', $householdId)
            ->orderByDesc('total_xp')
            ->orderByDesc('level');
    }
}

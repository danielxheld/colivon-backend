<?php

namespace App\Services;

use App\Models\Chore;
use App\Models\ChoreAssignment;
use App\Models\ChoreCompletion;
use App\Models\Household;
use App\Models\User;
use App\Models\UserChorePreference;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ChoreAssignmentService
{
    public function __construct(
        protected GamificationService $gamificationService,
        protected AwardService $awardService
    ) {
    }

    /**
     * Get all assignments for a household.
     */
    public function getAssignmentsForHousehold(int $householdId, ?string $status = null): Collection
    {
        $query = ChoreAssignment::whereHas('chore', function ($q) use ($householdId) {
            $q->where('household_id', $householdId);
        })->with(['chore', 'user', 'completion']);

        if ($status) {
            $query->where('status', $status);
        }

        return $query->orderBy('due_date')->get();
    }

    /**
     * Get assignments for a user.
     */
    public function getAssignmentsForUser(User $user, ?string $status = null): Collection
    {
        $query = $user->choreAssignments()->with(['chore', 'completion']);

        if ($status) {
            $query->where('status', $status);
        }

        return $query->orderBy('due_date')->get();
    }

    /**
     * Manually assign a chore to a user.
     */
    public function assignChore(Chore $chore, User $user, Carbon $dueDate): ChoreAssignment
    {
        return ChoreAssignment::create([
            'chore_id' => $chore->id,
            'user_id' => $user->id,
            'due_date' => $dueDate,
            'status' => 'pending',
            'assigned_by' => 'manual',
        ]);
    }

    /**
     * Complete a chore assignment.
     */
    public function completeAssignment(
        ChoreAssignment $assignment,
        User $completedBy,
        ?string $photoPath = null,
        ?string $notes = null
    ): ChoreCompletion {
        return DB::transaction(function () use ($assignment, $completedBy, $photoPath, $notes) {
            // Calculate XP earned
            $xpEarned = $this->calculateXp($assignment);

            // Create completion record
            $completion = ChoreCompletion::create([
                'chore_assignment_id' => $assignment->id,
                'completed_by' => $completedBy->id,
                'completed_at' => now(),
                'photo_path' => $photoPath,
                'notes' => $notes,
                'xp_earned' => $xpEarned,
            ]);

            // Update assignment status
            $assignment->status = 'completed';
            $assignment->save();

            // Update gamification stats
            $this->gamificationService->recordChoreCompletion(
                $completedBy,
                $assignment->chore->household_id,
                $xpEarned
            );

            // Check and grant awards
            $this->awardService->checkAndGrantAwards($completedBy, $assignment->chore->household_id);

            // Create next assignment if recurring
            if ($assignment->chore->recurrence_type !== 'once') {
                $this->createNextAssignment($assignment);
            }

            return $completion->load('assignment.chore');
        });
    }

    /**
     * Calculate XP for completing a chore.
     */
    protected function calculateXp(ChoreAssignment $assignment): int
    {
        $baseXp = $assignment->chore->difficulty_points * 10;

        // Bonus for completing on time
        if (!$assignment->isOverdue()) {
            $baseXp += 5;
        }

        // Bonus for completing early
        $daysEarly = now()->diffInDays($assignment->due_date, false);
        if ($daysEarly > 0) {
            $baseXp += min($daysEarly * 2, 10);
        }

        return max($baseXp, 10); // Minimum 10 XP
    }

    /**
     * Create next assignment based on recurrence.
     */
    protected function createNextAssignment(ChoreAssignment $completedAssignment): ?ChoreAssignment
    {
        $chore = $completedAssignment->chore;

        if (!$chore->is_active) {
            return null;
        }

        $nextDueDate = $this->calculateNextDueDate($chore, $completedAssignment->due_date);

        // Assign based on assignment mode
        $nextUser = match ($chore->assignment_mode) {
            'auto' => $this->getNextUserRoundRobin($chore),
            'roulette' => $this->getNextUserRoulette($chore),
            default => $completedAssignment->user, // Same user if manual
        };

        if (!$nextUser) {
            return null;
        }

        return ChoreAssignment::create([
            'chore_id' => $chore->id,
            'user_id' => $nextUser->id,
            'due_date' => $nextDueDate,
            'status' => 'pending',
            'assigned_by' => $chore->assignment_mode,
        ]);
    }

    /**
     * Calculate next due date based on recurrence type.
     */
    protected function calculateNextDueDate(Chore $chore, Carbon $lastDueDate): Carbon
    {
        return match ($chore->recurrence_type) {
            'daily' => $lastDueDate->copy()->addDay(),
            'weekly' => $lastDueDate->copy()->addWeek(),
            'biweekly' => $lastDueDate->copy()->addWeeks(2),
            'monthly' => $lastDueDate->copy()->addMonth(),
            'custom' => $lastDueDate->copy()->addDays($chore->recurrence_interval),
            default => $lastDueDate->copy()->addWeek(),
        };
    }

    /**
     * Get next user in round-robin fashion.
     */
    protected function getNextUserRoundRobin(Chore $chore): ?User
    {
        $household = $chore->household;
        $lastAssignment = $chore->assignments()->latest('assigned_at')->first();

        if (!$lastAssignment) {
            return $household->users->first();
        }

        $users = $household->users;
        $lastUserIndex = $users->search(fn($u) => $u->id === $lastAssignment->user_id);

        $nextIndex = ($lastUserIndex + 1) % $users->count();
        return $users->get($nextIndex);
    }

    /**
     * Get next user using weighted roulette based on preferences.
     */
    protected function getNextUserRoulette(Chore $chore): ?User
    {
        $household = $chore->household;
        $users = $household->users;

        if ($users->isEmpty()) {
            return null;
        }

        // Get preferences for all users
        $weights = [];
        foreach ($users as $user) {
            $preference = UserChorePreference::where('user_id', $user->id)
                ->where('chore_id', $chore->id)
                ->first();

            $weight = $preference ? $preference->weight : 1.0;

            // Adjust weight based on recent assignments (fairness)
            $recentCount = ChoreAssignment::where('chore_id', $chore->id)
                ->where('user_id', $user->id)
                ->where('created_at', '>=', now()->subMonths(3))
                ->count();

            // Reduce weight if user has done this chore recently
            $weight *= max(0.5, 1 - ($recentCount * 0.1));

            $weights[$user->id] = max(0.1, $weight); // Minimum weight 0.1
        }

        // Weighted random selection
        $totalWeight = array_sum($weights);
        $random = mt_rand(0, (int)($totalWeight * 100)) / 100;

        $currentWeight = 0;
        foreach ($weights as $userId => $weight) {
            $currentWeight += $weight;
            if ($random <= $currentWeight) {
                return $users->firstWhere('id', $userId);
            }
        }

        return $users->random();
    }

    /**
     * Run roulette for all auto/roulette chores in a household.
     */
    public function runWeeklyRoulette(Household $household): SupportCollection
    {
        $chores = $household->chores()
            ->where('is_active', true)
            ->whereIn('assignment_mode', ['auto', 'roulette'])
            ->get();

        $newAssignments = collect();

        foreach ($chores as $chore) {
            // Check if there's already a pending assignment
            $existingAssignment = $chore->assignments()
                ->where('status', 'pending')
                ->where('due_date', '>=', now())
                ->first();

            if ($existingAssignment) {
                continue;
            }

            // Create new assignment for next week
            $nextUser = $chore->assignment_mode === 'roulette'
                ? $this->getNextUserRoulette($chore)
                : $this->getNextUserRoundRobin($chore);

            if ($nextUser) {
                $dueDate = $this->calculateNextDueDate($chore, now());

                $assignment = ChoreAssignment::create([
                    'chore_id' => $chore->id,
                    'user_id' => $nextUser->id,
                    'due_date' => $dueDate,
                    'status' => 'pending',
                    'assigned_by' => $chore->assignment_mode,
                ]);

                $newAssignments->push($assignment);
            }
        }

        return $newAssignments;
    }

    /**
     * Mark overdue assignments.
     */
    public function markOverdueAssignments(): int
    {
        return ChoreAssignment::where('status', 'pending')
            ->where('due_date', '<', now()->toDateString())
            ->update(['status' => 'overdue']);
    }

    /**
     * Update assignment status.
     */
    public function updateAssignmentStatus(ChoreAssignment $assignment, string $status): ChoreAssignment
    {
        $assignment->status = $status;
        $assignment->save();

        return $assignment->fresh();
    }

    /**
     * Delete an assignment.
     */
    public function deleteAssignment(ChoreAssignment $assignment): void
    {
        $assignment->delete();
    }

    /**
     * Store photo for chore completion.
     */
    public function storePhoto($photo): string
    {
        return $photo->store('chore-completions', 'public');
    }
}

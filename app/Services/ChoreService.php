<?php

namespace App\Services;

use App\Models\Chore;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class ChoreService
{
    /**
     * Get all chores for a household.
     */
    public function getChoresForHousehold(int $householdId): Collection
    {
        return Chore::where('household_id', $householdId)
            ->with(['creator', 'currentAssignment.user'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get active chores for a household.
     */
    public function getActiveChoresForHousehold(int $householdId): Collection
    {
        return Chore::where('household_id', $householdId)
            ->where('is_active', true)
            ->with(['creator', 'currentAssignment.user'])
            ->orderBy('title')
            ->get();
    }

    /**
     * Create a new chore.
     */
    public function createChore(array $data, User $user): Chore
    {
        $data['created_by'] = $user->id;

        return Chore::create($data);
    }

    /**
     * Update a chore.
     */
    public function updateChore(Chore $chore, array $data): Chore
    {
        $chore->update($data);

        return $chore->fresh(['creator', 'currentAssignment.user']);
    }

    /**
     * Delete a chore.
     */
    public function deleteChore(Chore $chore): void
    {
        $chore->delete();
    }

    /**
     * Toggle chore active status.
     */
    public function toggleActive(Chore $chore): Chore
    {
        $chore->is_active = !$chore->is_active;
        $chore->save();

        return $chore->fresh();
    }
}

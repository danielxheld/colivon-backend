<?php

namespace App\Services;

use App\Models\Household;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class HouseholdService
{
    /**
     * Get all households for a user with proper eager loading.
     */
    public function getHouseholdsForUser(User $user): Collection
    {
        return $user->households()->withCount('users')->with('users')->get();
    }

    /**
     * Create a new household and attach the user as owner.
     */
    public function createHousehold(array $data, User $user): Household
    {
        return DB::transaction(function () use ($data, $user) {
            $data['owner_id'] = $user->id;
            $household = Household::create($data);

            // Attach user as owner
            $household->users()->attach($user->id, ['role' => 'owner']);

            return $household->load(['owner', 'users']);
        });
    }

    /**
     * Update a household.
     */
    public function updateHousehold(Household $household, array $data): Household
    {
        DB::transaction(function () use ($household, $data) {
            $household->update($data);
        });

        return $household->fresh(['owner', 'users']);
    }

    /**
     * Delete a household.
     */
    public function deleteHousehold(Household $household): bool
    {
        return DB::transaction(function () use ($household) {
            // Detach all users
            $household->users()->detach();

            // Delete all shopping lists and their items (cascade)
            $household->shoppingLists()->delete();

            // Delete all favorite items
            $household->favoriteItems()->delete();

            return $household->delete();
        });
    }

    /**
     * Join a household by invite code.
     */
    public function joinHouseholdByCode(string $inviteCode, User $user): Household
    {
        return DB::transaction(function () use ($inviteCode, $user) {
            $household = Household::where('invite_code', $inviteCode)->firstOrFail();

            // Attach user if not already a member
            if (!$household->users->contains($user->id)) {
                $household->users()->attach($user->id, ['role' => 'member']);
            }

            return $household->load(['owner', 'users']);
        });
    }

    /**
     * Leave a household.
     */
    public function leaveHousehold(Household $household, User $user): void
    {
        DB::transaction(function () use ($household, $user) {
            $household->users()->detach($user->id);

            // If no users left, delete the household
            if ($household->users()->count() === 0) {
                $this->deleteHousehold($household);
            }
        });
    }
}

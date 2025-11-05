<?php

namespace App\Policies;

use App\Models\Household;
use App\Models\User;

class HouseholdPolicy
{
    /**
     * Determine if the user can view the household.
     */
    public function view(User $user, Household $household): bool
    {
        return $user->households->contains($household);
    }

    /**
     * Determine if the user can update the household.
     */
    public function update(User $user, Household $household): bool
    {
        return $user->households->contains($household);
    }

    /**
     * Determine if the user can delete the household.
     */
    public function delete(User $user, Household $household): bool
    {
        // Only allow deletion if user is member
        return $user->households->contains($household);
    }

    /**
     * Determine if the user can invite members to the household.
     */
    public function inviteMembers(User $user, Household $household): bool
    {
        return $user->households->contains($household);
    }
}

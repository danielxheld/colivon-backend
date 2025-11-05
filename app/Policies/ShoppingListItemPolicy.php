<?php

namespace App\Policies;

use App\Models\ShoppingListItem;
use App\Models\User;

class ShoppingListItemPolicy
{
    /**
     * Determine if the user can update the item.
     */
    public function update(User $user, ShoppingListItem $item): bool
    {
        $shoppingList = $item->shoppingList;

        // Owner can always update
        if ($shoppingList->user_id === $user->id) {
            return true;
        }

        // Household members can update items in public lists
        if ($shoppingList->is_public && $user->households->contains($shoppingList->household_id)) {
            return true;
        }

        return false;
    }

    /**
     * Determine if the user can delete the item.
     */
    public function delete(User $user, ShoppingListItem $item): bool
    {
        return $this->update($user, $item);
    }

    /**
     * Determine if the user can toggle the item completion status.
     */
    public function toggleComplete(User $user, ShoppingListItem $item): bool
    {
        return $this->update($user, $item);
    }
}

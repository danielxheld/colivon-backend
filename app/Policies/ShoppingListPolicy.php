<?php

namespace App\Policies;

use App\Models\ShoppingList;
use App\Models\User;

class ShoppingListPolicy
{
    /**
     * Determine if the user can view the shopping list.
     */
    public function view(User $user, ShoppingList $shoppingList): bool
    {
        // Owner can always view
        if ($shoppingList->user_id === $user->id) {
            return true;
        }

        // Household members can view public lists
        if ($shoppingList->is_public && $user->households->contains($shoppingList->household_id)) {
            return true;
        }

        return false;
    }

    /**
     * Determine if the user can update the shopping list.
     */
    public function update(User $user, ShoppingList $shoppingList): bool
    {
        return $shoppingList->user_id === $user->id;
    }

    /**
     * Determine if the user can delete the shopping list.
     */
    public function delete(User $user, ShoppingList $shoppingList): bool
    {
        return $shoppingList->user_id === $user->id;
    }

    /**
     * Determine if the user can add items to the shopping list.
     */
    public function addItems(User $user, ShoppingList $shoppingList): bool
    {
        // Owner can always add
        if ($shoppingList->user_id === $user->id) {
            return true;
        }

        // Household members can add to public lists
        if ($shoppingList->is_public && $user->households->contains($shoppingList->household_id)) {
            return true;
        }

        return false;
    }

    /**
     * Determine if the user can start shopping mode.
     */
    public function startShopping(User $user, ShoppingList $shoppingList): bool
    {
        return $this->view($user, $shoppingList);
    }
}

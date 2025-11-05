<?php

namespace App\Services;

use App\Models\ShoppingList;
use App\Models\ShoppingListItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ShoppingListService
{
    /**
     * Get all shopping lists for a household with proper eager loading.
     */
    public function getListsForHousehold(int $householdId): Collection
    {
        return ShoppingList::with(['user', 'currentlyShoppingBy', 'items'])
            ->where('household_id', $householdId)
            ->latest()
            ->get();
    }

    /**
     * Create a new shopping list.
     */
    public function createList(array $data, User $user): ShoppingList
    {
        return DB::transaction(function () use ($data, $user) {
            return $user->shoppingLists()->create($data);
        });
    }

    /**
     * Update a shopping list.
     */
    public function updateList(ShoppingList $shoppingList, array $data): ShoppingList
    {
        DB::transaction(function () use ($shoppingList, $data) {
            $shoppingList->update($data);
        });

        return $shoppingList->fresh(['user', 'currentlyShoppingBy', 'items']);
    }

    /**
     * Delete a shopping list.
     */
    public function deleteList(ShoppingList $shoppingList): bool
    {
        return DB::transaction(function () use ($shoppingList) {
            return $shoppingList->delete();
        });
    }

    /**
     * Add an item to a shopping list.
     */
    public function addItem(ShoppingList $shoppingList, array $data): ShoppingListItem
    {
        return DB::transaction(function () use ($shoppingList, $data) {
            return $shoppingList->items()->create($data);
        });
    }

    /**
     * Update a shopping list item.
     */
    public function updateItem(ShoppingListItem $item, array $data): ShoppingListItem
    {
        DB::transaction(function () use ($item, $data) {
            $item->update($data);
        });

        return $item->fresh();
    }

    /**
     * Toggle item completion status with recurring logic.
     */
    public function toggleItemComplete(ShoppingListItem $item): ShoppingListItem
    {
        return DB::transaction(function () use ($item) {
            $item->is_completed = !$item->is_completed;

            if ($item->is_completed) {
                $item->completed_at = now();

                // Handle recurring items
                if ($item->is_recurring && $item->recurrence_interval) {
                    $this->handleRecurringItem($item);
                }
            } else {
                $item->completed_at = null;
            }

            $item->save();
            return $item;
        });
    }

    /**
     * Delete a shopping list item.
     */
    public function deleteItem(ShoppingListItem $item): bool
    {
        return DB::transaction(function () use ($item) {
            return $item->delete();
        });
    }

    /**
     * Start shopping mode for a list.
     */
    public function startShopping(ShoppingList $shoppingList, User $user): ShoppingList
    {
        DB::transaction(function () use ($shoppingList, $user) {
            $shoppingList->update([
                'currently_shopping_by_id' => $user->id,
                'last_sync' => now(),
            ]);
        });

        return $shoppingList->fresh(['currentlyShoppingBy']);
    }

    /**
     * Stop shopping mode for a list.
     */
    public function stopShopping(ShoppingList $shoppingList): ShoppingList
    {
        DB::transaction(function () use ($shoppingList) {
            $shoppingList->update([
                'currently_shopping_by_id' => null,
            ]);
        });

        return $shoppingList->fresh();
    }

    /**
     * Handle recurring item logic when completed.
     */
    protected function handleRecurringItem(ShoppingListItem $item): void
    {
        // Create a new uncompleted copy of the item
        $newItem = $item->replicate();
        $newItem->is_completed = false;
        $newItem->completed_at = null;
        $newItem->save();
    }
}

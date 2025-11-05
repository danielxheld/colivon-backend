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
        return ShoppingList::with([
            'user',
            'currentlyShoppingBy',
            'items.claimedBy',
            'items.boughtBy'
        ])
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
                'shopping_started_at' => now(),
                'last_sync' => now(),
            ]);
        });

        return $shoppingList->fresh([
            'items.claimedBy',
            'items.boughtBy',
            'currentlyShoppingBy',
            'user'
        ]);
    }

    /**
     * Stop shopping mode for a list and calculate stats.
     */
    public function stopShopping(ShoppingList $shoppingList): ShoppingList
    {
        DB::transaction(function () use ($shoppingList) {
            // Calculate shopping stats
            $stats = $this->calculateShoppingStats($shoppingList);

            $shoppingList->update([
                'currently_shopping_by_id' => null,
                'shopping_started_at' => null,
                'shopping_stats' => $stats,
                'actual_total' => $stats['total_spent'] ?? null,
            ]);
        });

        return $shoppingList->fresh([
            'items.claimedBy',
            'items.boughtBy',
            'currentlyShoppingBy',
            'user'
        ]);
    }

    /**
     * Claim an item (user says "I'll buy this").
     */
    public function claimItem(ShoppingListItem $item, User $user): ShoppingListItem
    {
        $item->update(['claimed_by_id' => $user->id]);
        return $item->fresh(['claimedBy']);
    }

    /**
     * Unclaim an item.
     */
    public function unclaimItem(ShoppingListItem $item): ShoppingListItem
    {
        $item->update(['claimed_by_id' => null]);
        return $item->fresh();
    }

    /**
     * Mark item as bought with actual price.
     */
    public function markAsBought(ShoppingListItem $item, User $user, ?float $actualPrice = null): ShoppingListItem
    {
        $item->update([
            'is_completed' => true,
            'completed_at' => now(),
            'bought_by_id' => $user->id,
            'actual_price' => $actualPrice ?? $item->price,
        ]);

        // Handle recurring items
        if ($item->is_recurring && $item->recurrence_interval) {
            $this->handleRecurringItem($item);
            $item->save();
        }

        return $item->fresh(['boughtBy']);
    }

    /**
     * Calculate shopping statistics.
     */
    protected function calculateShoppingStats(ShoppingList $shoppingList): array
    {
        $items = $shoppingList->items()->where('is_completed', true)->get();

        $stats = [
            'total_items' => $items->count(),
            'total_spent' => $items->sum('actual_price'),
            'estimated_total' => $items->sum('price'),
            'shoppers' => [],
            'completed_at' => now()->toISOString(),
        ];

        // Calculate per-shopper stats
        $shopperStats = $items->groupBy('bought_by_id')->map(function ($userItems) {
            return [
                'items_count' => $userItems->count(),
                'total_spent' => $userItems->sum('actual_price'),
            ];
        });

        $stats['shoppers'] = $shopperStats->toArray();

        return $stats;
    }

    /**
     * Handle recurring item logic when completed.
     */
    protected function handleRecurringItem(ShoppingListItem $item): void
    {
        // Calculate next recurrence date based on interval
        $nextDate = $this->calculateNextRecurrenceDate($item->recurrence_interval);
        $item->next_recurrence_date = $nextDate;
    }

    /**
     * Calculate next recurrence date based on interval.
     */
    protected function calculateNextRecurrenceDate(string $interval): \Carbon\Carbon
    {
        return match ($interval) {
            'daily' => now()->addDay(),
            'weekly' => now()->addWeek(),
            'monthly' => now()->addMonth(),
            default => now()->addWeek(), // Default to weekly
        };
    }

    /**
     * Reactivate items that are due for recurrence.
     */
    public function reactivateRecurringItems(): int
    {
        $count = 0;

        $items = ShoppingListItem::where('is_completed', true)
            ->where('is_recurring', true)
            ->whereNotNull('next_recurrence_date')
            ->whereDate('next_recurrence_date', '<=', now())
            ->get();

        foreach ($items as $item) {
            $item->update([
                'is_completed' => false,
                'completed_at' => null,
                'next_recurrence_date' => null,
            ]);
            $count++;
        }

        return $count;
    }
}

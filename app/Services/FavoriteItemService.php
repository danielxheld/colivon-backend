<?php

namespace App\Services;

use App\Models\FavoriteItem;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class FavoriteItemService
{
    /**
     * Get favorite items for a household, ordered by usage count.
     */
    public function getFavoritesForHousehold(int $householdId, int $limit = 10): Collection
    {
        return FavoriteItem::where('household_id', $householdId)
            ->orderByDesc('usage_count')
            ->limit($limit)
            ->get();
    }

    /**
     * Add or increment a favorite item.
     */
    public function addOrIncrementFavorite(array $data): FavoriteItem
    {
        return DB::transaction(function () use ($data) {
            $favorite = FavoriteItem::where('household_id', $data['household_id'])
                ->where('name', $data['name'])
                ->first();

            if ($favorite) {
                $favorite->increment('usage_count');

                // Update other fields if provided
                if (isset($data['category'])) {
                    $favorite->category = $data['category'];
                }
                if (isset($data['quantity'])) {
                    $favorite->quantity = $data['quantity'];
                }
                if (isset($data['unit'])) {
                    $favorite->unit = $data['unit'];
                }

                $favorite->save();
            } else {
                $favorite = FavoriteItem::create($data);
            }

            return $favorite;
        });
    }

    /**
     * Delete a favorite item.
     */
    public function deleteFavorite(FavoriteItem $favorite): bool
    {
        return DB::transaction(function () use ($favorite) {
            return $favorite->delete();
        });
    }

    /**
     * Reset usage count for a favorite.
     */
    public function resetUsageCount(FavoriteItem $favorite): FavoriteItem
    {
        DB::transaction(function () use ($favorite) {
            $favorite->update(['usage_count' => 0]);
        });

        return $favorite->fresh();
    }
}

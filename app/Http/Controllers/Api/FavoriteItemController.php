<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFavoriteItemRequest;
use App\Http\Resources\FavoriteItemResource;
use App\Models\FavoriteItem;
use App\Services\FavoriteItemService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class FavoriteItemController extends Controller
{
    public function __construct(
        protected FavoriteItemService $favoriteItemService
    ) {
    }

    /**
     * Get favorite items for a household.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $request->validate([
            'household_id' => 'required|exists:households,id',
        ]);

        // Verify user is member of household
        abort_unless(
            $request->user()->households->contains($request->household_id),
            403,
            'You are not a member of this household.'
        );

        $favorites = $this->favoriteItemService->getFavoritesForHousehold(
            $request->household_id,
            $request->query('limit', 10)
        );

        return FavoriteItemResource::collection($favorites);
    }

    /**
     * Add or increment a favorite item.
     */
    public function store(StoreFavoriteItemRequest $request): JsonResponse
    {
        $favorite = $this->favoriteItemService->addOrIncrementFavorite(
            $request->validated()
        );

        return response()->json([
            'favorite' => new FavoriteItemResource($favorite),
            'message' => 'Favorite updated successfully.',
        ], 201);
    }

    /**
     * Delete a favorite item.
     */
    public function destroy(Request $request, FavoriteItem $favorite): JsonResponse
    {
        // Verify user is member of household
        abort_unless(
            $request->user()->households->contains($favorite->household_id),
            403,
            'Unauthorized.'
        );

        $this->favoriteItemService->deleteFavorite($favorite);

        return response()->json([
            'message' => 'Favorite deleted successfully.',
        ]);
    }
}

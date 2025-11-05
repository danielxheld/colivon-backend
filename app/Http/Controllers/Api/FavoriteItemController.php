<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FavoriteItem;
use Illuminate\Http\Request;

class FavoriteItemController extends Controller
{
    public function index(Request $request)
    {
        $householdId = $request->query('household_id');

        $favorites = FavoriteItem::where('household_id', $householdId)
            ->orderBy('usage_count', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'favorites' => $favorites,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'household_id' => 'required|exists:households,id',
            'name' => 'required|string',
            'category' => 'nullable|string',
            'quantity' => 'nullable|string',
            'unit' => 'nullable|string',
        ]);

        // Check if favorite already exists
        $favorite = FavoriteItem::where('household_id', $validated['household_id'])
            ->where('name', $validated['name'])
            ->first();

        if ($favorite) {
            // Increment usage count
            $favorite->increment('usage_count');
        } else {
            // Create new favorite
            $favorite = FavoriteItem::create($validated);
        }

        return response()->json([
            'favorite' => $favorite,
        ], 201);
    }

    public function destroy(FavoriteItem $favorite)
    {
        $favorite->delete();

        return response()->json([
            'message' => 'Favorite deleted successfully',
        ]);
    }
}

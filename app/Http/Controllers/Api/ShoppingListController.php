<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ShoppingList;
use App\Models\ShoppingListItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShoppingListController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'household_id' => 'required|exists:households,id',
        ]);

        $householdId = $request->household_id;

        // Check if user is member of household
        if (!$request->user()->households->contains($householdId)) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        // Get public lists + user's private lists
        $lists = ShoppingList::where('household_id', $householdId)
            ->where(function ($query) use ($request) {
                $query->where('is_public', true)
                    ->orWhere('user_id', $request->user()->id);
            })
            ->with(['items' => function ($query) {
                $query->orderBy('is_completed')->orderBy('created_at', 'desc');
            }, 'user'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'shopping_lists' => $lists,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'household_id' => 'required|exists:households,id',
            'name' => 'required|string|max:255',
            'is_public' => 'boolean',
        ]);

        // Check if user is member of household
        if (!$request->user()->households->contains($request->household_id)) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        $list = ShoppingList::create([
            'household_id' => $request->household_id,
            'user_id' => $request->user()->id,
            'name' => $request->name,
            'is_public' => $request->is_public ?? true,
        ]);

        return response()->json([
            'shopping_list' => $list->load('items', 'user'),
            'message' => 'Shopping list created successfully',
        ], 201);
    }

    public function show(Request $request, ShoppingList $shoppingList)
    {
        // Check if user can access this list
        if (!$request->user()->households->contains($shoppingList->household_id)) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        // Check if list is private and user is not owner
        if (!$shoppingList->is_public && $shoppingList->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'This list is private',
            ], 403);
        }

        return response()->json([
            'shopping_list' => $shoppingList->load(['items' => function ($query) {
                $query->orderBy('is_completed')->orderBy('created_at', 'desc');
            }, 'user']),
        ]);
    }

    public function update(Request $request, ShoppingList $shoppingList)
    {
        // Only owner can update
        if ($shoppingList->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Only the owner can update this list',
            ], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'is_public' => 'boolean',
            'store' => 'nullable|string|max:100',
            'is_template' => 'boolean',
            'template_name' => 'nullable|string|max:255',
            'estimated_total' => 'nullable|numeric|min:0',
            'actual_total' => 'nullable|numeric|min:0',
        ]);

        $shoppingList->update($request->only(['name', 'is_public', 'store', 'is_template', 'template_name', 'estimated_total', 'actual_total']));

        return response()->json([
            'shopping_list' => $shoppingList->load('items', 'user'),
            'message' => 'Shopping list updated successfully',
        ]);
    }

    public function destroy(Request $request, ShoppingList $shoppingList)
    {
        // Only owner can delete
        if ($shoppingList->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Only the owner can delete this list',
            ], 403);
        }

        $shoppingList->delete();

        return response()->json([
            'message' => 'Shopping list deleted successfully',
        ]);
    }

    // Item methods
    public function addItem(Request $request, ShoppingList $shoppingList)
    {
        // Check if user can access this list
        if (!$request->user()->households->contains($shoppingList->household_id)) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        if (!$shoppingList->is_public && $shoppingList->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Cannot add items to private list',
            ], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'quantity' => 'nullable|string|max:50',
            'unit' => 'nullable|string|max:50',
            'category' => 'nullable|string|max:100',
            'note' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'aisle_order' => 'nullable|integer|min:0',
            'is_recurring' => 'boolean',
            'recurrence_interval' => 'nullable|in:daily,weekly,monthly',
        ]);

        $item = $shoppingList->items()->create([
            'name' => $request->name,
            'quantity' => $request->quantity,
            'unit' => $request->unit,
            'category' => $request->category,
            'note' => $request->note,
            'price' => $request->price,
            'aisle_order' => $request->aisle_order,
            'is_recurring' => $request->is_recurring ?? false,
            'recurrence_interval' => $request->recurrence_interval,
        ]);

        return response()->json([
            'item' => $item,
            'message' => 'Item added successfully',
        ], 201);
    }

    public function updateItem(Request $request, ShoppingListItem $item)
    {
        $shoppingList = $item->shoppingList;

        // Check if user can access this list
        if (!$request->user()->households->contains($shoppingList->household_id)) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        if (!$shoppingList->is_public && $shoppingList->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Cannot update items in private list',
            ], 403);
        }

        $request->validate([
            'name' => 'string|max:255',
            'quantity' => 'nullable|string|max:50',
            'unit' => 'nullable|string|max:50',
            'category' => 'nullable|string|max:100',
            'note' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'aisle_order' => 'nullable|integer|min:0',
            'is_recurring' => 'boolean',
            'recurrence_interval' => 'nullable|in:daily,weekly,monthly',
        ]);

        $item->update($request->only(['name', 'quantity', 'unit', 'category', 'note', 'price', 'aisle_order', 'is_recurring', 'recurrence_interval']));

        return response()->json([
            'item' => $item,
            'message' => 'Item updated successfully',
        ]);
    }

    public function toggleItemComplete(Request $request, ShoppingListItem $item)
    {
        $shoppingList = $item->shoppingList;

        // Check if user can access this list
        if (!$request->user()->households->contains($shoppingList->household_id)) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        if (!$shoppingList->is_public && $shoppingList->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Cannot update items in private list',
            ], 403);
        }

        DB::beginTransaction();

        try {
            if ($item->is_completed) {
                // Uncomplete the item
                $item->update([
                    'is_completed' => false,
                    'completed_at' => null,
                ]);
            } else {
                // Complete the item
                $item->update([
                    'is_completed' => true,
                    'completed_at' => now(),
                ]);

                // If recurring, create new item
                if ($item->is_recurring) {
                    $shoppingList->items()->create([
                        'name' => $item->name,
                        'quantity' => $item->quantity,
                        'unit' => $item->unit,
                        'is_recurring' => true,
                        'recurrence_interval' => $item->recurrence_interval,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'item' => $item->fresh(),
                'message' => 'Item status updated',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to update item',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function startShopping(Request $request, ShoppingList $shoppingList)
    {
        // Check if user can access this list
        if (!$request->user()->households->contains($shoppingList->household_id)) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        $shoppingList->update([
            'currently_shopping_by_id' => $request->user()->id,
            'last_sync' => now(),
        ]);

        return response()->json([
            'shopping_list' => $shoppingList->load('currentlyShoppingBy'),
            'message' => 'Shopping started',
        ]);
    }

    public function stopShopping(Request $request, ShoppingList $shoppingList)
    {
        // Check if user can access this list
        if (!$request->user()->households->contains($shoppingList->household_id)) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        $shoppingList->update([
            'currently_shopping_by_id' => null,
            'last_sync' => now(),
        ]);

        return response()->json([
            'shopping_list' => $shoppingList,
            'message' => 'Shopping stopped',
        ]);
    }

    public function deleteItem(Request $request, ShoppingListItem $item)
    {
        $shoppingList = $item->shoppingList;

        // Check if user can access this list
        if (!$request->user()->households->contains($shoppingList->household_id)) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        if (!$shoppingList->is_public && $shoppingList->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Cannot delete items from private list',
            ], 403);
        }

        $item->delete();

        return response()->json([
            'message' => 'Item deleted successfully',
        ]);
    }
}

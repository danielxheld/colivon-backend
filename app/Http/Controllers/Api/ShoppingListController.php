<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreShoppingListRequest;
use App\Http\Requests\UpdateShoppingListRequest;
use App\Http\Requests\StoreShoppingListItemRequest;
use App\Http\Requests\UpdateShoppingListItemRequest;
use App\Http\Resources\ShoppingListResource;
use App\Http\Resources\ShoppingListItemResource;
use App\Models\ShoppingList;
use App\Models\ShoppingListItem;
use App\Services\ShoppingListService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ShoppingListController extends Controller
{
    public function __construct(
        protected ShoppingListService $shoppingListService
    ) {
    }

    /**
     * Get all shopping lists for a household.
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

        $lists = $this->shoppingListService->getListsForHousehold($request->household_id);

        // Filter to only show public lists + user's private lists
        $lists = $lists->filter(function ($list) use ($request) {
            return $list->is_public || $list->user_id === $request->user()->id;
        });

        return ShoppingListResource::collection($lists);
    }

    /**
     * Create a new shopping list.
     */
    public function store(StoreShoppingListRequest $request): JsonResponse
    {
        $list = $this->shoppingListService->createList(
            $request->validated(),
            $request->user()
        );

        return response()->json([
            'shopping_list' => new ShoppingListResource($list->load(['items', 'user'])),
            'message' => 'Shopping list created successfully.',
        ], 201);
    }

    /**
     * Get a single shopping list.
     */
    public function show(ShoppingList $shoppingList): ShoppingListResource
    {
        return new ShoppingListResource(
            $shoppingList->load(['items', 'user', 'currentlyShoppingBy'])
        );
    }

    /**
     * Update a shopping list.
     */
    public function update(UpdateShoppingListRequest $request, ShoppingList $shoppingList): JsonResponse
    {
        $list = $this->shoppingListService->updateList(
            $shoppingList,
            $request->validated()
        );

        return response()->json([
            'shopping_list' => new ShoppingListResource($list),
            'message' => 'Shopping list updated successfully.',
        ]);
    }

    /**
     * Delete a shopping list.
     */
    public function destroy(Request $request, ShoppingList $shoppingList): JsonResponse
    {
        // Only owner can delete
        abort_unless(
            $shoppingList->user_id === $request->user()->id,
            403,
            'Only the owner can delete this list.'
        );

        $this->shoppingListService->deleteList($shoppingList);

        return response()->json([
            'message' => 'Shopping list deleted successfully.',
        ]);
    }

    /**
     * Add an item to a shopping list.
     */
    public function addItem(StoreShoppingListItemRequest $request, ShoppingList $shoppingList): JsonResponse
    {
        $item = $this->shoppingListService->addItem(
            $shoppingList,
            $request->validated()
        );

        return response()->json([
            'item' => new ShoppingListItemResource($item),
            'message' => 'Item added successfully.',
        ], 201);
    }

    /**
     * Update a shopping list item.
     */
    public function updateItem(UpdateShoppingListItemRequest $request, ShoppingListItem $item): JsonResponse
    {
        $item = $this->shoppingListService->updateItem(
            $item,
            $request->validated()
        );

        return response()->json([
            'item' => new ShoppingListItemResource($item),
            'message' => 'Item updated successfully.',
        ]);
    }

    /**
     * Toggle item completion status.
     */
    public function toggleItemComplete(Request $request, ShoppingListItem $item): JsonResponse
    {
        $shoppingList = $item->shoppingList;

        // Check if user can access this list
        abort_unless(
            $request->user()->households->contains($shoppingList->household_id),
            403,
            'Unauthorized.'
        );

        if (!$shoppingList->is_public && $shoppingList->user_id !== $request->user()->id) {
            abort(403, 'Cannot update items in private list.');
        }

        $item = $this->shoppingListService->toggleItemComplete($item);

        return response()->json([
            'item' => new ShoppingListItemResource($item),
            'message' => 'Item status updated.',
        ]);
    }

    /**
     * Delete a shopping list item.
     */
    public function deleteItem(Request $request, ShoppingListItem $item): JsonResponse
    {
        $shoppingList = $item->shoppingList;

        // Check if user can access this list
        abort_unless(
            $request->user()->households->contains($shoppingList->household_id),
            403,
            'Unauthorized.'
        );

        if (!$shoppingList->is_public && $shoppingList->user_id !== $request->user()->id) {
            abort(403, 'Cannot delete items from private list.');
        }

        $this->shoppingListService->deleteItem($item);

        return response()->json([
            'message' => 'Item deleted successfully.',
        ]);
    }

    /**
     * Start shopping mode.
     */
    public function startShopping(Request $request, ShoppingList $shoppingList): JsonResponse
    {
        // Check if user can access this list
        abort_unless(
            $request->user()->households->contains($shoppingList->household_id),
            403,
            'Unauthorized.'
        );

        $list = $this->shoppingListService->startShopping($shoppingList, $request->user());

        return response()->json([
            'shopping_list' => new ShoppingListResource($list),
            'message' => 'Shopping mode started.',
        ]);
    }

    /**
     * Stop shopping mode.
     */
    public function stopShopping(Request $request, ShoppingList $shoppingList): JsonResponse
    {
        // Check if user can access this list
        abort_unless(
            $request->user()->households->contains($shoppingList->household_id),
            403,
            'Unauthorized.'
        );

        $list = $this->shoppingListService->stopShopping($shoppingList);

        return response()->json([
            'shopping_list' => new ShoppingListResource($list),
            'message' => 'Shopping mode stopped.',
        ]);
    }

    /**
     * Claim an item (user says "I'll buy this").
     */
    public function claimItem(Request $request, ShoppingListItem $item): JsonResponse
    {
        $shoppingList = $item->shoppingList;

        abort_unless(
            $request->user()->households->contains($shoppingList->household_id),
            403,
            'Unauthorized.'
        );

        $item = $this->shoppingListService->claimItem($item, $request->user());

        return response()->json([
            'item' => new ShoppingListItemResource($item),
            'message' => 'Item claimed successfully.',
        ]);
    }

    /**
     * Unclaim an item.
     */
    public function unclaimItem(Request $request, ShoppingListItem $item): JsonResponse
    {
        $shoppingList = $item->shoppingList;

        abort_unless(
            $request->user()->households->contains($shoppingList->household_id),
            403,
            'Unauthorized.'
        );

        $item = $this->shoppingListService->unclaimItem($item);

        return response()->json([
            'item' => new ShoppingListItemResource($item),
            'message' => 'Item unclaimed successfully.',
        ]);
    }

    /**
     * Mark item as bought with actual price.
     */
    public function markAsBought(Request $request, ShoppingListItem $item): JsonResponse
    {
        $request->validate([
            'actual_price' => 'nullable|numeric|min:0',
        ]);

        $shoppingList = $item->shoppingList;

        abort_unless(
            $request->user()->households->contains($shoppingList->household_id),
            403,
            'Unauthorized.'
        );

        $item = $this->shoppingListService->markAsBought(
            $item,
            $request->user(),
            $request->actual_price
        );

        return response()->json([
            'item' => new ShoppingListItemResource($item),
            'message' => 'Item marked as bought.',
        ]);
    }

    /**
     * Get expense breakdown for a list.
     */
    public function getExpenses(Request $request, ShoppingList $shoppingList): JsonResponse
    {
        abort_unless(
            $request->user()->households->contains($shoppingList->household_id),
            403,
            'Unauthorized.'
        );

        $completedItems = $shoppingList->items()
            ->where('is_completed', true)
            ->with(['boughtBy'])
            ->get();

        $expenses = [
            'total_spent' => $completedItems->sum('actual_price'),
            'total_items' => $completedItems->count(),
            'by_person' => [],
            'shared_cost_total' => $completedItems->where('shared_cost', true)->sum('actual_price'),
            'personal_cost_total' => $completedItems->where('shared_cost', false)->sum('actual_price'),
        ];

        // Group by buyer (only items with a buyer)
        $byPerson = $completedItems
            ->filter(fn($item) => $item->bought_by_id !== null)
            ->groupBy('bought_by_id')
            ->map(function ($items, $userId) {
                $user = $items->first()->boughtBy;
                return [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                    ],
                    'total_spent' => $items->sum('actual_price'),
                    'shared_items_total' => $items->where('shared_cost', true)->sum('actual_price'),
                    'personal_items_total' => $items->where('shared_cost', false)->sum('actual_price'),
                    'items_count' => $items->count(),
                ];
            })->values();

        $expenses['by_person'] = $byPerson;

        // Calculate what each person owes
        $memberCount = $shoppingList->household->members->count();
        $sharedPerPerson = $expenses['shared_cost_total'] / max($memberCount, 1);

        $expenses['split_calculation'] = $byPerson->map(function ($person) use ($sharedPerPerson) {
            $paidShared = $person['shared_items_total'];
            $owes = $sharedPerPerson - $paidShared;

            return [
                'user' => $person['user'],
                'paid' => $person['total_spent'],
                'should_pay' => $sharedPerPerson + $person['personal_items_total'],
                'balance' => $owes * -1, // negative means they're owed money
            ];
        });

        return response()->json($expenses);
    }
}

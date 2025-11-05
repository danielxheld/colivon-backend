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
        $this->authorizeResource(ShoppingList::class);
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
    public function destroy(ShoppingList $shoppingList): JsonResponse
    {
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
        $this->authorize('update', $item);

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
        $this->authorize('toggleComplete', $item);

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
        $this->authorize('delete', $item);

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
        $this->authorize('startShopping', $shoppingList);

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
        $this->authorize('startShopping', $shoppingList);

        $list = $this->shoppingListService->stopShopping($shoppingList);

        return response()->json([
            'shopping_list' => new ShoppingListResource($list),
            'message' => 'Shopping mode stopped.',
        ]);
    }
}

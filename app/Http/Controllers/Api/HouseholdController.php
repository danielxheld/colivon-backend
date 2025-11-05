<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreHouseholdRequest;
use App\Http\Requests\UpdateHouseholdRequest;
use App\Http\Requests\JoinHouseholdRequest;
use App\Http\Resources\HouseholdResource;
use App\Models\Household;
use App\Services\HouseholdService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class HouseholdController extends Controller
{
    public function __construct(
        protected HouseholdService $householdService
    ) {
    }

    /**
     * Get all households for the authenticated user.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $households = $this->householdService->getHouseholdsForUser($request->user());

        return HouseholdResource::collection($households);
    }

    /**
     * Create a new household.
     */
    public function store(StoreHouseholdRequest $request): JsonResponse
    {
        $household = $this->householdService->createHousehold(
            $request->validated(),
            $request->user()
        );

        return response()->json([
            'household' => new HouseholdResource($household),
            'message' => 'Household created successfully.',
        ], 201);
    }

    /**
     * Get a single household.
     */
    public function show(Request $request, Household $household): HouseholdResource
    {
        // Check if user is member
        abort_unless(
            $request->user()->households->contains($household),
            403,
            'You are not a member of this household.'
        );

        return new HouseholdResource($household->load(['owner', 'users']));
    }

    /**
     * Update a household.
     */
    public function update(UpdateHouseholdRequest $request, Household $household): JsonResponse
    {
        $household = $this->householdService->updateHousehold(
            $household,
            $request->validated()
        );

        return response()->json([
            'household' => new HouseholdResource($household),
            'message' => 'Household updated successfully.',
        ]);
    }

    /**
     * Delete a household.
     */
    public function destroy(Household $household): JsonResponse
    {
        // Check if user is owner
        abort_unless(
            $household->owner_id === auth()->id(),
            403,
            'Only the owner can delete the household.'
        );

        $this->householdService->deleteHousehold($household);

        return response()->json([
            'message' => 'Household deleted successfully.',
        ]);
    }

    /**
     * Join a household by invite code.
     */
    public function join(JoinHouseholdRequest $request): JsonResponse
    {
        $user = $request->user();

        // Check if already a member
        $household = Household::where('invite_code', $request->invite_code)->first();

        if ($user->households->contains($household->id)) {
            return response()->json([
                'message' => 'You are already a member of this household.',
            ], 400);
        }

        $household = $this->householdService->joinHouseholdByCode(
            $request->invite_code,
            $user
        );

        return response()->json([
            'household' => new HouseholdResource($household),
            'message' => 'Successfully joined household.',
        ]);
    }

    /**
     * Leave a household.
     */
    public function leave(Request $request, Household $household): JsonResponse
    {
        $user = $request->user();

        // Check if user is member
        abort_unless(
            $user->households->contains($household->id),
            400,
            'You are not a member of this household.'
        );

        // Owner cannot leave
        abort_if(
            $household->owner_id === $user->id,
            400,
            'Owner cannot leave the household. Transfer ownership or delete the household instead.'
        );

        $this->householdService->leaveHousehold($household, $user);

        return response()->json([
            'message' => 'Successfully left household.',
        ]);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreChoreRequest;
use App\Http\Requests\UpdateChoreRequest;
use App\Http\Resources\ChoreResource;
use App\Models\Chore;
use App\Services\ChoreService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ChoreController extends Controller
{
    public function __construct(
        protected ChoreService $choreService
    ) {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $request->validate(['household_id' => 'required|exists:households,id']);
        abort_unless($request->user()->households->contains($request->household_id), 403);

        $chores = $this->choreService->getChoresForHousehold($request->household_id);
        return ChoreResource::collection($chores);
    }

    public function store(StoreChoreRequest $request): JsonResponse
    {
        $chore = $this->choreService->createChore($request->validated(), $request->user());
        return response()->json([
            'chore' => new ChoreResource($chore->load('creator')),
            'message' => 'Chore created successfully.',
        ], 201);
    }

    public function show(Request $request, Chore $chore): ChoreResource
    {
        abort_unless($request->user()->households->contains($chore->household_id), 403);
        return new ChoreResource($chore->load(['creator', 'currentAssignment.user']));
    }

    public function update(UpdateChoreRequest $request, Chore $chore): JsonResponse
    {
        $chore = $this->choreService->updateChore($chore, $request->validated());
        return response()->json([
            'chore' => new ChoreResource($chore),
            'message' => 'Chore updated successfully.',
        ]);
    }

    public function destroy(Request $request, Chore $chore): JsonResponse
    {
        abort_unless($request->user()->households->contains($chore->household_id), 403);
        $this->choreService->deleteChore($chore);
        return response()->json(['message' => 'Chore deleted successfully.']);
    }
}

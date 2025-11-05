<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompleteChoreRequest;
use App\Http\Requests\StoreChoreAssignmentRequest;
use App\Http\Requests\UpdateChorePreferenceRequest;
use App\Http\Resources\ChoreAssignmentResource;
use App\Http\Resources\ChoreCompletionResource;
use App\Http\Resources\UserChorePreferenceResource;
use App\Models\Chore;
use App\Models\ChoreAssignment;
use App\Models\UserChorePreference;
use App\Services\ChoreAssignmentService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ChoreAssignmentController extends Controller
{
    public function __construct(
        protected ChoreAssignmentService $assignmentService
    ) {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $request->validate(['household_id' => 'required|exists:households,id']);
        abort_unless($request->user()->households->contains($request->household_id), 403);

        $assignments = $this->assignmentService->getAssignmentsForHousehold(
            $request->household_id,
            $request->query('status')
        );

        return ChoreAssignmentResource::collection($assignments);
    }

    public function myAssignments(Request $request): AnonymousResourceCollection
    {
        $assignments = $this->assignmentService->getAssignmentsForUser(
            $request->user(),
            $request->query('status')
        );

        return ChoreAssignmentResource::collection($assignments);
    }

    public function assign(StoreChoreAssignmentRequest $request, Chore $chore): JsonResponse
    {
        $assignment = $this->assignmentService->assignChore(
            $chore,
            \App\Models\User::findOrFail($request->user_id),
            Carbon::parse($request->due_date)
        );

        return response()->json([
            'assignment' => new ChoreAssignmentResource($assignment->load(['chore', 'user'])),
            'message' => 'Chore assigned successfully.',
        ], 201);
    }

    public function complete(CompleteChoreRequest $request, ChoreAssignment $assignment): JsonResponse
    {
        $photoPath = $request->hasFile('photo')
            ? $this->assignmentService->storePhoto($request->file('photo'))
            : null;

        $completion = $this->assignmentService->completeAssignment(
            $assignment,
            $request->user(),
            $photoPath,
            $request->notes
        );

        return response()->json([
            'completion' => new ChoreCompletionResource($completion),
            'assignment' => new ChoreAssignmentResource($assignment->fresh()),
            'message' => 'Chore completed successfully!',
        ]);
    }

    public function updatePreference(UpdateChorePreferenceRequest $request): JsonResponse
    {
        $preference = UserChorePreference::updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'chore_id' => $request->chore_id,
            ],
            [
                'preference' => $request->preference,
                'weight' => UserChorePreference::getWeightForPreference($request->preference),
            ]
        );

        return response()->json([
            'preference' => new UserChorePreferenceResource($preference),
            'message' => 'Preference updated.',
        ]);
    }

    public function runRoulette(Request $request): JsonResponse
    {
        $request->validate(['household_id' => 'required|exists:households,id']);
        abort_unless($request->user()->households->contains($request->household_id), 403);

        $household = \App\Models\Household::findOrFail($request->household_id);
        $assignments = $this->assignmentService->runWeeklyRoulette($household);

        return response()->json([
            'assignments' => ChoreAssignmentResource::collection($assignments),
            'message' => sprintf('Created %d new assignments.', $assignments->count()),
        ]);
    }
}

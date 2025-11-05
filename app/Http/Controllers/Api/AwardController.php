<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AwardResource;
use App\Http\Resources\UserAwardResource;
use App\Services\AwardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AwardController extends Controller
{
    public function __construct(
        protected AwardService $awardService
    ) {
    }

    /**
     * Get all available awards.
     */
    public function index(): AnonymousResourceCollection
    {
        $awards = $this->awardService->getAllAwards();
        return AwardResource::collection($awards);
    }

    /**
     * Get user's earned awards for a household.
     */
    public function userAwards(Request $request): AnonymousResourceCollection
    {
        $request->validate(['household_id' => 'required|exists:households,id']);
        abort_unless($request->user()->households->contains($request->household_id), 403);

        $userAwards = $this->awardService->getUserAwards($request->user(), $request->household_id);
        return UserAwardResource::collection($userAwards);
    }

    /**
     * Check and grant any new awards for a user.
     */
    public function checkAwards(Request $request): JsonResponse
    {
        $request->validate(['household_id' => 'required|exists:households,id']);
        abort_unless($request->user()->households->contains($request->household_id), 403);

        $newAwards = $this->awardService->checkAndGrantAwards($request->user(), $request->household_id);

        return response()->json([
            'new_awards' => UserAwardResource::collection($newAwards->load('award')),
            'message' => $newAwards->count() > 0
                ? 'Congratulations! You earned ' . $newAwards->count() . ' new award(s)!'
                : 'No new awards earned yet.',
        ]);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\GamificationStatResource;
use App\Services\GamificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class GamificationController extends Controller
{
    public function __construct(
        protected GamificationService $gamificationService
    ) {
    }

    public function myStats(Request $request): JsonResponse
    {
        $request->validate(['household_id' => 'required|exists:households,id']);
        abort_unless($request->user()->households->contains($request->household_id), 403);

        $stats = $this->gamificationService->getOrCreateStats($request->user(), $request->household_id);
        $rankInfo = $this->gamificationService->getUserRank($request->user(), $request->household_id, 'monthly');

        return response()->json([
            'stats' => new GamificationStatResource($stats),
            'rank' => $rankInfo['rank'],
            'total_members' => $rankInfo['total'],
        ]);
    }

    public function monthlyLeaderboard(Request $request): AnonymousResourceCollection
    {
        $request->validate(['household_id' => 'required|exists:households,id']);
        abort_unless($request->user()->households->contains($request->household_id), 403);

        $leaderboard = $this->gamificationService->getMonthlyLeaderboard($request->household_id);

        return GamificationStatResource::collection($leaderboard);
    }

    public function allTimeLeaderboard(Request $request): AnonymousResourceCollection
    {
        $request->validate(['household_id' => 'required|exists:households,id']);
        abort_unless($request->user()->households->contains($request->household_id), 403);

        $leaderboard = $this->gamificationService->getAllTimeLeaderboard($request->household_id);

        return GamificationStatResource::collection($leaderboard);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Household;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HouseholdController extends Controller
{
    public function index(Request $request)
    {
        $households = $request->user()->households()->with('owner', 'members')->get();

        return response()->json([
            'households' => $households,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            $household = Household::create([
                'name' => $request->name,
                'description' => $request->description,
                'owner_id' => $request->user()->id,
            ]);

            // Add creator as owner
            $household->users()->attach($request->user()->id, ['role' => 'owner']);

            DB::commit();

            return response()->json([
                'household' => $household->load('owner', 'members'),
                'message' => 'Household created successfully',
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create household',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(Request $request, Household $household)
    {
        // Check if user is member of household
        if (!$request->user()->households->contains($household->id)) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        return response()->json([
            'household' => $household->load('owner', 'members'),
        ]);
    }

    public function update(Request $request, Household $household)
    {
        // Check if user is owner or admin
        $userRole = $household->users()
            ->where('user_id', $request->user()->id)
            ->first()?->pivot->role;

        if (!in_array($userRole, ['owner', 'admin'])) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $household->update([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return response()->json([
            'household' => $household->load('owner', 'members'),
            'message' => 'Household updated successfully',
        ]);
    }

    public function destroy(Request $request, Household $household)
    {
        // Only owner can delete
        if ($household->owner_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Only the owner can delete the household',
            ], 403);
        }

        $household->delete();

        return response()->json([
            'message' => 'Household deleted successfully',
        ]);
    }

    public function join(Request $request)
    {
        $request->validate([
            'invite_code' => 'required|string',
        ]);

        $household = Household::where('invite_code', $request->invite_code)->first();

        if (!$household) {
            return response()->json([
                'message' => 'Invalid invite code',
            ], 404);
        }

        // Check if user is already a member
        if ($request->user()->households->contains($household->id)) {
            return response()->json([
                'message' => 'You are already a member of this household',
            ], 400);
        }

        $household->users()->attach($request->user()->id, ['role' => 'member']);

        return response()->json([
            'household' => $household->load('owner', 'members'),
            'message' => 'Successfully joined household',
        ]);
    }

    public function leave(Request $request, Household $household)
    {
        // Check if user is member
        if (!$request->user()->households->contains($household->id)) {
            return response()->json([
                'message' => 'You are not a member of this household',
            ], 400);
        }

        // Owner cannot leave their own household
        if ($household->owner_id === $request->user()->id) {
            return response()->json([
                'message' => 'Owner cannot leave the household. Transfer ownership or delete the household instead.',
            ], 400);
        }

        $household->users()->detach($request->user()->id);

        return response()->json([
            'message' => 'Successfully left household',
        ]);
    }
}

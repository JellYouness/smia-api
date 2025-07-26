<?php

namespace App\Http\Controllers;

use App\Models\TeamMember;
use App\Models\Ambassador;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class TeamMemberController extends CrudController
{
    protected function getModelClass(): string
    {
        return TeamMember::class;
    }

    protected function getCacheKey(): string
    {
        return 'team_members';
    }

    protected function getTable(): string
    {
        return 'team_members';
    }

    /**
     * Get all team members for a specific ambassador.
     */
    public function getByAmbassador(int $ambassadorId): JsonResponse
    {
        $ambassador = Ambassador::findOrFail($ambassadorId);

        $teamMembers = $ambassador->teamMembers()
            ->with('user.profile')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $teamMembers
        ]);
    }

    /**
     * Add a team member to an ambassador.
     */
    public function addTeamMember(Request $request, int $ambassadorId): JsonResponse
    {
        $ambassador = Ambassador::findOrFail($ambassadorId);

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'role' => 'nullable|string|max:255',
            'is_primary' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if user is already a team member
        $existingMember = $ambassador->teamMembers()
            ->where('user_id', $request->user_id)
            ->first();

        if ($existingMember) {
            return response()->json([
                'success' => false,
                'errors' => ['User is already a team member of this ambassador.']
            ], 422);
        }

        $teamMember = $ambassador->teamMembers()->create([
            'user_id' => $request->user_id,
            'role' => $request->role,
            'is_primary' => $request->is_primary ?? false,
        ]);

        $teamMember->load('user.profile');

        return response()->json([
            'success' => true,
            'data' => $teamMember
        ], 201);
    }

    /**
     * Remove a team member from an ambassador.
     */
    public function removeTeamMember(int $ambassadorId, int $teamMemberId): JsonResponse
    {
        $ambassador = Ambassador::findOrFail($ambassadorId);

        $teamMember = $ambassador->teamMembers()
            ->where('id', $teamMemberId)
            ->first();

        if (!$teamMember) {
            return response()->json([
                'success' => false,
                'errors' => ['Team member not found.']
            ], 404);
        }

        $teamMember->delete();

        return response()->json([
            'success' => true,
            'message' => 'Team member removed successfully.'
        ]);
    }

    /**
     * Update a team member's role or primary status.
     */
    public function updateTeamMember(Request $request, int $ambassadorId, int $teamMemberId): JsonResponse
    {
        $ambassador = Ambassador::findOrFail($ambassadorId);

        $teamMember = $ambassador->teamMembers()
            ->where('id', $teamMemberId)
            ->first();

        if (!$teamMember) {
            return response()->json([
                'success' => false,
                'errors' => ['Team member not found.']
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'role' => 'nullable|string|max:255',
            'is_primary' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $teamMember->update($request->only(['role', 'is_primary']));
        $teamMember->load('user.profile');

        return response()->json([
            'success' => true,
            'data' => $teamMember
        ]);
    }
}

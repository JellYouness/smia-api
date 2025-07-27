<?php

namespace App\Http\Controllers;

use App\Models\TeamMember;
use App\Models\Ambassador;
use App\Models\TeamMemberInvitation;
use App\Models\User;
use App\Notifications\TeamMemberInvitationNotification;
use App\Services\NotificationService;
use App\Enums\NotificationType;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TeamMemberController extends CrudController
{
    public function __construct(
        private NotificationService $notificationService
    ) {}

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

        // Check if there's already a pending invitation
        $existingInvitation = TeamMemberInvitation::where('ambassador_id', $ambassadorId)
            ->where('user_id', $request->user_id)
            ->where('status', 'PENDING')
            ->first();

        if ($existingInvitation) {
            return response()->json([
                'success' => false,
                'errors' => ['An invitation has already been sent to this user.']
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Create invitation
            $invitation = TeamMemberInvitation::create([
                'ambassador_id' => $ambassadorId,
                'user_id' => $request->user_id,
                'role' => $request->role,
                'is_primary' => $request->is_primary ?? false,
                'status' => 'PENDING',
                'token' => TeamMemberInvitation::generateToken(),
                'expires_at' => now()->addDays(7), // Invitation expires in 7 days
            ]);

            // Send invitation email
            $user = User::find($request->user_id);
            if ($user) {
                $user->notify(new TeamMemberInvitationNotification($invitation));
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Invitation sent successfully to the user.',
                'data' => [
                    'invitation_id' => $invitation->id,
                    'expires_at' => $invitation->expires_at,
                ]
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating team member invitation: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'errors' => ['Failed to send invitation. Please try again.']
            ], 500);
        }
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

    /**
     * Get invitation by token.
     */
    public function getInvitationByToken(string $token): JsonResponse
    {
        $invitation = TeamMemberInvitation::with(['ambassador.user', 'user'])
            ->where('token', $token)
            ->first();

        if (!$invitation) {
            return response()->json([
                'success' => false,
                'errors' => ['Invitation not found or invalid.']
            ], 404);
        }

        if ($invitation->isExpired()) {
            $invitation->update(['status' => 'EXPIRED']);
            return response()->json([
                'success' => false,
                'errors' => ['This invitation has expired.']
            ], 400);
        }

        if ($invitation->status !== 'PENDING') {
            return response()->json([
                'success' => false,
                'errors' => ['This invitation has already been responded to.']
            ], 400);
        }

        return response()->json([
            'success' => true,
            'data' => $invitation
        ]);
    }

    /**
     * Accept team member invitation.
     */
    public function acceptInvitation(string $token): JsonResponse
    {
        $invitation = TeamMemberInvitation::with(['ambassador', 'user'])
            ->where('token', $token)
            ->first();

        if (!$invitation) {
            return response()->json([
                'success' => false,
                'errors' => ['Invitation not found or invalid.']
            ], 404);
        }

        if (!$invitation->canBeAccepted()) {
            if ($invitation->isExpired()) {
                $invitation->update(['status' => 'EXPIRED']);
                return response()->json([
                    'success' => false,
                    'errors' => ['This invitation has expired.']
                ], 400);
            }

            return response()->json([
                'success' => false,
                'errors' => ['This invitation cannot be accepted.']
            ], 400);
        }

        try {
            DB::beginTransaction();

            // Accept the invitation
            $invitation->accept();

            // Check if user is already a team member
            $existingMember = $invitation->ambassador->teamMembers()
                ->where('user_id', $invitation->user_id)
                ->first();

            if ($existingMember) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'errors' => ['User is already a team member of this ambassador.']
                ], 422);
            }

            // Add user as team member
            $teamMember = $invitation->ambassador->teamMembers()->create([
                'user_id' => $invitation->user_id,
                'role' => $invitation->role,
                'is_primary' => $invitation->is_primary,
                'joined_at' => now(),
            ]);

            // Send notification to ambassador about accepted invitation
            if ($invitation->ambassador && $invitation->ambassador->user) {
                $this->notificationService->send(
                    $invitation->ambassador->user,
                    NotificationType::TEAM_INVITATION_ACCEPTED,
                    [
                        'invitation_id' => $invitation->id,
                        'user_id' => $invitation->user_id,
                        'user_name' => $invitation->user->name,
                        'user_email' => $invitation->user->email,
                        'role' => $invitation->role,
                        'is_primary' => $invitation->is_primary,
                        'team_name' => $invitation->ambassador->teamName ?? 'SMIA Ambassador Team',
                    ],
                    ['in_app', 'email']
                );
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Invitation accepted successfully. You are now a team member.',
                'data' => $teamMember->load('user.profile')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error accepting team member invitation: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'errors' => ['Failed to accept invitation. Please try again.']
            ], 500);
        }
    }

    /**
     * Decline team member invitation.
     */
    public function declineInvitation(string $token): JsonResponse
    {
        $invitation = TeamMemberInvitation::where('token', $token)->first();

        if (!$invitation) {
            return response()->json([
                'success' => false,
                'errors' => ['Invitation not found or invalid.']
            ], 404);
        }

        if (!$invitation->canBeAccepted()) {
            if ($invitation->isExpired()) {
                $invitation->update(['status' => 'EXPIRED']);
                return response()->json([
                    'success' => false,
                    'errors' => ['This invitation has expired.']
                ], 400);
            }

            return response()->json([
                'success' => false,
                'errors' => ['This invitation cannot be declined.']
            ], 400);
        }

        $invitation->decline();

        // Send notification to ambassador about declined invitation
        if ($invitation->ambassador && $invitation->ambassador->user) {
            $this->notificationService->send(
                $invitation->ambassador->user,
                NotificationType::TEAM_INVITATION_DECLINED,
                [
                    'invitation_id' => $invitation->id,
                    'user_id' => $invitation->user_id,
                    'user_name' => $invitation->user->name,
                    'user_email' => $invitation->user->email,
                    'role' => $invitation->role,
                    'is_primary' => $invitation->is_primary,
                    'team_name' => $invitation->ambassador->teamName ?? 'SMIA Ambassador Team',
                ],
                ['in_app', 'email']
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Invitation declined successfully.'
        ]);
    }

    /**
     * Get pending invitations for an ambassador.
     */
    public function getPendingInvitations(int $ambassadorId): JsonResponse
    {
        $ambassador = Ambassador::findOrFail($ambassadorId);

        $invitations = TeamMemberInvitation::with(['user.profile'])
            ->where('ambassador_id', $ambassadorId)
            ->where('status', 'PENDING')
            ->where('expires_at', '>', now())
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $invitations
        ]);
    }
}

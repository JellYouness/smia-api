<?php

namespace App\Http\Controllers;

use App\Enums\CREATOR_PROJECT_PERMISSION;
use App\Enums\PROJECT_INVITE_STATUS;
use App\Enums\PROJECT_PROPOSAL_STATUS;
use App\Models\Creator;
use App\Models\Project;
use App\Models\ProjectCreator;
use App\Models\ProjectInvite;
use App\Models\ProjectProposal;
use App\Models\ProposalComment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Log;

class ProjectController extends CrudController
{
    protected function getModel(): string
    {
        return Project::class;
    }

    protected function getTable(): string
    {
        return 'projects';
    }

    protected function getModelClass(): string
    {
        return Project::class;
    }

    protected function afterReadOne($project, Request $request)
    {
        $proposalsCount = $project->proposals()->count();

        // Load project creators with their creator and user relationships
        $projectCreators = $project->creators()->with(['creator.user'])->get();

        $hiredCreatorIds = $projectCreators->pluck('creator_id')->toArray();

        if ($project->creator_id) {
            $hiredCreatorIds[] = $project->creator_id;
        }

        $hiresCount = collect($hiredCreatorIds)
            ->filter()
            ->unique()
            ->count();

        $invitedCreatorIds = $project->invites()->pluck('creator_id')->toArray();

        $project->setAttribute('invited_creator_ids', $invitedCreatorIds);
        $project->setAttribute('proposals_count', $proposalsCount);
        $project->setAttribute('hires_count', $hiresCount);
        $project->setAttribute('project_creators', $projectCreators);
    }

    public function readAllByCreator(Request $request, $creatorId)
    {
        try {
            $user = $request->user();
            if (! $user->hasPermission('projects', 'read_all') && !$user->hasPermission('projects', 'read_own')) {
                return response()->json(['success' => false, 'errors' => [__('common.permission_denied')]]);
            }

            $query = Project::where('creator_id', $creatorId);

            $perPage = $request->input('per_page', 50);
            if ($perPage === 'all') {
                $projects = $query->get();
                $meta = [
                    'current_page' => 1,
                    'last_page' => 1,
                    'total_items' => $projects->count(),
                ];
            } else {
                $projects = $query->paginate($perPage);
                $meta = [
                    'current_page' => $projects->currentPage(),
                    'last_page' => $projects->lastPage(),
                    'total_items' => $projects->total(),
                ];
                $projects = $projects->items();
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'items' => $projects,
                    'meta' => $meta,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error caught in function ProjectController.readAllByCreator: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return response()->json(['success' => false, 'errors' => [__('common.unexpected_error')]]);
        }
    }

    public function readAllByClient(Request $request, $clientId)
    {
        try {
            $user = $request->user();
            if (! $user->hasPermission('projects', 'read_all') && !$user->hasPermission('projects', 'read_own')) {
                return response()->json(['success' => false, 'errors' => [__('common.permission_denied')]]);
            }

            $query = Project::withCount('proposals')->where('client_id', $clientId);

            $perPage = $request->input('per_page', 50);
            if ($perPage === 'all') {
                $projects = $query->get();
                $meta = [
                    'current_page' => 1,
                    'last_page' => 1,
                    'total_items' => $projects->count(),
                ];
            } else {
                $projects = $query->paginate($perPage);
                $meta = [
                    'current_page' => $projects->currentPage(),
                    'last_page' => $projects->lastPage(),
                    'total_items' => $projects->total(),
                ];
                $projects = $projects->items();
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'items' => $projects,
                    'meta' => $meta,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error caught in function ProjectController.readAllByClient: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return response()->json(['success' => false, 'errors' => [__('common.unexpected_error')]]);
        }
    }

    public function readAllByAmbassador(Request $request, $ambassadorId)
    {
        try {
            $user = $request->user();
            if (! $user->hasPermission('projects', 'read_all') && !$user->hasPermission('projects', 'read_own')) {
                return response()->json(['success' => false, 'errors' => [__('common.permission_denied')]]);
            }

            $query = Project::where('ambassador_id', $ambassadorId);

            $perPage = $request->input('per_page', 50);
            if ($perPage === 'all') {
                $projects = $query->get();
                $meta = [
                    'current_page' => 1,
                    'last_page' => 1,
                    'total_items' => $projects->count(),
                ];
            } else {
                $projects = $query->paginate($perPage);
                $meta = [
                    'current_page' => $projects->currentPage(),
                    'last_page' => $projects->lastPage(),
                    'total_items' => $projects->total(),
                ];
                $projects = $projects->items();
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'items' => $projects,
                    'meta' => $meta,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error caught in function ProjectController.readAllByAmbassador: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return response()->json(['success' => false, 'errors' => [__('common.unexpected_error')]]);
        }
    }

    public function inviteCreator(Request $request)
    {
        $user = $request->user();
        if (! $user->hasPermission('projects', 'invite_creator')) {
            return response()->json(['success' => false, 'errors' => [__('common.permission_denied')]]);
        }

        try {
            $projectId = $request->input('project_id');
            $creatorId = $request->input('creator_id');
            $message   = $request->input('message');

            $project = Project::find($projectId);
            if (! $project) {
                return response()->json(['success' => false, 'errors' => [__('common.project_not_found')]]);
            }

            $creator = Creator::find($creatorId);
            if (! $creator) {
                return response()->json(['success' => false, 'errors' => [__('common.creator_not_found')]]);
            }

            if ($project->creator_id && $project->creator_id == $creatorId || $project->creators()->where('creator_id', $creatorId)->exists()) {
                return response()->json([
                    'success' => false,
                    'errors'  => [__('projects.creator_already_hired')],
                ]);
            }

            $existingInvite = ProjectInvite::where('project_id', $projectId)
                ->where('creator_id', $creatorId)
                ->whereIn('status', [
                    PROJECT_INVITE_STATUS::PENDING,
                    PROJECT_INVITE_STATUS::ACCEPTED
                ])
                ->first();

            if ($existingInvite) {
                return response()->json([
                    'success' => false,
                    'errors'  => [__('projects.invite_already_sent')],
                ]);
            }

            $projectInvite = ProjectInvite::create([
                'project_id' => $projectId,
                'creator_id' => $creatorId,
                'message'    => $message,
                'status'     => PROJECT_INVITE_STATUS::PENDING,
                'expires_at' => Carbon::now()->addDays(7),
            ]);

            return response()->json(['success' => true, 'data' => $projectInvite]);
        } catch (\Exception $e) {
            Log::error('Error caught in function ProjectController.inviteCreator: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json(['success' => false, 'errors' => [__('common.unexpected_error')]]);
        }
    }

    public function readAllInvitesByCreator(Request $request, $creatorId)
    {
        try {
            $user = $request->user();
            if (
                ! $user->hasPermission('projects', 'read_invites') &&
                ! $user->hasPermission('projects', 'read_own_invites')
            ) {
                return response()->json([
                    'success' => false,
                    'errors'  => [__('common.permission_denied')],
                ]);
            }

            ProjectInvite::where('creator_id', $creatorId)
                ->where('status', PROJECT_INVITE_STATUS::PENDING)
                ->where('expires_at', '<', now())
                ->update(['status' => PROJECT_INVITE_STATUS::EXPIRED]);

            $query = ProjectInvite::with([
                'project:id,title,budget,status,start_date,end_date,client_id',

                'project.client:id,user_id,company_name,company_size,industry',

                'project.client.user:id,first_name,last_name,profile_image',
            ])
                ->where('creator_id', $creatorId)
                ->orderByDesc('created_at');

            $perPage = $request->input('per_page', 50);

            if ($perPage === 'all') {
                $invites = $query->get();
                $meta    = [
                    'current_page' => 1,
                    'last_page'    => 1,
                    'total_items'  => $invites->count(),
                ];
            } else {
                $invites = $query->paginate($perPage);
                $meta    = [
                    'current_page' => $invites->currentPage(),
                    'last_page'    => $invites->lastPage(),
                    'total_items'  => $invites->total(),
                ];
                $invites = $invites->items();
            }

            return response()->json([
                'success' => true,
                'data'    => [
                    'items' => $invites,
                    'meta'  => $meta,
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('Error in ProjectController.readAllInvitesByCreator: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json([
                'success' => false,
                'errors'  => [__('common.unexpected_error')],
            ]);
        }
    }

    public function declineInvite(Request $request, $id)
    {
        try {
            $user   = $request->user();
            $invite = ProjectInvite::with('creator.user')->find($id);

            if (! $invite) {
                return response()->json([
                    'success' => false,
                    'errors'  => [__('projects.invite_not_found')],
                ]);
            }

            $isReceiver = $invite->creator && $invite->creator->user_id === $user->id;
            if (! $isReceiver && ! $user->hasPermission('projects', 'manage_invites')) {
                return response()->json([
                    'success' => false,
                    'errors'  => [__('common.permission_denied')],
                ]);
            }

            if ($invite->status !== PROJECT_INVITE_STATUS::PENDING->value) {
                return response()->json([
                    'success' => false,
                    'errors'  => [__('projects.invite_not_pending')],
                ]);
            }

            $invite->update([
                'status'      => PROJECT_INVITE_STATUS::DECLINED,
                'declined_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'data'    => ['item' => $invite],
                'message' => __('projects.invite_declined'),
            ]);
        } catch (\Throwable $e) {
            Log::error('Error in ProjectController.declineInvite: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json([
                'success' => false,
                'errors'  => [__('common.unexpected_error')],
            ]);
        }
    }

    public function acceptInvite(Request $request, $id)
    {
        try {
            $user   = $request->user();
            $invite = ProjectInvite::with(['project', 'creator'])->find($id);

            if (! $invite) {
                return response()->json([
                    'success' => false,
                    'errors'  => [__('projects.invite_not_found')],
                ]);
            }

            $isReceiver = $invite->creator && $invite->creator->user_id === $user->id;
            if (! $isReceiver && ! $user->hasPermission('projects', 'manage_invites')) {
                return response()->json([
                    'success' => false,
                    'errors'  => [__('common.permission_denied')],
                ]);
            }

            if ($invite->status !== PROJECT_INVITE_STATUS::PENDING->value) {
                return response()->json([
                    'success' => false,
                    'errors'  => [__('projects.invite_not_pending')],
                ]);
            }

            $data = $request->only([
                'amount',
                'currency',
                'duration_days',
                'cover_letter',
                'attachments',
                'meta'
            ]);

            if (array_key_exists('currency', $data) && is_null($data['currency'])) {
                $data['currency'] = 'USD';
            }

            $proposal = ProjectProposal::create([
                'invite_id'     => $invite->id,
                'project_id'    => $invite->project_id,
                'creator_id'    => $invite->creator_id,
                'status'        => PROJECT_PROPOSAL_STATUS::PENDING->value,
                ...$data,
            ]);

            $invite->update([
                'status'      => PROJECT_INVITE_STATUS::ACCEPTED,
                'accepted_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'data'    => ['item' => $proposal],
                'message' => __('projects.invite_accepted'),
            ]);
        } catch (\Throwable $e) {
            Log::error('Error in ProjectController.acceptInvite: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json([
                'success' => false,
                'errors'  => [__('common.unexpected_error')],
            ]);
        }
    }

    public function readAllProposalsByCreator(Request $request, $creatorId)
    {
        try {
            $user = $request->user();
            if (
                ! $user->hasPermission('projects', 'read_proposals') &&
                ! $user->hasPermission('projects', 'read_own_proposals')
            ) {
                return response()->json(['success' => false, 'errors' => [__('common.permission_denied')]]);
            }

            $query = ProjectProposal::with([
                'project:id,title,budget,status,start_date,end_date,client_id',
                'project.client:id,user_id,company_name',
                'project.client.user:id,first_name,last_name,profile_image',
            ])->where('creator_id', $creatorId)
                ->orderByDesc('created_at');

            $perPage = $request->input('per_page', 50);
            if ($perPage === 'all') {
                $proposals = $query->get();
                $meta = ['current_page' => 1, 'last_page' => 1, 'total_items' => $proposals->count()];
            } else {
                $proposals = $query->paginate($perPage);
                $meta = [
                    'current_page' => $proposals->currentPage(),
                    'last_page' => $proposals->lastPage(),
                    'total_items' => $proposals->total(),
                ];
                $proposals = $proposals->items();
            }

            return response()->json([
                'success' => true,
                'data' => ['items' => $proposals, 'meta' => $meta],
            ]);
        } catch (\Throwable $e) {
            Log::error('readAllProposalsByCreator: ' . $e->getMessage());
            return response()->json(['success' => false, 'errors' => [__('common.unexpected_error')]]);
        }
    }

    public function readAllProposalsByProject(Request $request, $projectId)
    {
        try {
            $user = $request->user();
            if (
                ! $user->hasPermission('projects', 'read_proposals') &&
                ! $user->hasPermission('projects', 'read_own_proposals')
            ) {
                return response()->json(['success' => false, 'errors' => [__('common.permission_denied')]]);
            }

            $query = ProjectProposal::with([
                'creator:id,user_id',
                'creator.user:id,first_name,last_name,profile_image',

                'project:id,title,budget,status,start_date,end_date,creator_id',
            ])->where('project_id', $projectId)
                ->orderByDesc('created_at');

            $perPage = $request->input('per_page', 50);
            if ($perPage === 'all') {
                $proposals = $query->get();
                $meta = ['current_page' => 1, 'last_page' => 1, 'total_items' => $proposals->count()];
            } else {
                $proposals = $query->paginate($perPage);
                $meta = [
                    'current_page' => $proposals->currentPage(),
                    'last_page' => $proposals->lastPage(),
                    'total_items' => $proposals->total(),
                ];
                $proposals = $proposals->items();
            }

            return response()->json([
                'success' => true,
                'data' => ['items' => $proposals, 'meta' => $meta],
            ]);
        } catch (\Throwable $e) {
            Log::error('readAllProposalsByProject: ' . $e->getMessage());
            return response()->json(['success' => false, 'errors' => [__('common.unexpected_error')]]);
        }
    }

    public function addCommentToProposal(Request $request, $proposalId)
    {
        try {
            $user      = $request->user();
            $proposal  = ProjectProposal::with(['creator.user', 'project.client.user'])->findOrFail($proposalId);

            $isCreator = $proposal->creator?->user_id === $user->id;
            $isClient  = $proposal->project?->client?->user_id === $user->id;

            if (! ($isCreator || $isClient || $user->hasPermission('projects', 'manage_comments'))) {
                return response()->json([
                    'success' => false,
                    'errors'  => [__('common.permission_denied')]
                ]);
            }

            $data = $request->validate(ProposalComment::rules());

            if ($data['parent_id'] ?? false) {
                $parent = ProposalComment::where('proposal_id', $proposalId)
                    ->whereNull('parent_id')
                    ->findOrFail($data['parent_id']);
            }

            $comment = ProposalComment::create([
                'proposal_id' => $proposalId,
                'user_id'     => $user->id,
                'parent_id'   => $data['parent_id'] ?? null,
                'body'        => $data['body'],
                'attachments' => $data['attachments'] ?? null,
            ]);

            return response()->json([
                'success' => true,
                'data'    => ['item' => $comment]
            ]);
        } catch (\Throwable $e) {
            Log::error('addCommentToProposal: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'errors'  => [__('common.unexpected_error')],
            ]);
        }
    }

    public function readAllCommentsByProposal(Request $request, $proposalId)
    {
        try {
            $user = $request->user();

            $proposal = ProjectProposal::findOrFail($proposalId);

            $isCreator = $proposal->creator?->user_id === $user->id;
            $isClient  = $proposal->project?->client?->user_id === $user->id;

            if (! ($isCreator || $isClient || $user->hasPermission('projects', 'manage_comments'))) {
                return response()->json([
                    'success' => false,
                    'errors'  => [__('common.permission_denied')],
                ]);
            }

            $comments = ProposalComment::where('proposal_id', $proposalId)
                ->whereNull('parent_id')
                ->orderBy('created_at')
                ->get();

            return response()->json([
                'success' => true,
                'data'    => ['items' => $comments],
            ]);
        } catch (\Throwable $e) {
            Log::error('readAllCommentsByProposal: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'errors'  => [__('common.unexpected_error')],
            ]);
        }
    }

    public function approveProposal(Request $request, $proposalId)
    {
        try {
            $user = $request->user();
            $proposal = ProjectProposal::with(['creator', 'project'])->findOrFail($proposalId);

            $isClient = $proposal->project?->client?->user_id === $user->id;

            if (!$isClient && !$user->hasPermission('projects', 'manage_proposals')) {
                return response()->json(['success' => false, 'errors' => [__('common.permission_denied')]]);
            }

            if ($proposal->status !== PROJECT_PROPOSAL_STATUS::PENDING->value) {
                return response()->json(['success' => false, 'errors' => [__('projects.proposal_not_pending')]]);
            }

            $proposal->update(['status' => PROJECT_PROPOSAL_STATUS::ACCEPTED]);

            ProjectCreator::create([
                'project_id' => $proposal->project_id,
                'creator_id' => $proposal->creator_id,
                'role'       => null,
                'status'     => 'ACTIVE',
                'permission' => CREATOR_PROJECT_PERMISSION::EDITOR,
            ]);

            return response()->json([
                'success' => true,
                'data'    => ['item' => $proposal],
                'message' => __('projects.proposal_approved'),
            ]);
        } catch (\Throwable $e) {
            Log::error('approveProposal: ' . $e->getMessage());
            return response()->json(['success' => false, 'errors' => [__('common.unexpected_error')]]);
        }
    }

    public function declineProposal(Request $request, $proposalId)
    {
        try {
            $user = $request->user();
            $proposal = ProjectProposal::with('project.client.user')->findOrFail($proposalId);

            $isClient = $proposal->project?->client?->user_id === $user->id;

            if (!$isClient && !$user->hasPermission('projects', 'manage_proposals')) {
                return response()->json(['success' => false, 'errors' => [__('common.permission_denied')]]);
            }

            if ($proposal->status !== PROJECT_PROPOSAL_STATUS::PENDING->value) {
                return response()->json(['success' => false, 'errors' => [__('projects.proposal_not_pending')]]);
            }

            $proposal->update(['status' => PROJECT_PROPOSAL_STATUS::REJECTED]);

            return response()->json([
                'success' => true,
                'data'    => ['item' => $proposal],
                'message' => __('projects.proposal_declined'),
            ]);
        } catch (\Throwable $e) {
            Log::error('declineProposal: ' . $e->getMessage());
            return response()->json(['success' => false, 'errors' => [__('common.unexpected_error')]]);
        }
    }

    public function updateCreatorPermission(Request $request, $projectId, $creatorId)
    {
        try {
            $user = $request->user();
            $project = Project::findOrFail($projectId);

            // Check if user has permission to manage this project
            $isClient = $project->client?->user_id === $user->id;
            $isAmbassador = $project->ambassador?->user_id === $user->id;

            if (!$isClient && !$isAmbassador && !$user->hasPermission('projects', 'manage_creators')) {
                return response()->json(['success' => false, 'errors' => [__('common.permission_denied')]]);
            }

            $validated = $request->validate([
                'permission' => 'required|in:' . implode(',', array_column(CREATOR_PROJECT_PERMISSION::cases(), 'value')),
            ]);

            $projectCreator = ProjectCreator::where('project_id', $projectId)
                ->where('creator_id', $creatorId)
                ->first();

            if (!$projectCreator) {
                return response()->json(['success' => false, 'errors' => [__('projects.creator_not_found')]]);
            }

            $projectCreator->update(['permission' => $validated['permission']]);

            return response()->json([
                'success' => true,
                'data' => ['item' => $projectCreator],
                'message' => __('projects.creator_permission_updated'),
            ]);
        } catch (\Throwable $e) {
            Log::error('updateCreatorPermission: ' . $e->getMessage());
            return response()->json(['success' => false, 'errors' => [__('common.unexpected_error')]]);
        }
    }

    public function readAllPublicProjects(Request $request)
    {
        try {
            $query = Project::query()->where('is_public', true);

            // Apply filters
            $filters = $request->input('filters', []);
            if (is_string($filters)) {
                $filters = json_decode($filters, true) ?? [];
            }
            foreach ($filters as $filter) {
                $filter = is_string($filter) ? json_decode($filter, true) : $filter;
                if (isset($filter['filterColumn'], $filter['filterOperator'], $filter['filterValue'])) {
                    if ($filter['filterColumn'] === 'status' && $filter['filterOperator'] === 'equals') {
                        $query->where('status', $filter['filterValue']);
                    } elseif ($filter['filterColumn'] === 'budget' && $filter['filterOperator'] === 'gte') {
                        $query->where('budget', '>=', $filter['filterValue']);
                    } elseif ($filter['filterColumn'] === 'budget' && $filter['filterOperator'] === 'lte') {
                        $query->where('budget', '<=', $filter['filterValue']);
                    } elseif ($filter['filterColumn'] === 'search' && $filter['filterOperator'] === 'contains') {
                        $search = $filter['filterValue'];
                        $query->where(function ($q) use ($search) {
                            $q->where('title', 'like', "%$search%")
                                ->orWhere('description', 'like', "%$search%")
                                ->orWhere('status', 'like', "%$search%")
                                ->orWhere('budget', 'like', "%$search%")
                            ;
                        });
                    }
                }
            }

            $perPage = $request->input('per_page', 50);
            if ($perPage === 'all') {
                $projects = $query->get();
                $meta = [
                    'current_page' => 1,
                    'last_page' => 1,
                    'total_items' => $projects->count(),
                ];
            } else {
                $projects = $query->paginate($perPage);
                $meta = [
                    'current_page' => $projects->currentPage(),
                    'last_page' => $projects->lastPage(),
                    'total_items' => $projects->total(),
                ];
                $projects = $projects->items();
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'items' => $projects,
                    'meta' => $meta,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error caught in function ProjectController.readAllPublicProjects: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return response()->json(['success' => false, 'errors' => [__('common.unexpected_error')]]);
        }
    }
}

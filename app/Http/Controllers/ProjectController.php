<?php

namespace App\Http\Controllers;

use App\Enums\PROJECT_INVITE_STATUS;
use App\Models\Creator;
use App\Models\Project;
use App\Models\ProjectInvite;
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

    $hiredCreatorIds = $project->creators()
      ->pluck('creator_id')
      ->toArray();

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
}

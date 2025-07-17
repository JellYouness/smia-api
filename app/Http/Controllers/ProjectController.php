<?php

namespace App\Http\Controllers;

use App\Models\Project;
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
}

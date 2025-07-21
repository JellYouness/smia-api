<?php

namespace App\Http\Controllers;

use App\Models\ProjectUpdate;
use Illuminate\Http\Request;

class ProjectUpdateController extends CrudController
{
  protected function getModel(): string
  {
    return ProjectUpdate::class;
  }

  protected function getTable(): string
  {
    return 'project_updates';
  }

  protected function getModelClass(): string
  {
    return ProjectUpdate::class;
  }

  public function readAllByProject(Request $request, $projectId)
  {
    try {
      $user = $request->user();
      // Permission check (adjust as needed)
      if (!$user->hasPermission('project_updates', 'read_all') && !$user->hasPermission('project_updates', 'read_own')) {
        return response()->json(['success' => false, 'errors' => [__('common.permission_denied')]]);
      }

      $query = ProjectUpdate::where('project_id', $projectId)
        ->with(['project', 'client', 'ambassador']);

      $perPage = $request->input('per_page', 50);
      if ($perPage === 'all') {
        $updates = $query->get();
        $meta = [
          'current_page' => 1,
          'last_page' => 1,
          'total_items' => $updates->count(),
        ];
      } else {
        $updates = $query->paginate($perPage);
        $meta = [
          'current_page' => $updates->currentPage(),
          'last_page' => $updates->lastPage(),
          'total_items' => $updates->total(),
        ];
        $updates = $updates->items();
      }

      return response()->json([
        'success' => true,
        'data' => [
          'items' => $updates,
          'meta' => $meta,
        ],
      ]);
    } catch (\Exception $e) {
      \Log::error('Error in ProjectUpdateController.readAllByProject: ' . $e->getMessage());
      \Log::error($e->getTraceAsString());
      return response()->json(['success' => false, 'errors' => [__('common.unexpected_error')]]);
    }
  }
}

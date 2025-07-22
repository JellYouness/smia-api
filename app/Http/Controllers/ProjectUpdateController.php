<?php

namespace App\Http\Controllers;

use App\Models\ProjectUpdate;
use Illuminate\Http\Request;
use App\Services\NotificationService;
use App\Enums\NotificationType;

class ProjectUpdateController extends CrudController
{
    private NotificationService $notificationService;
    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

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

    public function createOne(Request $request)
    {
        $response = parent::createOne($request);
        // After creation, send notifications
        if ($response->getStatusCode() === 200) {
            $data = $response->getData(true);
            if (!empty($data['data']['item']['id'])) {
                $update = \App\Models\ProjectUpdate::with('project.client.user', 'project.ambassador.user', 'project.creators.creator.user')->find($data['data']['item']['id']);
                $notified = [];
                // Notify client
                if ($update->project && $update->project->client && $update->project->client->user) {
                    $this->notificationService->send(
                        $update->project->client->user,
                        NotificationType::NEW_PROJECT_UPDATE,
                        [
                            'project_id' => $update->project_id,
                            'update_id' => $update->id,
                            'project_title' => $update->project->title ?? null,
                            'update_type' => $update->type->value ?? null,
                            'update_body' => $update->body ?? null,
                        ],
                        ['in_app', 'email']
                    );
                    $notified[] = $update->project->client->user->id;
                }
                // Notify ambassador
                if ($update->project && $update->project->ambassador && $update->project->ambassador->user && !in_array($update->project->ambassador->user->id, $notified)) {
                    $this->notificationService->send(
                        $update->project->ambassador->user,
                        NotificationType::NEW_PROJECT_UPDATE,
                        [
                            'project_id' => $update->project_id,
                            'update_id' => $update->id,
                            'project_title' => $update->project->title ?? null,
                            'update_type' => $update->type->value ?? null,
                            'update_body' => $update->body ?? null,
                        ],
                        ['in_app', 'email']
                    );
                    $notified[] = $update->project->ambassador->user->id;
                }
                // Notify all project creators
                if ($update->project && $update->project->creators) {
                    foreach ($update->project->creators as $projectCreator) {
                        if ($projectCreator->creator && $projectCreator->creator->user && !in_array($projectCreator->creator->user->id, $notified)) {
                            $this->notificationService->send(
                                $projectCreator->creator->user,
                                NotificationType::NEW_PROJECT_UPDATE,
                                [
                                    'project_id' => $update->project_id,
                                    'update_id' => $update->id,
                                    'project_title' => $update->project->title ?? null,
                                    'update_type' => $update->type->value ?? null,
                                    'update_body' => $update->body ?? null,
                                ],
                                ['in_app', 'email']
                            );
                            $notified[] = $projectCreator->creator->user->id;
                        }
                    }
                }
            }
        }
        return $response;
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Ambassador;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use App\Notifications\ApplicationStatusNotification;

class AmbassadorController extends CrudController
{
    protected function getModel(): string
    {
        return Ambassador::class;
    }

    protected function getTable(): string
    {
        return 'ambassadors';
    }

    protected function getModelClass(): string
    {
        return Ambassador::class;
    }

    protected function getRelations(): array
    {
        return ['user'];
    }

    protected function afterCreateOne($model, Request $request)
    {
        // Send email notification when application is first submitted
        if ($model->user) {
            $model->user->notify(new ApplicationStatusNotification(
                'ambassador',
                'submitted',
                null
            ));
        }
    }

    protected function getReadAllQuery(): Builder
    {
        return $this->model()->with('user');
    }

    public function readOne($id, Request $request)
    {
        try {
            if (in_array('read_one', $this->restricted)) {
                $user = $request->user();
                if (! $user->hasPermission($this->getTable(), 'read', $id)) {
                    return response()->json(
                        [
                            'success' => false,
                            'errors' => [__('common.permission_denied')],
                        ]
                    );
                }
            }

            $item = $this->model()->with(['user.profile'])->find($id);

            if (! $item) {
                return response()->json(
                    [
                        'success' => false,
                        'errors' => [__($this->getTable() . '.not_found')],
                    ]
                );
            }

            if (method_exists($this, 'afterReadOne')) {
                $this->afterReadOne($item, $request);
            }

            return response()->json(
                [
                    'success' => true,
                    'data' => ['item' => $item],
                ]
            );
        } catch (\Exception $e) {
            Log::error('Error caught in function AmbassadorController.readOne: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json(['success' => false, 'errors' => [__('common.unexpected_error')]]);
        }
    }

    public function updateApplicationStatus($id, Request $request)
    {
        try {
            $request->validate([
                'application_status' => 'required|in:PENDING,APPROVED,REJECTED',
                'review_notes' => 'nullable|string|max:1000',
            ]);

            $ambassador = $this->model()->with('user')->find($id);

            if (!$ambassador) {
                return response()->json([
                    'success' => false,
                    'errors' => [__($this->getTable() . '.not_found')],
                ]);
            }

            $oldStatus = $ambassador->application_status;
            $newStatus = $request->application_status;

            $ambassador->update([
                'application_status' => $newStatus,
                'review_notes' => $request->review_notes,
                'reviewed_at' => now(),
                'reviewed_by' => $request->user() ? $request->user()->id : null,
            ]);

            // Send email notification to the user
            if ($oldStatus !== $newStatus && $ambassador->user) {
                $status = match ($newStatus) {
                    'APPROVED' => 'approved',
                    'REJECTED' => 'rejected',
                    'PENDING' => 'pending',
                    default => 'updated',
                };

                $ambassador->user->notify(new ApplicationStatusNotification(
                    'ambassador',
                    $status,
                    $request->review_notes
                ));
            }

            return response()->json([
                'success' => true,
                'data' => ['item' => $ambassador->load('user')],
            ]);
        } catch (\Exception $e) {
            Log::error('Error caught in function AmbassadorController.updateApplicationStatus: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json(['success' => false, 'errors' => [__('common.unexpected_error')]]);
        }
    }

    public function getPendingApplications(Request $request)
    {
        try {
            $query = $this->model()->with('user')->where('application_status', 'PENDING');

            $perPage = $request->get('per_page', 15);
            $applications = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $applications,
            ]);
        } catch (\Exception $e) {
            Log::error('Error caught in function AmbassadorController.getPendingApplications: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json(['success' => false, 'errors' => [__('common.unexpected_error')]]);
        }
    }
}

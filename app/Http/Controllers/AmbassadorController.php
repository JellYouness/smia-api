<?php

namespace App\Http\Controllers;

use App\Models\Ambassador;
use App\Models\Creator;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use App\Notifications\ApplicationStatusNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

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
        return ['user', 'teamMembers.user.profile'];
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
        return $this->model()->with(['user', 'teamMembers.user.profile']);
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

            $item = $this->model()->with(['user.profile', 'teamMembers.user.profile', 'user.creator', 'user.ambassador'])->find($id);

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

    public function patchOne($id, Request $request)
    {
        try {
            return DB::transaction(
                function () use ($id, $request) {
                    if (in_array('update', $this->restricted)) {
                        $user = $request->user();
                        if (! $user->hasPermission($this->getTable(), 'update', $id) && ! $user->hasPermission('users', 'update', $user->id)) {
                            return response()->json(
                                [
                                    'success' => false,
                                    'errors' => [__('common.permission_denied')],
                                ]
                            );
                        }
                    }
                    $model = $this->model()->find($id);

                    if (! $model) {
                        return response()->json(
                            [
                                'success' => false,
                                'errors' => [__($this->getTable() . '.not_found')],
                            ]
                        );
                    }

                    $rules = app($this->getModelClass())->rules($id);
                    $fields = array_keys($request->all());
                    $validated = $request->validate(Arr::only($rules, $fields));

                    $model->update($validated);

                    if (method_exists($this, 'afterPatchOne')) {
                        $this->afterPatchOne($model, $request);
                    }

                    return response()->json(
                        [
                            'success' => true,
                            'data' => ['item' => $model],
                            'validated' => $validated,
                            'message' => __($this->getTable() . '.updated'),
                        ]
                    );
                }
            );
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'errors' => Arr::flatten($e->errors())]);
        } catch (\Exception $e) {
            Log::error('Error caught in function CrudController.patchOne: ' . $e->getMessage());
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

    // apply for ambassador
    public function applyForAmbassador(Request $request)
    {
        $request->validate([
            'team_name' => 'required|string|max:255',
            'team_description' => 'required|string|max:1000',
            'specializations' => 'required|array',
            'service_offerings' => 'required|array',
            'years_in_business' => 'required|integer|min:0|max:50',
            'business_street' => 'required|string|max:255',
            'business_city' => 'required|string|max:255',
            'business_state' => 'required|string|max:255',
            'business_country' => 'required|string|max:255',
            'business_postal_code' => 'required|string|max:255',
            'team_members' => 'required|array',
            'regional_expertise' => 'required|array',
        ]);

        $user = $request->user();
        $user->ambassador()->create($request->all());

        return response()->json([
            'success' => true,
            'data' => ['item' => $user->ambassador],
        ]);
    }
}

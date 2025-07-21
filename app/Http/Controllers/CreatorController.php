<?php

namespace App\Http\Controllers;

use App\Models\Creator;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use App\Notifications\ApplicationStatusNotification;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Schema;

class CreatorController extends CrudController
{
    protected function getModel(): string
    {
        return Creator::class;
    }

    protected function getTable(): string
    {
        return 'creators';
    }

    protected function getModelClass(): string
    {
        return Creator::class;
    }

    protected function getRelations(): array
    {
        return ['user'];
    }

    protected function getReadAllQuery(): Builder
    {
        return $this->model()->with('user.profile');
    }

    public function readAll(Request $request)
    {
        $query = $this->getReadAllQuery();
        $filters = $request->input('filters', []);
        $perPage = $request->input('per_page', 50);

        // Custom filters
        foreach ($filters as $filter) {
            $filter = is_string($filter) ? json_decode($filter, true) : $filter;
            if (isset($filter['filterColumn'], $filter['filterOperator'], $filter['filterValue'])) {
                if ($filter['filterColumn'] === 'skills' && $filter['filterOperator'] === 'contains') {
                    $query->whereJsonContains('skills', $filter['filterValue']);
                } elseif ($filter['filterColumn'] === 'average_rating' && $filter['filterOperator'] === 'gte') {
                    $query->where('average_rating', '>=', $filter['filterValue']);
                } elseif ($filter['filterColumn'] === 'availability' && $filter['filterOperator'] === 'equals') {
                    $query->where('availability', $filter['filterValue']);
                } elseif ($filter['filterColumn'] === 'search' && $filter['filterOperator'] === 'contains') {
                    $search = $filter['filterValue'];
                    $query->where(function ($q) use ($search) {
                        // Search in related user fields
                        $q->orWhereHas('user', function ($uq) use ($search) {
                            $uq->where('first_name', 'like', "%$search%")
                                ->orWhere('last_name', 'like', "%$search%")
                                ->orWhere('email', 'like', "%$search%")
                                ->orWhere('username', 'like', "%$search%")
                            ;
                        });
                        // Search in related user profile bio
                        $q->orWhereHas('user.profile', function ($pq) use ($search) {
                            $pq->where('bio', 'like', "%$search%");
                        });
                        // Optionally, search in portfolio if it's a JSON/text column
                        if (Schema::hasColumn('creators', 'portfolio')) {
                            $q->orWhere('portfolio', 'like', "%$search%");
                        }
                    });
                }
                // Add more custom filters as needed
            }
        }

        $items = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => [
                'items' => $items->items(),
                'meta' => [
                    'current_page' => $items->currentPage(),
                    'last_page' => $items->lastPage(),
                    'total_items' => $items->total(),
                ],
            ],
        ]);
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
            Log::error('Error caught in function CreatorController.readOne: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json(['success' => false, 'errors' => [__('common.unexpected_error')]]);
        }
    }

    protected function afterReadAll(LengthAwarePaginator $items)
    {
        $statusRank = [
            'FEATURED'    => 4,
            'VERIFIED'    => 3,
            'UNVERIFIED'  => 2,
            'PENDING'     => 1,
        ];

        $items->setCollection(
            collect($items->items())
                ->sortByDesc(fn($c) => $statusRank[$c->verification_status] ?? 0)
                ->values()
        );
    }

    protected function afterCreateOne($model, Request $request)
    {
        // Send email notification when application is first submitted
        if ($model->user) {
            $model->user->notify(new ApplicationStatusNotification(
                'creator',
                'submitted',
                null
            ));
        }
    }

    public function updateApplicationStatus($id, Request $request)
    {
        try {
            $request->validate([
                'verification_status' => 'required|in:PENDING,VERIFIED,UNVERIFIED,FEATURED',
                'review_notes' => 'nullable|string|max:1000',
            ]);

            $creator = $this->model()->with('user')->find($id);

            if (!$creator) {
                return response()->json([
                    'success' => false,
                    'errors' => [__($this->getTable() . '.not_found')],
                ]);
            }

            $oldStatus = $creator->verification_status;
            $newStatus = $request->verification_status;

            $creator->update([
                'verification_status' => $newStatus,
                'review_notes' => $request->review_notes,
                'reviewed_at' => now(),
                'reviewed_by' => $request->user() ? $request->user()->id : null,
            ]);

            // Send email notification to the user
            if ($oldStatus !== $newStatus && $creator->user) {
                $status = match ($newStatus) {
                    'VERIFIED', 'FEATURED' => 'approved',
                    'UNVERIFIED' => 'rejected',
                    'PENDING' => 'pending',
                    default => 'updated',
                };

                $creator->user->notify(new ApplicationStatusNotification(
                    'creator',
                    $status,
                    $request->review_notes
                ));
            }

            return response()->json([
                'success' => true,
                'data' => ['item' => $creator->load('user')],
            ]);
        } catch (\Exception $e) {
            Log::error('Error caught in function CreatorController.updateApplicationStatus: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json(['success' => false, 'errors' => [__('common.unexpected_error')]]);
        }
    }

    public function getPendingApplications(Request $request)
    {
        try {
            $query = $this->model()->with('user')->whereIn('verification_status', ['UNVERIFIED', 'PENDING']);
            Log::info($query->toSql());

            $perPage = $request->get('per_page', 15);
            $applications = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $applications,
            ]);
        } catch (\Exception $e) {
            Log::error('Error caught in function CreatorController.getPendingApplications: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json(['success' => false, 'errors' => [__('common.unexpected_error')]]);
        }
    }
}

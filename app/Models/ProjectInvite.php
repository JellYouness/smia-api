<?php

namespace App\Models;

use App\Enums\PROJECT_INVITE_STATUS;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Validation\Rule;

class ProjectInvite extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'creator_id',
        'message',
        'status',
        'expires_at',
        'accepted_at',
        'declined_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'accepted_at' => 'datetime',
        'declined_at' => 'datetime',
    ];

    protected static function booted()
    {
        parent::booted();

        static::created(function ($projectInvite) {
            // Give read permission to the invited creator
            if ($projectInvite->creator && $projectInvite->creator->user && $projectInvite->project) {
                $user = $projectInvite->creator->user;
                $user->givePermission('projects.'.$projectInvite->project_id.'.read');
            }
        });

        static::updated(function ($projectInvite) {
            $originalStatus = $projectInvite->getOriginal('status');
            $newStatus = $projectInvite->status;

            if ($originalStatus !== $newStatus) {
                if ($projectInvite->creator && $projectInvite->creator->user && $projectInvite->project) {
                    $user = $projectInvite->creator->user;

                    if ($newStatus === PROJECT_INVITE_STATUS::ACCEPTED->value) {
                        // If status is accepted, keep the permission (do nothing)
                    } elseif (in_array($newStatus, [PROJECT_INVITE_STATUS::DECLINED->value, PROJECT_INVITE_STATUS::EXPIRED->value])) {
                        $user->removePermission('projects.'.$projectInvite->project_id.'.read');
                    }
                }
            }
        });

        static::deleted(function ($projectInvite) {
            // Remove read permission from the invited creator
            if ($projectInvite->creator && $projectInvite->creator->user && $projectInvite->project) {
                $user = $projectInvite->creator->user;
                $user->removePermission('projects.'.$projectInvite->project_id.'.read');
            }
        });
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(Creator::class);
    }

    public function proposal(): HasOne
    {
        return $this->hasOne(ProjectProposal::class, 'invite_id');
    }

    public static function rules($id = null): array
    {
        $id ??= request()->route('id');
        $required = $id ? 'sometimes|required' : 'required';

        return [
            'project_id' => "$required|exists:projects,id",
            'creator_id' => "$required|exists:creators,id",
            'message' => 'nullable|string',
            'status' => [
                'sometimes',
                Rule::in(array_column(PROJECT_INVITE_STATUS::cases(), 'value')),
            ],
            'expires_at' => 'nullable|date|after:now',
        ];
    }
}

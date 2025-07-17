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
    'expires_at'  => 'datetime',
    'accepted_at' => 'datetime',
    'declined_at' => 'datetime',
  ];

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
      'message'    => 'nullable|string',
      'status'     => [
        'sometimes',
        Rule::in(array_column(PROJECT_INVITE_STATUS::cases(), 'value')),
      ],
      'expires_at' => 'nullable|date|after:now',
    ];
  }
}

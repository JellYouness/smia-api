<?php

namespace App\Models;

use App\Enums\PROJECT_PROPOSAL_STATUS;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\Rule;

class ProjectProposal extends Model
{
  use HasFactory;

  protected $fillable = [
    'invite_id',
    'project_id',
    'creator_id',
    'amount',
    'currency',
    'duration_days',
    'cover_letter',
    'attachments',
    'status',
    'meta',
  ];

  protected $casts = [
    'attachments' => 'array',
    'meta'        => 'array',
    'amount'      => 'decimal:2',
  ];

  protected $with = [
    'comments',
  ];

  public function invite(): BelongsTo
  {
    return $this->belongsTo(ProjectInvite::class, 'invite_id');
  }

  public function project(): BelongsTo
  {
    return $this->belongsTo(Project::class);
  }

  public function creator(): BelongsTo
  {
    return $this->belongsTo(Creator::class);
  }

  public function comments(): HasMany
  {
    return $this->hasMany(ProposalComment::class, 'proposal_id');
  }

  public static function rules($id = null): array
  {
    $id ??= request()->route('id');
    $required = $id ? 'sometimes|required' : 'required';

    return [
      'invite_id'     => [
        "$required",
        'exists:project_invites,id',
        "unique:project_proposals,invite_id",
      ],
      'project_id'    => 'prohibited',
      'creator_id'    => 'prohibited',
      'amount'        => 'nullable|numeric|min:0',
      'currency'      => 'nullable|string|size:3',
      'duration_days' => 'nullable|integer|min:1',
      'cover_letter'  => 'nullable|string',
      'attachments'   => 'nullable|array',
      'status'        => [
        'sometimes',
        'string',
        Rule::in(array_column(PROJECT_PROPOSAL_STATUS::cases(), 'value'))
      ],
      'meta'          => 'nullable|array',
    ];
  }
}

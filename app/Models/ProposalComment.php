<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\Rule;

class ProposalComment extends Model
{
  use HasFactory;

  protected $fillable = [
    'proposal_id',
    'user_id',
    'parent_id',
    'body',
    'attachments',
    'read_at',
  ];

  protected $casts = [
    'attachments' => 'array',
    'read_at'     => 'datetime',
  ];

  protected $with = [
    'user',
    'children.user',
  ];

  public function proposal(): BelongsTo
  {
    return $this->belongsTo(ProjectProposal::class, 'proposal_id');
  }

  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class);
  }

  public function parent(): BelongsTo
  {
    return $this->belongsTo(ProposalComment::class, 'parent_id');
  }

  public function children(): HasMany
  {
    return $this->hasMany(ProposalComment::class, 'parent_id');
  }

  public static function rules($id = null): array
  {
    $id ??= request()->route('id');
    $required = $id ? 'sometimes|required' : 'required';

    return [
      'proposal_id' => 'sometimes|exists:project_proposals,id',
      'user_id'     => 'sometimes|exists:users,id',

      'parent_id'   => [
        'nullable',
        Rule::exists('proposal_comments', 'id')
          ->where('proposal_id', request()->route('proposalId'))
          ->whereNull('parent_id'),
      ],

      'body'        => 'required|string',
      'attachments' => 'nullable|array',
      'read_at'     => 'nullable|date',
    ];
  }
}

<?php

namespace App\Models;

use App\Enums\CREATOR_PROJECT_PERMISSION;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\Rule;

class MediaPostAssignment extends BaseModel
{
  use HasFactory;

  public static $cacheKey = 'media_post_assignments';

  protected $fillable = [
    'post_id',
    'creator_id',
    'role',
    'assigned_at',
  ];

  public function post(): BelongsTo
  {
    return $this->belongsTo(MediaPost::class, 'post_id');
  }

  public function creator(): BelongsTo
  {
    return $this->belongsTo(Creator::class, 'creator_id');
  }

  public static function rules($id = null): array
  {
    $id = $id ?? request()->route('id');
    $required = $id ? 'sometimes|required' : 'required';
    return [
      'post_id' => "$required|exists:media_posts,id",
      'creator_id' => "$required|exists:creators,id",
      'role' => ['nullable', 'string', Rule::in(array_column(CREATOR_PROJECT_PERMISSION::cases(), 'value'))],
      'assigned_at' => 'nullable|date',
    ];
  }
}

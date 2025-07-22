<?php

namespace App\Models;

use App\Enums\MEDIA_POST_STATUS;
use App\Enums\MEDIA_POST_PRIORITY;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\Rule;

class MediaPost extends BaseModel
{
  use HasFactory;

  public static $cacheKey = 'media_posts';

  protected $fillable = [
    'project_id',
    'title',
    'description',
    'status',
    'priority',
    'position',
    'due_date',
  ];

  protected $casts = [
    'due_date' => 'date',
  ];

  public function project(): BelongsTo
  {
    return $this->belongsTo(Project::class);
  }

  public function assignments(): HasMany
  {
    return $this->hasMany(MediaPostAssignment::class, 'post_id');
  }

  public function assets(): HasMany
  {
    return $this->hasMany(MediaPostAsset::class, 'post_id');
  }

  public function reviews(): HasMany
  {
    return $this->hasMany(MediaPostReview::class, 'post_id');
  }

  public function comments(): HasMany
  {
    return $this->hasMany(MediaPostComment::class, 'post_id');
  }

  public static function rules($id = null): array
  {
    $id = $id ?? request()->route('id');
    $required = $id ? 'sometimes|required' : 'required';
    return [
      'project_id' => "$required|exists:projects,id",
      'title' => 'nullable|string|max:255',
      'description' => 'nullable|string',
      'status' => ['nullable', 'string', Rule::in(array_column(MEDIA_POST_STATUS::cases(), 'value'))],
      'priority' => ['nullable', 'string', Rule::in(array_column(MEDIA_POST_PRIORITY::cases(), 'value'))],
      'position' => 'nullable|integer',
      'due_date' => 'nullable|date',
    ];
  }
}

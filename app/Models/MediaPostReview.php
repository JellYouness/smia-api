<?php

namespace App\Models;

use App\Enums\MEDIA_POST_REVIEW_DECISION;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\Rule;

class MediaPostReview extends BaseModel
{
  use HasFactory;

  public static $cacheKey = 'media_post_reviews';

  protected $fillable = [
    'post_id',
    'reviewer_id',
    'reviewer_type',
    'version_id',
    'decision',
    'comment',
  ];

  protected $with = [
    'reviewer',
    'version',
  ];

  public function post(): BelongsTo
  {
    return $this->belongsTo(MediaPost::class, 'post_id');
  }

  public function reviewer()
  {
    return $this->morphTo();
  }

  public function version(): BelongsTo
  {
    return $this->belongsTo(MediaPostVersion::class, 'version_id');
  }

  public static function rules($id = null): array
  {
    $id = $id ?? request()->route('id');
    $required = $id ? 'sometimes|required' : 'required';
    return [
      'post_id' => "$required|exists:media_posts,id",
      'reviewer_id' => "$required|integer",
      'reviewer_type' => "$required|string|in:AMBASSADOR,CLIENT",
      'version_id'  => [
        $required,
        Rule::exists('media_post_versions', 'id')
          ->where('post_id', request('post_id')),
      ],
      'decision' => ['nullable', 'string', Rule::in(array_column(MEDIA_POST_REVIEW_DECISION::cases(), 'value'))],
      'comment' => 'nullable|string',
    ];
  }
}

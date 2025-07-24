<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MediaPostComment extends BaseModel
{
  use HasFactory;

  public static $cacheKey = 'media_post_comments';

  protected $fillable = [
    'post_id',
    'asset_id',
    'author_id',
    'body',
    'timecode',
  ];

  protected $with = [
    'author',
  ];

  public function post(): BelongsTo
  {
    return $this->belongsTo(MediaPost::class, 'post_id');
  }

  public function asset(): BelongsTo
  {
    return $this->belongsTo(MediaPostAsset::class, 'asset_id');
  }

  public function author(): BelongsTo
  {
    return $this->belongsTo(User::class, 'author_id');
  }

  public static function rules($id = null): array
  {
    $id = $id ?? request()->route('id');
    $required = $id ? 'sometimes|required' : 'required';
    return [
      'asset_id' => 'nullable|exists:media_post_assets,id',
      'author_id' => "$required|exists:users,id",
      'body' => "$required|string",
      'timecode' => 'nullable|integer',
    ];
  }
}

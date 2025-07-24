<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MediaPostAsset extends BaseModel
{
  use HasFactory;

  public static $cacheKey = 'media_post_assets';

  protected $fillable = [
    'post_id',
    'version',
    'upload_id',
    'mime_type',
    'uploaded_by',
  ];

  protected $with = [
    'uploader',
    'upload',
  ];

  public function post(): BelongsTo
  {
    return $this->belongsTo(MediaPost::class, 'post_id');
  }

  public function uploader(): BelongsTo
  {
    return $this->belongsTo(Creator::class, 'uploaded_by');
  }

  public function upload(): BelongsTo
  {
    return $this->belongsTo(Upload::class, 'upload_id');
  }

  public static function rules($id = null): array
  {
    $id = $id ?? request()->route('id');
    $required = $id ? 'sometimes|required' : 'required';
    return [
      'post_id' => "$required|exists:media_posts,id",
      'version' => "$required|integer",
      'upload_id' => "$required|exists:uploads,id",
      'mime_type'   => "$required|string|mimetypes:image/*,video/*,audio/*,application/pdf",
      'uploaded_by' => 'nullable|exists:creators,id',
    ];
  }
}

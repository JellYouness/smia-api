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
    'version_id',
    'is_reference',
    'upload_id',
    'mime_type',
    'uploaded_by',
  ];

  protected $with = [
    'uploader',
    'upload',

    'version',
  ];

  public function post(): BelongsTo
  {
    return $this->belongsTo(MediaPost::class, 'post_id');
  }

  public function uploader(): BelongsTo
  {
    return $this->belongsTo(User::class, 'uploaded_by');
  }

  public function upload(): BelongsTo
  {
    return $this->belongsTo(Upload::class, 'upload_id');
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
      'version_id' => 'nullable|exists:media_post_versions,id',
      'is_reference' => 'boolean',
      'upload_id' => "$required|exists:uploads,id",
      'mime_type'   => [
        $required,
        'string',
        'starts_with:image/,video/,audio/,application/pdf',
      ],
      'uploaded_by' => 'nullable|exists:users,id',
    ];
  }
}

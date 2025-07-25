<?php

namespace App\Models;

use App\Enums\VERSION_STATUS;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\Rule;

class MediaPostVersion extends BaseModel
{
  use HasFactory;

  protected $fillable = [
    'post_id',
    'number',
    'status',
    'created_by',
  ];

  protected $with = [
    'creator',
  ];

  public function post(): BelongsTo
  {
    return $this->belongsTo(MediaPost::class);
  }
  public function files(): HasMany
  {
    return $this->hasMany(MediaPostAsset::class, 'version_id');
  }
  public function creator(): BelongsTo
  {
    return $this->belongsTo(Creator::class, 'created_by');
  }

  public static function rules($id = null): array
  {
    return [
      'post_id' => 'required|exists:media_posts,id',
      'number' => 'required|integer|min:1',
      'status' => ['required', 'string', Rule::in(array_column(VERSION_STATUS::cases(), 'value'))],
      'created_by' => 'nullable|exists:creators,id',
    ];
  }
}

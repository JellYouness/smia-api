<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MediaPostReview extends BaseModel
{
    use HasFactory;

    public static $cacheKey = 'media_post_reviews';

    protected $fillable = [
        'post_id',
        'reviewer_id',
        'decision',
        'comment',
        'created_at',
    ];

    public $timestamps = false;

    public function post(): BelongsTo
    {
        return $this->belongsTo(MediaPost::class, 'post_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }
} 
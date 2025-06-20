<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Creator extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'skills',
        'verification_status',
        'portfolio',
        'experience',
        'hourly_rate',
        'availability',
        'average_rating',
        'rating_count',
        'regional_expertise',
        'languages',
        'is_journalist',
        'media_types',
        'certifications',
        'biography',
        'equipment_info',
    ];

    protected $casts = [
        'social_media_links' => 'array',
        'preferred_industries' => 'array',
        'skills' => 'array',
        'equipment' => 'array',
        'software' => 'array',
        'languages' => 'array',
        'certifications' => 'array',
        'preferred_project_types' => 'array',
        'working_hours' => 'array',
        'insurance_info' => 'array',
    ];

    /**
     * Get the user that owns the creator.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the projects for the creator.
     */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }
}

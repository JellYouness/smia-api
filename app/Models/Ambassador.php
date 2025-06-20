<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Project;

class Ambassador extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'bio',
        'specialization',
        'portfolio_url',
        'social_media_links',
        'years_of_experience',
        'hourly_rate',
        'availability_status',
        'preferred_industries',
        'project_count',
        'rating',
        'completed_projects',
        'skills',
        'equipment',
        'software',
        'languages',
        'certifications',
        'preferred_project_types',
        'working_hours',
        'travel_preference',
        'insurance_info',
        'ambassador_level',
        'referral_count',
        'commission_rate',
        'total_earnings',
        'active_campaigns',
        'performance_metrics',
        'promotional_materials',
        'training_completed',
        'support_team',
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
        'active_campaigns' => 'array',
        'performance_metrics' => 'array',
        'promotional_materials' => 'array',
        'training_completed' => 'array',
        'support_team' => 'array',
    ];

    /**
     * Get the user that owns the ambassador.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the projects for the ambassador.
     */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }
}

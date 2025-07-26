<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use App\Models\Project;
use App\Models\TeamMember;

class Ambassador extends BaseModel
{
    use HasFactory;

    public static $cacheKey = 'ambassadors';

    protected $fillable = [
        'user_id',
        'team_name',
        'specializations',
        'regional_expertise',
        'service_offerings',
        'client_count',
        'project_capacity',
        'application_status',
        'application_date',
        'verification_documents',
        'commission_rate',
        'team_description',
        'featured_work',
        'years_in_business',
        'business_street',
        'business_city',
        'business_state',
        'business_postal_code',
        'business_country',
        'review_notes',
        'reviewed_at',
        'reviewed_by',
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
        'featured_work' => 'array',
        'specializations' => 'array',
        'regional_expertise' => 'array',
        'service_offerings' => 'array',
        'verification_documents' => 'array',
    ];

    protected static function booted()
    {
        parent::booted();
        static::saved(function ($ambassador) {
            $user = $ambassador->user()->with('profile', 'creator', 'client')->first();
            if ($user && $user->profile) {
                $completeness = $user->profile->calculateCompleteness($user);
                if ($user->profile->profile_completeness !== $completeness) {
                    $user->profile->profile_completeness = $completeness;
                    $user->profile->saveQuietly();
                }
            }
        });
    }

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

    /**
     * Get the team members for the ambassador.
     */
    public function teamMembers(): HasMany
    {
        return $this->hasMany(TeamMember::class);
    }

    /**
     * Get the users who are team members of this ambassador.
     */
    public function teamMemberUsers(): HasManyThrough
    {
        return $this->hasManyThrough(User::class, TeamMember::class, 'ambassador_id', 'id', 'id', 'user_id');
    }

    /**
     * Validation rules for creating or updating an Ambassador.
     * If $id is provided, use 'sometimes|required' for update context.
     */
    public static function rules($id = null): array
    {
        $id = $id ?? request()->route('id');
        $required = $id ? 'sometimes|required' : 'required';
        return [
            'user_id' => $id ? 'sometimes|exists:users,id' : 'required|exists:users,id',
            'team_name' => 'nullable|string|max:255',
            'specializations' => 'nullable|array',
            'specializations.*' => 'string|max:255',
            'regional_expertise' => 'nullable|array',
            'regional_expertise.*.region' => 'required|string|max:255',
            'regional_expertise.*.proficiency_level' => 'required|in:BEGINNER,INTERMEDIATE,EXPERT',
            'service_offerings' => 'nullable|array',
            'service_offerings.*' => 'string|max:255',
            'client_count' => 'nullable|integer|min:0',
            'project_capacity' => 'nullable|integer|min:0',
            'application_status' => 'nullable|in:PENDING,APPROVED,REJECTED',
            'application_date' => 'nullable|date',
            'verification_documents' => 'nullable|array',
            'verification_documents.*' => 'string|max:500',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'team_description' => 'nullable|string|max:1000',
            'featured_work' => 'nullable|array',
            'featured_work.*.project_id' => 'required|integer|exists:projects,id',
            'featured_work.*.description' => 'required|string|max:500',
            'years_in_business' => 'nullable|integer|min:0|max:100',
            'business_street' => 'nullable|string|max:255',
            'business_city' => 'nullable|string|max:255',
            'business_state' => 'nullable|string|max:255',
            'business_postal_code' => 'nullable|string|max:20',
            'business_country' => 'nullable|string|max:255',
            'review_notes' => 'nullable|string|max:1000',
            'reviewed_at' => 'nullable|date',
            'reviewed_by' => 'nullable|integer|exists:users,id',
        ];
    }
}

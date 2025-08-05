<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Creator extends BaseModel
{
  use HasFactory;

  public static $cacheKey = 'creators';

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
    'equipment_info',
    'education',
    'professional_background',
    'achievements',
    'review_notes',
    'reviewed_at',
    'reviewed_by',
    'is_profile_complete',
  ];

  protected $casts = [
    'social_media_links' => 'array',
    'preferred_industries' => 'array',
    'skills' => 'array',
    'media_types' => 'array',
    'regional_expertise' => 'array',
    'equipment' => 'array',
    'software' => 'array',
    'languages' => 'array',
    'certifications' => 'array',
    'preferred_project_types' => 'array',
    'working_hours' => 'array',
    'insurance_info' => 'array',
    'education' => 'array',
    'professional_background' => 'array',
    'achievements' => 'array',
    'portfolio' => 'array',
    'equipment_info' => 'array',
    'hourly_rate' => 'float',
    'average_rating' => 'float',
    'rating_count' => 'integer',
    'is_profile_complete' => 'boolean',
  ];

  protected $with = [
    'user',
  ];

  protected static function booted()
  {
    parent::booted();
    static::saved(function ($creator) {
      $user = $creator->user()->with('profile', 'client', 'ambassador')->first();
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

  public function invites(): HasMany
  {
    return $this->hasMany(ProjectInvite::class);
  }

  public function proposals(): HasMany
  {
    return $this->hasMany(ProjectProposal::class);
  }

  /**
   * Validation rules for creating or updating a Creator.
   * If $id is provided, use 'sometimes|required' for update context.
   */
  public static function rules($id = null): array
  {
    $id = $id ?? request()->route('id');
    $required = $id ? 'sometimes|required' : 'required';
    return [
      'user_id' => $id ? 'sometimes|exists:users,id' : 'required|exists:users,id',
      'bio' => "$required|string",
      'specialization' => "$required|in:VIDEO,PHOTOGRAPHY,GRAPHIC_DESIGN,ANIMATION,OTHER",
      'portfolio_url' => 'nullable|url|max:255',
      'social_media_links' => 'nullable|array',
      'equipment' => 'nullable|array',
      'availability' => 'nullable|array',
      'pricing' => 'nullable|array',
      'is_verified' => 'boolean',
      'verification_documents' => 'nullable|array',
      'rating' => 'nullable|numeric|min:0|max:5',
      'total_projects' => 'integer|min:0',
      'completed_projects' => 'integer|min:0',
      'cancelled_projects' => 'integer|min:0',
      'is_featured' => 'boolean',
      'featured_until' => 'nullable|date',
      'payment_info' => 'nullable|array',
      'tax_info' => 'nullable|array',
      'preferred_communication' => 'nullable|in:EMAIL,PHONE,VIDEO_CALL,IN_PERSON',
      'languages' => 'nullable|array',
      'travel_radius' => 'nullable|integer|min:0',
      'insurance_info' => 'nullable|array',
      'contract_templates' => 'nullable|array',
      'is_available' => 'boolean',
      'unavailable_until' => 'nullable|date',
      'unavailable_reason' => 'nullable|string',
    ];
  }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Project;

class Ambassador extends BaseModel
{
  use HasFactory;

  public static $cacheKey = 'ambassadors';

  protected $fillable = [
    'user_id',
    'team_members',
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
    'team_members' => 'array',
    'verification_documents' => 'array',
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
      'referral_code' => $id ? 'sometimes|required|string|unique:ambassadors,referral_code,' . $id : 'required|string|unique:ambassadors,referral_code',
      'referral_count' => 'integer|min:0',
      'commission_rate' => $id ? 'sometimes|required|numeric|min:0|max:100' : 'required|numeric|min:0|max:100',
      'total_earnings' => 'numeric|min:0',
      'payment_history' => 'nullable|array',
      'promotion_materials' => 'nullable|array',
      'target_audience' => 'nullable|array',
      'promotion_channels' => 'nullable|array',
      'performance_metrics' => 'nullable|array',
      'is_active' => 'boolean',
      'activation_date' => 'nullable|date',
      'deactivation_date' => 'nullable|date',
      'deactivation_reason' => 'nullable|string',
    ];
  }
}

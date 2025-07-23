<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends BaseModel
{
  use HasFactory;

  public static $cacheKey = 'clients';

  protected $fillable = [
    'user_id',
    'company_name',
    'company_size',
    'industry',
    'website_url',
    'billing_street',
    'billing_city',
    'billing_state',
    'billing_postal_code',
    'billing_country',
    'tax_identifier',
    'budget',
    'languages',
    'preferred_creators',
    'project_count',
    'default_project_settings',
  ];

  protected $casts = [
    'languages' => 'array',
    'preferred_creators' => 'array',
    'default_project_settings' => 'array',
  ];

  /**
   * Get the user that owns the client.
   */
  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class);
  }

  /**
   * Get the projects for the client.
   */
  public function projects(): HasMany
  {
    return $this->hasMany(Project::class);
  }

  /**
   * Validation rules for creating or updating a Client.
   * If $id is provided, use 'sometimes|required' for update context.
   */
  public static function rules($id = null): array
  {
    $id = $id ?? request()->route('id');
    $required = $id ? 'sometimes|required' : 'required';
    return [
      'user_id' => $id ? 'sometimes|exists:users,id' : 'required|exists:users,id',
      'company_name' => "$required|string|max:255",
      'company_size' => "$required|in:INDIVIDUAL,SMALL,MEDIUM,LARGE,ENTERPRISE",
      'industry' => "$required|in:MEDIA,EDUCATION,HEALTHCARE,TECHNOLOGY,FINANCE,ENTERTAINMENT,OTHER",
      'website_url' => 'nullable|url|max:255',
      'billing_street' => "$required|string|max:255",
      'billing_city' => "$required|string|max:255",
      'billing_state' => "$required|string|max:255",
      'billing_postal_code' => "$required|string|max:20",
      'billing_country' => "$required|string|max:255",
      'tax_identifier' => 'nullable|string|max:50',
      'budget' => "$required|in:SMALL,MEDIUM,LARGE,ENTERPRISE",
      'languages' => 'nullable|array',
      'preferred_creators' => 'nullable|array',
      'default_project_settings' => 'nullable|array',
    ];
  }
}

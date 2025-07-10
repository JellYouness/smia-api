<?php

namespace App\Models;

use App\Enums\Language;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserProfile extends Model
{
  use HasFactory;

  protected $fillable = [
    'user_id',
    'first_name',
    'last_name',
    'phone_number',
    'address',
    'city',
    'state',
    'country',
    'postal_code',
    'profile_picture',
    'bio',
    'title',
    'date_of_birth',
    'gender',
    'preferred_language',
    'timezone',
    'notification_preferences',
    'privacy_settings',
    'social_media_links',
    'emergency_contact',
    'preferences',
    'contact_email',
    'contact_phone',
    'profile_visibility',
    'profile_completeness',
    'cover_image',
    'display_name',
    'audio_introduction',
  ];

  protected $casts = [
    'date_of_birth' => 'date',
    'notification_preferences' => 'array',
    'privacy_settings' => 'array',
    'social_media_links' => 'array',
    'emergency_contact' => 'array',
    'preferences' => 'array',
    'last_updated' => 'datetime',
  ];

  /**
   * Get the user that owns the profile.
   */
  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class);
  }

  /**
   * Validation rules for creating or updating a UserProfile.
   * If $id is provided, use 'sometimes|required' for update context.
   */
  public static function rules($id = null): array
  {
    $id = $id ?? request()->route('id');
    $required = $id ? 'sometimes|required' : 'required';
    return [
      'user_id' => $id ? 'sometimes|exists:users,id' : 'required|exists:users,id',
      'first_name' => "$required|string|max:255",
      'last_name' => "$required|string|max:255",
      'phone_number' => 'nullable|string|max:20',
      'address' => 'nullable|string|max:255',
      'city' => 'nullable|string|max:255',
      'state' => 'nullable|string|max:255',
      'country' => 'nullable|string|max:255',
      'postal_code' => 'nullable|string|max:20',
      'profile_picture' => $id ? 'nullable|url|max:255' : 'nullable|string|max:255',
      'bio' => 'nullable|string',
      'date_of_birth' => 'nullable|date',
      'gender' => 'nullable|in:MALE,FEMALE,OTHER',
      'preferred_language' => 'nullable|string|in:' . implode(',', array_values(Language::getCodes())),
      'timezone' => 'nullable|string|max:100',
      'notification_preferences' => 'nullable|array',
      'privacy_settings' => 'nullable|array',
      'social_media_links' => 'nullable|array',
      'emergency_contact' => 'nullable|array',
      'preferences' => 'nullable|array',
    ];
  }
}

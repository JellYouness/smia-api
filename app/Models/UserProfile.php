<?php

namespace App\Models;

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
}

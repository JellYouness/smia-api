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
        'date_of_birth',
        'gender',
        'preferred_language',
        'timezone',
        'notification_preferences',
        'privacy_settings',
        'social_media_links',
        'emergency_contact',
        'preferences',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'notification_preferences' => 'array',
        'privacy_settings' => 'array',
        'social_media_links' => 'array',
        'emergency_contact' => 'array',
        'preferences' => 'array',
    ];

    /**
     * Get the user that owns the profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

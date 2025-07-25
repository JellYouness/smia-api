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
        'title',
        'phone_number',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'profile_picture',
        'contact_email',
        'contact_phone',
        'bio',
        'short_bio',
        'date_of_birth',
        'gender',
        'preferred_language',
        'timezone',
        'notification_preferences',
        'privacy_settings',
        'social_media_links',
        'emergency_contact',
        'preferences',
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

    protected static function booted()
    {
        parent::booted();
        static::saved(function ($profile) {
            // Always recalculate and persist completeness after any update
            $user = $profile->user()->with(['creator', 'client', 'ambassador'])->first();
            if ($user) {
                $completeness = $profile->calculateCompleteness($user);
                if ($profile->profile_completeness !== $completeness) {
                    $profile->profile_completeness = $completeness;
                    $profile->saveQuietly(); // Avoid infinite loop
                }
            }
        });
    }

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
            'title' => 'nullable|string|max:255',
            'short_bio' => 'nullable|string|max:200',
            'phone_number' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:20',
            'profile_picture' => $id ? 'nullable|url|max:255' : 'nullable|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:20',
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

    /**
     * Dynamically calculate profile completeness based on filled fields and userType.
     * @param $user (optional) - pass the user model if available for userType-specific logic
     * @return int
     */
    public function calculateCompleteness($user = null): int
    {
        $fields = [
            'bio',
            'short_bio',
            'title',
            'phone_number',
            'address',
            'city',
            'state',
            'country',
            'postal_code',
            'contact_email',
            'contact_phone',
            'profile_picture',
            'date_of_birth',
            'gender',
            'preferred_language',
            'timezone',
            'notification_preferences',
            'privacy_settings',
            'social_media_links',
            'emergency_contact',
            'preferences',
            'profile_visibility',
            // 'cover_image',
            // 'display_name',
            // 'audio_introduction',
        ];
        $filled = 0;
        $total = 0;
        foreach ($fields as $field) {
            $total++;
            if (!empty($this->{$field})) {
                $filled++;
            }
        }

        // Add userType-specific fields only if the related model exists
        if ($user) {
            if ($user->creator) {
                $creatorFields = [
                    'skills',
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
                ];
                $creator = $user->creator;
                foreach ($creatorFields as $field) {
                    $total++;
                    if (!empty($creator->{$field})) {
                        $filled++;
                    }
                }
            }
            if ($user->client) {
                $clientFields = [
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
                $client = $user->client;
                foreach ($clientFields as $field) {
                    $total++;
                    if (!empty($client->{$field})) {
                        $filled++;
                    }
                }
            }
            if ($user->ambassador) {
                $ambassadorFields = [
                    'team_members',
                    'team_name',
                    'specializations',
                    'regional_expertise',
                    'service_offerings',
                    'client_count',
                    'project_capacity',
                    'application_status',
                    'commission_rate',
                    'team_description',
                    'featured_work',
                    'years_in_business',
                    'business_street',
                    'business_city',
                    'business_state',
                    'business_postal_code',
                    'business_country',
                ];
                $ambassador = $user->ambassador;
                foreach ($ambassadorFields as $field) {
                    $total++;
                    if (!empty($ambassador->{$field})) {
                        $filled++;
                    }
                }
            }
        }

        return $total > 0 ? (int) round(($filled / $total) * 100) : 0;
    }
}

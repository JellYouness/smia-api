<?php

namespace App\Http\Controllers;

use App\Models\UserProfile;
use Illuminate\Http\Request;

class UserProfileController extends CrudController
{
    protected function getModel(): string
    {
        return UserProfile::class;
    }

    protected function getTable(): string
    {
        return 'user_profiles';
    }

    protected function getModelClass(): string
    {
        return UserProfile::class;
    }

    protected function getValidationRules(): array
    {
        return [
            'user_id' => 'required|exists:users,id',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone_number' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:20',
            'profile_picture' => 'nullable|string|max:255',
            'bio' => 'nullable|string',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:MALE,FEMALE,OTHER',
            'preferred_language' => 'nullable|in:ENGLISH,SPANISH,FRENCH,GERMAN,ITALIAN,PORTUGUESE',
            'timezone' => 'nullable|string|max:100',
            'notification_preferences' => 'nullable|array',
            'privacy_settings' => 'nullable|array',
            'social_media_links' => 'nullable|array',
            'emergency_contact' => 'nullable|array',
            'preferences' => 'nullable|array',
        ];
    }

    protected function getUpdateValidationRules(): array
    {
        return [
            'first_name' => 'sometimes|required|string|max:255',
            'last_name' => 'sometimes|required|string|max:255',
            'phone_number' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:20',
            'profile_picture' => 'nullable|url|max:255',
            'bio' => 'nullable|string',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:MALE,FEMALE,OTHER',
            'preferred_language' => 'nullable|in:ENGLISH,SPANISH,FRENCH,GERMAN,ITALIAN,PORTUGUESE',
            'timezone' => 'nullable|string|max:100',
            'notification_preferences' => 'nullable|array',
            'privacy_settings' => 'nullable|array',
            'social_media_links' => 'nullable|array',
            'emergency_contact' => 'nullable|array',
            'preferences' => 'nullable|array',
        ];
    }

    protected function getRelations(): array
    {
        return ['user'];
    }
}

<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Database\Seeder;

class UserProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all users
        $users = User::all();

        foreach ($users as $user) {
            UserProfile::create([
                'user_id' => $user->id,
                'title' => fake()->jobTitle(),
                'bio' => fake()->paragraph(),
                'short_bio' => fake()->sentence(),
                'phone_number' => fake()->phoneNumber(),
                'address' => fake()->address(),
                'city' => fake()->city(),
                'state' => fake()->state(),
                'country' => fake()->country(),
                'postal_code' => fake()->postcode(),
                'profile_picture' => fake()->imageUrl(),
                'date_of_birth' => fake()->date(),
                'gender' => fake()->randomElement(['MALE', 'FEMALE', 'OTHER']),
                'social_media_links' => [
                    [
                        'linkedin' => 'https://linkedin.com/in/' . strtolower($user->username),
                        'twitter' => 'https://twitter.com/' . strtolower($user->username),
                        'facebook' => 'https://facebook.com/' . strtolower($user->username)
                    ]
                ],
                'profile_visibility' => 'PUBLIC',
                'profile_completeness' => fake()->numberBetween(50, 100),
                'display_name' => $user->first_name . ' ' . $user->last_name,
                'notification_preferences' => [
                    [
                        'email' => true,
                        'sms' => false,
                        'push' => true,
                        'in_app' => true
                    ]
                ],
                'audio_introduction' => null,
                'preferred_language' => 'UTC',
                'timezone' => 'UTC'
            ]);
        }
    }
}

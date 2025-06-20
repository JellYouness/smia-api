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
                'bio' => fake()->paragraph(),
                'short_bio' => fake()->sentence(),
                'contact_email' => $user->email,
                'contact_phone' => fake()->phoneNumber(),
                'social_links' => json_encode([
                    'linkedin' => 'https://linkedin.com/in/' . strtolower($user->username),
                    'twitter' => 'https://twitter.com/' . strtolower($user->username),
                    'facebook' => 'https://facebook.com/' . strtolower($user->username)
                ]),
                'education' => json_encode([
                    [
                        'degree' => fake()->randomElement(['Bachelor', 'Master', 'PhD']),
                        'field' => fake()->jobTitle(),
                        'institution' => fake()->company(),
                        'year' => fake()->year()
                    ]
                ]),
                'professional_background' => json_encode([
                    [
                        'title' => fake()->jobTitle(),
                        'company' => fake()->company(),
                        'duration' => fake()->numberBetween(1, 10) . ' years',
                        'description' => fake()->paragraph()
                    ]
                ]),
                'achievements' => json_encode([
                    fake()->sentence(),
                    fake()->sentence()
                ]),
                'profile_visibility' => 'PUBLIC',
                'profile_completeness' => fake()->numberBetween(50, 100),
                'display_name' => $user->first_name . ' ' . $user->last_name,
                'notification_preferences' => json_encode([
                    'email' => true,
                    'sms' => false,
                    'push' => true
                ]),
                'audio_introduction' => null
            ]);
        }
    }
}

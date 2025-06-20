<?php

namespace Database\Seeders;

use App\Models\Creator;
use App\Models\User;
use Illuminate\Database\Seeder;

class CreatorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all users with type CREATOR
        $creators = User::where('user_type', 'CREATOR')->get();

        foreach ($creators as $creator) {
            Creator::create([
                'user_id' => $creator->id,
                'skills' => json_encode([
                    fake()->randomElement(['PHOTOGRAPHY', 'VIDEOGRAPHY', 'GRAPHIC_DESIGN', 'ANIMATION', 'AUDIO_PRODUCTION']),
                    fake()->randomElement(['PHOTOGRAPHY', 'VIDEOGRAPHY', 'GRAPHIC_DESIGN', 'ANIMATION', 'AUDIO_PRODUCTION'])
                ]),
                'verification_status' => fake()->randomElement(['UNVERIFIED', 'PENDING', 'VERIFIED', 'FEATURED']),
                'portfolio' => json_encode([
                    [
                        'title' => fake()->sentence(),
                        'description' => fake()->paragraph(),
                        'url' => fake()->url()
                    ],
                    [
                        'title' => fake()->sentence(),
                        'description' => fake()->paragraph(),
                        'url' => fake()->url()
                    ]
                ]),
                'experience' => fake()->numberBetween(1, 20),
                'hourly_rate' => fake()->randomFloat(2, 20, 200),
                'availability' => fake()->randomElement(['AVAILABLE', 'LIMITED', 'UNAVAILABLE', 'BUSY']),
                'average_rating' => fake()->randomFloat(1, 1, 5),
                'rating_count' => fake()->numberBetween(0, 100),
                'regional_expertise' => json_encode([
                    [
                        'region' => fake()->country(),
                        'expertise_level' => fake()->randomElement(['BEGINNER', 'INTERMEDIATE', 'EXPERT'])
                    ],
                    [
                        'region' => fake()->country(),
                        'expertise_level' => fake()->randomElement(['BEGINNER', 'INTERMEDIATE', 'EXPERT'])
                    ]
                ]),
                'languages' => json_encode([
                    [
                        'language' => fake()->randomElement(['ENGLISH', 'FRENCH', 'SPANISH', 'GERMAN', 'ITALIAN']),
                        'proficiency' => fake()->randomElement(['BASIC', 'INTERMEDIATE', 'FLUENT', 'NATIVE'])
                    ],
                    [
                        'language' => fake()->randomElement(['ENGLISH', 'FRENCH', 'SPANISH', 'GERMAN', 'ITALIAN']),
                        'proficiency' => fake()->randomElement(['BASIC', 'INTERMEDIATE', 'FLUENT', 'NATIVE'])
                    ]
                ]),
                'is_journalist' => fake()->boolean(),
                'media_types' => json_encode([
                    fake()->randomElement(['PHOTO', 'VIDEO', 'ARTICLE', 'AUDIO', 'DESIGN', 'OTHER']),
                    fake()->randomElement(['PHOTO', 'VIDEO', 'ARTICLE', 'AUDIO', 'DESIGN', 'OTHER'])
                ]),
                'certifications' => json_encode([
                    [
                        'title' => fake()->sentence(),
                        'issuer' => fake()->company(),
                        'date' => fake()->date()
                    ]
                ]),
                'biography' => fake()->paragraph(3),
                'equipment_info' => json_encode([
                    'cameras' => [fake()->word(), fake()->word()],
                    'lenses' => [fake()->word(), fake()->word()],
                    'audio' => [fake()->word()],
                    'lighting' => [fake()->word(), fake()->word()]
                ])
            ]);
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\Ambassador;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class AmbassadorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all users with type AMBASSADOR
        $ambassadors = User::where('user_type', 'AMBASSADOR')->get();

        foreach ($ambassadors as $ambassador) {
            $creatorIds = User::where('user_type', 'CREATOR')->pluck('id')->toArray();
            $max = min(count($creatorIds), 3);
            $teamMembers = $max > 0 ? Arr::random($creatorIds, rand(1, $max)) : [];
            Ambassador::create([
                'user_id' => $ambassador->id,
                'team_members' => $teamMembers,
                'team_name' => fake()->company(),
                'specializations' => [
                    fake()->randomElement(['VIDEO', 'PHOTOGRAPHY', 'AUDIO', 'WRITING', 'DESIGN', 'OTHER']),
                    fake()->randomElement(['VIDEO', 'PHOTOGRAPHY', 'AUDIO', 'WRITING', 'DESIGN', 'OTHER'])
                ],
                'regional_expertise' => [
                    [
                        'region' => fake()->country(),
                        'proficiency_level' => fake()->randomElement(['BASIC', 'INTERMEDIATE', 'EXPERT'])
                    ]
                ],
                'service_offerings' => [
                    fake()->randomElement(['CONSULTING', 'PRODUCTION', 'EDITING', 'TRAINING', 'DISTRIBUTION'])
                ],
                'client_count' => fake()->numberBetween(0, 100),
                'project_capacity' => fake()->numberBetween(1, 10),
                'application_status' => fake()->randomElement(['PENDING', 'APPROVED', 'REJECTED']),
                'application_date' => now()->subDays(rand(1, 365)),
                'verification_documents' => [
                    fake()->url(),
                    fake()->url()
                ],
                'commission_rate' => fake()->randomFloat(2, 0, 100),
                'team_description' => fake()->paragraphs(3, true),
                'featured_work' => [
                    [
                        'project_id' => fake()->numberBetween(1, 1000),
                        'description' => fake()->sentence()
                    ]
                ],
                'years_in_business' => fake()->numberBetween(0, 20),
                'business_street' => fake()->streetAddress(),
                'business_city' => fake()->city(),
                'business_state' => fake()->state(),
                'business_postal_code' => fake()->postcode(),
                'business_country' => fake()->country()
            ]);
        }
    }
}

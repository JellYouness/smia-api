<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all users with type CLIENT
        $clients = User::where('user_type', 'CLIENT')->get();

        foreach ($clients as $client) {
            Client::firstOrCreate(
                ['user_id' => $client->id],
                [
                    'company_name' => fake()->company(),
                    'company_size' => fake()->randomElement(['INDIVIDUAL', 'SMALL', 'MEDIUM', 'LARGE', 'ENTERPRISE']),
                    'industry' => fake()->randomElement(['MEDIA', 'EDUCATION', 'HEALTHCARE', 'TECHNOLOGY', 'FINANCE', 'ENTERTAINMENT', 'OTHER']),
                    'website_url' => fake()->url(),
                    'billing_street' => fake()->streetAddress(),
                    'billing_city' => fake()->city(),
                    'billing_state' => fake()->state(),
                    'billing_postal_code' => fake()->postcode(),
                    'billing_country' => fake()->country(),
                    'tax_identifier' => fake()->numerify('VAT-#######'),
                    'budget' => fake()->randomElement(['SMALL', 'MEDIUM', 'LARGE', 'ENTERPRISE']),
                    'preferred_creators' => json_encode([]),
                    'project_count' => 0,
                    'default_project_settings' => json_encode([
                        'timeline' => 'standard',
                        'communication_preference' => 'email',
                        'notification_frequency' => 'daily'
                    ])
                ]
            );
        }
    }
}

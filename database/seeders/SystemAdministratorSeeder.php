<?php

namespace Database\Seeders;

use App\Models\SystemAdministrator;
use App\Models\User;
use Illuminate\Database\Seeder;

class SystemAdministratorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all users with type SYSTEM_ADMINISTRATOR
        $admins = User::where('user_type', 'SYSTEM_ADMINISTRATOR')->get();

        foreach ($admins as $admin) {
            SystemAdministrator::create([
                'user_id' => $admin->id,
                'access_level' => fake()->randomElement(['STANDARD', 'ELEVATED', 'SUPER']),
                'admin_permissions' => json_encode([
                    'manage_users' => fake()->boolean(),
                    'manage_projects' => fake()->boolean(),
                    'manage_media' => fake()->boolean(),
                    'manage_distribution' => fake()->boolean(),
                    'manage_billing' => fake()->boolean(),
                    'manage_support' => fake()->boolean(),
                    'manage_system' => fake()->boolean()
                ]),
                'departments' => json_encode([
                    fake()->randomElement(['USERS', 'PROJECTS', 'MEDIA', 'DISTRIBUTION', 'BILLING', 'SUPPORT', 'SYSTEM'])
                ]),
                'audit_log' => true,
                'last_permission_update' => now()->subDays(rand(1, 365)),
                'restricted_ip_access' => fake()->boolean(),
                'allowed_ip_addresses' => json_encode([
                    fake()->ipv4(),
                    fake()->ipv4()
                ]),
                'emergency_contact' => fake()->phoneNumber(),
                'security_clearance' => fake()->randomElement(['BASIC', 'SENSITIVE', 'CONFIDENTIAL'])
            ]);
        }
    }
}

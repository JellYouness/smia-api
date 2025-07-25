<?php

namespace Database\Seeders;

use App\Enums\ROLE;
use App\Models\User;
use App\Models\SystemAdministrator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create or update super admin user
        $superAdmin = User::firstOrCreate(
            [
                'email' => 'superadmin@smia.com',
            ],
            [
                'username' => 'superadmin',
                'password' => Hash::make('password'),
                'first_name' => 'Super',
                'last_name' => 'Admin',
                'user_type' => ROLE::SUPER_ADMIN->value,
                'status' => 'active',
                'email_verified' => true,
                'email_verified_at' => now(),
                'accepted_terms' => true,
            ]
        );

        // Assign super admin role if not already assigned
        if (!$superAdmin->roles()->where('name', ROLE::SUPER_ADMIN->value)->exists()) {
            $superAdmin->assignRole(ROLE::SUPER_ADMIN);
        }

        // Create or update system administrator record
        SystemAdministrator::updateOrCreate(
            [
                'user_id' => $superAdmin->id,
            ],
            [
                'access_level' => 'SUPER',
                'admin_permissions' => json_encode(['*']),
                'departments' => json_encode(['IT']),
                'audit_log' => true,
                'last_permission_update' => now(),
                'restricted_ip_access' => false,
                'allowed_ip_addresses' => json_encode([]),
                'emergency_contact' => null,
                'security_clearance' => 'CONFIDENTIAL',
            ]
        );

        $this->command->info('Super Admin user ensured!');
        $this->command->info('Email: superadmin@smia.com');
        $this->command->info('Password: password');
    }
}

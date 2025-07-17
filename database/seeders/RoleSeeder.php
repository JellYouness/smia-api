<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name' => 'CLIENT',
                'description' => 'Client role with access to project management and creator search',
            ],
            [
                'name' => 'CREATOR',
                'description' => 'Creator role with access to project creation and management',
            ],
            [
                'name' => 'AMBASSADOR',
                'description' => 'Ambassador role with access to referral program and creator management',
            ],
            [
                'name' => 'ADMIN',
                'description' => 'System administrator role with full access to system management',
            ],
            [
                'name' => 'SUPERADMIN',
                'description' => 'Superadmin role with full access to all entities, including system administrators',
            ],
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }
    }
}

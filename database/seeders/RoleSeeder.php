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
                'name' => 'client',
                'description' => 'Client role with access to project management and creator search',
            ],
            [
                'name' => 'creator',
                'description' => 'Creator role with access to project creation and management',
            ],
            [
                'name' => 'ambassador',
                'description' => 'Ambassador role with access to referral program and creator management',
            ],
            [
                'name' => 'system_administrator',
                'description' => 'System administrator role with full access to system management',
            ],
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }
    }
}

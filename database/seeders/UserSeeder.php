<?php

namespace Database\Seeders;

use App\Enums\ROLE;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@smia.fr'],
            [
                'username' => 'admin',
                'password' => bcrypt('admin'),
                'first_name' => 'Admin',
                'last_name' => 'User',
                'status' => 'ACTIVE',
                'user_type' => 'ADMIN',
                'two_factor_enabled' => false,
                'accepted_terms' => true,
                'email_verified' => true,
                'preferred_language' => 'ENGLISH',
                'timezone' => 'UTC'
            ]
        );
        $admin->assignRole(ROLE::SYSTEM_ADMINISTRATOR);

        // Create client user
        $client = User::firstOrCreate(
            ['email' => 'client@smia.fr'],
            [
                'username' => 'client',
                'password' => bcrypt('client'),
                'first_name' => 'John',
                'last_name' => 'Doe',
                'status' => 'ACTIVE',
                'user_type' => 'CLIENT',
                'two_factor_enabled' => false,
                'accepted_terms' => true,
                'email_verified' => true,
                'preferred_language' => 'ENGLISH',
                'timezone' => 'UTC'
            ]
        );
        $client->assignRole(ROLE::CLIENT);

        // Create creator user
        $creator = User::firstOrCreate(
            ['email' => 'creator@smia.fr'],
            [
                'username' => 'creator',
                'password' => bcrypt('creator'),
                'first_name' => 'Jane',
                'last_name' => 'Smith',
                'status' => 'ACTIVE',
                'user_type' => 'CREATOR',
                'two_factor_enabled' => false,
                'accepted_terms' => true,
                'email_verified' => true,
                'preferred_language' => 'ENGLISH',
                'timezone' => 'UTC'
            ]
        );
        $creator->assignRole(ROLE::CREATOR);

        // Create ambassador user
        $ambassador = User::firstOrCreate(
            ['email' => 'ambassador@smia.fr'],
            [
                'username' => 'ambassador',
                'password' => bcrypt('ambassador'),
                'first_name' => 'Michael',
                'last_name' => 'Johnson',
                'status' => 'ACTIVE',
                'user_type' => 'AMBASSADOR',
                'two_factor_enabled' => false,
                'accepted_terms' => true,
                'email_verified' => true,
                'preferred_language' => 'ENGLISH',
                'timezone' => 'UTC'
            ]
        );
        $ambassador->assignRole(ROLE::AMBASSADOR);

        // Create additional test users
        for ($i = 1; $i <= 5; $i++) {
            $userType = ['CLIENT', 'CREATOR', 'AMBASSADOR'][rand(0, 2)];
            $firstName = fake()->firstName();
            $lastName = fake()->lastName();

            $user = User::firstOrCreate(
                ['email' => Str::lower($firstName . $lastName) . '@smia.fr'],
                [
                    'username' => Str::lower($firstName . $lastName),
                    'password' => bcrypt('password'),
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'status' => 'ACTIVE',
                    'user_type' => $userType,
                    'two_factor_enabled' => false,
                    'accepted_terms' => true,
                    'email_verified' => true,
                    'preferred_language' => 'ENGLISH',
                    'timezone' => 'UTC'
                ]
            );

            $role = match ($userType) {
                'CLIENT' => ROLE::CLIENT,
                'CREATOR' => ROLE::CREATOR,
                'AMBASSADOR' => ROLE::AMBASSADOR,
                default => ROLE::CLIENT
            };

            $user->assignRole($role);
        }
    }
}

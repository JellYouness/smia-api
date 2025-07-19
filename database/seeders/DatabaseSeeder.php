<?php

namespace Database\Seeders;

use Database\Seeders\Permissions\AdminPermissionSeeder;
use Database\Seeders\Permissions\CrudPermissionSeeder;
use Database\Seeders\Permissions\PermissionSeeder;
use Database\Seeders\Permissions\SuperAdminPermissionSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
  /**
   * Seed the application's database.
   */
  public function run(): void
  {
    $this->call([
      RoleSeeder::class,
      UserSeeder::class,
      UserProfileSeeder::class,
      ClientSeeder::class,
      CreatorSeeder::class,
      AmbassadorSeeder::class,
      SystemAdministratorSeeder::class,
      SuperAdminSeeder::class,
      ProjectSeeder::class,
      PermissionSeeder::class,
      CrudPermissionSeeder::class,
      AdminPermissionSeeder::class,
      SuperAdminPermissionSeeder::class,
      ChatSeeder::class,
    ]);
  }
}

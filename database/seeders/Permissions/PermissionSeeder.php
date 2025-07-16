<?php

namespace Database\Seeders\Permissions;

use App\Enums\ROLE as ROLE_ENUM;
use App\Models\Role;
use App\Services\ACLService;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
  private ACLService $aclService;

  public function __construct(ACLService $aclService)
  {
    $this->aclService = $aclService;
  }

  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run()
  {
    // Define roles
    $clientRole = $this->aclService->createRole(ROLE_ENUM::CLIENT);
    $creatorRole = $this->aclService->createRole(ROLE_ENUM::CREATOR);
    $ambassadorRole = $this->aclService->createRole(ROLE_ENUM::AMBASSADOR);
    $systemAdministratorRole = $this->aclService->createRole(ROLE_ENUM::SYSTEM_ADMINISTRATOR);
    $superAdminRole = $this->aclService->createRole(ROLE_ENUM::SUPER_ADMIN);

    // Create scoped permissions
    $this->aclService->createScopePermissions('users', ['create', 'read', 'update', 'delete']);

    // Assign permissions to roles
    $this->aclService->assignScopePermissionsToRole($systemAdministratorRole, 'users', ['create', 'read', 'update', 'delete']);
    $this->aclService->assignScopePermissionsToRole($superAdminRole, 'users', ['create', 'read', 'update', 'delete']);
  }

  public function rollback()
  {
    $systemAdministratorRole = Role::where('name', ROLE_ENUM::SYSTEM_ADMINISTRATOR->value)->first();
    $superAdminRole = Role::where('name', ROLE_ENUM::SUPER_ADMIN)->first();

    $this->aclService->removeScopePermissionsFromRole($systemAdministratorRole, 'users', ['create', 'read', 'update', 'delete']);
    $this->aclService->removeScopePermissionsFromRole($superAdminRole, 'users', ['create', 'read', 'update', 'delete']);
    $this->aclService->removeScopePermissionsFromRole($systemAdministratorRole, 'projects', ['create', 'read', 'read_own', 'update', 'delete']);
  }
}

<?php

namespace Database\Seeders\Permissions;

use App\Enums\ROLE as ROLE_ENUM;
use App\Models\Role;
use App\Services\ACLService;
use Illuminate\Database\Seeder;

class CrudPermissionSeeder extends Seeder
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
    $this->aclService->createScopePermissions('projects', [
      'create',
      'read',
      'read_own',
      'update',
      'delete',
      'invite_creator',
      'read_invites',
      'read_own_invites',
      'manage_invites',
      'read_proposals',
      'read_own_proposals',
      'manage_proposals',
      'manage_comments',
    ]);

    $this->aclService->createScopePermissions('project_updates', ['create', 'read', 'read_own', 'update', 'delete',]);

    $this->aclService->createScopePermissions('creators', ['create', 'read', 'read_own', 'update', 'delete']);

    $creatorRole = Role::where('name', ROLE_ENUM::CREATOR)->first();
    $this->aclService->assignScopePermissionsToRole($creatorRole, 'projects', ['read_own', 'read_own_invites', 'manage_invites', 'read_own_proposals', 'manage_comments']);
    $this->aclService->assignScopePermissionsToRole($creatorRole, 'project_updates', ['read_own']);

    $clientRole = Role::where('name', ROLE_ENUM::CLIENT)->first();
    $this->aclService->assignScopePermissionsToRole($clientRole, 'projects', ['create', 'read_own', 'update', 'delete', 'invite_creator', 'read_own_proposals', 'manage_proposals', 'manage_comments']);
    $this->aclService->assignScopePermissionsToRole($clientRole, 'project_updates', ['create', 'read_own', 'read_own']);
    $this->aclService->assignScopePermissionsToRole($clientRole, 'creators', ['read']);

    $ambassadorRole = Role::where('name', ROLE_ENUM::AMBASSADOR)->first();
    $this->aclService->assignScopePermissionsToRole($ambassadorRole, 'projects', ['create', 'read_own', 'update', 'delete']);

    $systemAdministratorRole = Role::where('name', ROLE_ENUM::SYSTEM_ADMINISTRATOR->value)->first();
    $this->aclService->assignScopePermissionsToRole($systemAdministratorRole, 'projects', ['create', 'read', 'update', 'delete']);
  }
}

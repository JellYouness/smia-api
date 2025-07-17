<?php

namespace Database\Seeders\Permissions;

use App\Enums\ROLE as ROLE_ENUM;
use App\Models\Role;
use App\Services\ACLService;
use Illuminate\Database\Seeder;

class SuperAdminPermissionSeeder extends Seeder
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
        // Create permissions for system_administrators table
        $this->aclService->createScopePermissions('system_administrators', ['create', 'read', 'update', 'delete']);

        // Get the super admin role
        $superAdminRole = Role::where('name', ROLE_ENUM::SUPER_ADMIN)->first();

        if ($superAdminRole) {
            // Assign full CRUD permissions to super admins for system_administrators table
            $this->aclService->assignScopePermissionsToRole($superAdminRole, 'system_administrators', ['create', 'read', 'update', 'delete']);

            // Super admins should also have all permissions that system administrators have
            $this->aclService->assignScopePermissionsToRole($superAdminRole, 'creators', ['create', 'read', 'update', 'delete']);
            $this->aclService->assignScopePermissionsToRole($superAdminRole, 'clients', ['create', 'read', 'update', 'delete']);
            $this->aclService->assignScopePermissionsToRole($superAdminRole, 'ambassadors', ['create', 'read', 'update', 'delete']);
            $this->aclService->assignScopePermissionsToRole($superAdminRole, 'users', ['create', 'read', 'update', 'delete']);
            $this->aclService->assignScopePermissionsToRole($superAdminRole, 'projects', ['create', 'read', 'update', 'delete']);
        }
    }

    public function rollback()
    {
        $superAdminRole = Role::where('name', ROLE_ENUM::SUPER_ADMIN)->first();

        if ($superAdminRole) {
            // Remove permissions from super admins
            $this->aclService->removeScopePermissionsFromRole($superAdminRole, 'system_administrators', ['create', 'read', 'update', 'delete']);
            $this->aclService->removeScopePermissionsFromRole($superAdminRole, 'creators', ['create', 'read', 'update', 'delete']);
            $this->aclService->removeScopePermissionsFromRole($superAdminRole, 'clients', ['create', 'read', 'update', 'delete']);
            $this->aclService->removeScopePermissionsFromRole($superAdminRole, 'ambassadors', ['create', 'read', 'update', 'delete']);
            $this->aclService->removeScopePermissionsFromRole($superAdminRole, 'users', ['create', 'read', 'update', 'delete']);
            $this->aclService->removeScopePermissionsFromRole($superAdminRole, 'projects', ['create', 'read', 'update', 'delete']);
        }
    }
}

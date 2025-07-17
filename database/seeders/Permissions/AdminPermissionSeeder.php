<?php

namespace Database\Seeders\Permissions;

use App\Enums\ROLE as ROLE_ENUM;
use App\Models\Role;
use App\Services\ACLService;
use Illuminate\Database\Seeder;

class AdminPermissionSeeder extends Seeder
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
        // Create permissions for creators, clients, and ambassadors tables
        $this->aclService->createScopePermissions('creators', ['create', 'read', 'update', 'delete']);
        $this->aclService->createScopePermissions('clients', ['create', 'read', 'update', 'delete']);
        $this->aclService->createScopePermissions('ambassadors', ['create', 'read', 'update', 'delete']);

        // Get the system administrator role
        $systemAdministratorRole = Role::where('name', ROLE_ENUM::SYSTEM_ADMINISTRATOR->value)->first();

        if ($systemAdministratorRole) {
            // Assign full CRUD permissions to system administrators for all three tables
            $this->aclService->assignScopePermissionsToRole($systemAdministratorRole, 'creators', ['create', 'read', 'update', 'delete']);
            $this->aclService->assignScopePermissionsToRole($systemAdministratorRole, 'clients', ['create', 'read', 'update', 'delete']);
            $this->aclService->assignScopePermissionsToRole($systemAdministratorRole, 'ambassadors', ['create', 'read', 'update', 'delete']);
        }
    }

    public function rollback()
    {
        $systemAdministratorRole = Role::where('name', ROLE_ENUM::SYSTEM_ADMINISTRATOR->value)->first();

        if ($systemAdministratorRole) {
            // Remove permissions from system administrators
            $this->aclService->removeScopePermissionsFromRole($systemAdministratorRole, 'creators', ['create', 'read', 'update', 'delete']);
            $this->aclService->removeScopePermissionsFromRole($systemAdministratorRole, 'clients', ['create', 'read', 'update', 'delete']);
            $this->aclService->removeScopePermissionsFromRole($systemAdministratorRole, 'ambassadors', ['create', 'read', 'update', 'delete']);
        }
    }
}

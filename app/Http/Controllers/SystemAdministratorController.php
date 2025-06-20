<?php

namespace App\Http\Controllers;

use App\Models\SystemAdministrator;
use Illuminate\Http\Request;

class SystemAdministratorController extends CrudController
{
    protected function getModel(): string
    {
        return SystemAdministrator::class;
    }

    protected function getTable(): string
    {
        return 'system_administrators';
    }

    protected function getModelClass(): string
    {
        return SystemAdministrator::class;
    }

    protected function getValidationRules(): array
    {
        return [
            'user_id' => 'required|exists:users,id',
            'role' => 'required|string|max:255',
            'permissions' => 'required|array',
            'access_level' => 'required|in:BASIC,STANDARD,ADVANCED,SUPER_ADMIN',
            'department' => 'required|string|max:255',
            'supervisor_id' => 'nullable|exists:system_administrators,id',
            'is_active' => 'boolean',
        ];
    }

    protected function getUpdateValidationRules(): array
    {
        return [
            'role' => 'sometimes|required|string|max:255',
            'permissions' => 'sometimes|required|array',
            'access_level' => 'sometimes|required|in:BASIC,STANDARD,ADVANCED,SUPER_ADMIN',
            'department' => 'sometimes|required|string|max:255',
            'supervisor_id' => 'nullable|exists:system_administrators,id',
            'is_active' => 'boolean',
        ];
    }

    protected function getRelations(): array
    {
        return ['user', 'supervisor'];
    }
}

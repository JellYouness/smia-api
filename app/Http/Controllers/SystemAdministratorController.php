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

    protected function getRelations(): array
    {
        return ['user', 'supervisor'];
    }
}

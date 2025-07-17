<?php

namespace App\Http\Controllers;

use App\Models\SystemAdministrator;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;

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
        return ['user'];
    }

    protected function getReadAllQuery(): Builder
    {
        return $this->model()->with('user');
    }
}

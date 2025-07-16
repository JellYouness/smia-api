<?php

namespace App\Http\Controllers;

use App\Models\Ambassador;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;

class AmbassadorController extends CrudController
{
    protected function getModel(): string
    {
        return Ambassador::class;
    }

    protected function getTable(): string
    {
        return 'ambassadors';
    }

    protected function getModelClass(): string
    {
        return Ambassador::class;
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

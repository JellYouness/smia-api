<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;

class ClientController extends CrudController
{
    protected function getModel(): string
    {
        return Client::class;
    }

    protected function getTable(): string
    {
        return 'clients';
    }

    protected function getModelClass(): string
    {
        return Client::class;
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

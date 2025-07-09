<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;

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
}

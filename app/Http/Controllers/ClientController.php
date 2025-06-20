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

    protected function getValidationRules(): array
    {
        return [
            'user_id' => 'required|exists:users,id',
            'company_name' => 'required|string|max:255',
            'company_size' => 'required|in:INDIVIDUAL,SMALL,MEDIUM,LARGE,ENTERPRISE',
            'industry' => 'required|in:MEDIA,EDUCATION,HEALTHCARE,TECHNOLOGY,FINANCE,ENTERTAINMENT,OTHER',
            'website_url' => 'nullable|url|max:255',
            'billing_street' => 'required|string|max:255',
            'billing_city' => 'required|string|max:255',
            'billing_state' => 'required|string|max:255',
            'billing_postal_code' => 'required|string|max:20',
            'billing_country' => 'required|string|max:255',
            'tax_identifier' => 'nullable|string|max:50',
            'budget' => 'required|in:SMALL,MEDIUM,LARGE,ENTERPRISE',
            'preferred_creators' => 'nullable|array',
            'default_project_settings' => 'nullable|array',
        ];
    }

    protected function getUpdateValidationRules(): array
    {
        return [
            'company_name' => 'sometimes|required|string|max:255',
            'company_size' => 'sometimes|required|in:INDIVIDUAL,SMALL,MEDIUM,LARGE,ENTERPRISE',
            'industry' => 'sometimes|required|in:MEDIA,EDUCATION,HEALTHCARE,TECHNOLOGY,FINANCE,ENTERTAINMENT,OTHER',
            'website_url' => 'nullable|url|max:255',
            'billing_street' => 'sometimes|required|string|max:255',
            'billing_city' => 'sometimes|required|string|max:255',
            'billing_state' => 'sometimes|required|string|max:255',
            'billing_postal_code' => 'sometimes|required|string|max:20',
            'billing_country' => 'sometimes|required|string|max:255',
            'tax_identifier' => 'nullable|string|max:50',
            'budget' => 'sometimes|required|in:SMALL,MEDIUM,LARGE,ENTERPRISE',
            'preferred_creators' => 'nullable|array',
            'default_project_settings' => 'nullable|array',
        ];
    }

    protected function getRelations(): array
    {
        return ['user'];
    }
}

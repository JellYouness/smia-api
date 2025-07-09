<?php

namespace App\Http\Controllers;

use App\Models\UserProfile;
use Illuminate\Http\Request;

class UserProfileController extends CrudController
{
    protected function getModel(): string
    {
        return UserProfile::class;
    }

    protected function getTable(): string
    {
        return 'user_profiles';
    }

    protected function getModelClass(): string
    {
        return UserProfile::class;
    }

    protected function getRelations(): array
    {
        return ['user'];
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Creator;
use Illuminate\Http\Request;

class CreatorController extends CrudController
{
    protected function getModel(): string
    {
        return Creator::class;
    }

    protected function getTable(): string
    {
        return 'creators';
    }

    protected function getModelClass(): string
    {
        return Creator::class;
    }

    protected function getValidationRules(): array
    {
        return [
            'user_id' => 'required|exists:users,id',
            'specialization' => 'required|in:VIDEO,PHOTOGRAPHY,GRAPHIC_DESIGN,ANIMATION,OTHER',
            'portfolio_url' => 'nullable|url|max:255',
            'social_media_links' => 'nullable|array',
            'equipment' => 'nullable|array',
            'availability' => 'nullable|array',
            'pricing' => 'nullable|array',
            'is_verified' => 'boolean',
            'verification_documents' => 'nullable|array',
            'rating' => 'nullable|numeric|min:0|max:5',
            'total_projects' => 'integer|min:0',
            'completed_projects' => 'integer|min:0',
            'cancelled_projects' => 'integer|min:0',
            'is_featured' => 'boolean',
            'featured_until' => 'nullable|date',
            'payment_info' => 'nullable|array',
            'tax_info' => 'nullable|array',
            'preferred_communication' => 'nullable|in:EMAIL,PHONE,VIDEO_CALL,IN_PERSON',
            'languages' => 'nullable|array',
            'travel_radius' => 'nullable|integer|min:0',
            'insurance_info' => 'nullable|array',
            'contract_templates' => 'nullable|array',
            'is_available' => 'boolean',
            'unavailable_until' => 'nullable|date',
            'unavailable_reason' => 'nullable|string',
        ];
    }

    protected function getUpdateValidationRules(): array
    {
        return [
            'specialization' => 'sometimes|required|in:VIDEO,PHOTOGRAPHY,GRAPHIC_DESIGN,ANIMATION,OTHER',
            'portfolio_url' => 'nullable|url|max:255',
            'social_media_links' => 'nullable|array',
            'equipment' => 'nullable|array',
            'availability' => 'nullable|array',
            'pricing' => 'nullable|array',
            'is_verified' => 'boolean',
            'verification_documents' => 'nullable|array',
            'rating' => 'nullable|numeric|min:0|max:5',
            'total_projects' => 'integer|min:0',
            'completed_projects' => 'integer|min:0',
            'cancelled_projects' => 'integer|min:0',
            'is_featured' => 'boolean',
            'featured_until' => 'nullable|date',
            'payment_info' => 'nullable|array',
            'tax_info' => 'nullable|array',
            'preferred_communication' => 'nullable|in:EMAIL,PHONE,VIDEO_CALL,IN_PERSON',
            'languages' => 'nullable|array',
            'travel_radius' => 'nullable|integer|min:0',
            'insurance_info' => 'nullable|array',
            'contract_templates' => 'nullable|array',
            'is_available' => 'boolean',
            'unavailable_until' => 'nullable|date',
            'unavailable_reason' => 'nullable|string',
        ];
    }

    protected function getRelations(): array
    {
        return ['user'];
    }
}

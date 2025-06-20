<?php

namespace App\Http\Controllers;

use App\Models\Ambassador;
use Illuminate\Http\Request;

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

    protected function getValidationRules(): array
    {
        return [
            'user_id' => 'required|exists:users,id',
            'bio' => 'required|string',
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
            'referral_code' => 'required|string|unique:ambassadors,referral_code',
            'referral_count' => 'integer|min:0',
            'commission_rate' => 'required|numeric|min:0|max:100',
            'total_earnings' => 'numeric|min:0',
            'payment_history' => 'nullable|array',
            'promotion_materials' => 'nullable|array',
            'target_audience' => 'nullable|array',
            'promotion_channels' => 'nullable|array',
            'performance_metrics' => 'nullable|array',
            'is_active' => 'boolean',
            'activation_date' => 'nullable|date',
            'deactivation_date' => 'nullable|date',
            'deactivation_reason' => 'nullable|string',
        ];
    }

    protected function getUpdateValidationRules(): array
    {
        return [
            'bio' => 'sometimes|required|string',
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
            'referral_code' => 'sometimes|required|string|unique:ambassadors,referral_code',
            'referral_count' => 'integer|min:0',
            'commission_rate' => 'sometimes|required|numeric|min:0|max:100',
            'total_earnings' => 'numeric|min:0',
            'payment_history' => 'nullable|array',
            'promotion_materials' => 'nullable|array',
            'target_audience' => 'nullable|array',
            'promotion_channels' => 'nullable|array',
            'performance_metrics' => 'nullable|array',
            'is_active' => 'boolean',
            'activation_date' => 'nullable|date',
            'deactivation_date' => 'nullable|date',
            'deactivation_reason' => 'nullable|string',
        ];
    }

    protected function getRelations(): array
    {
        return ['user'];
    }
}

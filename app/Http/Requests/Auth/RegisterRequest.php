<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
            'password_confirmation' => ['required', 'string', 'same:password'],
            'first_name' => ['required', 'string', 'min:2'],
            'last_name' => ['required', 'string', 'min:2'],
            'user_type' => ['required', 'string', 'in:client,creator'],
            'terms_accepted' => ['required', 'boolean', 'accepted'],
            // Creator-specific fields
            'skills' => ['required_if:user_type,creator', 'array'],
            'skills.*' => ['string'],
            'media_types' => ['required_if:user_type,creator', 'array'],
            'media_types.*' => ['string'],
            'regions' => ['required_if:user_type,creator', 'array'],
            'regions.*' => ['string'],
            'languages' => ['required_if:user_type,creator', 'array'],
            'languages.*.language' => ['required', 'string'],
            'languages.*.proficiency' => ['required', 'string'],
            'biography' => ['required_if:user_type,creator', 'string'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'email.unique' => __('auth.email_already_exists'),
            'password.min' => __('auth.password_min_length'),
            'password_confirmation.same' => __('auth.passwords_must_match'),
            'first_name.min' => __('auth.first_name_min_length'),
            'last_name.min' => __('auth.last_name_min_length'),
            'user_type.in' => __('auth.invalid_user_type'),
            'terms_accepted.accepted' => __('auth.terms_must_be_accepted'),
            'skills.min' => __('auth.skills_min_required'),
            'media_types.min' => __('auth.media_types_min_required'),
            'regions.min' => __('auth.regions_min_required'),
            'languages.min' => __('auth.languages_min_required'),
            'biography.min' => __('auth.biography_min_length'),
        ];
    }
}

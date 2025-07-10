<?php

namespace App\Http\Controllers;

use App\Enums\Language;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

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

    protected function getValidationRules(): array
    {
        return [
            'user_id' => 'required|exists:users,id',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone_number' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:20',
            'profile_picture' => 'nullable|string|max:255',
            'bio' => 'nullable|string',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:MALE,FEMALE,OTHER',
            'preferred_language' => 'nullable|string|in:' . implode(',', array_values(Language::getCodes())),
            'timezone' => 'nullable|string|max:100',
            'notification_preferences' => 'nullable|array',
            'privacy_settings' => 'nullable|array',
            'social_media_links' => 'nullable|array',
            'emergency_contact' => 'nullable|array',
            'preferences' => 'nullable|array',
        ];
    }

    protected function getUpdateValidationRules(): array
    {
        return [
            'first_name' => 'sometimes|required|string|max:255',
            'last_name' => 'sometimes|required|string|max:255',
            'phone_number' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:20',
            'profile_picture' => 'nullable|url|max:255',
            'bio' => 'nullable|string',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:MALE,FEMALE,OTHER',
            'preferred_language' => 'nullable|string|in:' . implode(',', array_values(Language::getCodes())),
            'timezone' => 'nullable|string|max:100',
            'notification_preferences' => 'nullable|array',
            'privacy_settings' => 'nullable|array',
            'social_media_links' => 'nullable|array',
            'emergency_contact' => 'nullable|array',
            'preferences' => 'nullable|array',
        ];
    }

>>>>>>> main
    protected function getRelations(): array
    {
        return ['user'];
    }

    public function createOne(Request $request)
    {
        try {
            return \DB::transaction(function () use ($request) {
                if (in_array('create', $this->restricted)) {
                    $user = $request->user();
                    if (!$user->hasPermission($this->getTable(), 'create')) {
                        return response()->json([
                            'success' => false,
                            'errors' => [__('common.permission_denied')],
                        ]);
                    }
                }

                $model = app($this->getModelClass());
                $customValidationMsgs = method_exists($model, 'validationMessages') ? $model->validationMessages() : [];
                $validated = $request->validate($this->getValidationRules(), $customValidationMsgs);

                // Convert preferred_language from frontend code to enum value
                if (isset($validated['preferred_language'])) {
                    $languageEnum = Language::fromCode($validated['preferred_language']);
                    if ($languageEnum) {
                        $validated['preferred_language'] = $languageEnum->value;
                    } else {
                        throw ValidationException::withMessages([
                            'preferred_language' => ['Invalid language code provided.']
                        ]);
                    }
                }

                $model = $this->model()->create($validated);

                if (method_exists($this, 'afterCreateOne')) {
                    $this->afterCreateOne($model, $request);
                }

                return response()->json([
                    'success' => true,
                    'data' => ['item' => $model],
                    'message' => __($this->getTable() . '.created'),
                ]);
            });
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'errors' => Arr::flatten($e->errors())]);
        } catch (\Exception $e) {
            \Log::error('Error caught in function UserProfileController.createOne: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());

            return response()->json(['success' => false, 'errors' => [__('common.unexpected_error')]]);
        }
    }

    public function updateOne($id, Request $request)
    {
        try {
            return \DB::transaction(function () use ($id, $request) {
                if (in_array('update', $this->restricted)) {
                    $user = $request->user();
                    if (!$user->hasPermission($this->getTable(), 'update', $id)) {
                        return response()->json([
                            'success' => false,
                            'errors' => [__('common.permission_denied')],
                        ]);
                    }
                }

                $model = app($this->getModelClass());
                $customValidationMsgs = method_exists($model, 'validationMessages') ? $model->validationMessages() : [];
                $validated = $request->validate($this->getUpdateValidationRules(), $customValidationMsgs);

                // Convert preferred_language from frontend code to enum value
                if (isset($validated['preferred_language'])) {
                    $languageEnum = Language::fromCode($validated['preferred_language']);
                    if ($languageEnum) {
                        $validated['preferred_language'] = $languageEnum->value;
                    } else {
                        throw ValidationException::withMessages([
                            'preferred_language' => ['Invalid language code provided.']
                        ]);
                    }
                }

                $model = $this->model()->find($id);

                if (!$model) {
                    return response()->json([
                        'success' => false,
                        'errors' => [__($this->getTable() . '.not_found')],
                    ]);
                }

                $model->update($validated);

                if (method_exists($this, 'afterUpdateOne')) {
                    $this->afterUpdateOne($model, $request);
                }

                return response()->json([
                    'success' => true,
                    'data' => ['item' => $model],
                    'validated' => $validated,
                    'message' => __($this->getTable() . '.updated'),
                ]);
            });
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'errors' => Arr::flatten($e->errors())]);
        } catch (\Exception $e) {
            \Log::error('Error caught in function UserProfileController.updateOne: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());

            return response()->json(['success' => false, 'errors' => [__('common.unexpected_error')]]);
        }
    }

    public function patchOne($id, Request $request)
    {
        try {
            return \DB::transaction(function () use ($id, $request) {
                if (in_array('update', $this->restricted)) {
                    $user = $request->user();
                    if (!$user->hasPermission($this->getTable(), 'update', $id)) {
                        return response()->json([
                            'success' => false,
                            'errors' => [__('common.permission_denied')],
                        ]);
                    }
                }

                $model = $this->model()->find($id);

                if (!$model) {
                    return response()->json([
                        'success' => false,
                        'errors' => [__($this->getTable() . '.not_found')],
                    ]);
                }

                $rules = $this->getUpdateValidationRules();
                $fields = array_keys($request->all());
                $validated = $request->validate(Arr::only($rules, $fields));

                // Convert preferred_language from frontend code to enum value
                if (isset($validated['preferred_language'])) {
                    $languageEnum = Language::fromCode($validated['preferred_language']);
                    if ($languageEnum) {
                        $validated['preferred_language'] = $languageEnum->value;
                    } else {
                        throw ValidationException::withMessages([
                            'preferred_language' => ['Invalid language code provided.']
                        ]);
                    }
                }

                $model->update($validated);

                if (method_exists($this, 'afterPatchOne')) {
                    $this->afterPatchOne($model, $request);
                }

                return response()->json([
                    'success' => true,
                    'data' => ['item' => $model],
                    'validated' => $validated,
                    'message' => __($this->getTable() . '.updated'),
                ]);
            });
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'errors' => Arr::flatten($e->errors())]);
        } catch (\Exception $e) {
            \Log::error('Error caught in function UserProfileController.patchOne: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());

            return response()->json(['success' => false, 'errors' => [__('common.unexpected_error')]]);
        }
    }
}

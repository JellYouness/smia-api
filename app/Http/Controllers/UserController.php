<?php

namespace App\Http\Controllers;

use App\Enums\ROLE;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Log;
use Illuminate\Validation\ValidationException;

class UserController extends CrudController
{
    protected $table = 'users';

    protected $modelClass = User::class;

    protected function getTable()
    {
        return $this->table;
    }

    protected function getModelClass()
    {
        return $this->modelClass;
    }

    public function createOne(Request $request)
    {
        try {
            $request->merge(['password' => Hash::make($request->password)]);

            return parent::createOne($request);
        } catch (\Exception $e) {
            Log::error('Error caught in function UserController.createOne : ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json(['success' => false, 'errors' => [__('common.unexpected_error')]]);
        }
    }

    public function afterCreateOne($item, $request)
    {
        try {
            $roleEnum = ROLE::from($request->role);
            $item->syncRoles([$roleEnum]);
        } catch (\Exception $e) {
            Log::error('Error caught in function UserController.afterCreateOne : ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json(['success' => false, 'errors' => [__('common.unexpected_error')]]);
        }
    }

    public function updateOne($id, Request $request)
    {
        try {
            if (isset($request->password) && ! empty($request->password)) {
                $request->merge(['password' => Hash::make($request->password)]);
            } else {
                $request->request->remove('password');
            }

            return parent::updateOne($id, $request);
        } catch (\Exception $e) {
            Log::error('Error caught in function UserController.updateOne : ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json(['success' => false, 'errors' => [__('common.unexpected_error')]]);
        }
    }

    public function afterUpdateOne($item, $request)
    {
        try {
            $roleEnum = ROLE::from($request->role);
            $item->syncRoles([$roleEnum]);
        } catch (\Exception $e) {
            Log::error('Error caught in function UserController.afterUpdateOne : ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json(['success' => false, 'errors' => [__('common.unexpected_error')]]);
        }
    }

    // Profile section specific update methods
    public function updateAbout($id, Request $request)
    {
        Log::info('Update about request data:', [
            'all' => $request->all(),
        ]);
        try {
            $user = User::findOrFail($id);
            $user->profile->update([
                'title' => $request->title,
                'bio' => $request->bio,
                'short_bio' => $request->short_bio
            ]);

            // Only update hourly_rate for creators
            if ($user->creator && $request->has('hourly_rate')) {
                $user->creator->update([
                    'hourly_rate' => $request->hourly_rate
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'About updated successfully',
                'data' => $user->fresh()->load('profile', 'creator')
            ]);
        } catch (\Exception $e) {
            Log::error('Error caught in function UserController.updateAbout : ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json(['success' => false, 'errors' => [__('common.unexpected_error')]]);
        }
    }

    public function updatePortfolio($id, Request $request)
    {
        try {
            $user = User::findOrFail($id);
            if (!$user->creator) {
                return response()->json([
                    'success' => false,
                    'errors' => ['User is not a creator']
                ]);
            }
            $user->creator->update([
                'portfolio' => json_encode($request->portfolio)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Portfolio updated successfully',
                'data' => $user->fresh()->load('profile', 'creator')
            ]);
        } catch (\Exception $e) {
            Log::error('Error caught in function UserController.updatePortfolio : ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json(['success' => false, 'errors' => [__('common.unexpected_error')]]);
        }
    }

    public function updateSkills($id, Request $request)
    {
        try {
            $user = User::findOrFail($id);
            if (!$user->creator) {
                return response()->json([
                    'success' => false,
                    'errors' => ['User is not a creator']
                ]);
            }
            $user->creator->update([
                'skills' => json_encode($request->skills)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Skills updated successfully',
                'data' => $user->fresh()->load('profile', 'creator')
            ]);
        } catch (\Exception $e) {
            Log::error('Error caught in function UserController.updateSkills : ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json(['success' => false, 'errors' => [__('common.unexpected_error')]]);
        }
    }

    public function updateCertifications($id, Request $request)
    {
        try {
            $user = User::findOrFail($id);
            if (!$user->creator) {
                return response()->json([
                    'success' => false,
                    'errors' => ['User is not a creator']
                ]);
            }
            $user->creator->update([
                'certifications' => json_encode($request->certifications)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Certifications updated successfully',
                'data' => $user->fresh()->load('profile', 'creator')
            ]);
        } catch (\Exception $e) {
            Log::error('Error caught in function UserController.updateCertifications : ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json(['success' => false, 'errors' => [__('common.unexpected_error')]]);
        }
    }

    public function updateEmployment($id, Request $request)
    {
        try {
            $user = User::findOrFail($id);
            if (!$user->creator) {
                return response()->json([
                    'success' => false,
                    'errors' => ['User is not a creator']
                ]);
            }
            $user->creator->update([
                'professional_background' => json_encode($request->professional_background)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Employment history updated successfully',
                'data' => $user->fresh()->load('profile', 'creator')
            ]);
        } catch (\Exception $e) {
            Log::error('Error caught in function UserController.updateEmployment : ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json(['success' => false, 'errors' => [__('common.unexpected_error')]]);
        }
    }

    public function updateAchievements($id, Request $request)
    {
        try {
            $user = User::findOrFail($id);
            if (!$user->creator) {
                return response()->json([
                    'success' => false,
                    'errors' => ['User is not a creator']
                ]);
            }
            $user->creator->update([
                'achievements' => json_encode($request->achievements)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Achievements updated successfully',
                'data' => $user->fresh()->load('profile', 'creator')
            ]);
        } catch (\Exception $e) {
            Log::error('Error caught in function UserController.updateAchievements : ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json(['success' => false, 'errors' => [__('common.unexpected_error')]]);
        }
    }

    public function updateEquipment($id, Request $request)
    {
        try {
            $user = User::findOrFail($id);
            if (!$user->creator) {
                return response()->json([
                    'success' => false,
                    'errors' => ['User is not a creator']
                ]);
            }
            $user->creator->update([
                'equipment_info' => json_encode($request->equipment_info)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Equipment updated successfully',
                'data' => $user->fresh()->load('profile', 'creator')
            ]);
        } catch (\Exception $e) {
            Log::error('Error caught in function UserController.updateEquipment : ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json(['success' => false, 'errors' => [__('common.unexpected_error')]]);
        }
    }

    public function updateRegionalExpertise($id, Request $request)
    {
        try {
            $user = User::findOrFail($id);
            if (!$user->creator) {
                return response()->json([
                    'success' => false,
                    'errors' => ['User is not a creator']
                ]);
            }
            $user->creator->update([
                'regional_expertise' => json_encode($request->regional_expertise)
            ]);

            Log::info('Regional expertise updated successfully', ['regionalExpertise' => $request->regional_expertise]);
            Log::info('Regional expertise updated successfully', ['jsonregionalExpertise' => json_encode($request->regional_expertise)]);

            return response()->json([
                'success' => true,
                'message' => 'Regional expertise updated successfully',
                'data' => $user->fresh()->load('profile', 'creator')
            ]);
        } catch (\Exception $e) {
            Log::error('Error caught in function UserController.updateRegionalExpertise : ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json(['success' => false, 'errors' => [__('common.unexpected_error')]]);
        }
    }

    public function updateMediaTypes($id, Request $request)
    {
        try {
            $user = User::findOrFail($id);
            if (!$user->creator) {
                return response()->json([
                    'success' => false,
                    'errors' => ['User is not a creator']
                ]);
            }
            $user->creator->update([
                'media_types' => json_encode($request->media_types)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Media types updated successfully',
                'data' => $user->fresh()->load('profile', 'creator')
            ]);
        } catch (\Exception $e) {
            Log::error('Error caught in function UserController.updateMediaTypes : ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json(['success' => false, 'errors' => [__('common.unexpected_error')]]);
        }
    }

    public function updateLanguages($id, Request $request)
    {
        try {
            $user = User::findOrFail($id);
            if (!$user->creator) {
                return response()->json([
                    'success' => false,
                    'errors' => ['User is not a creator']
                ]);
            }
            $validated = $request->validate([
                'languages' => 'required|array',
                'languages.*.language' => 'required|string',
                'languages.*.proficiency' => 'required|string',
            ]);
            $user->creator->update([
                'languages' => json_encode($validated['languages'])
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Languages updated successfully',
                'data' => [
                    'languages' => $validated['languages']
                ]
            ]);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'errors' => $e->errors()]);
        } catch (\Exception $e) {
            Log::error('Error caught in function UserController.updateLanguages : ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json(['success' => false, 'errors' => [__('common.unexpected_error')]]);
        }
    }

    public function updateEducation($id, Request $request)
    {
        try {
            $user = User::findOrFail($id);
            if (!$user->creator) {
                return response()->json([
                    'success' => false,
                    'errors' => ['User is not a creator']
                ]);
            }
            $user->creator->update([
                'education' => json_encode($request->education)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Education updated successfully',
                'data' => $user->fresh()->load('profile', 'creator')
            ]);
        } catch (\Exception $e) {
            Log::error('Error caught in function UserController.updateEducation : ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json(['success' => false, 'errors' => [__('common.unexpected_error')]]);
        }
    }

    // PATCH: Update social media links in user profile
    public function updateSocialMedia($id, Request $request)
    {
        try {
            $user = User::findOrFail($id);

            $validated = $request->validate([
                'profile.social_media_links' => 'required|array',
                'profile.social_media_links.linkedin' => 'nullable|string',
                'profile.social_media_links.twitter' => 'nullable|string',
                'profile.social_media_links.facebook' => 'nullable|string',
            ]);
            if ($user->profile) {
                $user->profile->update([
                    'social_media_links' => $validated['profile']['social_media_links'],
                ]);
            }
            return response()->json([
                'success' => true,
                'message' => 'Social media links updated successfully',
                'data' => $user->fresh()->load('profile')
            ]);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'errors' => $e->errors()]);
        } catch (\Exception $e) {
            Log::error('Error caught in function UserController.updateSocialMedia : ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return response()->json(['success' => false, 'errors' => [__('common.unexpected_error')]]);
        }
    }

    // Client-specific update methods
    public function updateCompany($id, Request $request)
    {
        try {
            $user = User::findOrFail($id);

            if (!$user->client) {
                return response()->json([
                    'success' => false,
                    'errors' => ['User is not a client']
                ]);
            }

            $validated = $request->validate([
                'company_name' => 'required|string|max:255',
                'company_size' => 'required|in:INDIVIDUAL,SMALL,MEDIUM,LARGE,ENTERPRISE',
                'industry' => 'required|in:MEDIA,EDUCATION,HEALTHCARE,TECHNOLOGY,FINANCE,ENTERTAINMENT,OTHER',
                'website_url' => 'nullable|url|max:255',
            ]);

            $user->client->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Company information updated successfully',
                'data' => $user->fresh()->load('profile', 'client')
            ]);
        } catch (\Exception $e) {
            Log::error('Error caught in function UserController.updateCompany : ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json(['success' => false, 'errors' => [__('common.unexpected_error')]]);
        }
    }

    public function updateBilling($id, Request $request)
    {
        try {
            $user = User::findOrFail($id);

            if (!$user->client) {
                return response()->json([
                    'success' => false,
                    'errors' => ['User is not a client']
                ]);
            }

            $validated = $request->validate([
                'billing_street' => 'required|string|max:255',
                'billing_city' => 'required|string|max:255',
                'billing_state' => 'required|string|max:255',
                'billing_postal_code' => 'required|string|max:20',
                'billing_country' => 'required|string|max:255',
                'tax_identifier' => 'nullable|string|max:50',
            ]);

            $user->client->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Billing information updated successfully',
                'data' => $user->fresh()->load('profile', 'client')
            ]);
        } catch (\Exception $e) {
            Log::error('Error caught in function UserController.updateBilling : ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json(['success' => false, 'errors' => [__('common.unexpected_error')]]);
        }
    }

    public function updateBudget($id, Request $request)
    {
        try {
            $user = User::findOrFail($id);

            if (!$user->client) {
                return response()->json([
                    'success' => false,
                    'errors' => ['User is not a client']
                ]);
            }

            $validated = $request->validate([
                'budget' => 'required|in:SMALL,MEDIUM,LARGE,ENTERPRISE',
            ]);

            $user->client->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Budget updated successfully',
                'data' => $user->fresh()->load('profile', 'client')
            ]);
        } catch (\Exception $e) {
            Log::error('Error caught in function UserController.updateBudget : ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json(['success' => false, 'errors' => [__('common.unexpected_error')]]);
        }
    }

    public function updateProjectSettings($id, Request $request)
    {
        try {
            $user = User::findOrFail($id);

            if (!$user->client) {
                return response()->json([
                    'success' => false,
                    'errors' => ['User is not a client']
                ]);
            }

            $validated = $request->validate([
                'default_project_settings' => 'nullable|array',
                'default_project_settings.budget' => 'nullable|numeric|min:0',
                'default_project_settings.timeline' => 'nullable|string|max:255',
                'default_project_settings.requirements' => 'nullable|string|max:1000',
            ]);

            $user->client->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Project settings updated successfully',
                'data' => $user->fresh()->load('profile', 'client')
            ]);
        } catch (\Exception $e) {
            Log::error('Error caught in function UserController.updateProjectSettings : ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json(['success' => false, 'errors' => [__('common.unexpected_error')]]);
        }
    }
}

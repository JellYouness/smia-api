<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ClientProfileController extends Controller
{
    public function updateCompany(Request $request, $userId)
    {
        try {
            $user = User::findOrFail($userId);

            if (!$user->client) {
                return response()->json([
                    'success' => false,
                    'errors' => ['User is not a client']
                ]);
            }

            $validated = $request->validate([
                'company_name' => 'required|string|max:255',
                'company_size' => 'required|string|in:INDIVIDUAL,SMALL,MEDIUM,LARGE,ENTERPRISE',
                'industry' => 'required|string|in:MEDIA,EDUCATION,HEALTHCARE,TECHNOLOGY,FINANCE,ENTERTAINMENT,OTHER',
                'website_url' => 'nullable|url|max:255',
            ]);

            $user->client->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Company information updated successfully',
                'data' => $user->fresh()->load('profile', 'client')
            ]);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'errors' => $e->errors()]);
        } catch (\Exception $e) {
            Log::error('Error caught in function ClientProfileController.updateCompany: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json(['success' => false, 'errors' => [__('common.unexpected_error')]]);
        }
    }

    public function updateBilling(Request $request, $userId)
    {
        try {
            $user = User::findOrFail($userId);

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
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'errors' => $e->errors()]);
        } catch (\Exception $e) {
            Log::error('Error caught in function ClientProfileController.updateBilling: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json(['success' => false, 'errors' => [__('common.unexpected_error')]]);
        }
    }

    public function updateBudget(Request $request, $userId)
    {
        try {
            $user = User::findOrFail($userId);

            if (!$user->client) {
                return response()->json([
                    'success' => false,
                    'errors' => ['User is not a client']
                ]);
            }

            $validated = $request->validate([
                'budget' => 'required|string|in:SMALL,MEDIUM,LARGE,ENTERPRISE',
            ]);

            $user->client->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Budget updated successfully',
                'data' => $user->fresh()->load('profile', 'client')
            ]);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'errors' => $e->errors()]);
        } catch (\Exception $e) {
            Log::error('Error caught in function ClientProfileController.updateBudget: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json(['success' => false, 'errors' => [__('common.unexpected_error')]]);
        }
    }

    public function updateProjectSettings(Request $request, $userId)
    {
        try {
            $user = User::findOrFail($userId);

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
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'errors' => $e->errors()]);
        } catch (\Exception $e) {
            Log::error('Error caught in function ClientProfileController.updateProjectSettings: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json(['success' => false, 'errors' => [__('common.unexpected_error')]]);
        }
    }

    public function updatePreferredCreators(Request $request, $userId)
    {
        try {
            $user = User::findOrFail($userId);

            if (!$user->client) {
                return response()->json([
                    'success' => false,
                    'errors' => ['User is not a client']
                ]);
            }

            $validated = $request->validate([
                'preferred_creators' => 'nullable|array',
                'preferred_creators.*' => 'integer|exists:users,id',
            ]);

            $user->client->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Preferred creators updated successfully',
                'data' => $user->fresh()->load('profile', 'client')
            ]);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'errors' => $e->errors()]);
        } catch (\Exception $e) {
            Log::error('Error caught in function ClientProfileController.updatePreferredCreators: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json(['success' => false, 'errors' => [__('common.unexpected_error')]]);
        }
    }

    public function updateLanguages(Request $request, $userId)
    {
        try {
            $user = User::findOrFail($userId);

            if (!$user->client) {
                return response()->json([
                    'success' => false,
                    'errors' => ['User is not a client']
                ]);
            }

            $validated = $request->validate([
                'languages' => 'required|array',
                'languages.*.language' => 'required|string',
                'languages.*.proficiency' => 'required|string',
            ]);

            $user->client->update([
                'languages' => json_encode($validated['languages']),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Client languages updated successfully',
                'data' => [
                    'languages' => $validated['languages']
                ]
            ]);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'errors' => $e->errors()]);
        } catch (\Exception $e) {
            Log::error('Error caught in function updateLanguages: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json(['success' => false, 'errors' => [__('common.unexpected_error')]]);
        }
    }

    public function getClientProfile($userId)
    {
        try {
            $user = User::with(['client', 'profile'])->findOrFail($userId);

            if (!$user->client) {
                return response()->json([
                    'success' => false,
                    'errors' => ['User is not a client']
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => $user
            ]);
        } catch (\Exception $e) {
            Log::error('Error caught in function ClientProfileController.getClientProfile: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json(['success' => false, 'errors' => [__('common.unexpected_error')]]);
        }
    }
}

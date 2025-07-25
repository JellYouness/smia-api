<?php

namespace App\Http\Controllers;

use App\Enums\Language;
use App\Enums\ROLE;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\Ambassador;
use App\Models\Client;
use App\Models\Creator;
use App\Models\SystemAdministrator;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function me(Request $request)
    {
        try {
            $user = Auth::user();
            if (! $user) {
                return response()->json(['success' => false, 'errors' => [__('auth.user_not_found')]]);
            }
            $admin = $request->input('admin');
            if ($admin && ! $user->hasRole(ROLE::SYSTEM_ADMINISTRATOR)) {
                return response()->json(['success' => false, 'errors' => [__('auth.not_admin')]]);
            }

            // Load user with all relationships
            $userWithRelations = User::with(['creator', 'client', 'ambassador', 'systemAdministrator', 'profile'])
                ->find($user->id);


            return response()->json(
                [
                    'success' => true,
                    'data' => [
                        'user' => $userWithRelations,
                    ],
                ]
            );
        } catch (\Exception $e) {
            Log::error('Error caught in function AuthController.me: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json(['success' => false, 'errors' => [__('common.unexpected_error')]]);
        }
    }

    public function login(LoginRequest $request)
    {
        try {
            $user = User::where('email', $request->input('email'))->first();

            if (! $user || ! Hash::check($request->input('password'), $user->password)) {
                return response()->json(['success' => false, 'errors' => [__('auth.failed')]]);
            }
            $admin = $request->input('admin');
            if ($admin && ! $user->hasRole(ROLE::SYSTEM_ADMINISTRATOR)) {
                return response()->json(['success' => false, 'errors' => [__('auth.not_admin')]]);
            }
            $token = $user->createToken('authToken', ['expires_in' => 60 * 24 * 30])->plainTextToken;

            return response()->json(['success' => true, 'message' => __('auth.login_success'), 'data' => ['token' => $token]]);
        } catch (\Exception $e) {
            Log::error('Error caught in function AuthController.login: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json(['success' => false, 'errors' => [__('common.unexpected_error')]]);
        }
    }

    public function register(RegisterRequest $request)
    {
        $data = $request->validated();

        try {
            Log::info('Registration request data:', [
                'all' => $request->all(),
                'validated' => $data,
                'headers' => $request->headers->all(),
            ]);

            return DB::transaction(
                function () use ($data) {
                    $user = User::where('email', $data['email'])->first();
                    if ($user) {
                        return response()->json(['success' => false, 'errors' => [__('auth.email_already_exists')]]);
                    }

                    // Generate unique username
                    $baseUsername = strtolower(explode('@', $data['email'])[0]);
                    $username = $baseUsername;
                    $counter = 1;

                    while (User::where('username', $username)->exists()) {
                        $username = $baseUsername . $counter;
                        $counter++;
                    }

                    // Create the base user
                    $user = User::create([
                        'email' => $data['email'],
                        'username' => $username,
                        'password' => Hash::make($data['password']),
                        'first_name' => $data['first_name'],
                        'last_name' => $data['last_name'],
                        'user_type' => strtoupper($data['user_type']),
                        'accepted_terms' => $data['terms_accepted'],
                        'status' => 'PENDING',
                        'email_verified_at' => null, // Will be set when email is verified
                        'date_registered' => now(),
                    ]);

                    // Assign role based on user type
                    $role = $data['user_type'] === 'client' ? ROLE::CLIENT : ROLE::CREATOR;
                    $user->assignRole($role);

                    // Create specific user type record
                    if ($data['user_type'] === 'client') {
                        $user->client()->create([
                            'user_id' => $user->id,
                        ]);
                    } elseif ($data['user_type'] === 'creator') {
                        // Format regional expertise data to match expected structure
                        $regionalExpertise = array_map(function ($region) {
                            return [
                                'region' => $region,
                                'expertise_level' => 'BEGINNER' // Default level, can be updated later
                            ];
                        }, $data['regions']);

                        $creator = Creator::create([
                            'user_id' => $user->id,
                            'skills' => $data['skills'],
                            'media_types' => $data['media_types'],
                            'experience' => 0, // Default experience level
                            'regional_expertise' => $regionalExpertise,
                            'languages' => $data['languages'],
                            'verification_status' => 'UNVERIFIED',
                            'availability' => 'AVAILABLE',
                        ]);
                    }

                    // Send email verification
                    $user->sendEmailVerificationNotification();

                    return response()->json([
                        'success' => true,
                        'message' => __('auth.registration_successful'),
                        'data' => [
                            'user_id' => $user->id,
                            'email' => $user->email,
                        ],
                    ]);
                }
            );
        } catch (\Exception $e) {
            Log::error('Error caught in function AuthController.register: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'errors' => [__('auth.registration_failed')],
            ], 500);
        }
    }

    public function logout()
    {
        try {
            $user = Auth::user();
            $user->tokens()->delete();

            return response()->json(['success' => true, 'message' => __('auth.logout_success')]);
        } catch (\Exception $e) {
            Log::error('Error caught in function AuthController.logout: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json(['success' => false, 'errors' => [__('common.unexpected_error')]]);
        }
    }

    public function requestPasswordReset(Request $request)
    {
        try {
            $email = $request->email;
            $status = Password::sendResetLink(['email' => $email]);
            if ($status === Password::RESET_LINK_SENT) {
                return response()->json(['success' => true, 'message' => __('auth.password_reset_link_sent')]);
            } elseif ($status === Password::INVALID_USER) {
                return response()->json(['success' => false, 'errors' => [__('users.not_found')]]);
            } elseif ($status === Password::INVALID_TOKEN) {
                return response()->json(['success' => false, 'errors' => [__('auth.invalid_token')]]);
            } elseif ($status === Password::RESET_THROTTLED) {
                return response()->json(['success' => false, 'errors' => [__('auth.reset_throttled')]]);
            }

            return response()->json(['success' => false, 'errors' => [__('common.unexpected_error')]]);
        } catch (\Exception $e) {
            Log::error('Error caught in function AuthController.requestPasswordReset: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json(['success' => false, 'errors' => [__('common.unexpected_error')]]);
        }
    }

    public function resetPassword(Request $request)
    {
        try {
            return DB::transaction(
                function () use ($request) {
                    $status = Password::reset(
                        $request->only('email', 'password', 'password_confirmation', 'token'),
                        function ($user, $password) {
                            $user->password = Hash::make($password);
                            $user->save();
                        }
                    );
                    if ($status === Password::PASSWORD_RESET) {
                        return response()->json(['success' => true, 'message' => __('auth.password_reset_success')]);
                    } elseif ($status === Password::INVALID_USER) {
                        return response()->json(['success' => false, 'errors' => [__('users.not_found')]]);
                    } elseif ($status === Password::INVALID_TOKEN) {
                        return response()->json(['success' => false, 'errors' => [__('auth.invalid_token')]]);
                    } elseif ($status === Password::RESET_THROTTLED) {
                        return response()->json(['success' => false, 'errors' => [__('auth.reset_throttled')]]);
                    }

                    return response()->json(['success' => false, 'errors' => [__('common.unexpected_error')]]);
                }
            );
        } catch (\Exception $e) {
            Log::error('Error caught in function AuthController.resetPassword: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json(['success' => false, 'errors' => [__('common.unexpected_error')]]);
        }
    }

    public function verifyEmail(Request $request)
    {
        try {
            $user = User::find($request->id);

            if (!$user) {
                return response()->json(['success' => false, 'errors' => [__('auth.user_not_found')]]);
            }

            if ($user->hasVerifiedEmail()) {
                return response()->json(['success' => false, 'errors' => [__('auth.email_already_verified')]]);
            }

            if (!hash_equals(sha1($user->getEmailForVerification()), $request->hash)) {
                return response()->json(['success' => false, 'errors' => [__('auth.invalid_verification_link')]]);
            }

            $user->markEmailAsVerified();

            // Create token for automatic login after verification
            $token = $user->createToken('authToken', ['expires_in' => 60 * 24 * 30])->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => __('auth.email_verified_success'),
                'data' => ['token' => $token]
            ]);
        } catch (\Exception $e) {
            Log::error('Error caught in function AuthController.verifyEmail: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json(['success' => false, 'errors' => [__('common.unexpected_error')]]);
        }
    }

    public function resendEmailVerification(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email|exists:users,email',
            ]);

            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json(['success' => false, 'errors' => [__('auth.user_not_found')]]);
            }

            if ($user->hasVerifiedEmail()) {
                return response()->json(['success' => false, 'errors' => [__('auth.email_already_verified')]]);
            }

            // Check if user is in PENDING status (not verified)
            if ($user->status !== 'PENDING') {
                return response()->json(['success' => false, 'errors' => [__('auth.user_not_pending_verification')]]);
            }

            // Send email verification
            $user->sendEmailVerificationNotification();

            return response()->json([
                'success' => true,
                'message' => __('auth.verification_email_resent'),
            ]);
        } catch (\Exception $e) {
            Log::error('Error caught in function AuthController.resendEmailVerification: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json(['success' => false, 'errors' => [__('common.unexpected_error')]]);
        }
    }

    public function updateProfile(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['success' => false, 'errors' => [__('auth.user_not_found')]]);
            }

            return DB::transaction(function () use ($request, $user) {
                // Validate user data
                $userValidationRules = [
                    'first_name' => 'sometimes|required|string|max:255',
                    'last_name' => 'sometimes|required|string|max:255',
                    'phone_number' => 'nullable|string|max:20',
                    'address' => 'nullable|string|max:255',
                    'city' => 'nullable|string|max:255',
                    'state' => 'nullable|string|max:255',
                    'country' => 'nullable|string|max:100',
                    'postal_code' => 'nullable|string|max:20',
                ];

                $validatedUserData = $request->validate($userValidationRules);

                $user->update($validatedUserData);
                $user->profile->update($validatedUserData);

                // Reload user with relationships
                $updatedUser = User::with(['creator', 'client', 'ambassador', 'systemAdministrator', 'profile'])
                    ->find($user->id);

                // --- DYNAMIC PROFILE COMPLETENESS CALCULATION ---
                if ($updatedUser && $updatedUser->profile) {
                    $completeness = $updatedUser->profile->calculateCompleteness($updatedUser);
                    $updatedUser->profile->update(['profile_completeness' => $completeness]);
                }

                return response()->json([
                    'success' => true,
                    'message' => __('user.profile_updated_successfully'),
                    'data' => [
                        'user' => $updatedUser,
                    ],
                ]);
            });
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'errors' => $e->errors()]);
        } catch (\Exception $e) {
            Log::error('Error caught in function AuthController.updateProfile: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json(['success' => false, 'errors' => [__('common.unexpected_error')]]);
        }
    }

    public function completeProfile(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['success' => false, 'errors' => [__('auth.user_not_found')]]);
            }

            // Check if user has verified email
            if (!$user->hasVerifiedEmail()) {
                return response()->json(['success' => false, 'errors' => [__('auth.email_not_verified')]]);
            }

            return DB::transaction(function () use ($request, $user) {
                // Validate profile data
                $profileValidationRules = [
                    'phone_number' => 'nullable|string|max:20',
                    'address' => 'nullable|string|max:255',
                    'city' => 'nullable|string|max:255',
                    'state' => 'nullable|string|max:255',
                    'country' => 'nullable|string|max:100',
                    'postal_code' => 'nullable|string|max:20',
                    'bio' => 'nullable|string|max:1000',
                    'title' => 'nullable|string|max:255',
                    'date_of_birth' => 'nullable|date',
                    'gender' => 'nullable|in:MALE,FEMALE,OTHER',
                    'preferred_language' => 'nullable|string|in:' . implode(',', array_values(Language::getCodesValues())),
                    'timezone' => 'nullable|string|max:100',
                    'profile_picture' => 'nullable|string|max:255',
                    'notification_preferences' => 'nullable|array',
                    'privacy_settings' => 'nullable|array',
                    'social_media_links' => 'nullable|array',
                    'emergency_contact' => 'nullable|array',
                    'preferences' => 'nullable|array',
                    'education' => 'nullable|array',
                    'education.*.degree' => 'nullable|string|max:255',
                    'education.*.field' => 'nullable|string|max:255',
                    'education.*.institution' => 'nullable|string|max:255',
                    'education.*.year' => 'nullable|string|max:10',
                    'professional_background' => 'nullable|array',
                    'professional_background.*.title' => 'nullable|string|max:255',
                    'professional_background.*.company' => 'nullable|string|max:255',
                    'professional_background.*.duration' => 'nullable|string|max:100',
                    'professional_background.*.description' => 'nullable|string|max:1000',
                    'achievements' => 'nullable|array',
                    'achievements.*' => 'nullable|string|max:255',
                ];

                $validatedProfileData = $request->validate($profileValidationRules);

                // Convert preferred_language from frontend code to enum value
                // if (isset($validatedProfileData['preferred_language'])) {
                //     $languageEnum = Language::fromCode($validatedProfileData['preferred_language']);
                //     if ($languageEnum) {
                //         $validatedProfileData['preferred_language'] = $languageEnum->value;
                //     } else {
                //         throw ValidationException::withMessages([
                //             'preferred_language' => ['Invalid language code provided.']
                //         ]);
                //     }
                // }

                // Create or update user profile
                $profileData = [
                    'user_id' => $user->id,
                    'phone_number' => $validatedProfileData['phone_number'] ?? null,
                    'address' => $validatedProfileData['address'] ?? null,
                    'city' => $validatedProfileData['city'] ?? null,
                    'state' => $validatedProfileData['state'] ?? null,
                    'country' => $validatedProfileData['country'] ?? null,
                    'postal_code' => $validatedProfileData['postal_code'] ?? null,
                    'bio' => $validatedProfileData['bio'] ?? null,
                    'short_bio' => $validatedProfileData['short_bio'] ?? null,
                    'title' => $validatedProfileData['title'] ?? null,
                    'date_of_birth' => $validatedProfileData['date_of_birth'] ?? null,
                    'gender' => $validatedProfileData['gender'] ?? null,
                    'preferred_language' => $validatedProfileData['preferred_language'] ?? null,
                    'timezone' => $validatedProfileData['timezone'] ?? null,
                    'profile_picture' => $validatedProfileData['profile_picture'] ?? null,
                    'notification_preferences' => $validatedProfileData['notification_preferences'] ?? null,
                    'privacy_settings' => $validatedProfileData['privacy_settings'] ?? null,
                    'social_media_links' => $validatedProfileData['social_media_links'] ?? null,
                    'emergency_contact' => $validatedProfileData['emergency_contact'] ?? null,
                    'preferences' => $validatedProfileData['preferences'] ?? null,
                ];

                // Remove null values
                $profileData = array_filter($profileData, function ($value) {
                    return $value !== null;
                });

                if ($user->profile) {
                    $user->profile->update($profileData);
                    // Re-fetch profile after update
                    $profile = $user->profile->fresh();
                } else {
                    $profile = UserProfile::create($profileData);
                }

                // Handle creator-specific data (education, professional background, achievements)
                if ($user->hasRole(ROLE::CREATOR) && $user->creator) {
                    $creatorUpdateData = [];

                    // Handle education data
                    if (isset($validatedProfileData['education'])) {
                        $education = $validatedProfileData['education'];
                        if (!empty(array_filter($education))) {
                            $creatorUpdateData['education'] = $education;
                        }
                    }

                    // Handle professional background data
                    if (isset($validatedProfileData['professional_background'])) {
                        $professionalBackground = $validatedProfileData['professional_background'];
                        if (!empty(array_filter($professionalBackground))) {
                            $creatorUpdateData['professional_background'] = $professionalBackground;
                        }
                    }

                    // Handle achievements data
                    if (isset($validatedProfileData['achievements']) && !empty($validatedProfileData['achievements'])) {
                        $achievements = $validatedProfileData['achievements'];
                        if (!empty($achievements)) {
                            $creatorUpdateData['achievements'] = $achievements;
                        }
                    }

                    // Update creator if there's data to update
                    if (!empty($creatorUpdateData)) {
                        $user->creator->update($creatorUpdateData);
                    }
                }

                // --- DYNAMIC PROFILE COMPLETENESS CALCULATION ---
                // Re-fetch user with relationships for completeness calculation
                $userWithRelations = User::with(['creator', 'client', 'ambassador', 'systemAdministrator', 'profile'])->find($user->id);
                if ($userWithRelations && $userWithRelations->profile) {
                    $completeness = $userWithRelations->profile->calculateCompleteness($userWithRelations);
                    $userWithRelations->profile->update(['profile_completeness' => $completeness]);
                }

                // Update user status to ACTIVE
                $user->update(['status' => 'ACTIVE']);

                // Reload user with relationships
                $updatedUser = User::with(['creator', 'client', 'ambassador', 'systemAdministrator', 'profile'])
                    ->find($user->id);

                return response()->json([
                    'success' => true,
                    'message' => __('user.profile_completed_successfully'),
                    'data' => [
                        'user' => $updatedUser,
                    ],
                ]);
            });
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'errors' => $e->errors()]);
        } catch (\Exception $e) {
            Log::error('Error caught in function AuthController.completeProfile: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json(['success' => false, 'errors' => [__('common.unexpected_error')]]);
        }
    }
}

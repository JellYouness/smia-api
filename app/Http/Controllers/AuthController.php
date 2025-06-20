<?php

namespace App\Http\Controllers;

use App\Enums\ROLE;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Models\Creator;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;

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

            return response()->json(
                [
                    'success' => true,
                    'data' => [
                        'user' => $user,
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
                        $creator = Creator::create([
                            'user_id' => $user->id,
                            'skills' => $data['skills'],
                            'media_types' => $data['media_types'],
                            'experience' => 0, // Default experience level
                            'regional_expertise' => $data['regions'],
                            'languages' => $data['languages'],
                            'biography' => $data['biography'],
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
}

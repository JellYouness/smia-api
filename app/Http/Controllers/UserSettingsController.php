<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UserSettingsController extends Controller
{
    public function show(Request $request)
    {
        $user = Auth::user();
        return response()->json([
            'email' => $user->email,
            'language' => $user->profile->language ?? 'en',
            'notifications' => $user->profile->notification_preferences ?? [],
            'privacy' => $user->profile->privacy_settings ?? [],
        ]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        $data = $request->validate([
            'language' => 'string|in:en,fr,es',
            'notifications.email' => 'boolean',
            'notifications.sms' => 'boolean',
            'notifications.push' => 'boolean',
            'notifications.in_app' => 'boolean',
            'privacy' => 'string|in:PUBLIC,PRIVATE,FRIENDS',
        ]);
        $user->profile->language = $data['language'];
        $user->profile->notification_preferences = $data['notifications'];
        $user->profile->privacy_settings = $data['privacy'];
        $user->save();
        return response()->json(['success' => true]);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserSettingsController extends Controller
{
    public function show(Request $request)
    {
        $user = Auth::user();
        return response()->json([
            'email' => $user->email,
            'language' => $user->language ?? 'en',
            'notifications' => [
                'email' => (bool)($user->notification_email ?? true),
                'sms' => (bool)($user->notification_sms ?? false),
                'push' => (bool)($user->notification_push ?? true),
                'inApp' => (bool)($user->notification_in_app ?? true),
            ],
            'privacy' => $user->privacy ?? 'PUBLIC',
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
            'notifications.inApp' => 'boolean',
            'privacy' => 'string|in:PUBLIC,PRIVATE,FRIENDS',
        ]);
        $user->language = $data['language'];
        $user->notification_email = $data['notifications']['email'];
        $user->notification_sms = $data['notifications']['sms'];
        $user->notification_push = $data['notifications']['push'];
        $user->notification_in_app = $data['notifications']['inApp'];
        $user->privacy = $data['privacy'];
        $user->save();
        return response()->json(['success' => true]);
    }
}

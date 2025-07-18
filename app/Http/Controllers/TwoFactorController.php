<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TwoFactorController extends Controller
{
    public function status()
    {
        $user = Auth::user();
        // Mock data for now - replace with real logic later
        return response()->json([
            'success' => true,
            'data' => ['enabled' => false],
            'message' => '2FA status retrieved successfully'
        ]);
    }

    public function enable(Request $request)
    {
        // Mock implementation for now
        return response()->json([
            'success' => true,
            'data' => [
                'qrCodeUrl' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==',
                'secret' => 'MOCK_SECRET_KEY_FOR_TESTING'
            ],
            'message' => '2FA setup initiated successfully'
        ]);
    }

    public function disable(Request $request)
    {
        // Mock implementation for now
        return response()->json([
            'success' => true,
            'message' => '2FA disabled successfully'
        ]);
    }
}

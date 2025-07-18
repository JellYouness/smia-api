<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConnectedAccountController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        // Mock data for now - replace with real logic later
        $accounts = [
            ['provider' => 'Google', 'connected' => false],
            ['provider' => 'Facebook', 'connected' => false],
        ];
        return response()->json([
            'success' => true,
            'data' => $accounts,
            'message' => 'Connected accounts retrieved successfully'
        ]);
    }

    public function disconnect($provider)
    {
        // Mock implementation for now
        return response()->json([
            'success' => true,
            'message' => 'Account disconnected successfully'
        ]);
    }

    public function redirectToProvider($provider)
    {
        // Mock implementation for now
        return response()->json([
            'success' => false,
            'errors' => ['Social login not implemented yet']
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SessionController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $sessions = $user->sessions()->get()->map(function ($session) {
            return [
                'id' => $session->id,
                'device' => $session->device,
                'lastActive' => $session->last_active,
                'current' => $session->id === session()->getId(),
            ];
        });
        return response()->json([
            'success' => true,
            'data' => $sessions,
            'message' => 'Sessions retrieved successfully'
        ]);
    }

    public function destroy($id)
    {
        $user = Auth::user();
        $session = $user->sessions()->findOrFail($id);
        $session->delete();
        return response()->json([
            'success' => true,
            'message' => 'Session revoked successfully'
        ]);
    }
}

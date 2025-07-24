<?php

namespace App\Http\Controllers;

use App\Models\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;

class SessionController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $sessions = $user->sessions()
            ->orderBy('last_active', 'desc')
            ->get()
            ->map(function ($session) {
                return [
                    'id' => $session->id,
                    'device' => $session->device,
                    'ipAddress' => $session->ip_address,
                    'lastActive' => $session->last_active?->toISOString(),
                    'formattedLastActive' => $session->formatted_last_active,
                    'current' => $session->isCurrentSession(),
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

        // If this is the current session, we need to revoke the token
        if ($session->isCurrentSession()) {
            $token = request()->bearerToken();
            if ($token) {
                $personalAccessToken = PersonalAccessToken::findToken($token);
                if ($personalAccessToken) {
                    $personalAccessToken->delete();
                }
            }
        }

        $session->delete();

        return response()->json([
            'success' => true,
            'message' => 'Session revoked successfully'
        ]);
    }

    /**
     * Revoke all sessions except the current one
     */
    public function revokeAllExceptCurrent()
    {
        $user = Auth::user();
        $currentTokenHash = hash('sha256', request()->bearerToken());

        // Get all sessions except current
        $sessionsToRevoke = $user->sessions()
            ->where('token_hash', '!=', $currentTokenHash)
            ->get();

        // Revoke all tokens except current
        foreach ($sessionsToRevoke as $session) {
            // Find and delete the token
            $token = PersonalAccessToken::where('token', 'like', '%' . $session->token_hash . '%')->first();
            if ($token) {
                $token->delete();
            }
            $session->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'All other sessions revoked successfully'
        ]);
    }
}

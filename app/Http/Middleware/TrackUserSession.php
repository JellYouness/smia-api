<?php

namespace App\Http\Middleware;

use App\Models\Session;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class TrackUserSession
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only track sessions for authenticated users
        if (Auth::check()) {
            $user = Auth::user();
            $token = $request->bearerToken();

            if ($token) {
                // Get or create session record
                $session = Session::firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'token_hash' => hash('sha256', $token),
                    ],
                    [
                        'device' => $this->getDeviceInfo($request),
                        'last_active' => now(),
                        'ip_address' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                    ]
                );

                // Update last active timestamp
                $session->update(['last_active' => now()]);
            }
        }

        return $response;
    }

    /**
     * Get device information from the request
     */
    private function getDeviceInfo(Request $request): string
    {
        $userAgent = $request->userAgent();

        if (preg_match('/Mobile|Android|iPhone|iPad/', $userAgent)) {
            return 'Mobile Device';
        }

        if (preg_match('/Windows/', $userAgent)) {
            return 'Windows PC';
        }

        if (preg_match('/Mac/', $userAgent)) {
            return 'Mac';
        }

        if (preg_match('/Linux/', $userAgent)) {
            return 'Linux PC';
        }

        return 'Unknown Device';
    }
}

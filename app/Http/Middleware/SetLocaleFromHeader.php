<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class SetLocaleFromHeader
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $locale = $request->header('Accept-Language');
        if ($locale) {
            // Extract the primary language code (e.g., 'en' from 'en-US')
            $primaryLocale = substr($locale, 0, 2);
            if (in_array($primaryLocale, ['en', 'fr', 'es'])) {
                App::setLocale($primaryLocale);
            } else {
                App::setLocale('en'); // Default to English if not supported
            }
        }

        return $next($request);
    }
}

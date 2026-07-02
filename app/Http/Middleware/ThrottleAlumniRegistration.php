<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class ThrottleAlumniRegistration
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $key = 'alumni-registration:' . $request->ip();

        // Allow 3 attempts per hour
        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);
            $minutes = ceil($seconds / 60);

            return redirect()->back()
                ->withInput()
                ->withErrors([
                    'error' => "Terlalu banyak percobaan pendaftaran. Silakan coba lagi dalam {$minutes} menit."
                ]);
        }

        RateLimiter::hit($key, 3600); // 1 hour

        return $next($request);
    }
}

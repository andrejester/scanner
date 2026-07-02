<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckDevelopmentEnvironment
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Cek apakah environment adalah local atau development
        if (!app()->environment('local', 'development')) {
            abort(403, 'Fitur ini hanya tersedia di environment development');
        }

        return $next($request);
    }
}

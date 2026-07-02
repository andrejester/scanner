<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Role
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $role): Response
    {
        if (!$request->user()) {
            abort(401, 'Unauthenticated');
        }

        if (empty($roles)) {
            return $next($request);
        }

        // Parse roles: support both "Role1|Role2" and "Role1,Role2,Role3"
        $allowedRoles = [];
        foreach ($roles as $roleString) {
            // Split by pipe or comma
            $parsed = preg_split('/[|,]/', $roleString);
            $allowedRoles = array_merge($allowedRoles, array_map('trim', $parsed));
        }

        // Remove duplicates
        $allowedRoles = array_unique($allowedRoles);

        // Check user role
        if (!in_array($request->user()->role, $allowedRoles)) {
            abort(403, 'You do not have permission to access this page');
        }

        return $next($request);
    }
}

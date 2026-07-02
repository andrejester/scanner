<?php

namespace App\Http\Middleware;

use App\Models\Visitor;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class Visitors
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip tracking for authenticated users (admin/backend users)
        $route = $request->route();

        //dd($route->gatherMiddleware());
        if ($route) {
            $middlewares = $route->gatherMiddleware();

            foreach ($middlewares as $middleware) {
                if (is_string($middleware) && str_starts_with($middleware, 'role:')) {
                    // Ambil bagian setelah 'role:'
                    $roles = str_replace('role:', '', $middleware);
                    $roleArray = explode('|', $roles);

                    if (in_array('Admin', $roleArray) || in_array('Super Admin', $roleArray)) {
                        return $next($request); // JANGAN CATAT
                    }
                }
            }
        }

        // Skip tracking for admin/backend routes
        $adminPrefixes = [
            '/admin',
            '/dashboard',
            '/user',
            '/backup',
            '/master',
            '/bookingadmin',
            '/blogadmin',
            '/pelatihanadmin',
            '/inbox',
            '/notes',
            '/profile',
            '/setting',
            '/permission',
            '/statistik',
            '/file-manager',
            '/storage-link',
            '/clear-cache',
            '/2fa',
            '/verify-email',
            '/confirm-password',
            '/password',
        ];

        $path = $request->path();
        foreach ($adminPrefixes as $prefix) {
            if (strpos($path, $prefix) === 0) {
                return $next($request);
            }
        }

        // Hanya track request halaman HTML (GET, bukan AJAX, bukan asset)
        $isPageRequest = $request->isMethod('GET')
            && !$request->ajax()
            && !$request->expectsJson()
            && !preg_match('/\.(css|js|png|jpg|jpeg|gif|svg|ico|woff|woff2|ttf|eot|map|webp)(\?.*)?$/i', $path);

        if (!$isPageRequest) {
            return $next($request);
        }

        // catat visitor hanya untuk non-admin
        Visitor::create([
            'ip_address'         => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
            'path'       => $request->path()
        ]);

        Cache::forget('visitors:monthly-all');
        Cache::forget('visitors:yearly-summary');

        // Update tabel statistik (ip, tanggal, hits, online)
        $ip      = $request->ip();
        $tanggal = date('Y-m-d');

        $stat = \App\Models\Statistik::where('ip', $ip)
            ->where('tanggal', $tanggal)
            ->first();

        if ($stat) {
            $stat->increment('hits');
            $stat->online = now();
            $stat->save();
        } else {
            \App\Models\Statistik::create([
                'ip'      => $ip,
                'tanggal' => $tanggal,
                'hits'    => 1,
                'online'  => now(),
            ]);
        }

        return $next($request);
    }
}

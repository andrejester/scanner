<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class TwoFactorMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 🚫 Abaikan route tertentu (public / login / logout / 2FA)
        if (
            $request->is('log-adm') ||
            $request->is('2fa/*') ||
            $request->is('choose-2fa') ||
            $request->is('logout') ||
            $request->is('otp/*') ||
            $request->is('2fa-otp')
        ) {
            return $next($request);
        }

        // ✅ Hanya terapkan untuk route backend (misalnya admin/dashboard)
        if ($request->is('admin/*') || $request->is('dashboard/*')) {

            // 🔒 Jika belum login → ke halaman login
            if (!Auth::check()) {
                return redirect()->route('log-adm');
            }

            $user = Auth::user();

            //Cek cookie remember_2fa: jika masih aktif, skip 2FA
            if ($request->cookie('remember_2fa') === 'true') {
                dd('masuk lewat cookie remember_2fa');
                session(['2fa_verified' => true]);
            }

            //Jika environment tertentu (mis. local) dan email tertentu
            if (app()->environment('production')) {
                $allowedEmails = ['andre.gunawan.lib@gmail.com'];

                if (in_array($user->email, $allowedEmails)) {

                    // Jika belum pernah verifikasi 2FA dan belum punya cookie remember_2fa
                    if (!session()->has('2fa_verified')) {
                        return redirect()->route('2fa.choose');
                    }
                }
            }
        }

        // 🚀 Lanjutkan proses
        return $next($request);
    }
}

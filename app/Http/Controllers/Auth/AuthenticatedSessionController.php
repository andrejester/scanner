<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Mail\TwoFactorCodeMail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Support\Facades\Mail;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        // 🔐 Autentikasi user
        $request->authenticate();
        $request->session()->regenerate();

        $user = $request->user();
        $remember = $request->filled('remember'); // dari checkbox

        $ip = $request->ip();
        $browser = $request->header('User-Agent');

        // ✅ Update waktu login terakhir
        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => $ip,
            'last_login_browser' => $browser,
        ]);

        // ✅ Simpan log aktivitas login
        log_custom('Login berhasil', [
            'email' => $user->email,
            'ip' => $ip,
            'user_agent' => $browser,
            'last_login_at' => now()->format('Y-m-d H:i:s'),
        ]);

        // ✅ Simpan preferensi remember me ke session
        session(['remember_me' => $remember]);

        // ✅ Jika remember_2fa aktif → skip verifikasi 2FA
        if ($remember && $request->cookie('remember_2fa') === 'true') {
            session(['2fa_verified' => true]);
            return redirect()->intended(route('dashboard', absolute: false));
        }

        // 🔒 Jika environment tertentu dan user tertentu → wajib 2FA
        if (
            app()->environment('local') &&
            in_array($user->email, ['andre.gunawan.lib@gmail.com'])
        ) {
            return redirect()->route('2fa.choose');
        }

        // 🚀 Default redirect setelah login sukses
        return redirect()->intended(route('dashboard', absolute: false));
    }


    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();
        $request->session()->forget('2fa_verified');
        $request->session()->forget('2fa:user:id');

        return redirect('/');
    }
}

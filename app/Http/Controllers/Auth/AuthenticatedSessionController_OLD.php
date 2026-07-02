<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Support\Facades\App;

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
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');

        // if (Auth::attempt($credentials, $request->has('remember'))) {
        //     // Jika login berhasil
        //     $request->session()->put('2fa:user:id', Auth::id());
        //     Auth::logout(); // Logout user sementara hingga kode 2FA dikonfirmasi
        //     return redirect()->route('2fa.show'); // Halaman untuk memasukkan kode 2FA
        // }

        if (Auth::attempt($credentials)) {
            // Set session untuk kode 2FA
            // $request->session()->put('2fa:user:id', Auth::id());
            // Auth::logout(); // Logout user sementara hingga kode 2FA dikonfirmasi
            // return redirect()->route('2fa.show'); // Halaman untuk memasukkan kode 2FA
            if (App::environment('produc')) {
                // Jika production, set session untuk 2FA dan logout sementara untuk proses 2FA
                $request->session()->put('2fa:user:id', Auth::id());
                Auth::logout(); // Logout user sementara hingga kode 2FA dikonfirmasi
                return redirect()->route('2fa.show'); // Halaman untuk memasukkan kode 2FA
            }

            // Jika dalam development, langsung arahkan ke dashboard
            return $this->redirectToDashboard(Auth::user());
        }

        // Kredensial salah
        return redirect()->back()
            ->withErrors(['email' => 'Data Tidak Valid.'])
            ->withErrors(['password' => 'Data Tidak Valid.']);
    }

    private function redirectToDashboard($user): RedirectResponse
    {
        $url = $user->role === 'Admin' ? '/dashboard' : '/';
        return redirect()->intended($url);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}

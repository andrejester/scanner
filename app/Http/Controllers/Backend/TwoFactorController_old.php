<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RealRashid\SweetAlert\Facades\Alert;

class TwoFactorController extends Controller
{
    //
    public function show(Request $request)
    {
        if (!$request->session()->has('2fa:user:id')) {
            return redirect()->route('log-adm'); // Redirect jika sesi 2FA tidak ada
        }

        return view('auth.two-factor');
    }

    public function verify(Request $request)
    {
        $request->validate(['google2fa_token' => 'required']);

        // Verifikasi kode 2FA
        $userId = $request->session()->get('2fa:user:id');
        $user = \App\Models\User::find($userId);

        if ($user && $this->verifyTwoFactor($user, $request->google2fa_token)) {
            Auth::login($user); // Login user setelah kode 2FA benar
            $request->session()->forget('2fa:user:id'); // Hapus sesi

            $url = "/home";
            if ($request->user()->role == "Admin") {
                $url = "/dashboard";
            } else {
                $url = "/home";
            }

            // Jika 2FA valid
            Alert::success('Success', 'OK !');
            return redirect()->intended($url);
        }

        return back()->withErrors(['google2fa_token' => 'Invalid 2FA code.']);
    }

    private function verifyTwoFactor($user, $token)
    {
        // Logika verifikasi kode 2FA (menggunakan library Google2FA, dll.)
        return \Google2FA::verifyKey($user->google2fa_secret, $token);
    }
}

<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\TwoFactorCodeMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorController extends Controller
{
    /**
     * Halaman memilih metode verifikasi
     */
    public function choose()
    {
        return view('auth.choose-2fa');
    }

    /**
     * Proses penyimpanan pilihan 2FA user
     */
    public function choosePost(Request $request)
    {
        $request->validate([
            'method' => 'required|in:google,email',
        ]);

        $user = Auth::user();

        if ($request->method === 'google') {
            // Generate secret key Google Authenticator
            //$google2fa = new Google2FA();
            //$secretKey = $google2fa->generateSecretKey();

            //$user->google2fa_secret = $secretKey;
            //$user->save();

            // Redirect ke halaman QR scan (bisa kamu buat nanti)
            return redirect()->route('2fa.google.setup');
        }

        if ($request->method === 'email') {
            // Generate OTP dan kirim via email
            $otp = rand(100000, 999999);

            $user->otp_code = $otp;
            $user->otp_expires_at = now()->addMinutes(5); // OTP berlaku 5 menit
            $user->save();

            Mail::to($user->email)->send(new TwoFactorCodeMail($otp, $user->name));

            return redirect()->route('otp.form')->with('status', 'Kode OTP telah dikirim ke email Anda.');
        }

        return back()->withErrors(['method' => 'Metode tidak valid.']);
    }

    /**
     * Tampilkan form OTP
     */
    public function showVerifyForm()
    {
        return view('auth.2fa-otp');
    }

    /**
     * Verifikasi OTP dari email
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|numeric',
        ]);

        $user = Auth::user();

        if (
            $user->otp_code == $request->otp &&
            $user->otp_expires_at &&
            now()->lessThan($user->otp_expires_at)
        ) {
            // Hapus kode OTP agar tidak bisa digunakan lagi
            $user->otp_code = null;
            $user->otp_expires_at = null;
            $user->save();
            session(['2fa_verified' => true]);

            // Jika user centang "ingat saya", simpan cookie remember_2fa selama 30 hari
            if (session('remember_me')) {
                return redirect()->intended(route('dashboard', absolute: false))
                    ->cookie('remember_2fa', 'true', 60 * 24 * 30); // 30 hari
            }

            return redirect()->route('dashboard')->with('success', 'Verifikasi 2FA berhasil.');
        }

        return back()->withErrors(['otp' => 'Kode OTP salah atau telah kadaluarsa.']);
    }
}

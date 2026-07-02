<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PragmaRX\Google2FA\Google2FA;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class Google2FAController extends Controller
{
    /**
     * Menampilkan halaman setup Google Authenticator
     */
    public function showSetup()
    {
        $user = Auth::user();
        $google2fa = new Google2FA();

        // Jika belum ada secret, buatkan
        if (!$user->google2fa_secret) {
            $user->google2fa_secret = $google2fa->generateSecretKey();
            $user->save();
        }

        // Generate QR code URL
        $QR_Image = $google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $user->google2fa_secret
        );

        // Buat QR dalam bentuk SVG (agar bisa langsung tampil di blade)
        $renderer = new ImageRenderer(
            new RendererStyle(200),
            new SvgImageBackEnd()
        );
        $writer = new Writer($renderer);
        $qrCodeSvg = $writer->writeString($QR_Image);

        return view('auth.google-setup', [
            'user' => $user,
            'qrCodeSvg' => $qrCodeSvg,
        ]);
    }

    /**
     * Verifikasi kode dari Google Authenticator
     */
    public function verify(Request $request)
    {
        $request->validate(['google2fa_token' => 'required']);

        $google2fa = new Google2FA();
        $user = Auth::user();

        $valid = $google2fa->verifyKey($user->google2fa_secret, $request->google2fa_token);

        if ($valid) {

            // Jika user centang "ingat saya", simpan cookie remember_2fa selama 30 hari
            if (session('remember_me')) {
                return redirect()->intended(route('dashboard', absolute: false))
                    ->cookie('remember_2fa', 'true', 60 * 24 * 30); // 30 hari
            }

            session(['2fa_verified' => true]);
            return redirect()->route('dashboard')->with('success', 'Verifikasi Google Authenticator berhasil.');
        }

        return back()->withErrors(['google2fa_token' => 'Kode tidak valid, coba lagi.']);
    }
}

<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Library\Template;
use App\Models\User;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Writer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use PragmaRX\Google2FA\Google2FA;
use RealRashid\SweetAlert\Facades\Alert;

class ProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        log_custom("Buka menu profile");
        $data = Template::get();
        array_push($data['pilihCss'],  "apex-charts", "card-analytics");

        $data['jsTambahan'] = "
        $('#cashier').addClass('active');
        ";

        $google2fa = new Google2FA();

        $qrCodeUrl = $google2fa->getQRCodeUrl(
            config('app.name'),
            Auth::user()->email,
            Auth::user()->google2fa_secret
        );

        $renderer = new ImageRenderer(
            new \BaconQrCode\Renderer\RendererStyle\RendererStyle(200),
            new SvgImageBackEnd()
        );

        $writer = new Writer($renderer);
        $data['qrCodeImage'] = base64_encode($writer->writeString($qrCodeUrl));

        return view("user.profile", $data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = User::find(Auth::user()->id);
        $request->validate([
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/^(?=.*[A-Z])(?=.*[\W_])/',
            ],
            'password_confirmation' => 'required|same:password',
            'old_password' => 'required',
        ]);
        if (!Hash::check($request->input('old_password'), $user->password)) {
            return back()->withErrors(['old_password' => 'The provided password does not match your current password.']);
        }
        // Update user password
        $user->update([
            'password' => Hash::make($request->input('password')),
        ]);
        log_custom("Change Password");

        Alert::info('Info Title', 'Password berhasil dirubah');
        return redirect()->back();
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = User::find($id);

        $request->validate([
            // 'email' => [
            //     'required',
            //     'email',
            //     Rule::unique('users')->ignore($user->id),
            // ],
            'name' => 'required',
        ]);
        $data = $request->all();
        log_custom("Update Profile", $data);
        // Update user data with the request data
        $user->update($data);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}

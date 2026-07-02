<?php

namespace App\Http\Controllers\System;

use App\DataTables\UserDataTable;
use App\Http\Controllers\Controller;
use App\Library\Template;
use App\Models\User;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Writer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use PragmaRX\Google2FAQRCode\Google2FA;

use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(UserDataTable $dataTable)
    {
        abort_if(Gate::denies('user_read'), 403);
        $data = Template::get("datatable");
        $data['jsTambahan'] = "
        $('#user').addClass('active');
        $('#role-permission').addClass('open active');
        ";
        return $dataTable->render("user/user", $data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

        abort_if(Gate::denies('user_write'), 403);

        $google2fa = new Google2FA();
        $secret = $google2fa->generateSecretKey();

        // Generate QR code URL
        $QR_Image = sprintf(
            'otpauth://totp/%s:%s?secret=%s&issuer=%s',
            config('app.name'),
            old('email', 'sofanipin@gmail.com'),
            $secret,
            config('app.name')
        );

        // Menggunakan BaconQrCode untuk membuat QR code
        $renderer = new ImageRenderer(
            new \BaconQrCode\Renderer\RendererStyle\RendererStyle(400),
            new SvgImageBackEnd()
        );

        $writer = new Writer($renderer);
        $qrCode = $writer->writeString($QR_Image);
        return view('user.user_create', [
            'qrCode' => $qrCode,
            'secret' => $secret
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        abort_if(Gate::denies('user_write'), 403);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users',
            'role' => 'required',
            'google2fa_secret' => 'required',
        ]);
        $data = $request->all();
        $data['password'] = Hash::make('123456789');

        //dd($data);

        $user = User::create($data);
        $role = Role::whereId($request->role)->first();

        $user->assignRole($role);
        return response()->json("ok");
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
        abort_if(Gate::denies('user_update'), 403);

        // Inisialisasi Google2FA
        $google2fa = new Google2FA();

        $users = User::findOrFail($id);

        // Inisialisasi Google2FA
        $google2fa = new Google2FA();

        // Dapatkan secret 2FA pengguna
        $secret = $users->google2fa_secret;
        $QR_Image = sprintf(
            'otpauth://totp/%s:%s?secret=%s&issuer=%s',
            config('app.name'),
            old('email', 'pentamediatraining@gmail.com'),
            $secret,
            config('app.name')
        );

        // Menggunakan BaconQrCode untuk membuat QR code
        $renderer = new ImageRenderer(
            new \BaconQrCode\Renderer\RendererStyle\RendererStyle(400),
            new SvgImageBackEnd()
        );

        $writer = new Writer($renderer);
        $data['googleChartApiUrl'] = $writer->writeString($QR_Image);
        $data['user'] = User::whereId($id)->first();

        return view('user.user_edit', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        abort_if(Gate::denies('user_update'), 403);
        $request->validate([
            'name' => 'required|string|max:255|unique:roles',
            'email' => [
                'required',
                'email',
                Rule::unique('users')->ignore($id),
            ],
            'role' => 'required'
        ]);
        $data = $request->all();
        // $reset = $data['reset'];
        // if ($reset == "1") {
        //     $data['password'] = Hash::make('111');
        // }
        unset($data['_token'], $data['role']); //, $data['reset']
        User::whereId($id)->update($data);

        $role = Role::whereId($request->role)->first();
        $user = User::find($id);
        $user->assignRole($role);

        log_custom("Simpan User", $data);
        return response()->json("ok");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {

        abort_if(Gate::denies('user_delete'), 403);

        $user = User::find($id);
        if ($user) {
            $user->roles()->detach();
            $user->delete();
        }
        return response()->json("ok");
    }
}

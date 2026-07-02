<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Backend\TelegramService;
use App\Http\Controllers\Controller;
use App\Library\Template;
use App\Models\System\Config;
use App\Models\System\Setting;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;

class SettingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    protected $telegram;
    protected $request;

    public function __construct(TelegramService $telegram, Request $request)
    {
        $this->telegram = $telegram;
        $this->request = $request;
    }

    public function index()
    {
        log_custom("Buka menu setting");
        abort_if(Gate::denies('setting_read'), 403);
        $data = Template::get();
        array_push($data['pilihCss'],  "apex-charts", "card-analytics");
        $data['setting'] = Setting::first();
        $data['jsTambahan'] = "
        $('#cashier').addClass('active');
        ";
        return view("user.setting", $data);
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
    public function store(Request $request) {}

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
    public function update(Request $request, Setting $setting)
    {
        abort_if(Gate::denies('setting_read'), 403);

        $request->validate([
            'company_name'     => 'required|string|max:255',
            'company_address'  => 'required|string',
            'company_email'    => 'required|email|max:255',
            'company_phone'    => 'nullable|string|max:50',
            'company_maps'     => 'nullable|string',
            'company_photo'    => 'nullable|string|max:255',
            'company_whatsapp' => 'nullable|string|max:50',
            'company_website'  => 'nullable|string|max:255',
            'company_ig'       => 'nullable|string|max:255',
            'company_admin'    => 'nullable|string|max:255',
            'company_youtube'  => 'nullable|string|max:255',
            'company_summary'  => 'nullable|string',
            'company_deskripsi' => 'nullable|string',
            'company_visi'     => 'nullable|string',
            'company_misi'     => 'nullable|string',
            'company_file'     => 'nullable|string|max:255',
            'company_favicon'  => 'nullable|string|max:255',
        ]);

        $data = $request->only([
            'company_name',
            'company_address',
            'company_email',
            'company_phone',
            'company_maps',
            'company_photo',
            'company_whatsapp',
            'company_website',
            'company_ig',
            'company_admin',
            'company_youtube',
            'company_summary',
            'company_deskripsi',
            'company_visi',
            'company_misi',
            'company_file',
            'company_favicon',
        ]);


        $data['company_slug'] = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $request->company_name)));
        // dd($request);
        $setting->update($data);

        Alert::info('Info', 'Data berhasil diperbarui');

        $message = "IP " . $this->request->ip() . " Telah Melakukan Pembaruan Menu Setting Pada Website " . env('APP_NAME');
        $this->telegram->sendMessage($message);

        return response()->json("reload", 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}

<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Library\Template;
use App\Models\backend\MasterHerosection;
use RealRashid\SweetAlert\Facades\Alert;

class MasterHerosectionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //log_custom("Buka menu hero section");
        abort_if(Gate::denies('masterherosection_read'), 403);

        $data = Template::get();

        // Tambahan CSS bila diperlukan
        array_push($data['pilihCss'], "apex-charts", "card-analytics");

        // Ambil data hero section pertama (karena biasanya hanya satu record aktif)
        $data['masterherosection'] = MasterHerosection::first();

        // Tambahkan JS khusus halaman
        $data['jsTambahan'] = " $('#content').addClass('active');";

        return view('backend.masterherosection', $data);
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
        //
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
    public function update(Request $request, MasterHerosection $masterherosection)
    {
        // Cek hak akses
        abort_if(Gate::denies('masterherosection_edit'), 403);

        // Ambil template atau data tambahan (opsional, bisa kamu sesuaikan)
        $data = Template::get();
        array_push($data['pilihCss'], "apex-charts", "card-analytics");
        $data['masterherosection'] = MasterHerosection::first();
        $data['jsTambahan'] = "
        $('#content').addClass('active');
    ";

        // Validasi input
        $request->validate([
            'subtitle' => 'nullable|string|max:255',
            'title_bold' => 'required|string|max:255',
            'title_highlight' => 'nullable|string|max:255',
            'title_suffix' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'promo_title' => 'nullable|string|max:255',
            'promo_text' => 'nullable|string',
            'promo_icon' => 'nullable|string|max:255',
            'customer_service_label' => 'nullable|string|max:255',
            'customer_service_phone' => 'nullable|string|max:255',
            'customer_service_link' => 'nullable|string|max:255',
            'image_path' => 'string|required',
            'is_active' => 'boolean',
        ]);
        // dd($request->all());
        // Update data
        $masterherosection->update($request->all());

        // Tampilkan notifikasi sukses
        Alert::info('Informasi', 'Data Hero Section berhasil diperbarui.');

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

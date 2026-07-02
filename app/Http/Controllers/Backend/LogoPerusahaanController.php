<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\LogoPerusahaanDataTable;
use App\Http\Controllers\Controller;
use App\Library\Template;
use App\Models\Backend\LogoPerusahaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use RealRashid\SweetAlert\Facades\Alert;


class LogoPerusahaanController extends Controller
{
    public function index(LogoPerusahaanDataTable $dataTable)
    {
        abort_if(Gate::denies('logoPerusahaan_read'), 403);
        log_custom("Buka menu master logo Perusahaan");
        $data = Template::get("datatable");
        $data['jsTambahan'] = "
        $('#logoPerusahaan').addClass('active');
        ";
        return $dataTable->render("backend.logoPerusahaan.logoPerusahaan", $data);
        //return $dataTable->view("backend.logoPerusahaan.logoPerusahaan",$data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        abort_if(Gate::denies('logoPerusahaan_write'), 403);
        log_custom("Buka menu tambah logoPerusahaan");
        $data = Template::get("datatable");

        return view('backend.logoPerusahaan.logoPerusahaan_create', $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        abort_if(Gate::denies('logoPerusahaan_write'), 403);

        // return $request->all();
        $request->validate([
            'title' => 'string|required|max:50',
            'description' => 'string|nullable',
            'photo' => 'string|required',
            'status' => 'required|in:active,inactive',
        ]);
        $data           = $request->all();
        $slug           = Str::slug($request->title);
        $count          = LogoPerusahaan::where('slug', $slug)->count();
        if ($count > 0) $slug = $slug . '-' . date('ymdis') . '-' . rand(0, 999);
        $data['slug'] = $slug;
        LogoPerusahaan::create($data);

        Alert::info('Info Title', 'Data Berhasil Di tambah');
        return redirect()->route('logoPerusahaan.index');
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
    public function edit($LogoPerusahaan)
    {
        // dd($LogoPerusahaan);
        abort_if(Gate::denies('logoperusahaan_update'), 403);
        //log_custom("Buka menu edit master pinjaman " . $LogoPerusahaan->id);
        $data = Template::get("datatable");
        $data['logoPerusahaan'] = LogoPerusahaan::findOrFail($LogoPerusahaan);

        return view('backend.logoPerusahaan.logoPerusahaan_edit', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LogoPerusahaan $logoPerusahaan)
    {
        // dd("asdasd");
        abort_if(Gate::denies('logoperusahaan_update'), 403);
        $request->validate([
            'title' => 'string|required|max:50',
            'description' => 'string|nullable',
            'photo' => 'string|required',
            'status' => 'required|in:active,inactive',
        ]);
        log_custom("Update data logoPerusahaan " . $logoPerusahaan->id, $logoPerusahaan->toArray());
        $logoPerusahaan->update($request->all());

        Alert::info(
            'Info Title',
            'Data Berhasil Diperbarui'
        );
        return redirect()->route('logoPerusahaan.index');
        // return response()->json($LogoPerusahaan, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LogoPerusahaan $logoPerusahaan)
    {
        abort_if(Gate::denies('logoPerusahaan_delete'), 403);
        $logoPerusahaan->delete();
        log_custom("Hapus data " . $logoPerusahaan->id, $logoPerusahaan->toArray());
        return response()->json('ok'); // Mengirim respons JSON 'ok'
    }
}

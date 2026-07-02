<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\MasterLayananDataTable;
use App\Http\Controllers\Controller;
use App\Library\Template;
use App\Models\Backend\MasterLayanan;
use App\Models\Master\MasterLayananKami;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use RealRashid\SweetAlert\Facades\Alert;

class MasterLayananController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(MasterLayananDataTable $dataTable)
    {
        abort_if(Gate::denies('masterlayanan_read'), 403);
        log_custom("Buka menu master masterlayanan");
        $data = Template::get("datatable");
        $data['jsTambahan'] = "
        $('#masterlayanan').addClass('active');
        ";
        return $dataTable->render("backend.masterlayanan.masterlayanan", $data);
        //return $dataTable->view("backend.masterlayanan.masterlayanan",$data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        abort_if(Gate::denies('masterlayanan_write'), 403);
        log_custom("Buka menu tambah MasterLayanan");
        $data = Template::get("datatable");

        return view('backend.masterlayanan.masterlayanan_create', $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        abort_if(Gate::denies('masterlayanan_write'), 403);

        // return $request->all();
        $request->validate([
            'title' => 'string|required|max:255',
            'keterangan' => 'string|required'
        ]);

        $data           = $request->all();
        MasterLayananKami::create($data);

        Alert::info('Info Title', 'Data Berhasil Di tambah');
        return redirect()->route('masterlayanan.index');
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
    public function edit($MasterLayanan)
    {
        // dd($MasterLayanan);
        abort_if(Gate::denies('masterlayanan_update'), 403);
        //log_custom("Buka menu edit master pinjaman " . $MasterLayanan->id);
        $data = Template::get("datatable");
        $data['masterlayanan'] = MasterLayananKami::findOrFail($MasterLayanan);

        return view('backend.masterlayanan.masterlayanan_edit', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MasterLayananKami $masterlayanan)
    {
        abort_if(Gate::denies('masterlayanan_update'), 403);

        $request->validate([
            'title' => 'string|required|max:255',
            'keterangan' => 'string|required'
        ]);

        log_custom("Update data masterlayanan " . $masterlayanan->id, $masterlayanan->toArray());
        $masterlayanan->update($request->all());

        Alert::info(
            'Info Title',
            'Data Berhasil Diperbarui'
        );
        return redirect()->route('masterlayanan.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MasterLayananKami $masterlayanan)
    {
        abort_if(Gate::denies('masterlayanan_delete'), 403);
        $masterlayanan->delete();
        log_custom("Hapus data " . $masterlayanan->id, $masterlayanan->toArray());
        return response()->json('ok'); // Mengirim respons JSON 'ok'
    }
}

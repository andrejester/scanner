<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\MasterAkreditasiDataTable;
use App\Http\Controllers\Controller;
use App\Library\Template;
use App\Models\Backend\MasterAkreditasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use RealRashid\SweetAlert\Facades\Alert;

class MasterAkreditasiController extends Controller
{
    public function index(MasterAkreditasiDataTable $dataTable)
    {
        abort_if(Gate::denies('masterakreditasi_read'), 403);
        log_custom("Buka menu akreditasi");
        $data = Template::get("datatable");
        $data['jsTambahan'] = "$('#masterakreditasi').addClass('active');";
        return $dataTable->render("backend.masterakreditasi.masterakreditasi", $data);
    }

    public function create()
    {
        abort_if(Gate::denies('masterakreditasi_write'), 403);
        $data = Template::get("datatable");
        return view('backend.masterakreditasi.masterakreditasi_create', $data);
    }

    public function store(Request $request)
    {
        abort_if(Gate::denies('masterakreditasi_write'), 403);

        $request->validate([
            'nama_lembaga'   => 'required|string|max:255',
            'program_studi'  => 'nullable|string|max:255',
            'jenjang'        => 'required|in:s2,s3,institusi,lainnya',
            'peringkat'      => 'required|string|max:100',
            'nomor_sk'       => 'nullable|string|max:255',
            'tanggal_sk'     => 'nullable|date',
            'berlaku_sampai' => 'nullable|date|after_or_equal:tanggal_sk',
            'sertifikat'     => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
            'keterangan'     => 'nullable|string',
            'status'         => 'required|in:active,inactive',
        ]);

        $data = $request->except('sertifikat');

        if ($request->hasFile('sertifikat')) {
            $data['sertifikat'] = $request->file('sertifikat')->store('akreditasi', 'public/files/2');
        }

        MasterAkreditasi::create($data);

        Alert::info('Info', 'Data Berhasil Ditambah');
        return redirect()->route('masterakreditasi.index');
    }

    public function show(string $id)
    {
        //
    }

    public function edit($id)
    {
        abort_if(Gate::denies('masterakreditasi_update'), 403);
        $data = Template::get("datatable");
        $data['masterakreditasi'] = MasterAkreditasi::findOrFail($id);
        return view('backend.masterakreditasi.masterakreditasi_edit', $data);
    }

    public function update(Request $request, MasterAkreditasi $masterakreditasi)
    {
        abort_if(Gate::denies('masterakreditasi_update'), 403);

        $request->validate([
            'nama_lembaga'   => 'required|string|max:255',
            'program_studi'  => 'nullable|string|max:255',
            'jenjang'        => 'required|in:s2,s3,institusi,lainnya',
            'peringkat'      => 'required|string|max:100',
            'nomor_sk'       => 'nullable|string|max:255',
            'tanggal_sk'     => 'nullable|date',
            'berlaku_sampai' => 'nullable|date|after_or_equal:tanggal_sk',
            'sertifikat'     => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
            'keterangan'     => 'nullable|string',
            'status'         => 'required|in:active,inactive',
        ]);

        $data = $request->except('sertifikat');

        if ($request->hasFile('sertifikat')) {
            if ($masterakreditasi->sertifikat) Storage::disk('public/files/2')->delete($masterakreditasi->sertifikat);
            $data['sertifikat'] = $request->file('sertifikat')->store('akreditasi', 'public/files/2');
        }

        log_custom("Update akreditasi " . $masterakreditasi->id, $masterakreditasi->toArray());
        $masterakreditasi->update($data);

        Alert::info('Info', 'Data Berhasil Diperbarui');
        return redirect()->route('masterakreditasi.index');
    }

    public function destroy(MasterAkreditasi $masterakreditasi)
    {
        abort_if(Gate::denies('masterakreditasi_delete'), 403);

        if ($masterakreditasi->sertifikat) Storage::disk('public/files/2')->delete($masterakreditasi->sertifikat);

        $masterakreditasi->delete();
        log_custom("Hapus akreditasi " . $masterakreditasi->id);
        return response()->json('ok');
    }
}

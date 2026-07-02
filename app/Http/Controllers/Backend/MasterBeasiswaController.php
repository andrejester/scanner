<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\MasterBeasiswaDataTable;
use App\Http\Controllers\Controller;
use App\Library\Template;
use App\Models\Backend\MasterBeasiswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use RealRashid\SweetAlert\Facades\Alert;

class MasterBeasiswaController extends Controller
{
    public function index(MasterBeasiswaDataTable $dataTable)
    {
        abort_if(Gate::denies('masterbeasiswa_read'), 403);
        log_custom("Buka menu beasiswa");
        $data = Template::get("datatable");
        $data['jsTambahan'] = "$('#masterbeasiswa').addClass('active');";
        return $dataTable->render("backend.masterbeasiswa.masterbeasiswa", $data);
    }

    public function create()
    {
        abort_if(Gate::denies('masterbeasiswa_write'), 403);
        $data = Template::get("datatable");
        return view('backend.masterbeasiswa.masterbeasiswa_create', $data);
    }

    public function store(Request $request)
    {
        abort_if(Gate::denies('masterbeasiswa_write'), 403);

        $request->validate([
            'nama_beasiswa'     => 'required|string|max:255',
            'penyelenggara'     => 'required|string|max:255',
            'jenis'             => 'required|in:penuh,parsial,prestasi,ekonomi,lainnya',
            'deskripsi'         => 'nullable|string',
            'persyaratan'       => 'nullable|string',
            'benefit'           => 'nullable|string',
            'cara_daftar'       => 'nullable|string',
            'foto'              => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'tanggal_buka'      => 'nullable|date',
            'tanggal_tutup'     => 'nullable|date|after_or_equal:tanggal_buka',
            'link_pendaftaran'  => 'nullable|url|max:500',
            'kontak'            => 'nullable|string|max:255',
            'is_active'         => 'required|in:0,1',
        ]);

        $data = $request->except('foto');

        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('beasiswa', 'public/files/2');
        }

        MasterBeasiswa::create($data);

        Alert::info('Info', 'Data Berhasil Ditambah');
        return redirect()->route('masterbeasiswa.index');
    }

    public function show(string $id)
    {
        //
    }

    public function edit(MasterBeasiswa $masterbeasiswa)
    {
        abort_if(Gate::denies('masterbeasiswa_update'), 403);
        $data = Template::get("datatable");
        $data['beasiswa'] = $masterbeasiswa;
        return view('backend.masterbeasiswa.masterbeasiswa_edit', $data);
    }

    public function update(Request $request, MasterBeasiswa $masterbeasiswa)
    {
        abort_if(Gate::denies('masterbeasiswa_update'), 403);

        $request->validate([
            'nama_beasiswa'     => 'required|string|max:255',
            'penyelenggara'     => 'required|string|max:255',
            'jenis'             => 'required|in:penuh,parsial,prestasi,ekonomi,lainnya',
            'deskripsi'         => 'nullable|string',
            'persyaratan'       => 'nullable|string',
            'benefit'           => 'nullable|string',
            'cara_daftar'       => 'nullable|string',
            'foto'              => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'tanggal_buka'      => 'nullable|date',
            'tanggal_tutup'     => 'nullable|date|after_or_equal:tanggal_buka',
            'link_pendaftaran'  => 'nullable|url|max:500',
            'kontak'            => 'nullable|string|max:255',
            'is_active'         => 'required|in:0,1',
        ]);

        $data = $request->except('foto');

        if ($request->hasFile('foto')) {
            if ($masterbeasiswa->foto) Storage::disk('public/files/2')->delete($masterbeasiswa->foto);
            $data['foto'] = $request->file('foto')->store('beasiswa', 'public/files/2');
        }

        log_custom("Update beasiswa " . $masterbeasiswa->id, $masterbeasiswa->toArray());
        $masterbeasiswa->update($data);

        Alert::info('Info', 'Data Berhasil Diperbarui');
        return redirect()->route('masterbeasiswa.index');
    }

    public function destroy(MasterBeasiswa $masterbeasiswa)
    {
        abort_if(Gate::denies('masterbeasiswa_delete'), 403);

        if ($masterbeasiswa->foto) Storage::disk('public/files/2')->delete($masterbeasiswa->foto);

        $masterbeasiswa->delete();
        log_custom("Hapus beasiswa " . $masterbeasiswa->id);
        return response()->json('ok');
    }
}

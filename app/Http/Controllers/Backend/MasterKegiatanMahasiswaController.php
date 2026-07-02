<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\MasterKegiatanMahasiswaDataTable;
use App\Http\Controllers\Controller;
use App\Library\Template;
use App\Models\Backend\MasterKegiatanMahasiswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RealRashid\SweetAlert\Facades\Alert;

class MasterKegiatanMahasiswaController extends Controller
{
    public function index(MasterKegiatanMahasiswaDataTable $dataTable)
    {
        abort_if(Gate::denies('masterkegiatanmahasiswa_read'), 403);
        log_custom("Buka menu kegiatan mahasiswa");
        $data = Template::get("datatable");
        $data['jsTambahan'] = "$('#masterkegiatanmahasiswa').addClass('active');";
        return $dataTable->render("backend.masterkegiatanmahasiswa.masterkegiatanmahasiswa", $data);
    }

    public function create()
    {
        abort_if(Gate::denies('masterkegiatanmahasiswa_write'), 403);
        $data = Template::get("datatable");
        return view('backend.masterkegiatanmahasiswa.masterkegiatanmahasiswa_create', $data);
    }

    public function store(Request $request)
    {
        abort_if(Gate::denies('masterkegiatanmahasiswa_write'), 403);

        $request->validate([
            'judul'             => 'required|string|max:255',
            'kategori'          => 'nullable|string|max:100',
            'deskripsi'         => 'nullable|string',
            'konten'            => 'nullable|string',
            'tanggal_kegiatan'  => 'nullable|date',
            'lokasi'            => 'nullable|string|max:255',
            'foto'              => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'status'            => 'required|in:active,inactive',
        ]);

        $data = $request->except('foto');

        $slug  = Str::slug($request->judul);
        $count = MasterKegiatanMahasiswa::where('slug', $slug)->count();
        if ($count > 0) $slug .= '-' . date('ymdis') . '-' . rand(0, 999);
        $data['slug'] = $slug;

        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('kegiatan_mahasiswa', 'public/files/2');
        }

        MasterKegiatanMahasiswa::create($data);

        Alert::info('Info', 'Data Berhasil Ditambah');
        return redirect()->route('masterkegiatanmahasiswa.index');
    }

    public function show(string $id)
    {
        //
    }

    public function edit($id)
    {
        abort_if(Gate::denies('masterkegiatanmahasiswa_update'), 403);
        $data = Template::get("datatable");
        $data['masterkegiatanmahasiswa'] = MasterKegiatanMahasiswa::findOrFail($id);
        return view('backend.masterkegiatanmahasiswa.masterkegiatanmahasiswa_edit', $data);
    }

    public function update(Request $request, MasterKegiatanMahasiswa $masterkegiatanmahasiswa)
    {
        abort_if(Gate::denies('masterkegiatanmahasiswa_update'), 403);

        $request->validate([
            'judul'             => 'required|string|max:255',
            'kategori'          => 'nullable|string|max:100',
            'deskripsi'         => 'nullable|string',
            'konten'            => 'nullable|string',
            'tanggal_kegiatan'  => 'nullable|date',
            'lokasi'            => 'nullable|string|max:255',
            'foto'              => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'status'            => 'required|in:active,inactive',
        ]);

        $data = $request->except('foto');

        if ($request->judul !== $masterkegiatanmahasiswa->judul) {
            $slug  = Str::slug($request->judul);
            $count = MasterKegiatanMahasiswa::where('slug', $slug)->where('id', '!=', $masterkegiatanmahasiswa->id)->count();
            if ($count > 0) $slug .= '-' . date('ymdis') . '-' . rand(0, 999);
            $data['slug'] = $slug;
        }

        if ($request->hasFile('foto')) {
            if ($masterkegiatanmahasiswa->foto) Storage::disk('public/files/2')->delete($masterkegiatanmahasiswa->foto);
            $data['foto'] = $request->file('foto')->store('kegiatan_mahasiswa', 'public/files/2');
        }

        log_custom("Update kegiatan mahasiswa " . $masterkegiatanmahasiswa->id, $masterkegiatanmahasiswa->toArray());
        $masterkegiatanmahasiswa->update($data);

        Alert::info('Info', 'Data Berhasil Diperbarui');
        return redirect()->route('masterkegiatanmahasiswa.index');
    }

    public function destroy(MasterKegiatanMahasiswa $masterkegiatanmahasiswa)
    {
        abort_if(Gate::denies('masterkegiatanmahasiswa_delete'), 403);

        if ($masterkegiatanmahasiswa->foto) Storage::disk('public/files/2')->delete($masterkegiatanmahasiswa->foto);

        $masterkegiatanmahasiswa->delete();
        log_custom("Hapus kegiatan mahasiswa " . $masterkegiatanmahasiswa->id);
        return response()->json('ok');
    }
}

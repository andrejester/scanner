<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\MasterEventDataTable;
use App\Http\Controllers\Controller;
use App\Library\Template;
use App\Models\Backend\MasterEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RealRashid\SweetAlert\Facades\Alert;

class MasterEventController extends Controller
{
    public function index(MasterEventDataTable $dataTable)
    {
        abort_if(Gate::denies('masterevent_read'), 403);
        log_custom("Buka menu event");
        $data = Template::get("datatable");
        $data['jsTambahan'] = "$('#masterevent').addClass('active');";
        return $dataTable->render("backend.masterevent.masterevent", $data);
    }

    public function create()
    {
        abort_if(Gate::denies('masterevent_write'), 403);
        $data = Template::get("datatable");
        return view('backend.masterevent.masterevent_create', $data);
    }

    public function store(Request $request)
    {
        abort_if(Gate::denies('masterevent_write'), 403);

        $request->validate([
            'judul'            => 'required|string|max:255',
            'kategori'         => 'nullable|string|max:100',
            'deskripsi'        => 'nullable|string',
            'konten'           => 'nullable|string',
            'tanggal_mulai'    => 'required|date',
            'tanggal_selesai'  => 'nullable|date|after_or_equal:tanggal_mulai',
            'lokasi'           => 'nullable|string|max:255',
            'penyelenggara'    => 'nullable|string|max:255',
            'foto'             => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'status'           => 'required|in:active,inactive',
        ]);

        $data = $request->except('foto');

        $slug  = Str::slug($request->judul);
        $count = MasterEvent::where('slug', $slug)->count();
        if ($count > 0) $slug .= '-' . date('ymdis') . '-' . rand(0, 999);
        $data['slug'] = $slug;

        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('events', 'public/files/2');
        }

        MasterEvent::create($data);

        Alert::info('Info', 'Data Berhasil Ditambah');
        return redirect()->route('masterevent.index');
    }

    public function show(string $id)
    {
        //
    }

    public function edit($id)
    {
        abort_if(Gate::denies('masterevent_update'), 403);
        $data = Template::get("datatable");
        $data['masterevent'] = MasterEvent::findOrFail($id);
        return view('backend.masterevent.masterevent_edit', $data);
    }

    public function update(Request $request, MasterEvent $masterevent)
    {
        abort_if(Gate::denies('masterevent_update'), 403);

        $request->validate([
            'judul'            => 'required|string|max:255',
            'kategori'         => 'nullable|string|max:100',
            'deskripsi'        => 'nullable|string',
            'konten'           => 'nullable|string',
            'tanggal_mulai'    => 'required|date',
            'tanggal_selesai'  => 'nullable|date|after_or_equal:tanggal_mulai',
            'lokasi'           => 'nullable|string|max:255',
            'penyelenggara'    => 'nullable|string|max:255',
            'foto'             => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'status'           => 'required|in:active,inactive',
        ]);

        $data = $request->except('foto');

        if ($request->judul !== $masterevent->judul) {
            $slug  = Str::slug($request->judul);
            $count = MasterEvent::where('slug', $slug)->where('id', '!=', $masterevent->id)->count();
            if ($count > 0) $slug .= '-' . date('ymdis') . '-' . rand(0, 999);
            $data['slug'] = $slug;
        }

        if ($request->hasFile('foto')) {
            if ($masterevent->foto) Storage::disk('public/files/2')->delete($masterevent->foto);
            $data['foto'] = $request->file('foto')->store('events', 'public/files/2');
        }

        log_custom("Update event " . $masterevent->id, $masterevent->toArray());
        $masterevent->update($data);

        Alert::info('Info', 'Data Berhasil Diperbarui');
        return redirect()->route('masterevent.index');
    }

    public function destroy(MasterEvent $masterevent)
    {
        abort_if(Gate::denies('masterevent_delete'), 403);

        if ($masterevent->foto) Storage::disk('public/files/2')->delete($masterevent->foto);

        $masterevent->delete();
        log_custom("Hapus event " . $masterevent->id);
        return response()->json('ok');
    }
}

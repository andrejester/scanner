<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\MasterKategoriBeritaDataTable;
use App\Http\Controllers\Controller;
use App\Library\Template;
use App\Models\Backend\MasterKategoriBerita;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Support\Str;

class MasterKategoriBeritaController extends Controller
{
    public function index(MasterKategoriBeritaDataTable $dataTable)
    {
        abort_if(Gate::denies('masterkategoriberita_read'), 403);
        log_custom("Buka menu master masterkategoriberita");
        $data = Template::get("datatable");
        $data['jsTambahan'] = "
        $('#masterkategoriberita').addClass('active');
        ";
        return $dataTable->render("backend.masterkategoriberita.masterkategoriberita", $data);
        //return $dataTable->view("backend.masterkategoriberita.masterkategoriberita",$data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        abort_if(Gate::denies('masterkategoriberita_write'), 403);
        log_custom("Buka menu tambah Kategori Berita");
        $data = Template::get("datatable");

        return view('backend.masterkategoriberita.masterkategoriberita_create', $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        abort_if(Gate::denies('masterkategoriberita_write'), 403);

        // return $request->all();
        $request->validate([
            'nama_kategori' => 'string|required|max:50',
            'is_active' => 'required|in:active,inactive',
        ]);
        $data           = $request->all();
        $slug           = Str::slug($request->nama_kategori);
        $count          = MasterKategoriBerita::where('slug', $slug)->count();
        if ($count > 0) $slug = $slug . '-' . date('ymdis') . '-' . rand(0, 999);
        $data['slug'] = $slug;
        MasterKategoriBerita::create($data);

        Alert::info('Info Title', 'Data Berhasil Di tambah');
        return redirect()->route('masterkategoriberita.index');
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
    public function edit($MasterKategoriBerita)
    {
        // dd($MasterKategoriBerita);
        abort_if(Gate::denies('masterkategoriberita_update'), 403);
        //log_custom("Buka menu edit master pinjaman " . $MasterKategoriBerita->id);
        $data = Template::get("datatable");
        $data['masterkategoriberita'] = MasterKategoriBerita::findOrFail($MasterKategoriBerita);

        return view('backend.masterkategoriberita.masterkategoriberita_edit', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MasterKategoriBerita $masterkategoriberita)
    {
        abort_if(Gate::denies('masterkategoriberita_update'), 403);

        $validated = $request->validate([
            'nama_kategori' => 'string|required|max:50',
            'is_active' => 'required|in:active,inactive',
        ]);

        log_custom("Update data masterkategoriberita " . $masterkategoriberita->id, $masterkategoriberita->toArray());

        if ($validated['nama_kategori'] !== $masterkategoriberita->nama_kategori) {
            $slug = Str::slug($validated['nama_kategori']);
            if (MasterKategoriBerita::where('slug', $slug)->where('id', '!=', $masterkategoriberita->id)->exists()) {
                $slug .= '-' . date('ymdis') . '-' . rand(0, 999);
            }
            $validated['slug'] = $slug;
        }

        $masterkategoriberita->update($validated);

        Alert::info(
            'Info Title',
            'Data Berhasil Diperbarui'
        );
        return redirect()->route('masterkategoriberita.index');
        // return response()->json($MasterKategoriBerita, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MasterKategoriBerita $masterkategoriberita)
    {
        abort_if(Gate::denies('masterkategoriberita_delete'), 403);
        $masterkategoriberita->delete();
        log_custom("Hapus data " . $masterkategoriberita->id, $masterkategoriberita->toArray());
        return response()->json('ok'); // Mengirim respons JSON 'ok'
    }
}

<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\MasterKategoriDownloadDataTable;
use App\Http\Controllers\Controller;
use App\Library\Template;
use App\Models\Backend\MasterKategoriDownload;
use App\Models\Master\MasterDownloadCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use RealRashid\SweetAlert\Facades\Alert;

class MasterKategoriDownloadController extends Controller
{
    public function index(MasterKategoriDownloadDataTable $dataTable)
    {
        abort_if(Gate::denies('masterkategoridownload_read'), 403);
        log_custom("Buka menu kategori download");
        $data = Template::get("datatable");
        $data['jsTambahan'] = "$('#masterkategoridownload').addClass('active');";
        return $dataTable->render("backend.masterkategoridownload.masterkategoridownload", $data);
    }

    public function create()
    {
        abort_if(Gate::denies('masterkategoridownload_write'), 403);
        $data = Template::get("datatable");
        return view('backend.masterkategoridownload.masterkategoridownload_create', $data);
    }

    public function store(Request $request)
    {
        abort_if(Gate::denies('masterkategoridownload_write'), 403);

        $request->validate([
            'nama'      => 'required|string|max:255',
            'jenjang'   => 'required|in:s2,s3,semua',
            'deskripsi' => 'nullable|string',
            'is_active' => 'required|in:0,1',
        ]);

        $data         = $request->all();
        $slug         = Str::slug($request->nama);
        $count        = MasterDownloadCategory::where('slug', $slug)->count();
        if ($count > 0) $slug .= '-' . date('ymdis') . '-' . rand(0, 999);
        $data['slug'] = $slug;

        MasterDownloadCategory::create($data);

        Alert::info('Info', 'Kategori Berhasil Ditambah');
        return redirect()->route('masterkategoridownload.index');
    }

    public function show(string $id) {}

    public function edit($id)
    {
        abort_if(Gate::denies('masterkategoridownload_update'), 403);
        $data = Template::get("datatable");
        $data['masterkategoridownload'] = MasterDownloadCategory::findOrFail($id);
        return view('backend.masterkategoridownload.masterkategoridownload_edit', $data);
    }

    public function update(Request $request, MasterDownloadCategory $masterkategoridownload)
    {
        abort_if(Gate::denies('masterkategoridownload_update'), 403);

        $request->validate([
            'nama'      => 'required|string|max:255',
            'jenjang'   => 'required|in:s2,s3,semua',
            'deskripsi' => 'nullable|string',
            'is_active' => 'required|in:0,1',
        ]);

        $data = $request->all();

        if ($request->nama !== $masterkategoridownload->nama) {
            $slug = Str::slug($request->nama);
            if (MasterDownloadCategory::where('slug', $slug)->where('id', '!=', $masterkategoridownload->id)->exists()) {
                $slug .= '-' . date('ymdis') . '-' . rand(0, 999);
            }
            $data['slug'] = $slug;
        }

        log_custom("Update kategori download " . $masterkategoridownload->id);
        $masterkategoridownload->update($data);

        Alert::info('Info', 'Kategori Berhasil Diperbarui');
        return redirect()->route('masterkategoridownload.index');
    }

    public function destroy(MasterDownloadCategory $masterkategoridownload)
    {
        abort_if(Gate::denies('masterkategoridownload_delete'), 403);
        $masterkategoridownload->delete();
        log_custom("Hapus kategori download " . $masterkategoridownload->id);
        return response()->json('ok');
    }
}

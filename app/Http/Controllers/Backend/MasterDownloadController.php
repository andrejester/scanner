<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\MasterDownloadDataTable;
use App\Http\Controllers\Controller;
use App\Library\Template;
use App\Models\Master\MasterDownload;
use App\Models\Master\MasterDownloadCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use RealRashid\SweetAlert\Facades\Alert;

class MasterDownloadController extends Controller
{
    public function index(MasterDownloadDataTable $dataTable)
    {
        abort_if(Gate::denies('masterdownload_read'), 403);
        log_custom("Buka menu download");
        $data = Template::get("datatable");
        $data['jsTambahan'] = "$('#masterdownload').addClass('active');";
        return $dataTable->render("backend.masterdownload.masterdownload", $data);
    }

    public function create()
    {
        abort_if(Gate::denies('masterdownload_write'), 403);
        $data = Template::get("datatable");
        $data['kategoris'] = MasterDownloadCategory::where('status', 'active')->orderBy('title')->get();
        return view('backend.masterdownload.masterdownload_create', $data);
    }

    public function store(Request $request)
    {
        abort_if(Gate::denies('masterdownload_write'), 403);

        $request->validate([
            'id_kategori' => 'nullable|exists:master_download_categories,id',
            'title'       => 'required|string|max:500',
            'title_seo'   => 'required|string|max:500',
            'file'        => 'nullable|file|mimes:pdf|max:10240',
        ]);

        $data = $request->except('file');

        if ($request->hasFile('file')) {
            $data['file'] = $request->file('file')->store('download', 'public/files/2');
        }

        MasterDownload::create($data);

        Alert::info('Info', 'Download Berhasil Ditambah');
        return redirect()->route('masterdownload.index');
    }

    public function show(string $id) {}

    public function edit($id)
    {
        abort_if(Gate::denies('masterdownload_update'), 403);
        $data = Template::get("datatable");
        $data['masterdownload'] = MasterDownload::findOrFail($id);
        $data['kategoris']    = MasterDownloadCategory::where('status', 'active')->orderBy('title')->get();
        return view('backend.masterdownload.masterdownload_edit', $data);
    }

    public function update(Request $request, MasterDownload $masterdownload)
    {
        abort_if(Gate::denies('masterdownload_update'), 403);

        $request->validate([
            'id_kategori' => 'nullable|exists:master_download_categories,id',
            'title'       => 'required|string|max:500',
            'title_seo'   => 'required|string|max:500',
            'file'        => 'nullable|file|mimes:pdf|max:10240',
        ]);

        $data = $request->except('file');

        if ($request->hasFile('file')) {
            if ($masterdownload->file) Storage::disk('public/files/2')->delete($masterdownload->file);
            $data['file'] = $request->file('file')->store('download', 'public/files/2');
        }

        log_custom("Update download " . $masterdownload->id, $masterdownload->toArray());
        $masterdownload->update($data);

        Alert::info('Info', 'Download Berhasil Diperbarui');
        return redirect()->route('masterdownload.index');
    }

    public function destroy(MasterDownload $masterdownload)
    {
        abort_if(Gate::denies('masterdownload_delete'), 403);

        if ($masterdownload->file) Storage::disk('public/files/2')->delete($masterdownload->file);

        $masterdownload->delete();
        log_custom("Hapus download " . $masterdownload->id);
        return response()->json('ok');
    }
}

<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\MasterSambutanDirekturDataTable;
use App\Http\Controllers\Controller;
use App\Library\Template;
use App\Models\Master\MasterSambutanDirektur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use RealRashid\SweetAlert\Facades\Alert;

class MasterSambutanDirekturController extends Controller
{
    public function index(MasterSambutanDirekturDataTable $dataTable)
    {
        abort_if(Gate::denies('mastersambutandirektur_read'), 403);
        log_custom("Buka menu sambutan direktur");
        $data = Template::get("datatable");
        $data['jsTambahan'] = "
        $('#mastersambutandirektur').addClass('active');
        ";
        return $dataTable->render("backend.mastersambutandirektur.mastersambutandirektur", $data);
    }

    public function create()
    {
        abort_if(Gate::denies('mastersambutandirektur_write'), 403);
        log_custom("Buka menu tambah sambutan direktur");
        $data = Template::get("datatable");
        return view('backend.mastersambutandirektur.mastersambutandirektur_create', $data);
    }

    public function store(Request $request)
    {
        abort_if(Gate::denies('mastersambutandirektur_write'), 403);

        $request->validate([
            'nama_direktur' => 'required|string|max:255',
            'jabatan'       => 'nullable|string|max:255',
            'sambutan'      => 'nullable|string',
            // 'foto'          => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'is_active'     => 'required|in:0,1',
        ]);


        $data = $request->all();

        MasterSambutanDirektur::create($data);

        Alert::info('Info', 'Data Berhasil Ditambah');
        return redirect()->route('mastersambutandirektur.index');
    }

    public function show(string $id)
    {
        //
    }

    public function edit($id)
    {
        abort_if(Gate::denies('mastersambutandirektur_update'), 403);
        $data = Template::get("datatable");
        $data['mastersambutandirektur'] = MasterSambutanDirektur::findOrFail($id);
        return view('backend.mastersambutandirektur.mastersambutandirektur_edit', $data);
    }

    public function update(Request $request, MasterSambutanDirektur $mastersambutandirektur)
    {
        abort_if(Gate::denies('mastersambutandirektur_update'), 403);

        $request->validate([
            'nama_direktur' => 'required|string|max:255',
            'jabatan'       => 'nullable|string|max:255',
            'sambutan'      => 'nullable|string',
            // 'foto'          => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'is_active'     => 'required|in:0,1',
        ]);

        $data = $request->all();

        log_custom("Update sambutan direktur " . $mastersambutandirektur->id, $mastersambutandirektur->toArray());
        $mastersambutandirektur->update($data);

        Alert::info('Info', 'Data Berhasil Diperbarui');
        return redirect()->route('mastersambutandirektur.index');
    }

    public function destroy(MasterSambutanDirektur $mastersambutandirektur)
    {
        abort_if(Gate::denies('mastersambutandirektur_delete'), 403);

        // if ($mastersambutandirektur->foto) {
        //     Storage::disk('public/files/2')->delete($mastersambutandirektur->foto);
        // }

        $mastersambutandirektur->delete();
        log_custom("Hapus sambutan direktur " . $mastersambutandirektur->id);
        return response()->json('ok');
    }
}

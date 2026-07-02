<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\MasterSupportteamDataTable;
use App\Http\Controllers\Controller;
use App\Library\Template;
use App\Models\Backend\MasterSupportteam;
use App\Models\System\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use RealRashid\SweetAlert\Facades\Alert;

class MasterSupportteamController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index(MasterSupportteamDataTable $dataTable)
    {
        abort_if(Gate::denies('mastersupportteam_read'), 403);
        log_custom("Buka menu master mastersupportteam");
        $data = Template::get("datatable");
        $data['jsTambahan'] = "
        $('#mastersupportteam').addClass('active');
        ";
        return $dataTable->render("backend.mastersupportteam.mastersupportteam", $data);
        //return $dataTable->view("backend.mastersupportteam.mastersupportteam",$data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        abort_if(Gate::denies('mastersupportteam_write'), 403);
        log_custom("Buka menu tambah mastersupportteam");
        $data = Template::get("datatable");

        return view('backend.mastersupportteam.mastersupportteam_create', $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        abort_if(Gate::denies('mastersupportteam_write'), 403);

        // return $request->all();
        $request->validate([
            'name' => 'string|required|max:50',
            'url' => 'string|nullable',
            'photo' => 'string|required',
            'is_active' => 'string|required',
        ]);
        $data           = $request->all();
        MasterSupportteam::create($data);

        Alert::info('Info Title', 'Data Berhasil Di tambah');
        return redirect()->route('mastersupportteam.index');
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
    public function edit($MasterSupportteam)
    {
        // dd($MasterSupportteam);
        abort_if(Gate::denies('mastersupportteam_update'), 403);
        //log_custom("Buka menu edit master pinjaman " . $MasterSupportteam->id);
        $data = Template::get("datatable");
        $data['mastersupportteam'] = MasterSupportteam::findOrFail($MasterSupportteam);

        return view('backend.mastersupportteam.mastersupportteam_edit', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MasterSupportteam $mastersupportteam)
    {
        // dd("asdasd");
        abort_if(Gate::denies('mastersupportteam_update'), 403);
        $request->validate([
            'name' => 'string|required|max:50',
            'url' => 'string|nullable',
            'photo' => 'string|required',
            'is_active' => 'string|required',
        ]);
        log_custom("Update data mastersupportteam " . $mastersupportteam->id, $mastersupportteam->toArray());
        $mastersupportteam->update($request->all());

        Alert::info(
            'Info Title',
            'Data Berhasil Diperbarui'
        );
        return redirect()->route('mastersupportteam.index');
        // return response()->json($MasterSupportteam, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MasterSupportteam $mastersupportteam)
    {
        abort_if(Gate::denies('mastersupportteam_delete'), 403);
        $mastersupportteam->delete();
        log_custom("Hapus data " . $mastersupportteam->id, $mastersupportteam->toArray());
        return response()->json('ok'); // Mengirim respons JSON 'ok'
    }
}

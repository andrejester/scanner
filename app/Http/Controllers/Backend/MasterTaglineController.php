<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\MasterTaglineDataTable;
use App\Http\Controllers\Controller;
use App\Library\Template;
use App\Models\Backend\MasterTagline;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Support\Str;

class MasterTaglineController extends Controller
{
    public function index(MasterTaglineDataTable $dataTable)
    {
        abort_if(Gate::denies('mastertagline_read'), 403);
        log_custom("Buka menu master mastertagline");
        $data = Template::get("datatable");
        $data['jsTambahan'] = "
        $('#mastertagline').addClass('active');
        ";
        return $dataTable->render("backend.mastertagline.mastertagline", $data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        abort_if(Gate::denies('mastertagline_write'), 403);
        log_custom("Buka menu tambah Tagline");
        $data = Template::get("datatable");

        return view('backend.mastertagline.mastertagline_create', $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        abort_if(Gate::denies('mastertagline_write'), 403);

        $request->validate([
            'nama_kategori' => 'string|required|max:50',
            'is_active' => 'required|in:0,1',
        ]);
        $data           = $request->all();
        $slug           = Str::slug($request->nama_kategori);
        $count          = MasterTagline::where('slug', $slug)->count();
        if ($count > 0) $slug = $slug . '-' . date('ymdis') . '-' . rand(0, 999);
        $data['slug'] = $slug;
        MasterTagline::create($data);

        Alert::info('Info Title', 'Data Berhasil Di tambah');
        return redirect()->route('mastertagline.index');
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
    public function edit($MasterTagline)
    {
        abort_if(Gate::denies('mastertagline_update'), 403);
        $data = Template::get("datatable");
        $data['mastertagline'] = MasterTagline::findOrFail($MasterTagline);

        return view('backend.mastertagline.mastertagline_edit', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MasterTagline $mastertagline)
    {
        abort_if(Gate::denies('mastertagline_update'), 403);

        $validated = $request->validate([
            'nama_kategori' => 'string|required|max:50',
            'is_active' => 'required|in:0,1',
        ]);

        log_custom("Update data mastertagline " . $mastertagline->id, $mastertagline->toArray());

        if ($validated['nama_kategori'] !== $mastertagline->nama_kategori) {
            $slug = Str::slug($validated['nama_kategori']);
            if (MasterTagline::where('slug', $slug)->where('id', '!=', $mastertagline->id)->exists()) {
                $slug .= '-' . date('ymdis') . '-' . rand(0, 999);
            }
            $validated['slug'] = $slug;
        }

        $mastertagline->update($validated);

        Alert::info(
            'Info Title',
            'Data Berhasil Diperbarui'
        );
        return redirect()->route('mastertagline.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MasterTagline $mastertagline)
    {
        abort_if(Gate::denies('mastertagline_delete'), 403);
        $mastertagline->delete();
        log_custom("Hapus data " . $mastertagline->id, $mastertagline->toArray());
        return response()->json('ok');
    }
}


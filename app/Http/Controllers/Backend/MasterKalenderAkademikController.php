<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\MasterKalenderAkademikDataTable;
use App\Http\Controllers\Controller;
use App\Library\Template;
use App\Models\Backend\MasterKalenderAkademik;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use RealRashid\SweetAlert\Facades\Alert;

class MasterKalenderAkademikController extends Controller
{
    public function index(MasterKalenderAkademikDataTable $dataTable)
    {
        abort_if(Gate::denies('masterkalenderakademik_read'), 403);
        log_custom("Buka menu kalender akademik");
        $data = Template::get("datatable");
        $data['jsTambahan'] = "$('#masterkalenderakademik').addClass('active');";
        return $dataTable->render("backend.masterkalenderakademik.masterkalenderakademik", $data);
    }

    public function create()
    {
        abort_if(Gate::denies('masterkalenderakademik_write'), 403);
        $data = Template::get("datatable");
        return view('backend.masterkalenderakademik.masterkalenderakademik_create', $data);
    }

    public function store(Request $request)
    {
        abort_if(Gate::denies('masterkalenderakademik_write'), 403);

        $request->validate([
            'judul'           => 'required|string|max:255',
            'deskripsi'       => 'nullable|string',
            'tanggal_mulai'   => 'required|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'jenis'           => 'required|in:akademik,libur,ujian,pendaftaran,wisuda,lainnya',
            'warna'           => 'nullable|string|max:7',
            'is_active'       => 'required|in:0,1',
        ]);

        $data = $request->all();

        // Set default color based on jenis if not provided
        if (empty($data['warna'])) {
            $data['warna'] = $this->getDefaultColor($data['jenis']);
        }

        MasterKalenderAkademik::create($data);

        Alert::info('Info', 'Data Berhasil Ditambah');
        return redirect()->route('masterkalenderakademik.index');
    }

    public function show(string $id)
    {
        //
    }

    public function edit(MasterKalenderAkademik $masterkalenderakademik)
    {
        abort_if(Gate::denies('masterkalenderakademik_update'), 403);
        $data = Template::get("datatable");
        $data['kalender'] = $masterkalenderakademik;
        return view('backend.masterkalenderakademik.masterkalenderakademik_edit', $data);
    }

    public function update(Request $request, MasterKalenderAkademik $masterkalenderakademik)
    {
        abort_if(Gate::denies('masterkalenderakademik_update'), 403);

        $request->validate([
            'judul'           => 'required|string|max:255',
            'deskripsi'       => 'nullable|string',
            'tanggal_mulai'   => 'required|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'jenis'           => 'required|in:akademik,libur,ujian,pendaftaran,wisuda,lainnya',
            'warna'           => 'nullable|string|max:7',
            'is_active'       => 'required|in:0,1',
        ]);

        $data = $request->all();

        // Set default color based on jenis if not provided
        if (empty($data['warna'])) {
            $data['warna'] = $this->getDefaultColor($data['jenis']);
        }

        log_custom("Update kalender akademik " . $masterkalenderakademik->id, $masterkalenderakademik->toArray());
        $masterkalenderakademik->update($data);

        Alert::info('Info', 'Data Berhasil Diperbarui');
        return redirect()->route('masterkalenderakademik.index');
    }

    public function destroy(MasterKalenderAkademik $masterkalenderakademik)
    {
        abort_if(Gate::denies('masterkalenderakademik_delete'), 403);

        $masterkalenderakademik->delete();
        log_custom("Hapus kalender akademik " . $masterkalenderakademik->id);
        return response()->json('ok');
    }

    /**
     * Get default color based on jenis
     */
    private function getDefaultColor($jenis)
    {
        $colors = [
            'akademik'     => '#3498db', // Blue
            'libur'        => '#e74c3c', // Red
            'ujian'        => '#f39c12', // Orange
            'pendaftaran'  => '#2ecc71', // Green
            'wisuda'       => '#9b59b6', // Purple
            'lainnya'      => '#95a5a6', // Gray
        ];

        return $colors[$jenis] ?? '#3498db';
    }
}

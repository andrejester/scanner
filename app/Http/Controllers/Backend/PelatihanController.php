<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\PelatihanDataTable;
use App\Helpers\Func;
use App\Http\Controllers\Backend\TelegramService as BackendTelegramService;
use App\Http\Controllers\Controller;
use App\Library\Template;
use App\Models\Backend\Pelatihan;
use App\Models\Backend\MasterKategoriBerita;
use App\Models\Backend\MasterPelatihan;
use App\Models\Backend\MasterTagline;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use RealRashid\SweetAlert\Facades\Alert;
use App\Services\TelegramService;
use Illuminate\Database\QueryException;
use mysqli;

class PelatihanController extends Controller
{
    protected $telegram;

    public function __construct(BackendTelegramService $telegram)
    {
        $this->telegram = $telegram;
    }

    public function index(PelatihanDataTable $dataTable)
    {
        abort_if(Gate::denies('pelatihanadmin_read'), 403);
        log_custom("Buka menu master pelatihan");

        $data = Template::get("datatable");
        $data['jsTambahan'] = "$('#pelatihanadmin').addClass('active');";

        return $dataTable->render("backend.pelatihan.pelatihan", $data);
    }

    public function create()
    {
        abort_if(Gate::denies('pelatihanadmin_write'), 403);

        $data = Template::get("datatable");
        $data['MasterPelatihan'] = MasterPelatihan::orderBy('keterangan')->get();
        return view('backend.pelatihan.pelatihan_create', $data);
    }

    public function store(Request $request)
    {
        abort_if(Gate::denies('pelatihanadmin_write'), 403);

        $request->validate([
            'nama_pelatihan'    => 'required|string|max:250',
            'deskripsi_singkat' => 'nullable|string',
            'deskripsi'         => 'nullable|string',
            'thumbnail'         => 'nullable|string',
            'kategori'          => 'nullable|string',
            'level'             => 'nullable|string',
            'kode_instruktur'   => 'nullable|string',
            'durasi'            => 'nullable|integer',
            'jumlah_sesi'       => 'nullable|integer',
            'kapasitas'         => 'nullable|integer',
            'harga'             => 'nullable|numeric',
            'is_free'           => 'nullable|boolean',
            'tanggal_mulai'     => 'nullable|date',
            'tanggal_selesai'   => 'nullable|date',
            'lokasi'            => 'nullable|string',
            'status'            => 'required|in:draft,publish,nonaktif',
        ]);

        $data = $request->all();

        // slug dari nama_pelatihan
        $slug = Str::slug($request->nama_pelatihan);
        if (Pelatihan::where('slug', $slug)->exists()) {
            $slug .= "-" . time();
        }
        $data['slug'] = $slug;

        Pelatihan::create($data);

        Alert::success('Berhasil', 'Pelatihan berhasil ditambahkan');
        return redirect()->route('pelatihanadmin.index');
    }

    public function edit($id)
    {
        abort_if(Gate::denies('pelatihanadmin_update'), 403);

        $data = Template::get("datatable");

        $pelatihanadmin = Pelatihan::findOrFail($id);
        $data['pelatihanadmin'] = $pelatihanadmin;

        $data['categoryAll'] = MasterPelatihan::orderBy('keterangan')->get();

        return view('backend.pelatihan.pelatihan_edit', $data);
    }

    public function update(Request $request, Pelatihan $pelatihanadmin)
    {
        abort_if(Gate::denies('pelatihanadmin_update'), 403);

        $request->validate([
            'nama_pelatihan'    => 'required|string|max:250',
            'deskripsi_singkat' => 'nullable|string',
            'deskripsi'         => 'nullable|string',
            'thumbnail'         => 'nullable|string',
            'kategori'          => 'nullable|string',
            'level'             => 'nullable|string',
            'kode_instruktur'   => 'nullable|string',
            'durasi'            => 'nullable|integer',
            'jumlah_sesi'       => 'nullable|integer',
            'kapasitas'         => 'nullable|integer',
            'harga'             => 'nullable|numeric',
            'is_free'           => 'nullable|boolean',
            'tanggal_mulai'     => 'nullable|date',
            'tanggal_selesai'   => 'nullable|date',
            'lokasi'            => 'nullable|string',
            'status'            => 'required|in:draft,publish,nonaktif',
        ]);

        $data = $request->all();

        // slug
        $slug = Str::slug($request->nama_pelatihan);
        if (Pelatihan::where('slug', $slug)->where('id', '!=', $pelatihanadmin->id)->exists()) {
            $slug .= "-" . time();
        }
        $data['slug'] = $slug;

        log_custom("Update data pelatihan " . $pelatihanadmin->id, $pelatihanadmin->toArray());

        $pelatihanadmin->update($data);

        Alert::info('Info', 'Data Berhasil Diperbarui');
        return redirect()->route('pelatihanadmin.index');
    }

    public function destroy(Pelatihan $pelatihanadmin)
    {
        abort_if(Gate::denies('pelatihanadmin_delete'), 403);

        $pelatilanArray = $pelatihanadmin->toArray();

        $pelatihanadmin->delete();

        $this->telegram->sendMessage("Hapus Pelatihan ID: " . $pelatihanadmin->id);
        log_custom("Hapus Pelatihan ID " . $pelatihanadmin->id, $pelatilanArray);

        return response()->json('ok');
    }
}

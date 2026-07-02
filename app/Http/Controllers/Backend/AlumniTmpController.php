<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\AlumniTmpDataTable;
use App\Http\Controllers\Controller;
use App\Library\Template;
use App\Models\Backend\AlumniTmp;
use App\Models\Backend\MasterAlumni;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class AlumniTmpController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(AlumniTmpDataTable $dataTable)
    {
        abort_if(Gate::denies('masteralumni_read'), 403);
        log_custom("Buka menu pendaftaran alumni");
        $data = Template::get("datatable");
        $data['jsTambahan'] = "$('#alumnitmp').addClass('active');";
        return $dataTable->render('backend.alumnitmp.alumnitmp', $data);
    }

    /**
     * Display the specified resource.
     */
    public function show(AlumniTmp $alumnitmp)
    {
        abort_if(Gate::denies('masteralumni_read'), 403);
        $data = Template::get("datatable");
        $data['alumni'] = $alumnitmp;
        return view('backend.alumnitmp.alumnitmp_show', $data);
    }

    /**
     * Update status of the specified resource.
     */
    public function updateStatus(Request $request, AlumniTmp $alumnitmp)
    {
        abort_if(Gate::denies('masteralumni_update'), 403);

        $request->validate([
            'status' => 'required|in:1,2', // 1=acc, 2=tolak
        ]);

        $alumnitmp->status = $request->status;
        $alumnitmp->save();

        // Jika disetujui (status = 1), pindahkan ke tabel master_alumni
        if ($request->status == AlumniTmp::STATUS_APPROVED) {
            MasterAlumni::create([
                'nama' => $alumnitmp->nama,
                'nim' => $alumnitmp->nim,
                'jenjang' => $alumnitmp->jenjang,
                'program_studi' => $alumnitmp->program_studi,
                'tahun_lulus' => $alumnitmp->tahun_lulus,
                'pekerjaan' => $alumnitmp->pekerjaan,
                'instansi' => $alumnitmp->instansi,
                'testimoni' => $alumnitmp->testimoni,
                'foto' => $alumnitmp->foto,
                'is_active' => true,
            ]);

            log_custom("Approve pendaftaran alumni " . $alumnitmp->id . " - " . $alumnitmp->nama);

            return response()->json([
                'success' => true,
                'message' => 'Data alumni berhasil disetujui dan dipindahkan ke database alumni!'
            ]);
        }

        // Jika ditolak (status = 2)
        log_custom("Reject pendaftaran alumni " . $alumnitmp->id . " - " . $alumnitmp->nama);

        return response()->json([
            'success' => true,
            'message' => 'Data alumni berhasil ditolak!'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AlumniTmp $alumnitmp)
    {
        abort_if(Gate::denies('masteralumni_delete'), 403);

        // Hapus foto jika ada
        if ($alumnitmp->foto && Storage::disk('public/files/2')->exists($alumnitmp->foto)) {
            Storage::disk('public/files/2')->delete($alumnitmp->foto);
        }

        log_custom("Hapus pendaftaran alumni " . $alumnitmp->id . " - " . $alumnitmp->nama);
        $alumnitmp->delete();

        return response()->json('ok');
    }
}

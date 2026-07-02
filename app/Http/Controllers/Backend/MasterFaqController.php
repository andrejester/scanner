<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\MasterFaqDataTable;
use App\Http\Controllers\Controller;
use App\Library\Template;
use App\Models\Backend\MasterFaq;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use RealRashid\SweetAlert\Facades\Alert;

class MasterFaqController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(MasterFaqDataTable $dataTable)
    {
        abort_if(Gate::denies('masterfaq_read'), 403);
        log_custom("Buka menu master faq");
        $data = Template::get("datatable");
        $data['jsTambahan'] = "
        $('#masterfaq').addClass('active');
        ";
        return $dataTable->render("backend.masterfaq.masterfaq", $data);
        //return $dataTable->view("backend.masterfaq.masterfaq",$data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        abort_if(Gate::denies('masterfaq_write'), 403);
        log_custom("Buka menu tambah FAQ");
        $data = Template::get("datatable");

        return view('backend.masterfaq.masterfaq_create', $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        abort_if(Gate::denies('masterfaq_write'), 403);

        // return $request->all();
        $request->validate([
            'question' => 'string|required|max:255',
            'answer' => 'string|required',
            'status' => 'required|in:active,inactive',
        ]);

        $data           = $request->all();
        MasterFaq::create($data);

        Alert::info('Info Title', 'Data Berhasil Di tambah');
        return redirect()->route('masterfaq.index');
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
    public function edit($MasterFaq)
    {
        // dd($MasterFaq);
        abort_if(Gate::denies('masterfaq_update'), 403);
        //log_custom("Buka menu edit master pinjaman " . $MasterFaq->id);
        $data = Template::get("datatable");
        $data['masterfaq'] = MasterFaq::findOrFail($MasterFaq);

        return view('backend.masterfaq.masterfaq_edit', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MasterFaq $masterfaq)
    {
        abort_if(Gate::denies('masterfaq_update'), 403);

        $request->validate([
            'question' => 'string|required|max:255',
            'answer' => 'string|required',
            'status' => 'required|in:active,inactive',
        ]);

        log_custom("Update data masterfaq " . $masterfaq->id, $masterfaq->toArray());
        $masterfaq->update($request->all());

        Alert::info(
            'Info Title',
            'Data Berhasil Diperbarui'
        );
        return redirect()->route('masterfaq.index');
        // return response()->json($MasterFaq, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MasterFaq $masterfaq)
    {
        abort_if(Gate::denies('masterfaq_delete'), 403);
        $masterfaq->delete();
        log_custom("Hapus data " . $masterfaq->id, $masterfaq->toArray());
        return response()->json('ok'); // Mengirim respons JSON 'ok'
    }
}

<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\MasterPrivacyPolicyDataTable;
use App\Http\Controllers\Controller;
use App\Library\Template;
use App\Models\Backend\MasterPrivacyPolicy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use RealRashid\SweetAlert\Facades\Alert;

class MasterPrivacyPolicyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(MasterPrivacyPolicyDataTable $dataTable)
    {
        abort_if(Gate::denies('masterprivacypolicy_read'), 403);
        log_custom("Buka menu master faq");
        $data = Template::get("datatable");
        $data['jsTambahan'] = "
        $('#masterprivacypolicy').addClass('active');
        ";
        return $dataTable->render("backend.masterprivacypolicy.masterprivacypolicy", $data);
        //return $dataTable->view("backend.masterprivacypolicy.masterprivacypolicy",$data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        abort_if(Gate::denies('masterprivacypolicy_write'), 403);
        log_custom("Buka menu tambah Privacy Policy");
        $data = Template::get("datatable");

        return view('backend.masterprivacypolicy.masterprivacypolicy_create', $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        abort_if(Gate::denies('masterprivacypolicy_write'), 403);

        // return $request->all();
        $request->validate([
            'title' => 'string|required|max:255',
            'content' => 'string|required',
            'is_active' => 'required',
        ]);

        $data           = $request->all();
        $slug           = Str::slug($request->title);
        $count          = MasterPrivacyPolicy::where('slug', $slug)->count();
        if ($count > 0) $slug = $slug . '-' . date('ymdis') . '-' . rand(0, 999);
        $data['slug'] = $slug;
        MasterPrivacyPolicy::create($data);

        Alert::info('Info Title', 'Data Berhasil Di tambah');
        return redirect()->route('masterprivacypolicy.index');
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
    public function edit($MasterPrivacyPolicy)
    {
        // dd($MasterPrivacyPolicy);
        abort_if(Gate::denies('masterprivacypolicy_update'), 403);
        //log_custom("Buka menu edit master pinjaman " . $MasterPrivacyPolicy->id);
        $data = Template::get("datatable");
        $data['masterprivacypolicy'] = MasterPrivacyPolicy::findOrFail($MasterPrivacyPolicy);

        return view('backend.masterprivacypolicy.masterprivacypolicy_edit', $data);
    }

    /**
     * Update the specified resource in storage.
     */

    public function update(Request $request, MasterPrivacyPolicy $masterprivacypolicy)
    {
        abort_if(Gate::denies('masterprivacypolicy_update'), 403);

        $validated = $request->validate([
            'title' => 'string|required|max:255',
            'content' => 'string|required',
            'is_active' => 'required|boolean',
        ]);

        log_custom("Update data masterprivacypolicy " . $masterprivacypolicy->id, $masterprivacypolicy->toArray());

        // Jika title berubah, perbarui slug juga
        if ($validated['title'] !== $masterprivacypolicy->title) {
            $slug = Str::slug($validated['title']);
            if (MasterPrivacyPolicy::where('slug', $slug)->where('id', '!=', $masterprivacypolicy->id)->exists()) {
                $slug .= '-' . date('ymdis') . '-' . rand(0, 999);
            }
            $validated['slug'] = $slug;
        }

        $masterprivacypolicy->update($validated);

        Alert::info('Info', 'Data berhasil diperbarui');
        return redirect()->route('masterprivacypolicy.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MasterPrivacyPolicy $masterprivacypolicy)
    {
        abort_if(Gate::denies('masterprivacypolicy_delete'), 403);
        $masterprivacypolicy->delete();
        log_custom("Hapus data " . $masterprivacypolicy->id, $masterprivacypolicy->toArray());
        return response()->json('ok'); // Mengirim respons JSON 'ok'
    }
}

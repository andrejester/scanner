<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\MasterVideoCategoryDataTable;
use App\Http\Controllers\Controller;
use App\Library\Template;
use App\Models\Master\MasterVideoCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RealRashid\SweetAlert\Facades\Alert;

class MasterVideoCategoryController extends Controller
{
    public function index(MasterVideoCategoryDataTable $dataTable)
    {
        abort_if(Gate::denies('mastervideocategory_read'), 403);
        log_custom('Buka menu kategori video');
        $data = Template::get('datatable');
        $data['jsTambahan'] = "$('#mastervideocategory').addClass('active');";

        return $dataTable->render('backend.mastervideocategory.mastervideocategory', $data);
    }

    public function create()
    {
        abort_if(Gate::denies('mastervideocategory_write'), 403);
        $data = Template::get('datatable');

        return view('backend.mastervideocategory.mastervideocategory_create', $data);
    }

    public function store(Request $request)
    {
        abort_if(Gate::denies('mastervideocategory_write'), 403);

        $request->validate([
            'title'   => 'required|string|max:255',
            'summary' => 'nullable|string',
            'type'    => 'nullable|string|max:100',
            'photo'   => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'status'  => 'required|in:active,inactive',
        ]);

        $data = $request->except('photo');
        $slug  = Str::slug($request->title);
        $count = MasterVideoCategory::where('slug', $slug)->count();
        if ($count > 0) {
            $slug .= '-' . date('ymdis') . '-' . rand(0, 999);
        }
        $data['slug'] = $slug;

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('video-category', 'public/files/2');
        }

        MasterVideoCategory::create($data);

        Alert::info('Info', 'Kategori Video Berhasil Ditambah');
        return redirect()->route('mastervideocategory.index');
    }

    public function edit($id)
    {
        abort_if(Gate::denies('mastervideocategory_update'), 403);
        $data = Template::get('datatable');
        $data['category'] = MasterVideoCategory::findOrFail($id);

        return view('backend.mastervideocategory.mastervideocategory_edit', $data);
    }

    public function update(Request $request, MasterVideoCategory $mastervideocategory)
    {
        abort_if(Gate::denies('mastervideocategory_update'), 403);

        $request->validate([
            'title'   => 'required|string|max:255',
            'summary' => 'nullable|string',
            'type'    => 'nullable|string|max:100',
            'photo'   => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'status'  => 'required|in:active,inactive',
        ]);

        $data = $request->except('photo');

        if ($request->title !== $mastervideocategory->title) {
            $slug  = Str::slug($request->title);
            $count = MasterVideoCategory::where('slug', $slug)->where('id', '!=', $mastervideocategory->id)->count();
            if ($count > 0) {
                $slug .= '-' . date('ymdis') . '-' . rand(0, 999);
            }
            $data['slug'] = $slug;
        }

        if ($request->hasFile('photo')) {
            if ($mastervideocategory->photo && !filter_var($mastervideocategory->photo, FILTER_VALIDATE_URL)) {
                Storage::disk('public/files/2')->delete($mastervideocategory->photo);
            }
            $data['photo'] = $request->file('photo')->store('video-category', 'public/files/2');
        }

        log_custom('Update kategori video ' . $mastervideocategory->id, $mastervideocategory->toArray());
        $mastervideocategory->update($data);

        Alert::info('Info', 'Kategori Video Berhasil Diperbarui');
        return redirect()->route('mastervideocategory.index');
    }

    public function destroy(MasterVideoCategory $mastervideocategory)
    {
        abort_if(Gate::denies('mastervideocategory_delete'), 403);

        if ($mastervideocategory->photo && !filter_var($mastervideocategory->photo, FILTER_VALIDATE_URL)) {
            Storage::disk('public/files/2')->delete($mastervideocategory->photo);
        }

        $mastervideocategory->delete();
        log_custom('Hapus kategori video ' . $mastervideocategory->id, $mastervideocategory->toArray());

        return response()->json('ok');
    }
}

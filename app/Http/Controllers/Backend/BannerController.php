<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\BannerDataTable;
use App\Http\Controllers\Controller;
use App\Library\Template;
use App\Models\Backend\Banner;
use App\Models\Master\MasterBanner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RealRashid\SweetAlert\Facades\Alert;

class BannerController extends Controller
{
    public function index(BannerDataTable $dataTable)
    {
        abort_if(Gate::denies('banner_read'), 403);
        log_custom("Buka menu banner");
        $data = Template::get("datatable");
        $data['jsTambahan'] = "$('#banner').addClass('active');";
        return $dataTable->render("backend.banner.banner", $data);
    }

    public function create()
    {
        abort_if(Gate::denies('banner_write'), 403);
        $data = Template::get("datatable");
        return view('backend.banner.banner_create', $data);
    }

    public function store(Request $request)
    {
        abort_if(Gate::denies('banner_write'), 403);

        $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'photo'       => 'nullable|file|mimes:jpg,jpeg,png,webp,mp4,mov,avi,mkv|max:20480',
            'status'      => 'required|in:active,inactive',
        ]);

        $data = $request->except('photo');

        $slug  = Str::slug($request->title);
        $count = MasterBanner::where('slug', $slug)->count();
        if ($count > 0) $slug .= '-' . date('ymdis') . '-' . rand(0, 999);
        $data['slug'] = $slug;

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('banners', 'public/files/2');
        }

        MasterBanner::create($data);

        Alert::info('Info', 'Data Berhasil Ditambah');
        return redirect()->route('banner.index');
    }

    public function show(string $id)
    {
        //
    }

    public function edit($id)
    {
        abort_if(Gate::denies('banner_update'), 403);
        $data = Template::get("datatable");
        $data['banner'] = MasterBanner::findOrFail($id);
        return view('backend.banner.banner_edit', $data);
    }

    public function update(Request $request, MasterBanner $banner)
    {
        abort_if(Gate::denies('banner_update'), 403);

        $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'photo'       => 'nullable|file|mimes:jpg,jpeg,png,webp,mp4,mov,avi,mkv|max:20480',
            'status'      => 'required|in:active,inactive',
        ]);

        $data = $request->except('photo');

        if ($request->title !== $banner->title) {
            $slug  = Str::slug($request->title);
            $count = MasterBanner::where('slug', $slug)->where('id', '!=', $banner->id)->count();
            if ($count > 0) $slug .= '-' . date('ymdis') . '-' . rand(0, 999);
            $data['slug'] = $slug;
        }

        if ($request->hasFile('photo')) {
            // Delete old photo only if it's a local file (not external URL)
            if ($banner->photo && !filter_var($banner->photo, FILTER_VALIDATE_URL)) {
                Storage::disk('public/files/2')->delete($banner->photo);
            }
            $data['photo'] = $request->file('photo')->store('banners', 'public/files/2');
        }

        log_custom("Update banner " . $banner->id, $banner->toArray());
        $banner->update($data);

        Alert::info('Info', 'Data Berhasil Diperbarui');
        return redirect()->route('banner.index');
    }

    public function destroy(MasterBanner $banner)
    {
        abort_if(Gate::denies('banner_delete'), 403);

        // Delete photo only if it's a local file (not external URL)
        if ($banner->photo && !filter_var($banner->photo, FILTER_VALIDATE_URL)) {
            Storage::disk('public/files/2')->delete($banner->photo);
        }

        $banner->delete();
        log_custom("Hapus banner " . $banner->id);
        return response()->json('ok');
    }
}

<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\MasterVideoDataTable;
use App\Http\Controllers\Controller;
use App\Library\Template;
use App\Models\Master\MasterVideo;
use App\Models\Master\MasterVideoCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RealRashid\SweetAlert\Facades\Alert;

class MasterVideoController extends Controller
{
    public function index(MasterVideoDataTable $dataTable)
    {
        abort_if(Gate::denies('mastervideo_read'), 403);
        log_custom("Buka menu master video");
        $data = Template::get('datatable');
        $data['jsTambahan'] = "$('#mastervideo').addClass('active');";

        return $dataTable->render('backend.mastervideo.mastervideo', $data);
    }

    public function create()
    {
        abort_if(Gate::denies('mastervideo_write'), 403);
        $data = Template::get('datatable');
        $data['categories'] = MasterVideoCategory::orderBy('title')->get();

        return view('backend.mastervideo.mastervideo_create', $data);
    }

    public function store(Request $request)
    {
        abort_if(Gate::denies('mastervideo_write'), 403);

        $request->validate([
            'title'       => 'required|string|max:255',
            'id_kategori' => 'nullable|exists:master_video_categories,id',
            'tanggal'     => 'nullable|date',
            'deskripsi'   => 'nullable|string',
            'source_type' => 'required|in:banner,youtube,instagram,tiktok',
            'youtube'     => 'nullable',
            'video'       => 'nullable|file|mimes:mp4,mov,avi,mkv,webm|max:102400',
            'status'      => 'required|in:active,inactive',
        ]);

        $data = $request->except('video');
        $data['tanggal'] = $request->tanggal ?: now()->toDateString();

        $slug  = Str::slug($request->title);
        $count = MasterVideo::where('slug', $slug)->count();
        if ($count > 0) {
            $slug .= '-' . date('ymdis') . '-' . rand(0, 999);
        }
        $data['slug'] = $slug;

        if ($request->hasFile('video')) {
            $data['video'] = $request->file('video')->store('bannervideo', 'public/files/2');
        }

        MasterVideo::create($data);

        Alert::info('Info', 'Data Berhasil Ditambah');
        return redirect()->route('mastervideo.index');
    }

    public function edit($id)
    {
        abort_if(Gate::denies('mastervideo_update'), 403);
        $data = Template::get('datatable');
        $data['video'] = MasterVideo::findOrFail($id);
        $data['categories'] = MasterVideoCategory::orderBy('title')->get();

        return view('backend.mastervideo.mastervideo_edit', $data);
    }

    public function update(Request $request, MasterVideo $mastervideo)
    {
        abort_if(Gate::denies('mastervideo_update'), 403);

        $request->validate([
            'title'       => 'required|string|max:255',
            'id_kategori' => 'nullable|exists:master_video_categories,id',
            'tanggal'     => 'nullable|date',
            'deskripsi'   => 'nullable|string',
            'source_type' => 'required|in:banner,youtube,instagram,tiktok',
            'youtube'     => 'nullable',
            'video'       => 'nullable|file|mimes:mp4,mov,avi,mkv,webm|max:102400',
            'status'      => 'required|in:active,inactive',
        ]);

        $data = $request->except('video');
        $data['tanggal'] = $request->tanggal ?: now()->toDateString();

        if ($request->title !== $mastervideo->title) {
            $slug  = Str::slug($request->title);
            $count = MasterVideo::where('slug', $slug)->where('id', '!=', $mastervideo->id)->count();
            if ($count > 0) {
                $slug .= '-' . date('ymdis') . '-' . rand(0, 999);
            }
            $data['slug'] = $slug;
        }

        if ($request->hasFile('video')) {
            if ($mastervideo->video && !filter_var($mastervideo->video, FILTER_VALIDATE_URL)) {
                Storage::disk('public/files/2')->delete($mastervideo->video);
            }
            $data['video'] = $request->file('video')->store('bannervideo', 'public/files/2');
        }

        log_custom('Update master video ' . $mastervideo->id, $mastervideo->toArray());
        $mastervideo->update($data);

        Alert::info('Info', 'Data Berhasil Diperbarui');
        return redirect()->route('mastervideo.index');
    }

    public function destroy(MasterVideo $mastervideo)
    {
        abort_if(Gate::denies('mastervideo_delete'), 403);

        if ($mastervideo->video && !filter_var($mastervideo->video, FILTER_VALIDATE_URL)) {
            Storage::disk('public/files/2')->delete($mastervideo->video);
        }

        $mastervideo->delete();
        log_custom('Hapus master video ' . $mastervideo->id, $mastervideo->toArray());

        return response()->json('ok');
    }
}

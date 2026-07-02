<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\BlogDataTable;
use App\Helpers\Func;
use App\Http\Controllers\Backend\TelegramService as BackendTelegramService;
use App\Http\Controllers\Controller;
use App\Library\Template;
use App\Models\Master\MasterPost;
use App\Models\Master\MasterPostCategory;
use App\Models\Master\MasterPostTag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use RealRashid\SweetAlert\Facades\Alert;
use App\Services\TelegramService;
use Illuminate\Database\QueryException;
use mysqli;

class BlogController extends Controller
{
    protected $telegram;

    public function __construct(BackendTelegramService $telegram)
    {
        $this->telegram = $telegram;
    }

    public function index(BlogDataTable $dataTable)
    {
        abort_if(Gate::denies('blog_read'), 403);
        log_custom("Buka menu master blog");

        $data = Template::get("datatable");
        $data['jsTambahan'] = "$('#blog').addClass('active');";

        return $dataTable->render("backend.blog.blog", $data);
    }

    public function create()
    {
        abort_if(Gate::denies('blog_write'), 403);

        $data = Template::get("datatable");

        // ambil kategori berita dari tabel master
        $data['categoryAll'] = MasterPostCategory::where('status', 'active')
            ->orderBy('id', 'asc')
            ->get();
        $data['taglines'] = MasterPostTag::where('status', 'active')->orderBy('id')->get();

        return view('backend.blog.blog_create', $data);
    }

    public function store(Request $request)
    {
        abort_if(Gate::denies('blog_write'), 403);

        // dd($request->all());
        $request->validate([
            'title'             => 'required|string|max:250',
            'summary'           => 'nullable|string',
            'description'       => 'nullable|string',
            'photo'             => 'nullable|string',
            'post_cat_id'       => 'nullable|exists:master_post_categories,id',
            'tags'              => 'nullable|array',
            'status'            => 'required|in:active,inactive',
        ]);

        $data = [
            'title'       => $request->title,
            'summary'     => $request->summary,
            'description' => $request->description,
            'photo'       => $request->photo,
            'post_cat_id' => $request->post_cat_id,
            'tags'        => $request->tags ? implode(',', $request->tags) : null,
            'status'      => $request->status,
            'added_by'    => Auth::user()->username ?? 'Admin',
        ];

        $slug = Str::slug($request->title);
        if (MasterPost::where('slug', $slug)->exists()) {
            $slug .= '-' . time();
        }
        $data['slug'] = $slug;

        MasterPost::create($data);

        Alert::success('Berhasil', 'Blog berhasil ditambahkan');
        return redirect()->route('blogadmin.index');
    }


    public function edit($id)
    {
        abort_if(Gate::denies('blog_update'), 403);

        $data = Template::get("datatable");

        $blogadmin = MasterPost::findOrFail($id);

        // Ubah string "1,3,5" menjadi array [1,3,5]
        $blogadmin->tags = $blogadmin->tags ? explode(',', $blogadmin->tags) : [];

        $data['blogadmin'] = $blogadmin;

        $data['categoryAll'] = MasterPostCategory::where('status', 'active')
            ->orderBy('id', 'asc')
            ->get();

        $data['taglines'] = MasterPostTag::where('status', 'active')
            ->orderBy('id')
            ->get();

        return view('backend.blog.blog_edit', $data);
    }

    public function update(Request $request, MasterPost $blogadmin)
    {
        abort_if(Gate::denies('blog_update'), 403);
        $request->validate([
            'title'             => 'required|string|max:250',
            'summary'           => 'nullable|string',
            'description'       => 'nullable|string',
            'photo'             => 'nullable|string',
            'post_cat_id'       => 'nullable|exists:master_post_categories,id',
            'tags'              => 'nullable|array',
            'status'            => 'nullable|in:active,inactive',
        ]);

        $data = [
            'title'       => $request->title,
            'summary'     => $request->summary,
            'description' => $request->description,
            'photo'       => $request->photo,
            'post_cat_id' => $request->post_cat_id,
            'tags'        => $request->tags ? implode(',', $request->tags) : null,
            'status'      => $request->status,
        ];

        $slug = Str::slug($request->title);
        if (MasterPost::where('slug', $slug)->where('id', '!=', $blogadmin->id)->exists()) {
            $slug .= '-' . date('ymdHis');
        }

        $data['slug'] = $slug;

        log_custom("Update data blog " . $blogadmin->id, $blogadmin->toArray());
        $blogadmin->update($data);

        Alert::info('Info', 'Data Berhasil Diperbarui');
        return redirect()->route('blogadmin.index');
    }


    public function destroy(MasterPost $blogadmin)
    {
        abort_if(Gate::denies('blog_delete'), 403);

        $blogadmin->delete();

        $this->telegram->sendMessage("Hapus Blog ID: " . $blogadmin->id);
        log_custom("Hapus Blog ID " . $blogadmin->id, $blogadmin->toArray());

        return response()->json('ok');
    }
}

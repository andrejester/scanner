<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\CommentsDataTable;
use App\Http\Controllers\Controller;
use App\Library\Template;
use App\Models\Comments;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use RealRashid\SweetAlert\Facades\Alert;

class CommentsController extends Controller
{
    public function index(CommentsDataTable $dataTable)
    {
        abort_if(Gate::denies('comments_read'), 403);
        log_custom("Buka menu comments");
        $data = Template::get("datatable");
        $data['jsTambahan'] = "$('#comments').addClass('active');";
        return $dataTable->render("backend.comments.comments", $data);
    }

    public function show($id)
    {
        abort_if(Gate::denies('comments_read'), 403);
        $data = Template::get("datatable");
        $data['comment'] = Comments::with(['user', 'blog', 'replies.user'])->findOrFail($id);
        return view('backend.comments.comments_show', $data);
    }

    public function updateStatus(Request $request, $id)
    {
        abort_if(Gate::denies('comments_update'), 403);

        $comment = Comments::findOrFail($id);

        $request->validate([
            'status' => 'required|in:active,inactive,pending',
        ]);

        $comment->update(['status' => $request->status]);

        log_custom("Update status comment " . $comment->id . " to " . $request->status);

        Alert::info('Info', 'Status Berhasil Diperbarui');
        return back();
    }

    public function destroy(Comments $comment)
    {
        abort_if(Gate::denies('comments_delete'), 403);
        $comment->delete();
        log_custom("Hapus comment " . $comment->id);
        return response()->json('ok');
    }
}

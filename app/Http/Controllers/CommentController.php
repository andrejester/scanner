<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Comment;
use App\Models\Comments;
use RealRashid\SweetAlert\Facades\Alert;

class CommentController extends Controller
{
    // Menyimpan komentar yang dikirimkan melalui form
    public function store(Request $request)
    {
        // Validasi input dari form
        $validated = $request->validate([
            'user_id' => 'required|integer',
            'email' => 'nullable|email',
            'video_id' => 'nullable|integer',
            'comment' => 'required|string|max:1000',
            'parent_id' => 'nullable|integer',
        ]);

        // Menyimpan komentar ke database
        Comments::create([
            'user_id' => $validated['user_id'],
            'email' => $validated['email'],
            'video_id' => $validated['video_id'] ?? null,
            'comment' => $validated['comment'],
            'status' => 'active',
            'parent_id' => $validated['parent_id'] ?? null,
        ]);

        // Redirect ke halaman yang diinginkan setelah menyimpan
        // return redirect()->route('home')->with('success', 'Komentar berhasil disimpan!');
        Alert::success('Success', 'OK !');
        return back()->with('success', 'Komentar berhasil disimpan!');
    }
}

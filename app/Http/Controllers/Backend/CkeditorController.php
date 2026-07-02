<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CkeditorController extends Controller
{
    // 
    public function upload(Request $request)
    {
        if ($request->hasFile('upload')) {

            $file = $request->file('upload');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/ckeditor'), $filename);

            $url = asset('uploads/ckeditor/' . $filename);

            return response()->json([
                "uploaded" => 1,
                "fileName" => $filename,
                "url" => $url,
            ]);
        }
    }
}

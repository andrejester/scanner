<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\MasterDosen;
use Illuminate\Http\Request;

class DosenController extends Controller
{
    public function index()
    {
        $dosens = MasterDosen::where('status', 'aktif')
            ->orderBy('urutan', 'asc')
            ->orderBy('nama', 'asc')
            ->get();

        return view('frontend.dosen.index', compact('dosens'));
    }

    public function show($id)
    {
        $dosen = MasterDosen::where('status', 'aktif')
            ->findOrFail($id);

        return view('frontend.dosen.detail', compact('dosen'));
    }
}

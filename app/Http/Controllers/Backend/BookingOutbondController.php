<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Backend\BookingOutbond;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BookingOutbondController extends Controller
{
    //
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'nullable|email',
            'no_hp' => 'nullable|string|max:20',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'jumlah_peserta' => 'required|integer|min:1',
            'paket_outbond' => 'required|string',
            'catatan' => 'nullable|string',
        ]);

        $validated['kode_booking'] = 'OB-' . strtoupper(Str::random(8));
        $validated['total_biaya'] = 0; // bisa hitung otomatis nanti

        $booking = BookingOutbond::create($validated);

        return response()->json([
            'success' => true,
            'data' => $booking,
        ]);
    }
}

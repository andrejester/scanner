<?php

namespace App\Http\Controllers;

use App\Models\Statistik;
use Carbon\Carbon;
use Illuminate\Http\Request;

class StatistikController extends Controller
{
    public function index()
    {
        // // Get IP address
        // $ip = request()->ip();

        // // Get the current date
        // $tanggal = Carbon::now()->format('Ymd');
        // $waktu = time();

        // // Check if the IP has visited today
        // $statistik = Statistik::where('ip', $ip)->where('tanggal', $tanggal)->first();

        // if (!$statistik) {
        //     // Insert a new entry for the visitor
        //     Statistik::create([
        //         'ip' => $ip,
        //         'tanggal' => $tanggal,
        //         'hits' => 1,
        //         'online' => $waktu,
        //     ]);
        // } else {
        //     // Update the existing entry for the visitor
        //     $statistik->increment('hits');
        //     $statistik->online = $waktu;
        //     $statistik->save();
        // }

        // // Pengunjung hari ini
        // $pengunjung = Statistik::where('tanggal', $tanggal)->groupBy('ip')->count();

        // // Total Pengunjung
        // $totPengunjung = Statistik::sum('hits');

        // // Hits hari ini
        // $hits = Statistik::where('tanggal', $tanggal)->sum('hits');

        // // Total Hits
        // $totHits = Statistik::sum('hits');

        // // Pengunjung yang sedang online
        // $bataswaktu = time() - 300;
        // $pengunjungOnline = Statistik::where('online', '>', $bataswaktu)->count();

        // // Convert the total visitors to image representation
        // $folder = 'counter';
        // $ext = '.png';
        // $totpengunjunggbr = str_pad($totPengunjung, 6, '0', STR_PAD_LEFT);

        // for ($i = 0; $i <= 9; $i++) {
        //     $totpengunjunggbr = str_replace($i, "<img src='$folder/$i$ext' alt='$i'>", $totpengunjunggbr);
        // }

        // // Pass data to the view
        // return view('blog::pages.statistik', compact('totpengunjunggbr', 'pengunjung', 'totPengunjung', 'hits', 'totHits', 'pengunjungOnline'));
    }
}

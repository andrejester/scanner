<?php

namespace App\Http\Controllers;

use App\Library\Template;
use App\Models\Backend\Note;
use App\Models\Master\MasterPost;
use App\Models\Visitor;
use App\Models\Statistik;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    public function clearCache()
    {
        if (App::environment('local')) {
            // Hanya jalankan ini di lingkungan 'local' (development)
            Artisan::call('cache:clear');
            Artisan::call('route:clear');
            Artisan::call('view:clear');
            Artisan::call('config:clear');

            alert()->success('Success', 'Cache berhasil dibersihkan!');
            return redirect()->back();
        } else {
            // Jika di production, hanya tampilkan alert tanpa menjalankan perintah Artisan
            alert()->info('Info', 'Aplikasi sedang berjalan di produksi. Cache tidak dibersihkan.');
            return redirect()->back();
        }
    }

    public function storageLink()
    {
        if (App::environment('local')) {
            // Tentukan folder target
            $targetFolder = public_path('storage');

            // Periksa jika folder storage ada, kemudian hapus
            if (File::exists($targetFolder)) {
                File::deleteDirectory($targetFolder);  // Menghapus seluruh isi folder
            }

            // Membuat symbolic link untuk storage
            Artisan::call('storage:link');

            // Tampilkan alert jika berhasil
            alert()->success('Success', 'Storage link berhasil dibuat!');
            return redirect()->back();
        } else {
            // Jika aplikasi berjalan di lingkungan production
            alert()->info('Info', 'Aplikasi berjalan di produksi, storage link tidak dibuat.');
            return redirect()->back();
        }
    }


    function fileManager()
    {
        $data = Template::get();
        array_push($data['pilihCss'],  "chart",  "apex-charts", "card-analytics");
        array_push($data['pilihJs'],   "chart");
        return view("layouts.file-manager", $data);
    }

    function show()
    {
        $data = Template::get();
        array_push($data['pilihCss'],  "chart",  "apex-charts", "card-analytics");
        array_push($data['pilihJs'],   "chart");

        // Tambahkan menu active
        $data['jsTambahan'] = "$('#dashboards').addClass('open active');";

        // Notes user
        return view("home", $data);
    }


    function backup()
    {
        // Pastikan pengguna memiliki izin untuk memperbarui backup
        //abort_if(Gate::denies('backup_update'), 403);

        // Tentukan path folder backup di storage
        $backupPath = 'public/' . env("APP_NAME");

        if (!File::exists($backupPath)) {
            // Jika folder tidak ada, buat folder
            File::makeDirectory($backupPath, 0755, true);
        }

        // Ambil daftar file dalam folder backup
        $files = Storage::files($backupPath);
        $data  = Template::get();
        $data['pathBackup'] = []; // Inisialisasi array pathBackup

        // Tentukan batas waktu (30 hari yang lalu)
        $thirtyDaysAgo = Carbon::now()->subDays(29);

        // Loop untuk memeriksa setiap file
        foreach ($files as $file) {
            // Ambil timestamp terakhir file dimodifikasi
            $lastModified = Storage::lastModified($file);
            // Konversi timestamp ke objek Carbon untuk manipulasi tanggal
            $modifiedDate = Carbon::createFromTimestamp($lastModified, 'UTC');

            // Jika file lebih lama dari 30 hari, hapus file tersebut
            if ($modifiedDate->lessThan($thirtyDaysAgo)) {
                Storage::delete($file);
            } else {
                // Ambil nama file dari path
                $fileName = basename($file);
                // Tambahkan informasi file ke array pathBackup
                $data['pathBackup'][] = [
                    'text' => $fileName,
                    'type' => 'database',
                    'a_attr' => [
                        "href" => asset("storage/" . env("APP_NAME") . "/" . $fileName)
                    ]
                ];
            }
        }

        // Tambahkan JavaScript tambahan untuk manipulasi UI jika diperlukan
        $data['jsTambahan'] = "
        $('#backup').addClass('open active');
    ";

        // Kembalikan view dengan data yang sudah disiapkan
        return view("user.backup", $data);
    }

    public function versi()
    {
        $commitMessage  = "";
        $projectRoot    = base_path();
        chdir($projectRoot);
        $output = shell_exec('git log');

        $commits = [];

        $lines = explode("\n", $output);
        foreach ($lines as $line) {
            if (empty($line)) {
                continue;
            }

            if (strpos($line, 'commit') === 0) {
                $commitHash = trim(substr($line, 6));
                $commitMessage = '';
                $author = ''; // Initialize author variable
            } else if (strpos($line, 'Author:') === 0) {
                $author = trim(substr($line, 7)); // Extract author name (excluding "Author: ")
            } else if (strpos($line, 'Date:') === 0) {
                $commitDate = trim(substr($line, 5));
            } else {
                $commitMessage .= $line . "\n";
            }

            if (!empty($commitHash) && !empty($commitMessage) && !empty($commitDate) && !empty($author)) {
                $commits[] = [
                    'hash' => $commitHash,
                    'message' => trim($commitMessage),
                    'date' => $commitDate,
                    'author' => $author, // Add author to the array
                ];

                $commitHash = '';
                $commitMessage = '';
                $commitDate = '';
                $author = ''; // Reset author for next commit
            }
        }
        $reversedCommits = array_reverse($commits, true);
        $version = "1.0.0";
        foreach ($reversedCommits as $key => $value) {
            $version = incrementVersion($version);
            $commits[$key]['version'] = $version;
        }
        $data = Template::get();
        $data['update'] = $commits;
        $data['jsTambahan'] = "
        $('#versi').addClass('open active');
        ";
        return view("user.versi", $data);
    }
}

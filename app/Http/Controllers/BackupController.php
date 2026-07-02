<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use RealRashid\SweetAlert\Facades\Alert;

class BackupController extends Controller
{
    public function runDatabaseBackup()
    {
        // Jalankan backup
        Artisan::call('backup:run', ['--only-db' => true]);

        // Ambil nama file backup terakhir
        $backupPath = storage_path('app/public/backup-temp'); // Sesuaikan dengan lokasi backup
        $backupFiles = glob($backupPath . '/*.zip'); // Sesuaikan dengan ekstensi backup

        if ($backupFiles) {
            // Ambil file terbaru
            $latestBackupFile = max($backupFiles);
            $dateTime = Carbon::now()->format('Y-m-d-His');
            $newBackupName = "backup-{$dateTime}.zip";

            // Ganti nama file backup
            $newBackupPath = $backupPath . '/' . $newBackupName;
            rename($latestBackupFile, $newBackupPath);

            Alert::info('Info Title', 'Database backup completed!');
            return redirect()->route('backup.index');
        }
    }
}


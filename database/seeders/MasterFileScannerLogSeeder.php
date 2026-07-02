<?php

namespace Database\Seeders;

use App\Models\Master\MasterFileScannerLog;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MasterFileScannerLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $logs = [
            [
                'file_path' => '/uploads/documents/file1.pdf',
                'file_name' => 'file1.pdf',
                'file_size' => 1024000,
                'file_hash' => md5('file1.pdf'),
                'threat_level' => 'safe',
                'threat_type' => null,
                'suspicious_patterns' => null,
                'scan_result' => 'clean',
                'is_quarantined' => false,
                'scanned_by' => 1,
                'scanned_at' => now(),
            ],
            [
                'file_path' => '/uploads/documents/file2.docx',
                'file_name' => 'file2.docx',
                'file_size' => 512000,
                'file_hash' => md5('file2.docx'),
                'threat_level' => 'safe',
                'threat_type' => null,
                'suspicious_patterns' => null,
                'scan_result' => 'clean',
                'is_quarantined' => false,
                'scanned_by' => 1,
                'scanned_at' => now(),
            ],
            [
                'file_path' => '/uploads/documents/file3.exe',
                'file_name' => 'file3.exe',
                'file_size' => 2048000,
                'file_hash' => md5('file3.exe'),
                'threat_level' => 'high',
                'threat_type' => 'Executable File',
                'suspicious_patterns' => json_encode(['pattern1', 'pattern2']),
                'scan_result' => 'threat_detected',
                'is_quarantined' => true,
                'scanned_by' => 1,
                'scanned_at' => now(),
            ],
        ];

        foreach ($logs as $log) {
            MasterFileScannerLog::create($log);
        }
    }
}

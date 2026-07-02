<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Library\Template;

class DatabaseResetController extends Controller
{
    /**
     * Tabel-tabel yang TIDAK boleh di-truncate (tabel sistem penting)
     */
    private $protectedTables = [
        'users',
        'password_reset_tokens',
        'sessions',
        'cache',
        'cache_locks',
        'jobs',
        'job_batches',
        'failed_jobs',
        'migrations',
        'permissions',
        'roles',
        'model_has_permissions',
        'model_has_roles',
        'role_has_permissions',
        'settings',
        'configs',
        'personal_access_tokens',
    ];

    /**
     * Tampilkan halaman reset database
     */
    public function index()
    {
        // Pastikan hanya bisa diakses di development
        if (!app()->environment('local', 'development')) {
            abort(403, 'Fitur ini hanya tersedia di environment development');
        }

        log_custom("Buka menu database reset");

        // Get template data
        $data = Template::get("datatable");
        $data['jsTambahan'] = "$('#database-reset').addClass('active');";

        // Ambil semua tabel
        $allTables = $this->getAllTables();

        // Filter tabel yang bisa di-truncate
        $truncatableTables = array_diff($allTables, $this->protectedTables);

        // Hitung jumlah record di setiap tabel
        $tablesWithCount = [];
        foreach ($truncatableTables as $table) {
            try {
                $count = DB::table($table)->count();
                $tablesWithCount[] = [
                    'name' => $table,
                    'count' => $count
                ];
            } catch (\Exception $e) {
                // Skip jika ada error
                continue;
            }
        }

        $data['tables'] = $tablesWithCount;
        $data['protectedTables'] = $this->protectedTables;

        return view('backend.system.database-reset', $data);
    }

    /**
     * Proses truncate tabel-tabel yang dipilih
     */
    public function truncate(Request $request)
    {
        if (!app()->environment('local', 'development')) {
            return response()->json([
                'success' => false,
                'message' => 'Fitur ini hanya tersedia di environment development'
            ], 403);
        }

        $request->validate([
            'tables' => 'required|array',
            'tables.*' => 'string'
        ]);

        $tablesToTruncate = $request->tables;

        $deleted = [];
        $failed = [];

        try {

            Schema::disableForeignKeyConstraints();

            foreach ($tablesToTruncate as $table) {

                // Protected table
                if (in_array($table, $this->protectedTables)) {

                    $failed[] = [
                        'table' => $table,
                        'reason' => 'Tabel dilindungi'
                    ];

                    continue;
                }

                // Table exists
                if (!Schema::hasTable($table)) {

                    $failed[] = [
                        'table' => $table,
                        'reason' => 'Tabel tidak ditemukan'
                    ];

                    continue;
                }

                try {

                    // Delete data
                    DB::table($table)->delete();

                    // Reset AUTO_INCREMENT
                    DB::statement("ALTER TABLE `$table` AUTO_INCREMENT = 1");

                    $deleted[] = $table;

                    log_custom("Delete data tabel: {$table}");
                } catch (\Throwable $e) {

                    $failed[] = [
                        'table' => $table,
                        'reason' => $e->getMessage()
                    ];

                    log_custom("Gagal delete tabel {$table}: " . $e->getMessage());
                }
            }

            Schema::enableForeignKeyConstraints();

            return response()->json([
                'success' => true,
                'message' => 'Proses delete selesai',
                'deleted' => $deleted,
                'failed' => $failed,
                'total_deleted' => count($deleted),
                'total_failed' => count($failed),
            ]);
        } catch (\Throwable $e) {

            Schema::enableForeignKeyConstraints();

            log_custom("Error delete: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Truncate semua tabel kecuali yang protected
     */
    public function truncateAll()
    {
        // Pastikan hanya bisa diakses di development
        if (!app()->environment('local', 'development')) {

            return response()->json([
                'success' => false,
                'message' => 'Fitur ini hanya tersedia di environment development'
            ], 403);
        }

        $allTables = $this->getAllTables();

        // Exclude protected tables
        $deletableTables = array_diff($allTables, $this->protectedTables);

        $deleted = [];
        $failed = [];

        try {

            Schema::disableForeignKeyConstraints();

            foreach ($deletableTables as $table) {

                try {

                    // Hapus semua data
                    DB::table($table)->delete();

                    // Reset AUTO_INCREMENT
                    DB::statement("ALTER TABLE `$table` AUTO_INCREMENT = 1");

                    $deleted[] = $table;

                    log_custom("Delete data tabel: {$table}");
                } catch (\Throwable $e) {

                    $failed[] = [
                        'table' => $table,
                        'reason' => $e->getMessage()
                    ];

                    log_custom("Gagal delete tabel {$table}: " . $e->getMessage());
                }
            }

            Schema::enableForeignKeyConstraints();

            log_custom(
                "Delete ALL selesai - Berhasil: "
                    . count($deleted)
                    . ", Gagal: "
                    . count($failed)
            );

            return response()->json([
                'success' => true,
                'message' => 'Semua data tabel berhasil dihapus',
                'deleted' => $deleted,
                'failed' => $failed,
                'total_deleted' => count($deleted),
                'total_failed' => count($failed)
            ]);
        } catch (\Throwable $e) {

            Schema::enableForeignKeyConstraints();

            log_custom("Error delete ALL: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ambil semua nama tabel di database
     */
    private function getAllTables()
    {
        $tables = DB::select('SHOW TABLES');
        $dbName = 'Tables_in_' . env('DB_DATABASE');

        return array_map(function ($table) use ($dbName) {
            return $table->$dbName;
        }, $tables);
    }
}

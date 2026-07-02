<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Library\Template;

class DatabaseConvertController extends Controller
{
    /**
     * Koneksi database sumber (lama)
     * Bisa dikonfigurasi via .env: DB_SOURCE_*
     */
    private function getSourceConnection()
    {
        return 'db_source';
    }

    /**
     * Pastikan koneksi sumber terdaftar secara dinamis
     */
    private function registerSourceConnection(Request $request)
    {
        $host     = $request->session()->get('db_source_host', env('DB_SOURCE_HOST', '127.0.0.1'));
        $port     = $request->session()->get('db_source_port', env('DB_SOURCE_PORT', '3306'));
        $database = $request->session()->get('db_source_database', env('DB_SOURCE_DATABASE', ''));
        $username = $request->session()->get('db_source_username', env('DB_SOURCE_USERNAME', 'root'));
        $password = $request->session()->get('db_source_password', env('DB_SOURCE_PASSWORD', ''));

        config([
            'database.connections.db_source' => [
                'driver'    => 'mysql',
                'host'      => $host,
                'port'      => $port,
                'database'  => $database,
                'username'  => $username,
                'password'  => $password,
                'charset'   => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix'    => '',
            ]
        ]);
    }

    /**
     * Tampilkan halaman konversi database
     */
    public function index(Request $request)
    {
        log_custom("Buka menu database convert");

        $data = Template::get("datatable");
        $data['jsTambahan'] = "$('#database-convert').addClass('active');";

        // Ambil konfigurasi sumber dari session
        $data['sourceConfig'] = [
            'host'     => $request->session()->get('db_source_host', env('DB_SOURCE_HOST', '127.0.0.1')),
            'port'     => $request->session()->get('db_source_port', env('DB_SOURCE_PORT', '3306')),
            'database' => $request->session()->get('db_source_database', env('DB_SOURCE_DATABASE', '')),
            'username' => $request->session()->get('db_source_username', env('DB_SOURCE_USERNAME', 'root')),
        ];

        $data['sourceTables']  = [];
        $data['targetTables']  = [];
        $data['isConnected']   = false;
        $data['errorMessage']  = null;

        // Coba ambil tabel dari database sumber
        if (!empty($data['sourceConfig']['database'])) {
            try {
                $this->registerSourceConnection($request);
                DB::connection('db_source')->getPdo();

                $data['sourceTables'] = $this->getTablesFromConnection('db_source');
                $data['isConnected']  = true;
            } catch (\Exception $e) {
                $data['errorMessage'] = $e->getMessage();
            }
        }

        // Ambil tabel dari database target (current)
        $data['targetTables'] = $this->getTablesFromConnection('mysql');

        return view('backend.system.database-convert', $data);
    }

    /**
     * Simpan konfigurasi koneksi database sumber ke session & test koneksi
     */
    public function saveConfig(Request $request)
    {
        $request->validate([
            'host'     => 'required|string',
            'port'     => 'required|numeric',
            'database' => 'required|string',
            'username' => 'required|string',
            'password' => 'nullable|string',
        ]);

        $request->session()->put('db_source_host',     $request->host);
        $request->session()->put('db_source_port',     $request->port);
        $request->session()->put('db_source_database', $request->database);
        $request->session()->put('db_source_username', $request->username);
        $request->session()->put('db_source_password', $request->password ?? '');

        // Test koneksi
        try {
            $this->registerSourceConnection($request);
            DB::connection('db_source')->getPdo();

            $tables = $this->getTablesFromConnection('db_source');

            return response()->json([
                'success' => true,
                'message' => 'Koneksi berhasil! Ditemukan ' . count($tables) . ' tabel.',
                'tables'  => $tables,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Koneksi gagal: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Ambil kolom dari tabel sumber
     */
    public function getSourceColumns(Request $request)
    {
        $request->validate([
            'table' => 'required|string|alpha_dash',
        ]);

        try {
            $this->registerSourceConnection($request);
            $columns = $this->getColumnsFromConnection('db_source', $request->table);

            return response()->json([
                'success' => true,
                'columns' => $columns,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Ambil kolom dari tabel target
     */
    public function getTargetColumns(Request $request)
    {
        $request->validate([
            'table' => 'required|string|alpha_dash',
        ]);

        try {
            if (!Schema::hasTable($request->table)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tabel tidak ditemukan di database target.',
                ], 404);
            }

            $columns = $this->getColumnsFromConnection('mysql', $request->table);

            return response()->json([
                'success' => true,
                'columns' => $columns,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Preview data dari tabel sumber (5 baris pertama)
     */
    public function previewSource(Request $request)
    {
        $request->validate([
            'table' => 'required|string|alpha_dash',
        ]);

        try {
            $this->registerSourceConnection($request);
            $data = DB::connection('db_source')->table($request->table)->limit(5)->get()->toArray();
            $count = DB::connection('db_source')->table($request->table)->count();

            return response()->json([
                'success' => true,
                'data'    => $data,
                'count'   => $count,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Proses konversi / migrasi data dari tabel sumber ke tabel target
     */
    public function convert(Request $request)
    {
        $request->validate([
            'source_table'       => 'required|string|alpha_dash',
            'target_table'       => 'required|string|alpha_dash',
            'field_mapping'      => 'required|array',
            'field_mapping.*'    => 'nullable|string',
            'mode'               => 'required|in:insert,upsert,replace',
            'truncate_first'     => 'boolean',
            'batch_size'         => 'nullable|integer|min:1|max:5000',
        ]);

        $sourceTable  = $request->source_table;
        $targetTable  = $request->target_table;
        $fieldMapping = $request->field_mapping; // ['source_col' => 'target_col']
        $mode         = $request->mode;
        $truncate     = $request->boolean('truncate_first', false);
        $batchSize    = $request->input('batch_size', 500);

        // Validasi tabel target ada
        if (!Schema::hasTable($targetTable)) {
            return response()->json([
                'success' => false,
                'message' => "Tabel target '{$targetTable}' tidak ditemukan.",
            ], 422);
        }

        try {
            $this->registerSourceConnection($request);
            DB::connection('db_source')->getPdo();
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Koneksi ke database sumber gagal: ' . $e->getMessage(),
            ], 500);
        }

        // Filter field_mapping: hanya yang punya target_column terisi
        $activeMapping = array_filter($fieldMapping, fn($v) => !empty($v));

        if (empty($activeMapping)) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada mapping field yang aktif.',
            ], 422);
        }

        $inserted  = 0;
        $updated   = 0;
        $skipped   = 0;
        $errors    = [];

        try {
            Schema::disableForeignKeyConstraints();

            // Truncate dulu jika diminta
            if ($truncate) {
                DB::table($targetTable)->delete();
                DB::statement("ALTER TABLE `{$targetTable}` AUTO_INCREMENT = 1");
            }

            // Ambil data dari sumber secara batch
            $total   = DB::connection('db_source')->table($sourceTable)->count();
            $offset  = 0;

            while ($offset < $total) {
                $rows = DB::connection('db_source')
                    ->table($sourceTable)
                    ->offset($offset)
                    ->limit($batchSize)
                    ->get();

                foreach ($rows as $row) {
                    $rowArr = (array) $row;

                    // Petakan field sesuai mapping
                    $mappedRow = [];
                    foreach ($activeMapping as $sourceCol => $targetCol) {
                        $mappedRow[$targetCol] = $rowArr[$sourceCol] ?? null;
                    }

                    if (empty($mappedRow)) {
                        $skipped++;
                        continue;
                    }

                    try {
                        if ($mode === 'insert') {
                            DB::table($targetTable)->insert($mappedRow);
                            $inserted++;
                        } elseif ($mode === 'upsert') {
                            // Coba cari berdasarkan id jika ada
                            $idField = isset($mappedRow['id']) ? 'id' : null;

                            if ($idField && DB::table($targetTable)->where('id', $mappedRow['id'])->exists()) {
                                DB::table($targetTable)->where('id', $mappedRow['id'])->update($mappedRow);
                                $updated++;
                            } else {
                                DB::table($targetTable)->insert($mappedRow);
                                $inserted++;
                            }
                        } elseif ($mode === 'replace') {
                            DB::statement(
                                "REPLACE INTO `{$targetTable}` (" .
                                    implode(',', array_map(fn($c) => "`{$c}`", array_keys($mappedRow))) .
                                    ") VALUES (" .
                                    implode(',', array_fill(0, count($mappedRow), '?')) .
                                    ")",
                                array_values($mappedRow)
                            );
                            $inserted++;
                        }
                    } catch (\Throwable $rowErr) {
                        $skipped++;
                        if (count($errors) < 20) {
                            $errors[] = $rowErr->getMessage();
                        }
                    }
                }

                $offset += $batchSize;
            }

            Schema::enableForeignKeyConstraints();

            log_custom("Konversi tabel {$sourceTable} → {$targetTable}: inserted={$inserted}, updated={$updated}, skipped={$skipped}");

            return response()->json([
                'success'  => true,
                'message'  => 'Konversi selesai.',
                'inserted' => $inserted,
                'updated'  => $updated,
                'skipped'  => $skipped,
                'total'    => $total,
                'errors'   => $errors,
            ]);
        } catch (\Throwable $e) {
            Schema::enableForeignKeyConstraints();

            log_custom("Error konversi {$sourceTable} → {$targetTable}: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Ambil daftar tabel dari koneksi tertentu
     */
    private function getTablesFromConnection(string $connection): array
    {
        if ($connection === 'mysql') {
            $dbName = env('DB_DATABASE');
        } else {
            $dbName = config("database.connections.{$connection}.database");
        }

        $tables = DB::connection($connection)->select('SHOW TABLES');
        $key    = 'Tables_in_' . $dbName;

        return array_map(fn($t) => $t->$key, $tables);
    }

    /**
     * Ambil daftar kolom dari tabel di koneksi tertentu
     */
    private function getColumnsFromConnection(string $connection, string $table): array
    {
        $columns = DB::connection($connection)->select("SHOW COLUMNS FROM `{$table}`");
        return array_map(fn($c) => [
            'field' => $c->Field,
            'type'  => $c->Type,
            'null'  => $c->Null,
            'key'   => $c->Key,
        ], $columns);
    }
}

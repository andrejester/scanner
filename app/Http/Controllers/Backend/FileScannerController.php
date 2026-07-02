<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\FileScannerDataTable;
use App\Http\Controllers\Controller;
use App\Library\Template;
use App\Models\Backend\FileScanner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use RealRashid\SweetAlert\Facades\Alert;

class FileScannerController extends Controller
{
    // =========================================================================
    // 22 KATEGORI PEMERIKSAAN (sesuai panduan)
    // =========================================================================

    // 1. Signature Scanner – fungsi berbahaya dasar
    private array $signaturePatterns = [
        'eval\s*\('             => '[Sig] Eval Function',
        'assert\s*\('           => '[Sig] Assert Function',
        'system\s*\('           => '[Sig] System Command',
        'exec\s*\('             => '[Sig] Exec Command',
        'shell_exec\s*\('       => '[Sig] Shell Exec',
        'passthru\s*\('         => '[Sig] Passthru',
        'proc_open\s*\('        => '[Sig] Process Open',
        'popen\s*\('            => '[Sig] Pipe Open',
        'curl_exec\s*\('        => '[Sig] CURL Exec',
        'fsockopen\s*\('        => '[Sig] Socket Open',
        'base64_decode\s*\('    => '[Sig] Base64 Decode',
        'gzinflate\s*\('        => '[Sig] Gzinflate',
    ];

    // 2. Dangerous Combination – kombinasi fungsi berbahaya
    private array $dangerousCombinations = [
        'eval\s*\(\s*base64_decode\s*\('      => '[Comb] eval+base64_decode',
        'eval\s*\(\s*gzinflate\s*\('          => '[Comb] eval+gzinflate',
        'eval\s*\(\s*str_rot13\s*\('          => '[Comb] eval+str_rot13',
        'eval\s*\(\s*gzuncompress\s*\('       => '[Comb] eval+gzuncompress',
        'eval\s*\(\s*gzdecode\s*\('           => '[Comb] eval+gzdecode',
        'eval\s*\(\s*strrev\s*\('             => '[Comb] eval+strrev',
        'system\s*\(\s*\$_GET'               => '[Comb] system+$_GET',
        'exec\s*\(\s*\$_POST'               => '[Comb] exec+$_POST',
        'shell_exec\s*\(\s*\$_REQUEST'      => '[Comb] shell_exec+$_REQUEST',
        'assert\s*\(\s*\$_'                 => '[Comb] assert+superglobal',
        // Exfiltration via HTTP – kirim data ke server luar
        'file_get_contents\s*\(\s*["\']https?://' => '[Comb] Remote Exfiltration (file_get_contents)',
        'curl_exec.*CURLOPT_URL.*http'            => '[Comb] Remote Exfiltration (curl)',
        'fsockopen\s*\(.*\$_'                     => '[Comb] Socket+Superglobal',
    ];


    // 3. Superglobal Input – input user diteruskan ke fungsi berbahaya
    private array $superglobalPatterns = [
        '\$_GET\s*\['     => '[Super] GET Parameter',
        '\$_POST\s*\['    => '[Super] POST Parameter',
        '\$_REQUEST\s*\[' => '[Super] REQUEST Parameter',
        '\$_COOKIE\s*\['  => '[Super] COOKIE Parameter',
        '\$_FILES\s*\['   => '[Super] FILES Parameter',
        '\$_SERVER\s*\['  => '[Super] SERVER Variable',
        'php://input'     => '[Super] php://input',
    ];

    // 4. Obfuscation – teknik menyusun kode berbahaya
    private array $obfuscationPatterns = [
        'chr\s*\(\s*\d+'        => '[Obf] chr() Assembly',
        'ord\s*\('              => '[Obf] ord()',
        'pack\s*\('             => '[Obf] pack()',
        'strrev\s*\('           => '[Obf] strrev()',
        'str_rot13\s*\('        => '[Obf] str_rot13()',
        'implode\s*\(.*explode' => '[Obf] implode+explode Assembly',
    ];

    // 5. Encoded String – string Base64 sangat panjang (>500 karakter)
    // 6. Hex String – payload \x41 atau 0x41
    // 7. Very Long Line – baris > 1000 karakter (diperiksa terpisah)

    // 8. Suspicious Variable – variable variable, GLOBALS, nama acak
    private array $suspiciousVariablePatterns = [
        '\$\$\w+'               => '[Var] Variable Variable ($$)',
        '\${GLOBALS}'           => '[Var] ${GLOBALS}',
        '\$GLOBALS\s*\['        => '[Var] $GLOBALS Access',
        '\$\{["\']'             => '[Var] Dynamic Variable',
    ];

    // 9. Dynamic Function Call – pemanggilan fungsi via variabel
    private array $dynamicCallPatterns = [
        '\$\w+\s*\('            => '[Dyn] Dynamic Function Call',
        'call_user_func\s*\('   => '[Dyn] call_user_func',
        'call_user_func_array\s*\(' => '[Dyn] call_user_func_array',
        'preg_replace.*\/e'     => '[Dyn] preg_replace /e modifier',
        'create_function\s*\('  => '[Dyn] create_function',
    ];

    // 10. Dynamic Include – include/require dengan input dinamis
    // 11. Remote Include – include/require ke URL
    private array $includePatterns = [
        'include\s*\(\s*\$'     => '[Inc] Dynamic Include',
        'require\s*\(\s*\$'     => '[Inc] Dynamic Require',
        'include_once\s*\(\s*\$' => '[Inc] Dynamic Include Once',
        'require_once\s*\(\s*\$' => '[Inc] Dynamic Require Once',
        'include\s*\(\s*["\']https?://' => '[Inc] Remote Include URL',
        'file_get_contents\s*\(\s*["\']https?://' => '[Inc] Remote file_get_contents',
    ];

    // 12. Hidden Upload – move_uploaded_file tanpa validasi
    private array $uploadPatterns = [
        'move_uploaded_file\s*\(' => '[Upload] move_uploaded_file',
        'file_put_contents\s*\(' => '[Upload] file_put_contents',
        'fwrite\s*\('             => '[Upload] fwrite',
    ];

    // 13. Image Shell – ekstensi ganda / berbahaya
    // 14. Fake Image – tag PHP di dalam file gambar
    // (diperiksa via nama file dan konten)

    // 15. Suspicious Filename – shell.php, cmd.php, dan pola nama aneh
    private array $suspiciousFilenames = [
        // Webshell terkenal
        'shell',
        'cmd',
        'webshell',
        'backdoor',
        'c99',
        'r57',
        'b374k',
        'wso',
        'filesmanager',
        'filemanager',
        'bypass',
        'exploit',
        'hack',
        'rootkit',
        'trojan',
        'xss',
        'sqli',
        'pwn',
        'payload',
        'upload',
        'uploader',
        'grabber',
        'stealer',
        'keylog',
        // Penyamaran umum
        'error_log',
        'debug',
        'test',
        'tmp',
        'cache',
        'temp',
        'config_backup',
        'db_backup',
        'dump',
        'passwd',
        'shadow',
    ];

    // 13. Suspicious extensions (image shell)
    private array $suspiciousExtensions = [
        'phtml',
        'phar',
        'php3',
        'php4',
        'php5',
        'php7',
        'shtml',
    ];

    // 16. Permission Scanner – chmod(0777)
    private array $permissionPatterns = [
        'chmod\s*\(\s*[^,]+,\s*0?777' => '[Perm] chmod(0777)',
        'chmod\s*\(\s*[^,]+,\s*0?0777' => '[Perm] chmod(00777)',
        'chmod\s*\(\s*[^,]+,\s*511'   => '[Perm] chmod(511/0777)',
    ];

    // 21. IOC Scanner – tanda tangan webshell terkenal
    private array $iocPatterns = [
        'FilesMan'                           => '[IOC] FilesMan Shell',
        'WSO\s*Shell'                        => '[IOC] WSO Shell',
        'C99Shell'                           => '[IOC] C99 Shell',
        'r57shell'                           => '[IOC] R57 Shell',
        'b374k'                              => '[IOC] B374K Shell',
        'China\s*Chopper'                    => '[IOC] China Chopper',
        'Weevely'                            => '[IOC] Weevely Shell',
        'IndoXploit'                         => '[IOC] IndoXploit Shell',
        'Deface'                             => '[IOC] Deface Marker',
        '@error_reporting\(0\).*@set_time_limit' => '[IOC] Shell Init Pattern',
        // Telegram exfiltration
        'api\.telegram\.org'                 => '[IOC] Telegram Exfiltration',
        'sendMessage.*chat_id'               => '[IOC] Telegram Bot sendMessage',
        // Exfil via URL dengan data sensitif
        'REMOTE_ADDR.*sendMessage'           => '[IOC] IP Exfiltration via Bot',
        'file_get_contents.*sendMessage'     => '[IOC] Silent HTTP Exfiltration',
        // Known C2 patterns
        'curl_setopt.*CURLOPT_POSTFIELDS.*\$_' => '[IOC] C2 Data Exfil via cURL',
        'base64_decode.*eval\|eval.*base64_decode' => '[IOC] Eval-Decode Loop',
        // File manager shells terkenal
        'Gelay\|Mini\s*Shell\|MiniShell'     => '[IOC] Gelay/Mini Shell',
        '\$scandir\s*=\s*array\s*\('         => '[IOC] FilesMan scandir pattern',
        'fm_password_hash\|fm_login'         => '[IOC] FM Login System (dua/filemanager)',
        '\$login_password_hash'              => '[IOC] FM Password Hash',
        'ob_start.*session_start.*error_reporting\(E_ALL\)' => '[IOC] File Manager Init Pattern',
        'function\s+msb\s*\(\$t\)'          => '[IOC] msb() — Gelay Shell signature',

        // ---- Pattern Tambahan: Secure PHP File Manager & WSO Variants ----

        // 1. SECURE PHP FILE MANAGER - COMPLETE
        // Terdeteksi dari string judul/header yang muncul di file manager jenis ini
        'SECURE\s+PHP\s+FILE\s+MANAGER'             => '[IOC] Secure PHP File Manager Shell',
        'SecureFileManager\|secure_file_manager'     => '[IOC] Secure File Manager Identifier',

        // 2. Adminer – Compact database management
        // Adminer adalah tool DB management single-file yang sering disalahgunakan
        'Adminer\s*[-–]\s*Compact\s+database\s+management' => '[IOC] Adminer (DB Management Tool)',
        'adminer\.org'                               => '[IOC] Adminer Reference',
        'class\s+Adminer\s*\{'                       => '[IOC] Adminer Class Definition',

        // 3. SMokWSO – varian WSO Shell dengan nama "SMok"
        'SMokWSO'                                    => '[IOC] SMokWSO Shell',
        'SMok\s*WSO'                                 => '[IOC] SMok WSO Shell Variant',
        'smok_wso\|SmokWso'                          => '[IOC] SMok WSO Identifier',

        // 4. Jakub Vrana – nama pembuat Adminer/phpMyAdmin tools (file berbahaya sering menyertakan nama ini)
        'Jakub\s+Vrana'                              => '[IOC] Jakub Vrana (Adminer Author)',

        // 5. bcrypt hash $2y$12$ – hash password hardcoded (admin backdoor credential)
        // Pattern: $2y$12$ diikuti 53 karakter base64-alphabet
        '\$2y\$\d+\$[A-Za-z0-9./]{53}'              => '[IOC] Hardcoded bcrypt Password Hash',
        '\$2a\$\d+\$[A-Za-z0-9./]{53}'              => '[IOC] Hardcoded bcrypt Password Hash (2a)',

        // 6. ADMIN_PASSWORD_HASH – konstanta/variabel password hash yang hardcoded
        'ADMIN_PASSWORD_HASH'                        => '[IOC] Hardcoded Admin Password Hash Constant',
        'define\s*\(\s*[\'"]ADMIN_PASSWORD_HASH[\'"]' => '[IOC] Admin Password Hash Define',

        // 7. ADMIN_PASSWORD_LEGACY – kredensial legacy yang tertinggal
        'ADMIN_PASSWORD_LEGACY'                      => '[IOC] Legacy Admin Password Constant',
        'define\s*\(\s*[\'"]ADMIN_PASSWORD_LEGACY[\'"]' => '[IOC] Admin Password Legacy Define',

        // 8. fm_secure_session – session management khas Secure PHP File Manager
        'fm_secure_session'                          => '[IOC] FM Secure Session (File Manager Auth)',
        'fm_session_id\|fm_set_session'              => '[IOC] File Manager Session Function',

        // 9. NU AING BRO – string tanda tangan lokal / graffiti backdoor Indonesia
        'NU\s+AING\s+BRO'                            => '[IOC] NU AING BRO (Indonesian Backdoor Tag)',
        'NUAINGBRO\|nu_aing_bro'                     => '[IOC] NU AING BRO Variant',

        // ---- Pattern Tambahan: GitHub, YP, Bule, Token ----

        // 10. GitHub – referensi ke GitHub di dalam file PHP (download payload / C2 via raw.githubusercontent)
        'raw\.githubusercontent\.com'                => '[IOC] GitHub Raw Content (kemungkinan payload download)',
        'github\.com/[a-zA-Z0-9_-]+/[a-zA-Z0-9_-]+' => '[IOC] GitHub Repository Reference',
        'github_token\|GITHUB_TOKEN\|gh_token'       => '[IOC] GitHub Token / Credential',

        // 11. YP – tanda tangan / tag graffiti attacker Indonesia (singkatan kelompok/alias)
        '\bYP\b'                                     => '[IOC] YP Tag (Attacker Signature)',
        'yp_shell\|yp_backdoor\|ypshell'             => '[IOC] YP Shell Variant',

        // 12. Bule – istilah slang yang muncul di beberapa backdoor lokal Indonesia
        '\bbule\b'                                   => '[IOC] Bule Tag (Indonesian Backdoor Marker)',
        'bule_shell\|buleshell'                      => '[IOC] Bule Shell Identifier',

        // 13. Token – token hardcoded (API key, bearer token, bot token)
        // Pattern: variabel bernama token berisi string panjang alfanumerik (≥20 char)
        'bot_token\|BOT_TOKEN\|botToken'             => '[IOC] Hardcoded Bot Token',
        'api_token\|API_TOKEN\|apiToken'             => '[IOC] Hardcoded API Token',
        'bearer\s+[A-Za-z0-9_\-\.]{20,}'            => '[IOC] Hardcoded Bearer Token',
        '\$token\s*=\s*[\'"][A-Za-z0-9_\-\.]{20,}[\'"]' => '[IOC] Hardcoded Token Value',
    ];

    // =========================================================================
    // CONTROLLER METHODS
    // =========================================================================


    public function index(FileScannerDataTable $dataTable)
    {
        abort_if(Gate::denies('filescanner_read'), 403);
        log_custom("Buka menu file scanner");

        $data = Template::get("datatable");
        $data['jsTambahan'] = "$('#filescanner').addClass('active');";

        $data['total_scans']     = FileScanner::count();
        $data['critical_threats'] = FileScanner::where('threat_level', 'critical')->count();
        $data['high_threats']    = FileScanner::where('threat_level', 'high')->count();
        $data['medium_threats']  = FileScanner::where('threat_level', 'medium')->count();
        $data['low_threats']     = FileScanner::where('threat_level', 'low')->count();
        $data['safe_files']      = FileScanner::where('threat_level', 'safe')->count();
        $data['quarantined']     = FileScanner::where('is_quarantined', true)->count();

        return $dataTable->render("backend.filescanner.index", $data);
    }

    public function scan(Request $request)
    {
        abort_if(Gate::denies('filescanner_write'), 403);

        $request->validate([
            'scan_path'       => 'required|string',
            'scan_depth'      => 'nullable|integer|min:1|max:10',
            'scan_extensions' => 'nullable|array',
        ]);

        $baseUploadPath = storage_path('app/public/files/2');
        $subPath        = trim($request->scan_path);

        // Resolve prefix → nama folder lengkap
        // Contoh: "assist-bpr.net" → "assist-bpr.net_20260702_103416"
        if ($subPath !== '.' && $subPath !== '') {
            $resolved = $this->resolveFolderByPrefix($baseUploadPath, $subPath);
            if ($resolved === null) {
                Alert::error('Error', "Folder dengan prefix '{$subPath}' tidak ditemukan di files/2/");
                return redirect()->back();
            }
            $scanPath = $resolved;
        } else {
            $scanPath = $baseUploadPath;
        }

        $scanDepth      = (int) ($request->scan_depth ?? 10);
        // Scan semua tipe file (bukan hanya php) karena ini adalah file upload
        $extensions     = $request->scan_extensions ?? [];

        if (!File::exists($scanPath)) {
            Alert::error('Error', 'Folder tidak ditemukan: ' . $scanPath);
            return redirect()->back();
        }

        $scannedFiles = 0;
        $threatsFound = 0;

        try {
            $files = $this->getFilesRecursive($scanPath, $scanDepth, $extensions);

            // Hapus semua log lama yang berada di dalam folder yang di-scan
            // agar hasil scan selalu segar (tidak tercampur data scan sebelumnya)
            $relBase = $this->makeRelativePath($scanPath);
            $deleted = FileScanner::where('file_path', 'LIKE', $relBase . '%')
                ->whereNot('is_quarantined', true)   // jangan hapus yang sudah dikarantina
                ->delete();
            Log::info("Scan ulang: {$deleted} record lama dihapus untuk path: {$relBase}");

            foreach ($files as $file) {
                $result = $this->scanFile($file);
                if ($result) {
                    $scannedFiles++;
                    if (in_array($result['threat_level'], ['critical', 'high', 'medium'])) {
                        $threatsFound++;
                    }
                }
            }

            log_custom("Scan file selesai: {$scannedFiles} files, {$threatsFound} threats");
            Alert::success('Scan Selesai', "{$scannedFiles} file dipindai, {$threatsFound} ancaman ditemukan.");
        } catch (\Exception $e) {
            Log::error('Scanner error: ' . $e->getMessage());
            Alert::error('Error', 'Terjadi kesalahan: ' . $e->getMessage());
        }

        return redirect()->route('filescanner.index');
    }

    public function show(int $id)
    {
        abort_if(Gate::denies('filescanner_read'), 403);

        $data         = Template::get("datatable");
        $scan         = FileScanner::with('user')->findOrFail($id);
        $data['scan'] = $scan;

        // Resolve path: bisa relatif terhadap storage_path() atau absolute
        $filePath = $this->resolveFilePath($scan->file_path);
        if ($filePath && File::exists($filePath)) {
            // Batasi ukuran baca agar tidak timeout (maks 512KB)
            $size = File::size($filePath);
            if ($size <= 524288) {
                $data['file_content'] = File::get($filePath);
            } else {
                $data['file_content'] = '(File terlalu besar untuk ditampilkan: ' . number_format($size / 1024, 1) . ' KB)';
            }
        } else {
            $data['file_content'] = 'File tidak ditemukan atau sudah dipindahkan.';
        }

        $data['pattern_groups'] = $this->groupPatternsByCategory($scan->suspicious_patterns ?? []);

        return view('backend.filescanner.show', $data);
    }


    public function quarantine(int $id)
    {
        abort_if(Gate::denies('filescanner_write'), 403);

        $scan     = FileScanner::findOrFail($id);
        $filePath = $this->resolveFilePath($scan->file_path);

        if (!$filePath || !File::exists($filePath)) {
            Alert::error('Error', 'File tidak ditemukan');
            return redirect()->back();
        }

        try {
            $quarantinePath = storage_path('app/quarantine');
            if (!File::exists($quarantinePath)) {
                File::makeDirectory($quarantinePath, 0755, true);
            }

            $quarantineFile = $quarantinePath . '/' . $scan->file_hash . '_' . basename($scan->file_path);
            File::move($filePath, $quarantineFile);

            $scan->update([
                'is_quarantined' => true,
                'file_path'      => 'quarantine/' . basename($quarantineFile),
            ]);

            log_custom("File dikarantina: " . $scan->file_name);
            Alert::success('Berhasil', 'File berhasil dikarantina');
        } catch (\Exception $e) {
            Alert::error('Error', 'Gagal mengkarantina: ' . $e->getMessage());
        }

        return redirect()->back();
    }

    public function restore(int $id)
    {
        abort_if(Gate::denies('filescanner_write'), 403);

        $scan = FileScanner::findOrFail($id);

        if (!$scan->is_quarantined) {
            Alert::warning('Peringatan', 'File tidak dalam karantina');
            return redirect()->back();
        }

        try {
            $quarantineFile = storage_path('app/' . $scan->file_path);

            if (!File::exists($quarantineFile)) {
                Alert::error('Error', 'File karantina tidak ditemukan');
                return redirect()->back();
            }

            $restorePath = storage_path('app/restored/' . basename($scan->file_name));
            File::move($quarantineFile, $restorePath);

            $scan->update([
                'is_quarantined' => false,
                'file_path'      => 'restored/' . basename($scan->file_name),
            ]);

            log_custom("File dipulihkan dari karantina: " . $scan->file_name);
            Alert::success('Berhasil', 'File dipulihkan ke folder restored');
        } catch (\Exception $e) {
            Alert::error('Error', 'Gagal memulihkan file: ' . $e->getMessage());
        }

        return redirect()->back();
    }

    public function destroy(int $id)
    {
        abort_if(Gate::denies('filescanner_delete'), 403);

        $scan = FileScanner::findOrFail($id);
        $scan->delete();

        log_custom("Hapus log scan file: " . $scan->file_name);
        return response()->json('ok');
    }

    public function clearAll()
    {
        abort_if(Gate::denies('filescanner_delete'), 403);

        // Hanya hapus yang tidak dalam karantina — data karantina tetap dijaga
        $deleted = FileScanner::whereNot('is_quarantined', true)->delete();

        log_custom("Clear all scan logs: {$deleted} record dihapus");
        return response()->json([
            'status'  => 'ok',
            'deleted' => $deleted,
            'message' => "{$deleted} data hasil scan berhasil dihapus.",
        ]);
    }


    // =========================================================================
    // CORE SCANNER ENGINE
    // =========================================================================

    // =========================================================================
    // WHITELIST CHECK
    // =========================================================================

    /**
     * Cek apakah konten file adalah guard/placeholder yang sah dari framework,
     * sehingga tidak perlu di-scan lebih lanjut.
     *
     * Contoh file yang dikecualikan:
     *   <?php defined( 'main' ) or die( 'Restricted access' ) ?>
     *   <?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
     *   <?php if ( ! defined( 'ABSPATH' ) ) { die; } ?>
     */
    private function isWhitelisted(string $content): bool
    {
        // Trim BOM dan whitespace luar
        $trimmed = trim($content, "\xEF\xBB\xBF \t\n\r\0\x0B");

        // Whitelist hanya berlaku untuk file sangat pendek (<= 300 karakter)
        if (strlen($trimmed) > 300) {
            return false;
        }

        // Pattern guard framework yang sah.
        // Menggunakan strpos untuk menghindari masalah quoting di regex.
        // Cukup deteksi: file hanya berisi satu baris "defined(...) or die/exit"
        // tanpa kode lain di belakangnya.

        // Hapus tag PHP pembuka dan penutup, trim sisa
        $inner = preg_replace('/^<\?php\s*/i', '', $trimmed);
        $inner = rtrim($inner, " \t\n\r?>");
        $inner = trim($inner);

        // File benar-benar kosong setelah tag PHP
        if ($inner === '') {
            return true;
        }

        // Harus hanya satu statement: defined(...) or die/exit(...)
        // Tidak boleh ada baris lain / kode tambahan
        if (substr_count($trimmed, "\n") > 2) {
            return false;
        }

        // Cocokkan pola: defined('APAPUN') or die/exit(...)
        if (preg_match("/^defined\s*\(\s*[\'\"][A-Za-z_][A-Za-z0-9_]*[\'\"]\s*\)\s+(?:or|OR)\s+(?:die|exit)\s*(?:\([^)]{0,100}\))?\s*;?$/i", $inner)) {
            return true;
        }

        // Cocokkan pola: if (!defined('APAPUN')) { die; }
        if (preg_match("/^if\s*\(\s*!\s*defined\s*\(\s*[\'\"][A-Za-z_][A-Za-z0-9_]*[\'\"]\s*\)\s*\)\s*\{?\s*(?:die|exit)\s*(?:\([^)]{0,100}\))?\s*;?\s*\}?$/i", $inner)) {
            return true;
        }

        // Hanya komentar PHP
        if (preg_match('/^\/\/[^\n]*$/i', $inner)) {
            return true;
        }

        return false;
    }

        private function scanFile(string $filePath): ?array
    {
        try {
            $content  = File::get($filePath);
            $fileSize = File::size($filePath);
            $fileHash = hash_file('sha256', $filePath);
            $fileName = basename($filePath);
            $ext      = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

            // ---- Whitelist Check ----
            // File yang hanya berisi guard statement (mis. defined('main') or die(...))
            // adalah file placeholder framework yang sah — skip scan langsung.
            if ($this->isWhitelisted($content)) {
                Log::info("Whitelisted (guard file): {$filePath}");
                return FileScanner::create([
                    'file_path'           => $this->makeRelativePath($filePath),
                    'file_name'           => $fileName,
                    'file_size'           => $fileSize,
                    'file_hash'           => $fileHash,
                    'threat_level'        => 'safe',
                    'threat_type'         => 'Bersih (Guard File — Whitelisted)',
                    'suspicious_patterns' => [],
                    'scan_result'         => 'clean',
                    'scanned_by'          => auth()->id(),
                    'scanned_at'          => now(),
                    'is_quarantined'      => false,
                ])->toArray();
            }

            $detections = [];
            $score      = 0;

            // ---- 1. Signature Scanner ----
            foreach ($this->signaturePatterns as $pattern => $label) {
                if (@preg_match('/' . $pattern . '/is', $content)) {
                    $detections[] = $label;
                    $score += 8;
                }
            }

            // ---- 2. Dangerous Combinations ----
            foreach ($this->dangerousCombinations as $pattern => $label) {
                if (@preg_match('/' . $pattern . '/is', $content)) {
                    $detections[] = $label;
                    $score += 40; // Dinaikkan dari 20 → 40: kombinasi ini hampir pasti backdoor
                }
            }

            // ---- 3. Superglobal Input ----
            foreach ($this->superglobalPatterns as $pattern => $label) {
                if (@preg_match('/' . $pattern . '/is', $content)) {
                    $detections[] = $label;
                    $score += 5;
                }
            }

            // ---- 4. Obfuscation ----
            foreach ($this->obfuscationPatterns as $pattern => $label) {
                if (@preg_match('/' . $pattern . '/is', $content)) {
                    $detections[] = $label;
                    $score += 10;
                }
            }

            // ---- 5. Encoded String ----
            // Base64 panjang tanpa spasi (>500 char)
            if (preg_match_all('/[A-Za-z0-9+\/]{500,}={0,2}/', $content, $matches)) {
                $detections[] = '[Enc] Long Base64 String (' . count($matches[0]) . 'x)';
                $score += count($matches[0]) * 15;
            }
            // Base64 medium dengan whitespace di tengah (≥200 char setelah strip spasi) — teknik evasion umum
            if (preg_match_all('/[A-Za-z0-9+\/\s]{300,}={0,2}/', $content, $matchesWs)) {
                foreach ($matchesWs[0] as $candidate) {
                    $stripped = preg_replace('/\s+/', '', $candidate);
                    if (strlen($stripped) >= 200 && base64_decode($stripped, true) !== false) {
                        $detections[] = '[Enc] Obfuscated Base64 with Whitespace (len=' . strlen($stripped) . ')';
                        $score += 25;
                        break;
                    }
                }
            }
            // Base64 di dalam string PHP literal yang di-eval
            if (@preg_match('/eval\s*\(\s*base64_decode\s*\(\s*[\'"][A-Za-z0-9+\/\s]{50,}={0,2}[\'"]/', $content)) {
                $detections[] = '[Enc] eval(base64_decode) with embedded payload';
                $score += 50; // Langsung +50: ini signature backdoor klasik
            }

            // ---- 6. Hex String ----
            if (@preg_match('/\\\\x[0-9a-fA-F]{2}|0x[0-9a-fA-F]{4,}/i', $content)) {
                $detections[] = '[Hex] Hex Encoded Payload';
                $score += 12;
            }

            // ---- 7. Very Long Line (> 1000 karakter) ----
            $lines = explode("\n", $content);
            $longLineCount = 0;
            foreach ($lines as $line) {
                if (strlen($line) > 1000) {
                    $longLineCount++;
                }
            }
            if ($longLineCount > 0) {
                $detections[] = '[LongLine] Very Long Line (' . $longLineCount . ' baris)';
                $score += $longLineCount * 10;
            }

            // ---- 8. Suspicious Variable ----
            foreach ($this->suspiciousVariablePatterns as $pattern => $label) {
                if (@preg_match('/' . $pattern . '/is', $content)) {
                    $detections[] = $label;
                    $score += 8;
                }
            }

            // ---- 9. Dynamic Function Call ----
            foreach ($this->dynamicCallPatterns as $pattern => $label) {
                if (@preg_match('/' . $pattern . '/is', $content)) {
                    $detections[] = $label;
                    $score += 10;
                }
            }

            // ---- 10 & 11. Dynamic Include / Remote Include ----
            foreach ($this->includePatterns as $pattern => $label) {
                if (@preg_match('/' . $pattern . '/is', $content)) {
                    $detections[] = $label;
                    $score += 15;
                }
            }

            // ---- 12. Hidden Upload ----
            foreach ($this->uploadPatterns as $pattern => $label) {
                if (@preg_match('/' . $pattern . '/is', $content)) {
                    $detections[] = $label;
                    $score += 7;
                }
            }

            // ---- 13. Image Shell (ekstensi ganda) ----
            if (preg_match('/\.(jpg|jpeg|png|gif|bmp|webp)\.(php\d?|phtml|phar)/i', $fileName)) {
                $detections[] = '[ImgShell] Double Extension: ' . $fileName;
                $score += 30;
            }
            if (in_array($ext, $this->suspiciousExtensions)) {
                $detections[] = '[ImgShell] Suspicious Extension: .' . $ext;
                $score += 20;
            }

            // ---- 14. Fake Image (tag PHP di file gambar) ----
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'bmp'])) {
                if (@preg_match('/<\?php/i', $content)) {
                    $detections[] = '[FakeImg] PHP Tag in Image File';
                    $score += 40;
                }
            }

            // ---- 15. Suspicious Filename ----
            $fileNameLower = strtolower(pathinfo($fileName, PATHINFO_FILENAME));
            foreach ($this->suspiciousFilenames as $sus) {
                if (str_contains($fileNameLower, $sus)) {
                    $detections[] = '[SusFile] Nama file mencurigakan: ' . $fileName;
                    $score += 25;
                    break;
                }
            }

            // ---- 15b. Random / Obfuscated Filename ----
            $nameOnly = pathinfo($fileName, PATHINFO_FILENAME);
            // Nama file terlalu pendek (1-2 karakter) dan berekstensi php
            if (strlen($nameOnly) <= 2 && in_array($ext, ['php', 'phtml', 'phar'])) {
                $detections[] = '[SusFile] Nama file sangat pendek: ' . $fileName;
                $score += 20;
            }
            // Nama file terlihat random: semua hex / string alfanumerik panjang tanpa makna
            if (strlen($nameOnly) >= 8 && preg_match('/^[a-f0-9]{8,}$/i', $nameOnly)) {
                $detections[] = '[SusFile] Nama file tampak hash/random: ' . $fileName;
                $score += 15;
            }
            // Nama file angka semua
            if (preg_match('/^\d{4,}$/', $nameOnly)) {
                $detections[] = '[SusFile] Nama file berupa angka: ' . $fileName;
                $score += 10;
            }
            // Nama file dengan karakter tidak lazim (bukan huruf/angka/strip/titik)
            if (preg_match('/[^a-zA-Z0-9\-_.()]/', $nameOnly)) {
                $detections[] = '[SusFile] Nama file mengandung karakter tidak lazim: ' . $fileName;
                $score += 10;
            }
            // Ekstensi ganda (file.php.jpg, file.jpg.php)
            if (substr_count($fileName, '.') >= 2) {
                // Cek apakah ada ekstensi php/phtml tersembunyi
                if (
                    preg_match('/\.(php\d?|phtml|phar)\./i', $fileName) ||
                    preg_match('/\.(jpg|jpeg|png|gif|bmp|webp)\.(php\d?|phtml|phar)$/i', $fileName)
                ) {
                    $detections[] = '[SusFile] Ekstensi ganda berbahaya: ' . $fileName;
                    $score += 35;
                }
            }

            // ---- 15c. PHP Tersembunyi di File Non-PHP ----
            // Mendeteksi file seperti "gelay", "dua", "index" (tanpa ekstensi)
            // atau ekstensi tidak umum yang isinya PHP
            $phpExtensions = ['php', 'php3', 'php4', 'php5', 'php7', 'phtml', 'phar', 'phps'];
            if (!in_array($ext, $phpExtensions) && @preg_match('/<\?php|<\?=/i', $content)) {
                if ($ext === '') {
                    $detections[] = '[SusFile] File TANPA ekstensi berisi kode PHP: ' . $fileName;
                    $score += 50; // Sangat mencurigakan — tidak ada alasan sah untuk ini
                } else {
                    $detections[] = '[SusFile] Ekstensi .' . $ext . ' berisi kode PHP: ' . $fileName;
                    $score += 35;
                }
            }

            // ---- 15d. File Manager / Web Shell Fungsional ----
            // Mendeteksi file yang mengandung fungsi file manager lengkap dalam satu file
            // seperti "gelay" (FilesMan) dan "dua" dari screenshot
            $fmIndicators = 0;
            $fmChecks = [
                'scandir\s*\('           => 'scandir',
                'rename\s*\('            => 'rename',
                'unlink\s*\('            => 'unlink',
                'mkdir\s*\('             => 'mkdir',
                'rmdir\s*\('             => 'rmdir',
                'file_put_contents\s*\(' => 'file_put_contents',
                'move_uploaded_file\s*\(' => 'move_uploaded_file',
                'chmod\s*\('             => 'chmod',
                '\$_FILES\s*\['          => '$_FILES',
                'fileperms\s*\('         => 'fileperms',
            ];
            foreach ($fmChecks as $p => $_) {
                if (@preg_match('/' . $p . '/i', $content)) $fmIndicators++;
            }
            if ($fmIndicators >= 4) {
                $detections[] = '[FM] File Manager / Shell fungsional terdeteksi (' . $fmIndicators . ' fungsi FM)';
                $score += $fmIndicators * 12; // 4 fungsi = +48, 8 fungsi = +96
            }

            // ---- 16. Permission Scanner ----
            foreach ($this->permissionPatterns as $pattern => $label) {
                if (@preg_match('/' . $pattern . '/is', $content)) {
                    $detections[] = $label;
                    $score += 12;
                }
            }

            // ---- 17. Recently Modified (dalam 7 hari) ----
            $mtime = filemtime($filePath);
            if ($mtime && (time() - $mtime) < 604800) {
                $detections[] = '[Recent] Modified < 7 hari: ' . date('d/m/Y H:i', $mtime);
                $score += 5;
            }

            // ---- 18. Integrity Check ----
            // (Dinonaktifkan saat scan ulang karena data lama dihapus sebelum scan dimulai.
            //  Hash saat ini disimpan sebagai baseline untuk scan berikutnya.)
            $relPath = $this->makeRelativePath($filePath);

            // ---- 19. Entropy Scanner ----
            $entropy = $this->calculateEntropy($content);
            if ($entropy > 5.5) {
                $detections[] = sprintf('[Entropy] Entropy tinggi: %.2f (threshold 5.5)', $entropy);
                $score += (int)(($entropy - 5.5) * 20);
            }

            // ---- 20. YARA-style Rules ----
            if (@preg_match('/\$\w+\s*=\s*str_rot13\s*\(.*\);\s*\$\w+\s*=\s*base64_decode\s*\(/is', $content)) {
                $detections[] = '[YARA] Multi-layer obfuscation chain';
                $score += 30;
            }
            if (@preg_match('/error_reporting\s*\(\s*0\s*\).*@?set_time_limit/is', $content)) {
                $detections[] = '[YARA] Shell initialization pattern';
                $score += 25;
            }

            // ---- 20b. Decode Base64 literal dan scan isinya ----
            // Banyak backdoor menyembunyikan payload di base64_decode('...') langsung di file
            if (preg_match_all('/base64_decode\s*\(\s*[\'"]([A-Za-z0-9+\/\s=]{40,})[\'"]\s*\)/is', $content, $b64Matches)) {
                foreach ($b64Matches[1] as $b64Raw) {
                    $decoded = @base64_decode(preg_replace('/\s+/', '', $b64Raw));
                    if ($decoded === false || strlen($decoded) < 10) continue;

                    // Cek IOC di dalam decoded payload
                    $decodedChecks = [
                        'api\.telegram\.org'           => '[IOC-Decoded] Telegram C2',
                        'sendMessage'                  => '[IOC-Decoded] Telegram sendMessage',
                        'REMOTE_ADDR'                  => '[IOC-Decoded] IP Exfiltration',
                        'file_get_contents.*https?://' => '[IOC-Decoded] Remote Callback',
                        'curl_exec'                    => '[IOC-Decoded] cURL Callback',
                        'shell_exec|system|exec\s*\('  => '[IOC-Decoded] Exec in Payload',
                        'discord\.com/api'             => '[IOC-Decoded] Discord Webhook',
                        'pastebin\.com'                => '[IOC-Decoded] Pastebin C2',
                    ];
                    foreach ($decodedChecks as $p => $l) {
                        if (@preg_match('/' . $p . '/is', $decoded)) {
                            $detections[] = $l;
                            $score += 40; // Payload tersembunyi = sangat berbahaya
                        }
                    }
                }
            }

            // ---- 21. IOC Scanner ----
            foreach ($this->iocPatterns as $pattern => $label) {
                if (@preg_match('/' . $pattern . '/is', $content)) {
                    $detections[] = $label;
                    $score += 35;
                }
            }

            // ---- 21b. Hardcoded bcrypt Hash (literal string match) ----
            // Regex di $iocPatterns menggunakan $ yang ambigu dalam PHP string,
            // jadi kita deteksi langsung di sini dengan strpos/preg_match raw.
            // Pattern: $2y$10$..., $2y$12$..., $2a$10$... (bcrypt hash 60 karakter)
            if (
                preg_match('/\\\$2[ayb]\\\$\d{2}\\\$[A-Za-z0-9\.\/]{53}/s', $content)
                || strpos($content, '$2y$') !== false
                || strpos($content, '$2a$') !== false
                || strpos($content, '$2b$') !== false
            ) {
                // Hanya flag jika bcrypt muncul di luar konteks Laravel/framework normal
                // (yaitu bukan di dalam vendor/ atau file config resmi)
                if (
                    !str_contains($filePath, '/vendor/')
                    && !str_contains($filePath, 'config/')
                    && preg_match('/\$2[ayb]\$\d{2}\$[A-Za-z0-9\.\/]{53}/', $content)
                ) {
                    $detections[] = '[IOC] Hardcoded bcrypt Hash ditemukan (kemungkinan kredensial backdoor)';
                    $score += 40;
                }
            }

            // ---- 21c. ADMIN_PASSWORD_HASH / ADMIN_PASSWORD_LEGACY (literal) ----
            if (stripos($content, 'ADMIN_PASSWORD_HASH') !== false) {
                $detections[] = '[IOC] Konstanta ADMIN_PASSWORD_HASH ditemukan';
                $score += 35;
            }
            if (stripos($content, 'ADMIN_PASSWORD_LEGACY') !== false) {
                $detections[] = '[IOC] Konstanta ADMIN_PASSWORD_LEGACY ditemukan';
                $score += 35;
            }

            // ---- 21d. fm_secure_session (literal) ----
            if (stripos($content, 'fm_secure_session') !== false) {
                $detections[] = '[IOC] fm_secure_session — File Manager Auth Pattern';
                $score += 35;
            }

            // ---- 21e. NU AING BRO (literal, case-insensitive) ----
            if (
                stripos($content, 'NU AING BRO') !== false
                || stripos($content, 'NUAINGBRO') !== false
            ) {
                $detections[] = '[IOC] NU AING BRO — Indonesian Backdoor Graffiti Tag';
                $score += 50; // Ini tanda tangan spesifik, langsung +50
            }

            // ---- 21f. Jakub Vrana / Adminer (literal) ----
            if (stripos($content, 'Jakub Vrana') !== false) {
                $detections[] = '[IOC] Jakub Vrana (Adminer Author Signature)';
                $score += 30;
            }
            if (stripos($content, 'Adminer - Compact database management') !== false) {
                $detections[] = '[IOC] Adminer — Compact Database Management Tool';
                $score += 30;
            }

            // ---- 21g. SECURE PHP FILE MANAGER / SMokWSO (literal) ----
            if (stripos($content, 'SECURE PHP FILE MANAGER') !== false) {
                $detections[] = '[IOC] SECURE PHP FILE MANAGER — File Manager Shell';
                $score += 50;
            }
            if (
                stripos($content, 'SMokWSO') !== false
                || stripos($content, 'Smok WSO') !== false
            ) {
                $detections[] = '[IOC] SMokWSO — WSO Shell Variant';
                $score += 50;
            }

            // ---- 21h. GitHub reference (literal) ----
            // File PHP yang mengandung referensi raw.githubusercontent atau GitHub repo
            // sering digunakan untuk mengunduh payload tambahan atau C2 staging
            if (stripos($content, 'raw.githubusercontent.com') !== false) {
                $detections[] = '[IOC] GitHub Raw Content — kemungkinan unduh payload dari GitHub';
                $score += 45;
            }
            if (preg_match('/github\.com\/[a-zA-Z0-9_\-]+\/[a-zA-Z0-9_\-]+/i', $content)) {
                $detections[] = '[IOC] GitHub Repository Reference ditemukan di file PHP';
                $score += 25;
            }
            if (
                stripos($content, 'GITHUB_TOKEN') !== false
                || stripos($content, 'github_token') !== false
                || stripos($content, 'gh_token') !== false
            ) {
                $detections[] = '[IOC] GitHub Token/Credential Hardcoded';
                $score += 40;
            }

            // ---- 21i. YP Tag (literal, case-sensitive untuk hindari false positive) ----
            if (
                strpos($content, 'YP Shell') !== false
                || strpos($content, 'ypshell') !== false
                || strpos($content, 'yp_shell') !== false
                || preg_match('/\[\s*YP\s*\]|\bYP\s+backdoor\b/i', $content)
            ) {
                $detections[] = '[IOC] YP Tag — Attacker Signature Indonesia';
                $score += 50;
            }

            // ---- 21j. Bule Tag (literal) ----
            if (
                stripos($content, 'bule_shell') !== false
                || stripos($content, 'buleshell') !== false
                || preg_match('/\bbule\s+shell\b|\bbule\s+backdoor\b/i', $content)
            ) {
                $detections[] = '[IOC] Bule Tag — Indonesian Backdoor Marker';
                $score += 50;
            }

            // ---- 21k. Hardcoded Token (literal) ----
            // Bot token Telegram: format numerik:string (contoh: 123456789:AAHxxx...)
            if (preg_match('/\d{8,10}:[A-Za-z0-9_\-]{35,}/s', $content)) {
                $detections[] = '[IOC] Telegram Bot Token Hardcoded';
                $score += 50;
            }
            // Bearer / API token hardcoded
            if (
                stripos($content, 'BOT_TOKEN') !== false
                || stripos($content, 'bot_token') !== false
                || stripos($content, 'API_TOKEN') !== false
                || stripos($content, 'api_token') !== false
            ) {
                $detections[] = '[IOC] API/Bot Token Constant Hardcoded';
                $score += 35;
            }
            // Bearer token in Authorization header
            if (preg_match('/Authorization[\'"\s:]+Bearer\s+[A-Za-z0-9_\-\.]{20,}/i', $content)) {
                $detections[] = '[IOC] Hardcoded Bearer Token dalam Authorization header';
                $score += 40;
            }

            // ---- 23. Folder Context Check ----
            // Cek apakah file berada dalam folder yang prefixnya mencurigakan berdasarkan konteks
            $this->checkFolderContext($filePath, $detections, $score);

            // ---- 22. Malware Scoring ----
            $threatLevel = $this->scoreToLevel($score);
            $threatType  = $this->scoreToType($score, $detections);

            Log::info("Scanned: {$filePath} | Score: {$score} | Level: {$threatLevel} | Detections: " . count($detections));

            $data = [
                'file_path'           => $relPath,
                'file_name'           => $fileName,
                'file_size'           => $fileSize,
                'file_hash'           => $fileHash,
                'threat_level'        => $threatLevel,
                'threat_type'         => $threatType,
                'suspicious_patterns' => $detections,
                'scan_result'         => count($detections) > 0 ? 'threat_detected' : 'clean',
                'scanned_by'          => auth()->id(),
                'scanned_at'          => now(),
            ];

            // Data lama sudah dihapus di scan(), langsung insert baru
            return FileScanner::create(array_merge($data, ['is_quarantined' => false]))->toArray();
        } catch (\Exception $e) {
            Log::error('File scan error: ' . $e->getMessage() . ' | File: ' . $filePath);
            return null;
        }
    }


    // =========================================================================
    // HELPER METHODS
    // =========================================================================

    /**
     * Kategori risiko berdasarkan skor (sesuai panduan):
     * 0–20   : Aman
     * 21–50  : Perlu ditinjau
     * 51–100 : Mencurigakan
     * >100   : Sangat berbahaya
     */
    private function scoreToLevel(int $score): string
    {
        if ($score > 100) return 'critical';
        if ($score >= 51) return 'high';
        if ($score >= 21) return 'medium';
        if ($score >= 1)  return 'low';
        return 'safe';
    }

    private function scoreToType(int $score, array $detections): string
    {
        if ($score > 100) return 'Sangat Berbahaya (Skor: ' . $score . ') - ' . count($detections) . ' deteksi';
        if ($score >= 51) return 'Mencurigakan (Skor: ' . $score . ') - ' . count($detections) . ' deteksi';
        if ($score >= 21) return 'Perlu Ditinjau (Skor: ' . $score . ') - ' . count($detections) . ' deteksi';
        if ($score >= 1)  return 'Aman dengan Catatan (Skor: ' . $score . ')';
        return 'Bersih';
    }

    /**
     * Hitung Shannon Entropy konten file
     */
    private function calculateEntropy(string $content): float
    {
        if (strlen($content) === 0) return 0.0;

        $frequencies = array_count_values(str_split($content));
        $length      = strlen($content);
        $entropy     = 0.0;

        foreach ($frequencies as $count) {
            $p        = $count / $length;
            $entropy -= $p * log($p, 2);
        }

        return $entropy;
    }

    /**
     * Kelompokkan deteksi berdasarkan prefix kategori
     */
    private function groupPatternsByCategory(array $patterns): array
    {
        $groups = [];
        foreach ($patterns as $pattern) {
            if (preg_match('/^\[([^\]]+)\]/', $pattern, $m)) {
                $groups[$m[1]][] = $pattern;
            } else {
                $groups['Other'][] = $pattern;
            }
        }
        return $groups;
    }

    /**
     * Ambil file secara rekursif sesuai kedalaman.
     * $extensions kosong = scan SEMUA tipe file (untuk folder upload).
     * File tanpa ekstensi / ekstensi tidak dikenal SELALU disertakan jika
     * beberapa byte pertamanya mengandung tag PHP (<?php / <?).
     */
    private function getFilesRecursive(
        string $path,
        int $maxDepth,
        array $extensions = [],
        int $currentDepth = 0
    ): array {
        $files = [];

        if ($currentDepth >= $maxDepth) {
            return $files;
        }

        // Ekstensi yang aman untuk di-skip saat sniff PHP header
        $skipSniffExt = [
            'zip',
            'rar',
            'gz',
            'tar',
            'bz2',
            'jpg',
            'jpeg',
            'png',
            'gif',
            'bmp',
            'webp',
            'svg',
            'ico',
            'pdf',
            'doc',
            'docx',
            'xls',
            'xlsx',
            'ppt',
            'pptx',
            'mp3',
            'mp4',
            'avi',
            'mov',
            'woff',
            'woff2',
            'ttf'
        ];

        try {
            $items = File::glob($path . '/*');

            foreach ($items as $item) {
                if (File::isFile($item)) {
                    $ext = strtolower(pathinfo($item, PATHINFO_EXTENSION));

                    if (empty($extensions)) {
                        // Scan semua ekstensi PLUS file tanpa/asing ekstensi yang isinya PHP
                        $files[] = $item;
                    } else {
                        // Filter ekstensi dipilih user
                        if (in_array($ext, $extensions)) {
                            $files[] = $item;
                        } elseif (!in_array($ext, $skipSniffExt) && $this->hasPHPHeader($item)) {
                            // File tidak masuk filter ekstensi tapi isinya PHP — tetap scan
                            $files[] = $item;
                        }
                    }
                } elseif (File::isDirectory($item)) {
                    $files = array_merge(
                        $files,
                        $this->getFilesRecursive($item, $maxDepth, $extensions, $currentDepth + 1)
                    );
                }
            }
        } catch (\Exception $e) {
            Log::error('Directory scan error: ' . $e->getMessage());
        }

        return array_unique($files);
    }

    /**
     * Cek apakah file dimulai dengan tag PHP (baca 100 byte pertama saja).
     * Digunakan untuk mendeteksi webshell yang disimpan tanpa ekstensi .php.
     */
    private function hasPHPHeader(string $filePath): bool
    {
        try {
            $handle = @fopen($filePath, 'r');
            if (!$handle) return false;
            $header = fread($handle, 100);
            fclose($handle);
            return (bool) preg_match('/<\?php|<\?=/i', $header);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Resolve prefix folder → path lengkap.
     * Urutan prioritas:
     *  1. Cocok persis dengan nama folder
     *  2. Nama folder diawali dengan prefix (case-insensitive)
     * Jika ada lebih dari satu match → ambil yang paling baru (sort desc by name).
     */
    private function resolveFolderByPrefix(string $basePath, string $prefix): ?string
    {
        $prefix = ltrim($prefix, '/');

        // 1. Cocok persis
        $exact = $basePath . '/' . $prefix;
        if (File::isDirectory($exact)) {
            return $exact;
        }

        // 2. Prefix match — cari semua folder yang namanya diawali prefix
        $matches = [];
        try {
            $items = File::glob($basePath . '/*');
            foreach ($items as $item) {
                if (File::isDirectory($item)) {
                    $name = basename($item);
                    if (stripos($name, $prefix) === 0) {
                        $matches[] = $item;
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('resolveFolderByPrefix error: ' . $e->getMessage());
        }

        if (empty($matches)) return null;

        // Ambil yang paling baru (nama folder umumnya mengandung timestamp)
        rsort($matches);
        return $matches[0];
    }

    /**
     * Ambil daftar folder di files/2 beserta nama lengkap dan prefix-nya.
     * Digunakan untuk datalist di form scan.
     * Return: [['name' => 'assist-bpr.net_20260702_103416', 'prefix' => 'assist-bpr.net'], ...]
     */
    public function getFolders(): \Illuminate\Http\JsonResponse
    {
        $basePath = storage_path('app/public/files/2');
        $folders  = [];

        try {
            $items = File::glob($basePath . '/*');
            foreach ($items as $item) {
                if (File::isDirectory($item)) {
                    $name   = basename($item);
                    // Ambil prefix: bagian sebelum pola _YYYYMMDD_ atau _ diikuti angka 8 digit
                    $prefix = preg_replace('/_\d{8}_\d+$/', '', $name);
                    $folders[] = [
                        'name'    => $name,
                        'prefix'  => $prefix,
                        'display' => $prefix . ' (' . $name . ')',
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::error('getFolders error: ' . $e->getMessage());
        }

        // Sort by name desc (terbaru di atas)
        usort($folders, fn($a, $b) => strcmp($b['name'], $a['name']));

        return response()->json($folders);
    }
    /* Contoh: /var/www/storage/app/public/files/2/evil.php → app/public/files/2/evil.php
     */
    private function makeRelativePath(string $absolutePath): string
    {
        $base = storage_path() . DIRECTORY_SEPARATOR;
        if (str_starts_with($absolutePath, $base)) {
            return ltrim(substr($absolutePath, strlen($base)), DIRECTORY_SEPARATOR);
        }
        // Fallback: simpan apa adanya
        return $absolutePath;
    }

    /**
     * Resolve path tersimpan di DB kembali ke absolute path.
     * Mendukung: path relatif (storage), absolute path, path quarantine/restored.
     */
    private function resolveFilePath(string $storedPath): ?string
    {
        // Sudah absolute
        if (str_starts_with($storedPath, '/')) {
            return $storedPath;
        }
        // Relatif terhadap storage_path()
        $candidate = storage_path($storedPath);
        if (File::exists($candidate)) {
            return $candidate;
        }
        // Fallback: coba base_path() (legacy)
        $legacy = base_path($storedPath);
        if (File::exists($legacy)) {
            return $legacy;
        }
        return null;
    }

    // =========================================================================
    // FOLDER CONTEXT CHECK
    // =========================================================================

    /**
     * Periksa apakah file berada di dalam folder dengan konteks mencurigakan:
     *
     * A. Folder prefix "chat" → tidak boleh ada file .php sama sekali.
     *    Folder chat hanya seharusnya berisi teks/gambar/audio.
     *    Kehadiran .php di sini = kemungkinan webshell yang diunggah melalui fitur chat.
     *
     * B. Folder prefix "foto" (atau "photo", "image", "img", "gallery", "galeri") →
     *    hanya boleh berisi file gambar (jpg, jpeg, png, gif, bmp, webp, svg, ico).
     *    File dengan ekstensi lain = mencurigakan (mis. .php, .exe, .sh tersembunyi di folder foto).
     *
     * Method ini memodifikasi $detections dan $score by reference.
     */
    private function checkFolderContext(string $filePath, array &$detections, int &$score): void
    {
        // Ambil semua segmen path untuk menemukan folder yang relevan
        $segments = explode(DIRECTORY_SEPARATOR, str_replace('/', DIRECTORY_SEPARATOR, $filePath));
        $ext      = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $fileName = basename($filePath);

        // Ekstensi gambar yang dianggap sah di folder foto
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg', 'ico', 'tiff', 'tif', 'avif', 'heic'];

        // Prefix folder chat (nama folder yang dimulai dengan kata ini, case-insensitive)
        $chatPrefixes = ['chat'];

        // Prefix folder foto/gambar
        $fotoPrefixes = ['foto', 'photo', 'photos', 'image', 'images', 'img', 'gallery', 'galeri', 'gambar', 'picture', 'pictures'];

        foreach ($segments as $i => $segment) {
            $segLower = strtolower($segment);

            // ---- A. Folder CHAT: file .php tidak boleh ada ----
            foreach ($chatPrefixes as $prefix) {
                if (str_starts_with($segLower, $prefix)) {
                    $phpExtensions = ['php', 'php3', 'php4', 'php5', 'php7', 'phtml', 'phar', 'phps'];
                    if (in_array($ext, $phpExtensions)) {
                        $detections[] = sprintf(
                            '[FolderCtx] File PHP ditemukan di folder CHAT (%s): %s — kemungkinan webshell yang diunggah via fitur chat',
                            $segment,
                            $fileName
                        );
                        $score += 60; // File PHP di folder chat sangat mencurigakan
                    }
                    // File tanpa ekstensi di folder chat yang mengandung PHP header
                    // sudah tertangkap di check 15c, tidak perlu duplikasi
                    break;
                }
            }

            // ---- B. Folder FOTO: file non-gambar tidak boleh ada ----
            foreach ($fotoPrefixes as $prefix) {
                if (str_starts_with($segLower, $prefix)) {
                    if (!in_array($ext, $imageExtensions)) {
                        // Tentukan severity berdasarkan ekstensi
                        $phpExtensions = ['php', 'php3', 'php4', 'php5', 'php7', 'phtml', 'phar', 'phps'];
                        $execExtensions = ['sh', 'bash', 'py', 'pl', 'rb', 'exe', 'bat', 'cmd', 'ps1'];

                        if (in_array($ext, $phpExtensions)) {
                            $detections[] = sprintf(
                                '[FolderCtx] File PHP ditemukan di folder FOTO (%s): %s — bukan file gambar, kemungkinan webshell',
                                $segment,
                                $fileName
                            );
                            $score += 65; // PHP di folder foto = sangat mencurigakan
                        } elseif (in_array($ext, $execExtensions)) {
                            $detections[] = sprintf(
                                '[FolderCtx] File eksekusi (%s) ditemukan di folder FOTO (%s): %s',
                                $ext,
                                $segment,
                                $fileName
                            );
                            $score += 55;
                        } elseif ($ext === '') {
                            // File tanpa ekstensi di folder foto
                            $detections[] = sprintf(
                                '[FolderCtx] File TANPA ekstensi di folder FOTO (%s): %s — tidak wajar untuk folder gambar',
                                $segment,
                                $fileName
                            );
                            $score += 40;
                        } else {
                            // Ekstensi lain (txt, html, js, zip, dll) di folder foto
                            $detections[] = sprintf(
                                '[FolderCtx] File .%s ditemukan di folder FOTO (%s): %s — bukan file gambar',
                                $ext,
                                $segment,
                                $fileName
                            );
                            $score += 20;
                        }
                    }
                    break;
                }
            }
        }
    }
}

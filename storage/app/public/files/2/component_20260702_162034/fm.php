<?php
/**
 * ========================================
 * SECURE PHP FILE MANAGER - COMPLETE
 * ========================================
 * Features:
 * - Password Protected Login
 * - File Upload (Drag & Drop + Multi-file)
 * - Interactive Terminal (Command Execution)
 * - Directory Navigation (Unlimited)
 * - Compress to ZIP with Real-time Progress (FIXED)
 * - Extract ZIP with Real-time Progress
 * - Bulk Operations (Select All, Compress, Delete)
 * - Download Files
 * - Create Folders
 * - Modern Responsive UI
 * ========================================
 */

// ========================================
// CONFIGURATION - UBAH PASSWORD DI SINI!
// ========================================

// Password hash untuk login (default password asli: admin123, WAJIB DIGANTI!)
// Generate hash baru dengan: password_hash('password_baru', PASSWORD_DEFAULT)
define('ADMIN_PASSWORD_HASH', '$2y$12$K3BNmqDgHUF7VocHodVuO.N14e1JYhBRESRq.6UPgURRnSfx7RM26'); // ⚠️ GANTI HASH INI!
define('ADMIN_PASSWORD_LEGACY', ''); // opsional fallback plain password, kosongkan agar lebih aman

// Session configuration
define('SESSION_NAME', 'fm_secure_session');
define('SESSION_TIMEOUT', 3600); // 1 hour in seconds

// File manager configuration
define('BASE_DIR', '__AUTO__'); // AUTO detect root/start path sesuai OS
define('MAX_FILE_SIZE', 500 * 1024 * 1024); // 500MB
define('MAX_UPLOAD_SIZE', 500 * 1024 * 1024); // 500MB per file
define('MAX_VIEW_FILE_SIZE', 2 * 1024 * 1024); // 2MB for view/edit
define('MEMORY_LIMIT', '512M');

// Upload configuration
define('ALLOWED_EXTENSIONS', array('*')); // array('jpg', 'png', 'pdf') atau array('*') untuk semua
define('UPLOAD_ENABLED', true);

// Terminal configuration
define('TERMINAL_ENABLED', true); // Set false to disable terminal
define('TERMINAL_MAX_OUTPUT', 1000000); // Max output size (1MB)
define('TERMINAL_EXEC_TIMEOUT', 30); // Max execution time per command in seconds

// ========================================
// SESSION MANAGEMENT
// ========================================

function setSessionCookieParamsCompat($lifetime, $path, $domain, $secure, $httponly, $samesite) {
    $cookiePath = rtrim($path, '/\\');
    if ($cookiePath === '') {
        $cookiePath = '/';
    }

    $cookiePath .= '; samesite=' . $samesite;
    session_set_cookie_params($lifetime, $cookiePath, $domain, $secure, $httponly);
}

$sessionSecure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
session_name(SESSION_NAME);
setSessionCookieParamsCompat(SESSION_TIMEOUT, '/', '', $sessionSecure, true, 'Lax');
session_start();

// Check if session is expired
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
    session_unset();
    session_destroy();
    session_start();
}
$_SESSION['last_activity'] = time();

// Initialize terminal working directory
if (!isset($_SESSION['terminal_cwd'])) {
    $_SESSION['terminal_cwd'] = detectDefaultStartDir();
}

// ========================================
// AUTHENTICATION FUNCTIONS
// ========================================

function isLoggedIn() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

function arrayGetValue($array, $key, $default) {
    return (is_array($array) && isset($array[$key])) ? $array[$key] : $default;
}

function firstArrayValue($array, $default) {
    return (is_array($array) && isset($array[0])) ? $array[0] : $default;
}

function valueOrDefault($value, $default) {
    return ($value !== null && $value !== false && $value !== '') ? $value : $default;
}

function hashEqualsCompat($knownString, $userString) {
    if (function_exists('hash_equals')) {
        return hash_equals($knownString, $userString);
    }

    $knownString = (string)$knownString;
    $userString = (string)$userString;

    if (strlen($knownString) !== strlen($userString)) {
        return false;
    }

    $result = 0;
    $length = strlen($knownString);
    for ($i = 0; $i < $length; $i++) {
        $result |= ord($knownString[$i]) ^ ord($userString[$i]);
    }

    return $result === 0;
}

function passwordVerifyCompat($password, $hash) {
    $password = (string)$password;
    $hash = (string)$hash;

    if ($hash === '') {
        return false;
    }

    if (function_exists('password_verify')) {
        return password_verify($password, $hash);
    }

    if (function_exists('crypt')) {
        $cryptHash = crypt($password, $hash);
        if (is_string($cryptHash) && strlen($cryptHash) > 12) {
            return hashEqualsCompat($hash, $cryptHash);
        }
    }

    if (strlen($hash) === 32) {
        return hashEqualsCompat($hash, md5($password));
    }

    if (strlen($hash) === 40) {
        return hashEqualsCompat($hash, sha1($password));
    }

    if (function_exists('hash')) {
        return hashEqualsCompat($hash, hash('sha256', $password));
    }

    return false;
}

function getEnvValue($key, $default) {
    return valueOrDefault(getenv($key), $default);
}

function terminalFunctionAvailable($functionName) {
    if (!function_exists($functionName)) {
        return false;
    }

    $disabled = ini_get('disable_functions');
    if (!is_string($disabled) || trim($disabled) === '') {
        return true;
    }

    $disabledFunctions = array_map('trim', explode(',', $disabled));
    return !in_array($functionName, $disabledFunctions);
}

function terminalExecutionMethod() {
    if (terminalFunctionAvailable('proc_open')) {
        return 'proc_open';
    }
    if (terminalFunctionAvailable('exec')) {
        return 'exec';
    }
    if (terminalFunctionAvailable('system')) {
        return 'system';
    }
    if (terminalFunctionAvailable('shell_exec')) {
        return 'shell_exec';
    }
    return '';
}

function getTerminalShellPath() {
    if (isWindowsOS()) {
        return getEnvValue('ComSpec', 'cmd.exe');
    }

    if (is_file('/bin/sh')) {
        return '/bin/sh';
    }

    return 'sh';
}

function buildTerminalCommand($command) {
    $shellPath = getTerminalShellPath();

    if (isWindowsOS()) {
        return '"' . $shellPath . '" /Q /C ' . $command;
    }

    return escapeshellcmd($shellPath) . ' -lc ' . escapeshellarg($command);
}

function runArchiveShellCommand($command, &$exitCode) {
    $exitCode = 1;

    if (terminalFunctionAvailable('exec')) {
        $lines = array();
        @exec($command . ' 2>&1', $lines, $exitCode);
        return implode("\n", $lines);
    }

    if (terminalFunctionAvailable('shell_exec')) {
        if (isWindowsOS()) {
            $shellPath = getTerminalShellPath();
            $wrappedCommand = '"' . $shellPath . '" /Q /C "' . str_replace('"', '""', $command) . ' 2>&1 & echo __EXIT_CODE__:%ERRORLEVEL%"';
            $result = @shell_exec($wrappedCommand);
        } else {
            $result = @shell_exec($command . ' 2>&1; printf "\n__EXIT_CODE__:%s" "$?"');
        }

        if (!is_string($result)) {
            return false;
        }

        if (preg_match('/__EXIT_CODE__:(\d+)\s*$/', $result, $matches)) {
            $exitCode = (int)$matches[1];
            $result = preg_replace('/__EXIT_CODE__:\d+\s*$/', '', $result);
        } else {
            $exitCode = 0;
        }

        return trim($result);
    }

    return false;
}

function getTerminalPromptSymbol() {
    return isWindowsOS() ? '>' : '$';
}

function executeTerminalCommand($command, $cwd) {
    $command = trim((string)$command);
    $cwd = resolveExistingPath($cwd, getcwd());

    if ($command === '') {
        return array('output' => '', 'cwd' => $cwd, 'success' => true);
    }

    $method = terminalExecutionMethod();
    if ($method === '') {
        return array(
            'output' => 'Terminal tidak tersedia: fungsi eksekusi PHP dinonaktifkan oleh server.',
            'cwd' => $cwd,
            'success' => false
        );
    }

    $wrappedCommand = buildTerminalCommand($command);
    $result = '';
    $success = false;

    if ($method === 'proc_open') {
        $descriptorspec = array(
            0 => array('pipe', 'r'),
            1 => array('pipe', 'w'),
            2 => array('pipe', 'w')
        );

        $environment = array();
        if (isset($_SERVER['PATH'])) {
            $environment['PATH'] = $_SERVER['PATH'];
        }
        if (isWindowsOS() && isset($_SERVER['SystemRoot'])) {
            $environment['SystemRoot'] = $_SERVER['SystemRoot'];
        }

        $process = @proc_open($wrappedCommand, $descriptorspec, $pipes, $cwd, $environment);
        if (is_resource($process)) {
            fclose($pipes[0]);

            stream_set_timeout($pipes[1], TERMINAL_EXEC_TIMEOUT);
            stream_set_timeout($pipes[2], TERMINAL_EXEC_TIMEOUT);

            $output = stream_get_contents($pipes[1]);
            $error = stream_get_contents($pipes[2]);

            fclose($pipes[1]);
            fclose($pipes[2]);

            $returnValue = proc_close($process);
            $result = (string)$output . (string)$error;
            $success = ($returnValue === 0);
        } else {
            $result = 'Gagal menjalankan terminal process.';
        }
    } elseif ($method === 'shell_exec') {
        $output = @shell_exec($wrappedCommand . ' 2>&1');
        $result = (string)$output;
        $success = true;
    } elseif ($method === 'exec') {
        $outputLines = array();
        $returnValue = 1;
        @exec($wrappedCommand . ' 2>&1', $outputLines, $returnValue);
        $result = implode("\n", $outputLines);
        $success = ($returnValue === 0);
    } elseif ($method === 'system') {
        ob_start();
        $returnValue = 1;
        @system($wrappedCommand . ' 2>&1', $returnValue);
        $result = (string)ob_get_clean();
        $success = ($returnValue === 0);
    }

    if ($result === '') {
        $result = $success ? '[OK]' : '[Tidak ada output]';
    }

    if (strlen($result) > TERMINAL_MAX_OUTPUT) {
        $result = substr($result, 0, TERMINAL_MAX_OUTPUT) . "\n... (output truncated)";
    }

    return array('output' => $result, 'cwd' => $cwd, 'success' => $success);
}

function login($password) {
    $verified = false;

    if (defined('ADMIN_PASSWORD_HASH') && ADMIN_PASSWORD_HASH !== '') {
        $verified = passwordVerifyCompat($password, ADMIN_PASSWORD_HASH);
    } elseif (defined('ADMIN_PASSWORD_LEGACY') && ADMIN_PASSWORD_LEGACY !== '') {
        $verified = hashEqualsCompat(ADMIN_PASSWORD_LEGACY, $password);
    }

    if ($verified) {
        session_regenerate_id(true);
        $_SESSION['logged_in'] = true;
        $_SESSION['login_time'] = time();
        $_SESSION['last_regenerate'] = time();
        return true;
    }
    return false;
}

function logout() {
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();

        $cookiePath = rtrim($params['path'], '/\\');
        if ($cookiePath === '') {
            $cookiePath = '/';
        }
        $cookiePath .= '; samesite=Lax';
        setcookie(session_name(), '', time() - 42000, $cookiePath, $params['domain'], $params['secure'], $params['httponly']);
    }
    session_unset();
    session_destroy();
}

function getServerOsFamilyValue() {
    if (defined('PHP_OS_FAMILY') && PHP_OS_FAMILY) {
        return PHP_OS_FAMILY;
    }

    $phpOs = strtoupper(substr(PHP_OS, 0, 3));
    if ($phpOs === 'WIN') {
        return 'Windows';
    }
    if ($phpOs === 'DAR') {
        return 'Darwin';
    }
    if ($phpOs === 'LIN') {
        return 'Linux';
    }

    return PHP_OS;
}

function getServerOsLabel() {
    $family = getServerOsFamilyValue();

    if ($family === 'Windows') {
        return 'Windows';
    }
    if ($family === 'Darwin') {
        return 'macOS';
    }
    if ($family === 'Linux') {
        return 'Linux';
    }

    return $family !== '' ? $family : 'Unknown OS';
}

function isWindowsOS() {
    $family = getServerOsFamilyValue();
    return DIRECTORY_SEPARATOR === '\\' || strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' || $family === 'Windows';
}

function isUnixLikeOS() {
    return !isWindowsOS();
}

function commandExists($command) {
    $command = trim((string)$command);
    if ($command === '') {
        return false;
    }

    $exitCode = 1;
    if (isWindowsOS()) {
        runArchiveShellCommand('where ' . escapeshellarg($command), $exitCode);
    } else {
        runArchiveShellCommand('command -v ' . escapeshellarg($command), $exitCode);
    }

    return $exitCode === 0;
}

function getArchiveExtractMethod() {
    if (class_exists('ZipArchive')) {
        return 'ziparchive';
    }

    if (isWindowsOS()) {
        if (commandExists('powershell')) {
            return 'powershell';
        }
        if (commandExists('tar')) {
            return 'tar';
        }
        return '';
    }

    if (commandExists('unzip')) {
        return 'unzip';
    }
    if (commandExists('tar')) {
        return 'tar';
    }

    return '';
}

function getArchiveCompressMethod() {
    if (class_exists('ZipArchive')) {
        return 'ziparchive';
    }

    if (isWindowsOS()) {
        if (commandExists('powershell')) {
            return 'powershell';
        }
        if (commandExists('tar')) {
            return 'tar';
        }
        return '';
    }

    if (commandExists('zip')) {
        return 'zip';
    }
    if (commandExists('tar')) {
        return 'tar';
    }

    return '';
}

function runArchiveCompressionFallback($baseDir, $items, $zipPath, &$errorMessage) {
    $errorMessage = '';
    $method = getArchiveCompressMethod();
    $validItems = array();

    foreach ((array)$items as $item) {
        $item = trim((string)$item);
        if ($item === '' || $item === '.' || $item === '..') {
            continue;
        }
        if (strpos($item, '/') !== false || strpos($item, '\\') !== false) {
            continue;
        }
        if (file_exists(joinPathValue($baseDir, $item))) {
            $validItems[] = $item;
        }
    }

    if (empty($validItems)) {
        $errorMessage = 'No files to compress';
        return false;
    }

    if ($method === 'powershell') {
        $quotedItems = array();
        foreach ($validItems as $item) {
            $quotedItems[] = "'" . str_replace("'", "''", $item) . "'";
        }
        $psScript = "Set-Location -LiteralPath '" . str_replace("'", "''", $baseDir) . "'; Compress-Archive -LiteralPath @(" . implode(', ', $quotedItems) . ") -DestinationPath '" . str_replace("'", "''", $zipPath) . "' -Force";
        $cmd = 'powershell -NoProfile -ExecutionPolicy Bypass -Command ' . escapeshellarg($psScript);
        $exitCode = 1;
        $output = runArchiveShellCommand($cmd, $exitCode);
        if ($exitCode === 0 && file_exists($zipPath)) {
            return true;
        }
        $errorMessage = normalizeArchiveToolErrorMessage($output);
        return false;
    }

    if ($method === 'zip') {
        $parts = array();
        foreach ($validItems as $item) {
            $parts[] = escapeshellarg($item);
        }
        $cmd = 'cd ' . escapeshellarg($baseDir) . ' && zip -qry ' . escapeshellarg($zipPath) . ' ' . implode(' ', $parts);
        $exitCode = 1;
        $output = runArchiveShellCommand($cmd, $exitCode);
        if ($exitCode === 0 && file_exists($zipPath)) {
            return true;
        }
        $errorMessage = normalizeArchiveToolErrorMessage($output);
        return false;
    }

    if ($method === 'tar') {
        $parts = array();
        foreach ($validItems as $item) {
            $parts[] = escapeshellarg($item);
        }
        $cmd = 'cd ' . escapeshellarg($baseDir) . ' && tar -a -cf ' . escapeshellarg($zipPath) . ' ' . implode(' ', $parts);
        $exitCode = 1;
        $output = runArchiveShellCommand($cmd, $exitCode);
        if ($exitCode === 0 && file_exists($zipPath)) {
            return true;
        }
        $errorMessage = trim((string)$output);
        return false;
    }

    $errorMessage = 'No compression method available on this server';
    return false;
}

function normalizeArchiveToolErrorMessage($message) {
    $message = trim((string)$message);
    if ($message === '') {
        return 'Operasi arsip gagal di server';
    }

    $lower = function_exists('mb_strtolower') ? mb_strtolower($message, 'UTF-8') : strtolower($message);

    if (strpos($lower, 'empty zipfile') !== false || strpos($lower, 'zip file is empty') !== false) {
        return 'File ZIP kosong';
    }

    if (
        strpos($lower, 'end-of-central-directory') !== false ||
        strpos($lower, 'cannot find zipfile directory') !== false ||
        strpos($lower, 'not a zipfile') !== false ||
        strpos($lower, 'not a valid zip') !== false ||
        strpos($lower, 'is not a zip archive') !== false ||
        strpos($lower, 'central directory') !== false
    ) {
        return 'File ZIP rusak atau tidak valid';
    }

    if (strpos($lower, 'no such file') !== false || strpos($lower, 'cannot find path') !== false) {
        return 'File ZIP tidak ditemukan';
    }

    if (strpos($lower, 'permission denied') !== false || strpos($lower, 'access is denied') !== false) {
        return 'Server tidak memiliki izin untuk mengekstrak file ZIP';
    }

    return $message;
}

function getArchiveEntryListFallback($zipPath, &$errorMessage) {
    $errorMessage = '';
    $method = getArchiveExtractMethod();
    $exitCode = 1;
    $output = false;

    if ($method === 'powershell') {
        $psScript = "Add-Type -AssemblyName System.IO.Compression.FileSystem; $zip = [System.IO.Compression.ZipFile]::OpenRead('" . str_replace("'", "''", $zipPath) . "'); foreach ($entry in $zip.Entries) { $entry.FullName }; $zip.Dispose()";
        $output = runArchiveShellCommand('powershell -NoProfile -ExecutionPolicy Bypass -Command ' . escapeshellarg($psScript), $exitCode);
    } elseif ($method === 'unzip') {
        $output = runArchiveShellCommand('unzip -Z1 ' . escapeshellarg($zipPath), $exitCode);
    } elseif ($method === 'tar') {
        $output = runArchiveShellCommand('tar -tf ' . escapeshellarg($zipPath), $exitCode);
    } else {
        $errorMessage = 'No extraction method available on this server';
        return false;
    }

    if ($output === false || $exitCode !== 0) {
        $errorMessage = normalizeArchiveToolErrorMessage($output);
        return false;
    }

    $entries = preg_split('/\r\n|\r|\n/', trim((string)$output));
    $entries = array_values(array_filter($entries, 'strlen'));
    return $entries;
}

function runArchiveExtractionFallback($zipPath, $extractPath, &$errorMessage, &$extractedCount) {
    $errorMessage = '';
    $extractedCount = 0;
    $method = getArchiveExtractMethod();

    if ($method === 'ziparchive') {
        return true;
    }

    $entries = getArchiveEntryListFallback($zipPath, $errorMessage);
    if ($entries === false) {
        return false;
    }

    if (count($entries) === 0) {
        $errorMessage = 'File ZIP kosong';
        return false;
    }

    foreach ($entries as $entryName) {
        $safeEntry = sanitizeArchiveEntryPath($entryName);
        if ($safeEntry === false) {
            $errorMessage = 'ZIP berisi path tidak aman dan diblokir untuk mencegah zip slip';
            return false;
        }
    }

    $exitCode = 1;
    $output = false;

    if ($method === 'powershell') {
        $cmd = 'powershell -NoProfile -ExecutionPolicy Bypass -Command ' . escapeshellarg("Expand-Archive -LiteralPath '" . str_replace("'", "''", $zipPath) . "' -DestinationPath '" . str_replace("'", "''", $extractPath) . "' -Force");
        $output = runArchiveShellCommand($cmd, $exitCode);
    } elseif ($method === 'unzip') {
        $output = runArchiveShellCommand('unzip -oq ' . escapeshellarg($zipPath) . ' -d ' . escapeshellarg($extractPath), $exitCode);
    } elseif ($method === 'tar') {
        $output = runArchiveShellCommand('tar -xf ' . escapeshellarg($zipPath) . ' -C ' . escapeshellarg($extractPath), $exitCode);
    } else {
        $errorMessage = 'No extraction method available on this server';
        return false;
    }

    if ($output === false || $exitCode !== 0) {
        $errorMessage = normalizeArchiveToolErrorMessage($output);
        return false;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($extractPath, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    foreach ($iterator as $entry) {
        $extractedCount++;
    }

    if ($extractedCount <= 0) {
        $errorMessage = 'Tidak ada file valid yang berhasil diekstrak';
        return false;
    }

    return true;
}

function hasWindowsDrive($path) {
    return is_string($path) && strlen($path) >= 2 && ctype_alpha($path[0]) && $path[1] === ':';
}

function normalizePathValue($path) {
    if ($path === null) {
        return '';
    }

    $path = trim((string)$path);
    if ($path === '') {
        return '';
    }

    if (isWindowsOS()) {
        $path = str_replace('/', '\\', $path);
        if (hasWindowsDrive($path)) {
            $path = strtoupper($path[0]) . substr($path, 1);
            if (strlen($path) === 2) {
                return $path . '\\';
            }
        }
        return $path;
    }

    return str_replace('\\', '/', $path);
}

function isAbsolutePathValue($path) {
    $path = normalizePathValue($path);
    if ($path === '') {
        return false;
    }

    if (isWindowsOS()) {
        return hasWindowsDrive($path) || strpos($path, '\\\\') === 0;
    }

    return isset($path[0]) && $path[0] === '/';
}

function joinPathValue($base, $child) {
    $base = normalizePathValue($base);
    $child = normalizePathValue($child);

    if ($child === '') {
        return $base;
    }

    if (isAbsolutePathValue($child)) {
        return $child;
    }

    return rtrim($base, '\\/') . DIRECTORY_SEPARATOR . ltrim($child, '\\/');
}

function sanitizeArchiveEntryPath($entryName) {
    $entryName = str_replace('\\', '/', (string)$entryName);
    $entryName = preg_replace('/^[A-Za-z]:/', '', $entryName);
    $entryName = ltrim($entryName, '/');

    if ($entryName === '') {
        return '';
    }

    $parts = array();
    foreach (explode('/', $entryName) as $part) {
        if ($part === '' || $part === '.') {
            continue;
        }
        if ($part === '..') {
            return false;
        }
        $parts[] = $part;
    }

    return implode('/', $parts);
}

function createUniqueExtractDirectory($baseDir, $zipFileName) {
    $baseName = preg_replace('/\.zip$/i', '', basename((string)$zipFileName));
    $baseName = preg_replace('/[^a-zA-Z0-9._\- ]/', '_', $baseName);
    $baseName = trim($baseName);
    if ($baseName === '') {
        $baseName = 'archive';
    }

    $extractPath = joinPathValue($baseDir, $baseName . '_extracted');
    $counter = 1;
    while (file_exists($extractPath)) {
        $extractPath = joinPathValue($baseDir, $baseName . '_extracted_' . $counter);
        $counter++;
    }

    return $extractPath;
}

function cleanupExtractionDirectory($dir) {
    if (!is_dir($dir)) {
        return true;
    }

    $items = @scandir($dir);
    if (!is_array($items)) {
        return false;
    }

    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        $path = $dir . DIRECTORY_SEPARATOR . $item;
        if (is_dir($path)) {
            cleanupExtractionDirectory($path);
        } else {
            @unlink($path);
        }
    }

    return @rmdir($dir);
}


function mapZipArchiveOpenError($code) {
    $messages = array(
        ZipArchive::ER_EXISTS => 'File ZIP tujuan sudah ada',
        ZipArchive::ER_INCONS => 'Struktur file ZIP tidak konsisten atau rusak',
        ZipArchive::ER_INVAL => 'Parameter ZIP tidak valid',
        ZipArchive::ER_MEMORY => 'Memori server tidak cukup untuk membuka ZIP',
        ZipArchive::ER_NOENT => 'File ZIP tidak ditemukan',
        ZipArchive::ER_NOZIP => 'File bukan arsip ZIP yang valid',
        ZipArchive::ER_OPEN => 'File ZIP tidak dapat dibuka',
        ZipArchive::ER_READ => 'File ZIP tidak dapat dibaca',
        ZipArchive::ER_SEEK => 'Server gagal membaca posisi data ZIP'
    );

    if ($code === true || $code === 0) {
        return 'Gagal membuka file ZIP';
    }

    return isset($messages[$code]) ? $messages[$code] : ('Gagal membuka file ZIP (kode ' . $code . ')');
}

function collectZipArchiveEntries($zip, &$errorMessage) {
    $errorMessage = '';
    $totalEntries = (int)$zip->numFiles;
    if ($totalEntries <= 0) {
        $errorMessage = 'File ZIP kosong';
        return false;
    }

    $entries = array();
    for ($i = 0; $i < $totalEntries; $i++) {
        $originalName = (string)$zip->getNameIndex($i);
        if ($originalName === '') {
            continue;
        }

        $safeRelativePath = sanitizeArchiveEntryPath($originalName);
        if ($safeRelativePath === false) {
            $errorMessage = 'ZIP berisi path tidak aman dan diblokir untuk mencegah zip slip';
            return false;
        }
        if ($safeRelativePath === '') {
            continue;
        }

        $entries[] = array(
            'index' => $i,
            'original' => $originalName,
            'safe' => $safeRelativePath,
            'is_dir' => (substr($originalName, -1) === '/' || substr($originalName, -1) === '\\')
        );
    }

    if (count($entries) <= 0) {
        $errorMessage = 'Tidak ada file valid yang bisa diekstrak dari ZIP';
        return false;
    }

    return $entries;
}

function emitUnzipSseProgress($payload) {
    echo "data: " . json_encode($payload) . "\\n\\n";
    @flush();
    @ob_flush();
}

function extractZipArchiveSafely($zipPath, $extractPath, &$errorMessage, &$extractedCount, $progressCallback = null) {
    $errorMessage = '';
    $extractedCount = 0;

    if (!class_exists('ZipArchive')) {
        $errorMessage = 'ZipArchive tidak tersedia di server ini';
        return false;
    }

    $zip = new ZipArchive();
    $openResult = $zip->open($zipPath);
    if ($openResult !== true) {
        $errorMessage = mapZipArchiveOpenError($openResult);
        return false;
    }

    $entries = collectZipArchiveEntries($zip, $errorMessage);
    if ($entries === false) {
        $zip->close();
        return false;
    }

    $processed = 0;
    $totalEntries = count($entries);
    $startTime = microtime(true);

    foreach ($entries as $entry) {
        $processed++;
        $targetPath = joinPathValue($extractPath, $entry['safe']);

        if ($entry['is_dir']) {
            if (!is_dir($targetPath) && !@mkdir($targetPath, 0755, true)) {
                $zip->close();
                $errorMessage = 'Folder hasil unzip tidak dapat dibuat: ' . basename($entry['safe']);
                return false;
            }
            $extractedCount++;
        } else {
            $targetDir = dirname($targetPath);
            if (!is_dir($targetDir) && !@mkdir($targetDir, 0755, true)) {
                $zip->close();
                $errorMessage = 'Folder hasil unzip tidak dapat dibuat: ' . basename($targetDir);
                return false;
            }

            $stream = $zip->getStream($entry['original']);
            if ($stream === false) {
                $zip->close();
                $errorMessage = 'Gagal membaca data ZIP: ' . basename($entry['safe']);
                return false;
            }

            $output = @fopen($targetPath, 'wb');
            if ($output === false) {
                @fclose($stream);
                $zip->close();
                $errorMessage = 'Tidak dapat menulis file hasil unzip: ' . basename($entry['safe']);
                return false;
            }

            $writeOk = true;
            while (!feof($stream)) {
                $chunk = fread($stream, 8192);
                if ($chunk === false) {
                    $writeOk = false;
                    break;
                }
                if ($chunk !== '' && fwrite($output, $chunk) === false) {
                    $writeOk = false;
                    break;
                }
            }

            @fclose($output);
            @fclose($stream);

            if (!$writeOk) {
                @unlink($targetPath);
                $zip->close();
                $errorMessage = 'Gagal mengekstrak file: ' . basename($entry['safe']);
                return false;
            }

            $extractedCount++;
        }

        if ($progressCallback !== null && is_callable($progressCallback)) {
            $elapsed = microtime(true) - $startTime;
            $payload = array(
                'current' => $processed,
                'total' => $totalEntries,
                'percent' => round(($processed / max(1, $totalEntries)) * 100, 1),
                'speed' => round($elapsed > 0 ? $processed / $elapsed : 0, 1),
                'file' => basename($entry['safe'])
            );
            call_user_func($progressCallback, $payload);
        }
    }

    $zip->close();

    if ($extractedCount <= 0) {
        $errorMessage = 'Tidak ada file valid yang berhasil diekstrak dari ZIP';
        return false;
    }

    return true;
}

function detectDefaultStartDir() {
    $baseScriptDir = dirname(__FILE__);
    $resolvedScriptDir = realpath($baseScriptDir);
    $scriptDir = normalizePathValue(valueOrDefault($resolvedScriptDir, $baseScriptDir));

    if (BASE_DIR !== '__AUTO__') {
        $customBase = realpath(BASE_DIR);
        return $customBase !== false ? normalizePathValue($customBase) : normalizePathValue(BASE_DIR);
    }

    if (isWindowsOS() && hasWindowsDrive($scriptDir)) {
        return strtoupper($scriptDir[0]) . ':\\';
    }

    return isWindowsOS() ? $scriptDir : '/';
}

function resolveExistingPath($path, $fallback = null) {
    $fallback = $fallback !== null ? normalizePathValue($fallback) : detectDefaultStartDir();
    $path = normalizePathValue($path);

    if ($path === '') {
        return $fallback;
    }

    $real = realpath($path);
    if ($real !== false) {
        return normalizePathValue($real);
    }

    if (isWindowsOS() && hasWindowsDrive($path) && @is_dir($path)) {
        return normalizePathValue($path);
    }

    if (!isAbsolutePathValue($path)) {
        $candidate = joinPathValue($fallback, $path);
        $realCandidate = realpath($candidate);
        if ($realCandidate !== false) {
            return normalizePathValue($realCandidate);
        }
    }

    return $fallback;
}

function isPathAllowedValue($path) {
    $path = normalizePathValue($path);
    if ($path === '') {
        return false;
    }

    if (BASE_DIR === '__AUTO__') {
        return @file_exists($path) || @is_dir($path) || @is_file($path);
    }

    $base = normalizePathValue(detectDefaultStartDir());

    if (isWindowsOS()) {
        return stripos(rtrim($path, '\\'), rtrim($base, '\\')) === 0;
    }

    return strpos(rtrim($path, '/'), rtrim($base, '/')) === 0;
}

function getParentPathValue($path) {
    $path = normalizePathValue($path);

    if (isWindowsOS()) {
        $trimmed = rtrim($path, '\\/');
        if (hasWindowsDrive($trimmed) && strlen($trimmed) === 2) {
            return strtoupper($trimmed[0]) . ':\\';
        }
    } else {
        if ($path === '/') {
            return '/';
        }
    }

    $parent = normalizePathValue(dirname($path));
    if ($parent === '.' || $parent === '') {
        return detectDefaultStartDir();
    }

    return $parent;
}

function buildBreadcrumbs($path) {
    $path = normalizePathValue($path);
    $crumbs = array();

    if (isWindowsOS() && hasWindowsDrive($path)) {
        $drive = strtoupper($path[0]) . ':';
        $current = $drive . '\\';
        $crumbs[] = array('label' => $drive, 'path' => $current);
        $rest = ltrim(substr($path, 2), '\\/');
        $parts = array_values(array_filter(preg_split('/[\\\\\/]+/', $rest), 'strlen'));
        foreach ($parts as $part) {
            $current = rtrim($current, '\\') . '\\' . $part;
            $crumbs[] = array('label' => $part, 'path' => $current);
        }
        return $crumbs;
    }

    $crumbs[] = array('label' => 'Root', 'path' => '/');
    $parts = array_values(array_filter(explode('/', str_replace('\\', '/', $path)), 'strlen'));
    $current = '';
    foreach ($parts as $part) {
        $current .= '/' . $part;
        $crumbs[] = array('label' => $part, 'path' => $current);
    }
    return $crumbs;
}

function getUserAgentValue() {
    return arrayGetValue($_SERVER, 'HTTP_USER_AGENT', '');
}

function detectDeviceType() {
    $ua = strtolower(getUserAgentValue());

    if ($ua === '') {
        return 'desktop';
    }

    if (strpos($ua, 'ipad') !== false || strpos($ua, 'tablet') !== false || strpos($ua, 'playbook') !== false || (strpos($ua, 'android') !== false && strpos($ua, 'mobile') === false) || strpos($ua, 'kindle') !== false || strpos($ua, 'silk') !== false) {
        return 'tablet';
    }

    if (strpos($ua, 'mobile') !== false || strpos($ua, 'iphone') !== false || strpos($ua, 'ipod') !== false || strpos($ua, 'android') !== false || strpos($ua, 'blackberry') !== false || strpos($ua, 'opera mini') !== false || strpos($ua, 'windows phone') !== false) {
        return 'mobile';
    }

    return 'desktop';
}

function isTouchLikeDevice() {
    $deviceType = detectDeviceType();
    return $deviceType === 'mobile' || $deviceType === 'tablet';
}

function getDeviceLabel($deviceType) {
    switch ($deviceType) {
        case 'mobile':
            return 'Mobile';
        case 'tablet':
            return 'Tablet';
        default:
            return 'Desktop';
    }
}

function getDeviceIcon($deviceType) {
    switch ($deviceType) {
        case 'mobile':
            return '📱';
        case 'tablet':
            return '📲';
        default:
            return '🖥️';
    }
}

function getDeviceCssClass($deviceType) {
    return 'device-' . $deviceType;
}

function isSseActionRequest() {
    $action = arrayGetValue($_GET, 'action', '');
    return ($action === 'compress_execute' || $action === 'extract' || $action === 'unzip');
}

function isJsonActionRequest() {
    if (isset($_POST['terminal_action']) || isset($_FILES['upload_files'])) {
        return true;
    }

    $action = arrayGetValue($_GET, 'action', '');
    return in_array($action, array('compress_prepare', 'delete', 'create_folder', 'view', 'save_file', 'chmod', 'unzip_simple'));
}

function prepareApiOutput($contentType) {
    while (ob_get_level() > 0) {
        @ob_end_clean();
    }

    if (function_exists('apache_setenv')) {
        @apache_setenv('no-gzip', '1');
    }

    @ini_set('display_errors', '0');
    @ini_set('html_errors', '0');
    @ini_set('zlib.output_compression', '0');
    @ini_set('implicit_flush', '1');

    header('Content-Type: ' . $contentType);
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
}

function respondJson($data) {
    prepareApiOutput('application/json; charset=UTF-8');
    echo json_encode($data);
    exit;
}

function respondSseError($message) {
    prepareApiOutput('text/event-stream');
    header('Connection: keep-alive');
    header('X-Accel-Buffering: no');
    echo "data: " . json_encode(array('error' => $message)) . "\n\n";
    @flush();
    exit;
}

// ========================================
// HANDLE LOGIN/LOGOUT REQUESTS
// ========================================

if (isset($_POST['action'])) {
    if ($_POST['action'] === 'login') {
        $password = arrayGetValue($_POST, 'password', '');
        if (login($password)) {
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
        } else {
            $login_error = 'Password salah! Coba lagi.';
        }
    } elseif ($_POST['action'] === 'logout') {
        logout();
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
}

$deviceType = detectDeviceType();
$deviceLabel = getDeviceLabel($deviceType);
$deviceIcon = getDeviceIcon($deviceType);
$deviceCssClass = getDeviceCssClass($deviceType) . (isTouchLikeDevice() ? ' touch-device' : ' no-touch-device');

// ========================================
// LOGIN PAGE
// ========================================

if (!isLoggedIn()) {
    if (isSseActionRequest()) {
        respondSseError('Session expired. Silakan login kembali.');
    }

    if (isJsonActionRequest()) {
        respondJson(array('error' => 'Session expired. Silakan login kembali.'));
    }
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>🔐 Login - Secure File Manager</title>
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
            }
            .login-container {
                background: white;
                border-radius: 20px;
                box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
                padding: 40px;
                max-width: 400px;
                width: 100%;
                animation: slideIn 0.5s ease;
            }
            @keyframes slideIn {
                from { opacity: 0; transform: translateY(-30px); }
                to { opacity: 1; transform: translateY(0); }
            }
            .login-header { text-align: center; margin-bottom: 30px; }
            .login-header h1 { color: #667eea; font-size: 28px; margin-bottom: 10px; }
            .login-header .icon { font-size: 60px; margin-bottom: 15px; animation: float 3s ease-in-out infinite; }
            @keyframes float {
                0%, 100% { transform: translateY(0); }
                50% { transform: translateY(-10px); }
            }
            .login-header p { color: #666; font-size: 14px; }
            .login-form { display: flex; flex-direction: column; gap: 20px; }
            .form-group { position: relative; }
            .form-group label { display: block; color: #333; font-weight: 600; margin-bottom: 8px; font-size: 14px; }
            .form-group input {
                width: 100%;
                padding: 12px 40px 12px 15px;
                border: 2px solid #e0e0e0;
                border-radius: 10px;
                font-size: 16px;
                transition: all 0.3s;
            }
            .form-group input:focus {
                outline: none;
                border-color: #667eea;
                box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            }
            .toggle-password {
                position: absolute;
                right: 12px;
                top: 38px;
                cursor: pointer;
                font-size: 20px;
                user-select: none;
            }
            .btn-login {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                border: none;
                padding: 14px;
                border-radius: 10px;
                font-size: 16px;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.3s;
                box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
            }
            .btn-login:hover {
                transform: translateY(-2px);
                box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
            }
            .error-message {
                background: #fee;
                color: #c33;
                padding: 12px;
                border-radius: 8px;
                border-left: 4px solid #c33;
                font-size: 14px;
                animation: shake 0.5s;
            }
            @keyframes shake {
                0%, 100% { transform: translateX(0); }
                25% { transform: translateX(-10px); }
                75% { transform: translateX(10px); }
            }
            .security-info {
                margin-top: 20px;
                padding: 15px;
                background: #f5f5f5;
                border-radius: 10px;
                font-size: 12px;
                color: #666;
            }
            body.device-mobile .login-container,
            body.device-tablet .login-container {
                max-width: 100%;
                border-radius: 16px;
            }
            body.touch-device .btn-login,
            body.touch-device .form-group input {
                min-height: 48px;
                font-size: 16px;
            }
    
        /* Professional UI Upgrade */
        body {
            background: radial-gradient(circle at top left, #1e293b 0%, #0f172a 38%, #111827 100%);
            color: #0f172a;
        }
        .container {
            max-width: 1500px;
            border-radius: 24px;
            border: 1px solid rgba(148, 163, 184, 0.18);
            box-shadow: 0 28px 80px rgba(15, 23, 42, 0.42);
            overflow: hidden;
            background: #f8fafc;
        }
        .header {
            background: linear-gradient(135deg, #0f172a 0%, #111827 42%, #1f2937 100%);
            padding: 26px 30px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }
        .header h1 {
            font-size: 28px;
            letter-spacing: -0.03em;
            margin: 0;
        }
        .header-brand {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .header-subtitle {
            color: rgba(226, 232, 240, 0.82);
            font-size: 13px;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }
        .header-right {
            align-items: stretch;
        }
        .session-info {
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.12);
            padding: 12px 14px;
            border-radius: 14px;
            line-height: 1.6;
        }
        .btn-logout {
            border-color: rgba(255,255,255,0.28);
            background: rgba(255,255,255,0.08);
            min-height: 46px;
        }
        .overview-strip {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 14px;
            padding: 18px 22px 0;
            background: linear-gradient(180deg, rgba(248,250,252,0.92) 0%, rgba(248,250,252,1) 100%);
        }
        .overview-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            padding: 16px 18px;
            display: flex;
            align-items: center;
            gap: 14px;
            box-shadow: 0 12px 34px rgba(15, 23, 42, 0.06);
            min-height: 82px;
        }
        .overview-icon {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            background: linear-gradient(135deg, #e0e7ff 0%, #dbeafe 100%);
        }
        .overview-label {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #64748b;
        }
        .overview-value {
            font-size: 24px;
            font-weight: 800;
            color: #0f172a;
            margin-top: 4px;
        }
        .path-bar {
            margin: 18px 22px 0;
            padding: 18px 20px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            box-shadow: 0 14px 36px rgba(15, 23, 42, 0.06);
        }
        .path-top-row {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }
        .path-title-block {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .path-title {
            font-size: 18px;
            font-weight: 800;
            color: #0f172a;
        }
        .path-caption {
            color: #64748b;
            font-size: 13px;
        }
        .path-controls {
            width: 100%;
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }
        .path-input {
            min-height: 46px;
            border: 1px solid #cbd5e1;
            background: #f8fafc;
            border-radius: 14px;
            box-shadow: inset 0 1px 2px rgba(15, 23, 42, 0.04);
        }
        .path-chip-row {
            width: 100%;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 2px;
        }
        .path-chip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #eef2ff;
            color: #334155;
            padding: 9px 12px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 600;
        }
        .breadcrumb {
            width: 100%;
            margin-top: 4px;
            padding: 12px 14px;
            background: #f8fafc;
            border-radius: 14px;
            border: 1px solid #e2e8f0;
        }
        .breadcrumb a {
            color: #334155;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 8px 10px;
            font-weight: 600;
        }
        .actions-bar {
            margin: 16px 22px 0;
            padding: 0;
            background: transparent;
            border: 0;
        }
        .toolbar-shell {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            box-shadow: 0 14px 36px rgba(15, 23, 42, 0.06);
            padding: 16px 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
        }
        .toolbar-actions {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }
        .toolbar-meta {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-left: auto;
            flex-wrap: wrap;
        }
        .toolbar-search {
            min-width: 260px;
            min-height: 44px;
            border-radius: 12px;
            border: 1px solid #cbd5e1;
            padding: 10px 14px;
            background: #f8fafc;
        }
        .toolbar-count {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #eff6ff;
            color: #1d4ed8;
            padding: 10px 12px;
            border-radius: 12px;
            font-weight: 700;
            min-height: 44px;
        }
        .btn {
            min-height: 44px;
            border-radius: 12px;
            padding: 10px 14px;
            font-weight: 700;
            letter-spacing: 0.01em;
        }
        .btn-primary { background: linear-gradient(135deg, #2563eb 0%, #4f46e5 100%); }
        .btn-success { background: linear-gradient(135deg, #059669 0%, #10b981 100%); }
        .btn-warning { background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%); color: #111827; }
        .btn-danger { background: linear-gradient(135deg, #dc2626 0%, #ef4444 100%); }
        .btn-secondary { background: linear-gradient(135deg, #475569 0%, #64748b 100%); }
        .btn-terminal { background: linear-gradient(135deg, #0f172a 0%, #111827 100%); color: #86efac; }
        .file-list {
            padding: 18px 22px 10px;
        }
        .file-list table {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.06);
        }
        thead {
            background: #f8fafc;
        }
        th {
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.08em;
            color: #64748b;
            padding: 16px 14px;
        }
        td {
            padding: 16px 14px;
            vertical-align: middle;
        }
        .file-row:hover {
            background: #f8fbff;
        }
        .file-name-cell {
            display: flex;
            align-items: flex-start;
            gap: 14px;
        }
        .file-icon-badge {
            width: 42px;
            height: 42px;
            border-radius: 14px;
            background: linear-gradient(135deg, #e0e7ff 0%, #dbeafe 100%);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 21px;
            flex-shrink: 0;
        }
        .file-main {
            min-width: 0;
            display: flex;
            flex-direction: column;
            gap: 7px;
        }
        .file-title-link,
        .file-title-text {
            font-size: 15px;
            line-height: 1.4;
            font-weight: 700;
            color: #0f172a;
            text-decoration: none;
            word-break: break-word;
        }
        .file-title-link:hover {
            color: #2563eb;
        }
        .file-meta-row {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }
        .permission-badge {
            margin-left: 0;
            padding: 5px 8px;
            border-radius: 999px;
            font-size: 11px;
            letter-spacing: 0.02em;
        }
        .badge-r { background: #dbeafe; color: #1d4ed8; }
        .badge-w { background: #dcfce7; color: #15803d; }
        .badge-denied { background: #fee2e2; color: #b91c1c; }
        .action-group {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }
        .action-group .btn {
            min-width: 42px;
            min-height: 42px;
            padding: 8px 10px;
            justify-content: center;
        }
        .empty-search {
            display: none;
            margin-top: 16px;
            background: #ffffff;
            border: 1px dashed #cbd5e1;
            border-radius: 16px;
            padding: 20px;
            text-align: center;
            color: #64748b;
            font-weight: 600;
        }
        .empty-search.active { display: block; }
        .status-bar {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            padding: 0 22px 24px;
        }
        .status-pill {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            color: #334155;
            border-radius: 999px;
            padding: 10px 14px;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.05);
            font-size: 13px;
        }
        .bulk-actions {
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            box-shadow: 0 22px 56px rgba(15, 23, 42, 0.2);
        }
        .terminal-container {
            border-top: 1px solid rgba(59, 130, 246, 0.35);
            box-shadow: 0 -18px 45px rgba(2, 6, 23, 0.65);
        }
        .terminal-header {
            background: linear-gradient(135deg, #111827 0%, #0f172a 100%);
        }
        .upload-modal-content,
        .modal-content {
            border-radius: 20px;
        }
        body.device-tablet .overview-strip {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
        body.device-mobile .overview-strip {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        body.device-mobile .toolbar-meta,
        body.device-tablet .toolbar-meta,
        body.device-mobile .toolbar-actions,
        body.device-tablet .toolbar-actions {
            width: 100%;
        }
        body.device-mobile .toolbar-search,
        body.device-tablet .toolbar-search {
            flex: 1 1 100%;
            min-width: 0;
            width: 100%;
        }
        body.device-mobile .toolbar-count,
        body.device-tablet .toolbar-count {
            width: auto;
        }
        @media (max-width: 1024px) {
            .overview-strip {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }
        @media (max-width: 768px) {
            .container {
                border-radius: 16px;
            }
            .overview-strip {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                padding: 16px 16px 0;
            }
            .path-bar,
            .actions-bar,
            .file-list,
            .status-bar {
                margin-left: 16px;
                margin-right: 16px;
                padding-left: 0;
                padding-right: 0;
            }
            .path-bar {
                padding: 16px;
            }
            .toolbar-shell {
                padding: 14px;
            }
            .toolbar-actions .btn,
            .toolbar-meta .toolbar-count,
            .toolbar-meta .toolbar-search,
            .path-controls .btn,
            .path-controls .path-input {
                width: 100%;
            }
            .file-list table,
            .file-list thead,
            .file-list tbody,
            .file-list tr,
            .file-list th,
            .file-list td {
                display: block;
                width: 100%;
            }
            .file-list thead {
                display: none;
            }
            .file-row {
                margin-bottom: 12px;
                border: 1px solid #e2e8f0;
                border-radius: 18px;
                background: #ffffff;
                padding: 12px 14px;
                box-shadow: 0 10px 24px rgba(15,23,42,0.06);
            }
            .file-row td {
                border: none;
                padding: 7px 0;
            }
            .file-row td[data-label]::before {
                content: attr(data-label);
                display: block;
                font-size: 11px;
                letter-spacing: 0.08em;
                text-transform: uppercase;
                color: #94a3b8;
                margin-bottom: 6px;
                font-weight: 800;
            }
            .file-row td[data-label="Select"]::before {
                display: none;
            }
            .action-group {
                justify-content: flex-start;
            }
            .action-group .btn {
                flex: 1 1 calc(25% - 8px);
            }
            .status-bar {
                padding-bottom: 18px;
            }
            .terminal-container {
                height: 62vh;
            }
        }
        @media (max-width: 520px) {
            .overview-strip {
                grid-template-columns: 1fr;
            }
            .header {
                padding: 20px 16px;
            }
            .header h1 {
                font-size: 22px;
            }
            .header-subtitle {
                font-size: 11px;
                letter-spacing: 0.06em;
            }
            .path-top-row {
                align-items: flex-start;
            }
            .path-title {
                font-size: 16px;
            }
            .path-chip-row {
                gap: 8px;
            }
            .path-chip,
            .toolbar-count,
            .status-pill {
                width: 100%;
                justify-content: center;
            }
            .action-group .btn {
                flex: 1 1 calc(33.33% - 8px);
            }
            .bulk-actions {
                left: 12px;
                right: 12px;
                bottom: 12px;
                padding: 16px;
            }
        }

    </style>
    
    <style>
        /* Compact professional file manager override */
        body {
            background: linear-gradient(180deg, #5b7cfa 0%, #6f56d9 42%, #edf1f7 42%, #f8fafc 100%) fixed;
        }
        .container {
            max-width: 1480px;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 24px 60px rgba(15, 23, 42, 0.18);
        }
        .header {
            padding: 18px 22px;
            gap: 14px;
            align-items: flex-start;
            background: linear-gradient(135deg, rgba(91, 124, 250, 0.96) 0%, rgba(111, 86, 217, 0.96) 100%);
        }
        .header h1 {
            font-size: clamp(24px, 3vw, 34px);
            line-height: 1.15;
            letter-spacing: -0.02em;
        }
        .header-subtitle {
            font-size: 12px;
            line-height: 1.35;
            max-width: 560px;
            opacity: 0.92;
        }
        .header-right {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
            margin-left: auto;
        }
        .session-info {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 14px;
            min-height: 44px;
            font-size: 13px;
            border-radius: 14px;
            background: rgba(255,255,255,0.12);
            backdrop-filter: blur(8px);
        }
        .btn-logout {
            width: auto;
            min-width: 124px;
            min-height: 44px;
            padding: 10px 16px;
            border-radius: 14px;
            box-shadow: inset 0 0 0 1px rgba(255,255,255,0.12);
            white-space: nowrap;
        }
        .overview-strip {
            grid-template-columns: repeat(auto-fit, minmax(165px, 1fr));
            gap: 12px;
            padding: 14px 18px 0;
            background: #f5f7fb;
        }
        .overview-card {
            min-height: 78px;
            padding: 12px 14px;
            border-radius: 18px;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06);
        }
        .overview-icon {
            width: 38px;
            height: 38px;
            flex: 0 0 38px;
            font-size: 18px;
            border-radius: 12px;
        }
        .overview-label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #667085;
        }
        .overview-value {
            font-size: 22px;
            line-height: 1.1;
            margin-top: 4px;
        }
        .path-bar,
        .actions-bar,
        .file-list,
        .status-bar {
            margin-left: 18px;
            margin-right: 18px;
        }
        .path-bar,
        .toolbar-shell,
        .file-list table {
            border-radius: 18px;
            border: 1px solid #e4e7ec;
            box-shadow: 0 10px 26px rgba(15, 23, 42, 0.05);
        }
        .path-bar {
            padding: 14px;
        }
        .path-top-row {
            align-items: flex-start;
            margin-bottom: 10px;
        }
        .path-title {
            font-size: 20px;
            line-height: 1.2;
        }
        .path-caption {
            font-size: 13px;
            line-height: 1.45;
            max-width: 680px;
        }
        .path-chip-row {
            gap: 8px;
        }
        .path-chip {
            font-size: 12px;
            padding: 7px 10px;
            font-weight: 600;
        }
        .path-controls {
            gap: 10px;
        }
        .path-input {
            flex: 1 1 340px;
            min-height: 44px;
            padding: 10px 12px;
        }
        .breadcrumb {
            padding: 10px 12px;
            gap: 8px;
        }
        .breadcrumb a {
            padding: 7px 9px;
            font-size: 13px;
        }
        .actions-bar {
            margin-top: 14px;
        }
        .toolbar-shell {
            padding: 14px;
            gap: 12px;
        }
        .toolbar-actions,
        .toolbar-meta {
            gap: 10px;
        }
        .toolbar-search {
            min-width: 240px;
            min-height: 42px;
            padding: 10px 12px;
        }
        .toolbar-count {
            min-height: 42px;
            padding: 8px 12px;
            font-size: 13px;
        }
        .btn {
            min-height: 42px;
            padding: 10px 14px;
            border-radius: 12px;
            font-size: 14px;
        }
        .file-list {
            padding: 14px 18px 14px;
        }
        .file-list table {
            overflow: hidden;
            background: #ffffff;
        }
        thead {
            background: #f8fafc;
        }
        th {
            padding: 14px 12px;
            font-size: 11px;
            letter-spacing: 0.06em;
        }
        td {
            padding: 14px 12px;
        }
        .file-row:hover {
            background: #f8fbff;
        }
        .file-name-cell {
            gap: 10px;
        }
        .file-icon-badge {
            width: 36px;
            height: 36px;
            flex: 0 0 36px;
            font-size: 17px;
            border-radius: 10px;
        }
        .file-title-link,
        .file-title-text {
            font-size: 14px;
            line-height: 1.35;
        }
        .file-meta-row {
            margin-top: 5px;
        }
        .permission-badge {
            padding: 4px 8px;
            font-size: 10px;
        }
        .action-group {
            gap: 6px;
            justify-content: flex-end;
        }
        .action-group .btn {
            min-width: 40px;
            min-height: 38px;
            padding: 8px 10px;
            font-size: 13px;
        }
        .status-bar {
            padding: 0 18px 18px;
            gap: 8px;
        }
        .status-pill {
            padding: 8px 10px;
            font-size: 12px;
        }
        .bulk-actions {
            border-radius: 18px;
            box-shadow: 0 18px 42px rgba(15,23,42,0.18);
        }
        .terminal-container {
            box-shadow: 0 -18px 42px rgba(15,23,42,0.42);
        }
        @media (max-width: 900px) {
            .overview-strip {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
            .header-right {
                width: 100%;
                margin-left: 0;
            }
        }
        @media (max-width: 768px) {
            body {
                padding: 10px;
            }
            .container {
                border-radius: 20px;
            }
            .header {
                padding: 16px 14px;
            }
            .header-subtitle {
                font-size: 11px;
                max-width: none;
            }
            .header-right {
                width: 100%;
                justify-content: space-between;
                align-items: center;
                gap: 10px;
            }
            .session-info {
                flex: 1 1 180px;
                min-width: 0;
                font-size: 12px;
                line-height: 1.4;
            }
            .btn-logout {
                min-width: 116px;
                padding: 10px 14px;
            }
            .overview-strip {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 10px;
                padding: 12px 14px 0;
            }
            .overview-card {
                min-height: 66px;
                padding: 10px 12px;
            }
            .overview-icon {
                width: 34px;
                height: 34px;
                flex-basis: 34px;
                font-size: 16px;
            }
            .overview-value {
                font-size: 20px;
            }
            .path-bar,
            .actions-bar,
            .file-list,
            .status-bar {
                margin-left: 14px;
                margin-right: 14px;
            }
            .path-top-row,
            .path-controls,
            .toolbar-meta {
                flex-direction: column;
                align-items: stretch;
            }
            .path-chip-row {
                width: 100%;
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
            .path-chip {
                justify-content: center;
                text-align: center;
            }
            .path-input,
            .path-controls .btn,
            .toolbar-search,
            .toolbar-count {
                width: 100%;
                min-width: 0;
            }
            .toolbar-shell {
                padding: 12px;
            }
            .toolbar-actions {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                width: 100%;
                gap: 8px;
            }
            .toolbar-actions .btn {
                width: 100%;
                justify-content: center;
            }
            .toolbar-meta {
                width: 100%;
                margin-left: 0;
            }
            .breadcrumb {
                overflow-x: auto;
                white-space: nowrap;
                flex-wrap: nowrap;
                -webkit-overflow-scrolling: touch;
            }
            .breadcrumb a {
                flex: 0 0 auto;
            }
            .file-list table,
            .file-list thead,
            .file-list tbody,
            .file-list tr,
            .file-list th,
            .file-list td {
                display: block;
                width: 100%;
            }
            .file-list thead {
                display: none;
            }
            .file-row {
                border: 1px solid #e6e9f2;
                border-radius: 16px;
                background: #ffffff;
                margin-bottom: 12px;
                padding: 12px 14px;
                box-shadow: 0 6px 18px rgba(30,41,59,0.05);
            }
            .file-row td {
                border: none;
                padding: 7px 0;
            }
            .file-row td[data-label]::before {
                content: attr(data-label);
                display: block;
                margin-bottom: 4px;
                font-size: 10px;
                color: #667085;
                text-transform: uppercase;
                letter-spacing: 0.05em;
                font-weight: 700;
            }
            .file-row td[data-label="Select"]::before {
                display: none;
            }
            .action-group {
                justify-content: flex-start;
            }
            .action-group .btn {
                flex: 1 1 calc(25% - 6px);
            }
            .status-bar {
                display: none;
            }
        }
        @media (max-width: 420px) {
            .header h1 {
                font-size: 20px;
            }
            .header-right {
                flex-direction: column;
                align-items: stretch;
            }
            .btn-logout {
                width: 100%;
                justify-content: center;
            }
            .overview-strip {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
            .path-title {
                font-size: 18px;
            }
            .path-caption {
                font-size: 12px;
            }
            .path-chip-row {
                grid-template-columns: 1fr;
            }
            .toolbar-actions {
                grid-template-columns: 1fr 1fr;
            }
            .action-group .btn {
                flex: 1 1 calc(50% - 6px);
            }
        }
    </style>


    <style>
        /* Reference file manager table UI override */
        body {
            background: #f6f3f6;
            padding: 24px;
            color: #1f2937;
        }
        .container {
            max-width: 1280px;
            background: #ffffff;
            border: 1px solid #d5d9df;
            border-radius: 14px;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.05);
            overflow: hidden;
        }
        .header {
            margin: 18px 18px 0;
            padding: 22px 24px;
            display: block;
            background: transparent;
            border-bottom: none;
            color: #111827;
        }
        .header-brand {
            display: block;
            background: #d8dde2;
            border: 1px solid #cfd5db;
            border-radius: 6px;
            padding: 16px 20px;
            text-align: center;
        }
        .header-subtitle {
            display: none;
        }
        .header h1 {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0;
            margin: 0;
            font-size: 28px;
            font-weight: 700;
            color: #111827;
        }
        .header-right {
            margin-top: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }
        .session-info {
            background: transparent;
            border: none;
            padding: 0;
            border-radius: 0;
            color: #4b5563;
            font-size: 13px;
            line-height: 1.5;
        }
        .btn-logout {
            min-height: 40px;
            padding: 8px 14px;
            border-radius: 6px;
            border: 1px solid #5f6975;
            background: #5f6975;
            color: #ffffff;
            font-weight: 600;
            box-shadow: none;
        }
        .btn-logout:hover {
            background: #4b5563;
            color: #ffffff;
        }
        .overview-strip {
            display: none;
        }
        .path-bar {
            margin: 16px 18px 0;
            padding: 12px 14px;
            background: #fbfbfc;
            border: 1px solid #e4e7ec;
            border-radius: 8px;
            box-shadow: none;
        }
        .path-top-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 8px;
        }
        .path-title {
            font-size: 16px;
            font-weight: 700;
            color: #111827;
        }
        .path-caption {
            font-size: 12px;
            color: #6b7280;
        }
        .path-chip-row {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        .path-chip {
            background: #eef1f4;
            color: #4b5563;
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
        }
        .path-controls {
            display: flex;
            gap: 10px;
            align-items: center;
            margin-bottom: 10px;
        }
        .path-input {
            min-height: 40px;
            padding: 9px 12px;
            border: 1px solid #cfd5db;
            border-radius: 6px;
            background: #ffffff;
            box-shadow: none;
        }
        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
            padding: 0;
            margin-top: 0;
            background: transparent;
            border: none;
        }
        .breadcrumb a {
            padding: 4px 8px;
            background: transparent;
            border: none;
            border-radius: 4px;
            color: #374151;
            font-size: 13px;
            font-weight: 600;
        }
        .actions-bar {
            margin: 14px 18px 0;
            padding: 0;
            background: transparent;
            border: none;
        }
        .toolbar-shell {
            display: flex;
            flex-direction: column;
            gap: 12px;
            padding: 0;
            background: transparent;
            border: none;
            box-shadow: none;
        }
        .toolbar-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }
        .btn {
            min-height: 38px;
            padding: 8px 14px;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 600;
            box-shadow: none !important;
            transform: none !important;
        }
        .btn-primary,
        .btn-success,
        .btn-terminal,
        .btn-secondary,
        .btn-danger,
        .btn-warning {
            background: #5f6975;
            color: #ffffff;
            border: 1px solid #5f6975;
        }
        .toolbar-meta.datatable-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
            margin-left: 0;
            width: 100%;
            padding: 0 2px;
        }
        .entries-control,
        .search-control {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #111827;
            font-size: 14px;
        }
        .rows-select,
        .toolbar-search {
            min-height: 36px;
            padding: 6px 10px;
            border: 1px solid #cfd5db;
            border-radius: 4px;
            background: #ffffff;
            color: #111827;
            box-shadow: none;
        }
        .rows-select {
            min-width: 58px;
        }
        .toolbar-search {
            min-width: 220px;
        }
        .toolbar-count {
            display: none;
        }
        .file-list {
            margin: 0 18px;
            padding: 8px 0 0;
        }
        .file-list table {
            width: 100%;
            border-collapse: collapse;
            background: #ffffff;
            border: none;
            border-radius: 0;
            box-shadow: none;
            overflow: visible;
        }
        thead {
            background: #ffffff;
        }
        th {
            padding: 12px 10px;
            border-bottom: 2px solid #111827;
            color: #111827;
            font-size: 13px;
            font-weight: 700;
            text-transform: none;
            letter-spacing: 0;
        }
        td {
            padding: 11px 10px;
            border-bottom: 1px solid #eaecf0;
            vertical-align: middle;
            font-size: 14px;
            color: #111827;
        }
        .file-row:hover {
            background: #fafbfc;
        }
        .file-name-cell {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .file-icon-badge {
            width: 30px;
            height: 30px;
            flex: 0 0 30px;
            border-radius: 6px;
            background: #eef2f7;
            font-size: 14px;
        }
        .file-title-link,
        .file-title-text {
            font-size: 14px;
            font-weight: 600;
            color: #111827;
            text-decoration: none;
        }
        .file-meta-row {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
            margin-top: 4px;
        }
        .permission-badge {
            margin-left: 0;
            padding: 3px 7px;
            border-radius: 999px;
            font-size: 10px;
        }
        .action-group {
            display: flex;
            gap: 6px;
            justify-content: flex-start;
            flex-wrap: wrap;
        }
        .action-group .btn {
            min-width: 38px;
            min-height: 34px;
            padding: 6px 10px;
            font-size: 12px;
        }
        .empty-search {
            display: none;
            margin-top: 10px;
            padding: 12px;
            text-align: center;
            border: 1px dashed #cfd5db;
            border-radius: 6px;
            background: #ffffff;
            color: #6b7280;
        }
        .empty-search.active {
            display: block;
        }
        .table-footer-bar {
            margin: 0 18px 18px;
            padding: 12px 2px 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
            color: #4b5563;
            font-size: 14px;
        }
        .table-pagination {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .page-btn,
        .page-badge {
            min-height: 34px;
            padding: 6px 12px;
            border: 1px solid #cfd5db;
            background: #ffffff;
            color: #374151;
            border-radius: 4px;
            font-size: 14px;
        }
        .page-btn[disabled] {
            opacity: 0.5;
            cursor: not-allowed;
        }
        .page-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 38px;
            background: #f3f4f6;
            font-weight: 700;
        }
        .status-bar {
            display: none;
        }
        .bulk-actions {
            border-radius: 8px;
            border: 1px solid #d1d5db;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.12);
        }
        @media (max-width: 900px) {
            .toolbar-meta.datatable-controls,
            .path-top-row,
            .path-controls {
                flex-direction: column;
                align-items: stretch;
            }
            .search-control,
            .entries-control {
                width: 100%;
                justify-content: space-between;
            }
            .toolbar-search,
            .rows-select,
            .path-input,
            .path-controls .btn {
                width: 100%;
                min-width: 0;
            }
        }
        @media (max-width: 768px) {
            body {
                padding: 10px;
            }
            .container {
                border-radius: 10px;
            }
            .header,
            .path-bar,
            .actions-bar,
            .file-list,
            .table-footer-bar {
                margin-left: 12px;
                margin-right: 12px;
            }
            .header {
                padding: 12px 0 0;
            }
            .header-brand {
                padding: 14px 12px;
            }
            .header h1 {
                font-size: 24px;
            }
            .header-right {
                flex-direction: column;
                align-items: stretch;
                padding: 0 4px;
            }
            .btn-logout {
                width: 100%;
            }
            .toolbar-actions {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
            .toolbar-actions .btn {
                width: 100%;
                justify-content: center;
            }
            .file-list table,
            .file-list thead,
            .file-list tbody,
            .file-list tr,
            .file-list th,
            .file-list td {
                display: block;
                width: 100%;
            }
            .file-list thead {
                display: none;
            }
            .file-row {
                border: 1px solid #e5e7eb;
                border-radius: 8px;
                margin-bottom: 10px;
                padding: 10px 12px;
                background: #ffffff;
            }
            .file-row td {
                border: none;
                padding: 6px 0;
            }
            .file-row td[data-label]::before {
                content: attr(data-label);
                display: block;
                margin-bottom: 4px;
                font-size: 10px;
                font-weight: 700;
                color: #6b7280;
                text-transform: uppercase;
                letter-spacing: 0.04em;
            }
            .file-row td[data-label="Select"]::before {
                display: none;
            }
            .action-group .btn {
                flex: 1 1 calc(33.333% - 6px);
            }
            .table-footer-bar {
                padding-top: 8px;
                flex-direction: column;
                align-items: stretch;
            }
            .table-pagination {
                justify-content: space-between;
            }
        }
        @media (max-width: 480px) {
            .toolbar-actions {
                grid-template-columns: 1fr;
            }
            .action-group .btn {
                flex: 1 1 calc(50% - 6px);
            }
        }
    </style>


    <style>
        /* Compact professional file manager override */
        body {
            background: linear-gradient(180deg, #5b7cfa 0%, #6f56d9 42%, #edf1f7 42%, #f8fafc 100%) fixed;
        }
        .container {
            max-width: 1480px;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 24px 60px rgba(15, 23, 42, 0.18);
        }
        .header {
            padding: 18px 22px;
            gap: 14px;
            align-items: flex-start;
            background: linear-gradient(135deg, rgba(91, 124, 250, 0.96) 0%, rgba(111, 86, 217, 0.96) 100%);
        }
        .header h1 {
            font-size: clamp(24px, 3vw, 34px);
            line-height: 1.15;
            letter-spacing: -0.02em;
        }
        .header-subtitle {
            font-size: 12px;
            line-height: 1.35;
            max-width: 560px;
            opacity: 0.92;
        }
        .header-right {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
            margin-left: auto;
        }
        .session-info {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 14px;
            min-height: 44px;
            font-size: 13px;
            border-radius: 14px;
            background: rgba(255,255,255,0.12);
            backdrop-filter: blur(8px);
        }
        .btn-logout {
            width: auto;
            min-width: 124px;
            min-height: 44px;
            padding: 10px 16px;
            border-radius: 14px;
            box-shadow: inset 0 0 0 1px rgba(255,255,255,0.12);
            white-space: nowrap;
        }
        .overview-strip {
            grid-template-columns: repeat(auto-fit, minmax(165px, 1fr));
            gap: 12px;
            padding: 14px 18px 0;
            background: #f5f7fb;
        }
        .overview-card {
            min-height: 78px;
            padding: 12px 14px;
            border-radius: 18px;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06);
        }
        .overview-icon {
            width: 38px;
            height: 38px;
            flex: 0 0 38px;
            font-size: 18px;
            border-radius: 12px;
        }
        .overview-label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #667085;
        }
        .overview-value {
            font-size: 22px;
            line-height: 1.1;
            margin-top: 4px;
        }
        .path-bar,
        .actions-bar,
        .file-list,
        .status-bar {
            margin-left: 18px;
            margin-right: 18px;
        }
        .path-bar,
        .toolbar-shell,
        .file-list table {
            border-radius: 18px;
            border: 1px solid #e4e7ec;
            box-shadow: 0 10px 26px rgba(15, 23, 42, 0.05);
        }
        .path-bar {
            padding: 14px;
        }
        .path-top-row {
            align-items: flex-start;
            margin-bottom: 10px;
        }
        .path-title {
            font-size: 20px;
            line-height: 1.2;
        }
        .path-caption {
            font-size: 13px;
            line-height: 1.45;
            max-width: 680px;
        }
        .path-chip-row {
            gap: 8px;
        }
        .path-chip {
            font-size: 12px;
            padding: 7px 10px;
            font-weight: 600;
        }
        .path-controls {
            gap: 10px;
        }
        .path-input {
            flex: 1 1 340px;
            min-height: 44px;
            padding: 10px 12px;
        }
        .breadcrumb {
            padding: 10px 12px;
            gap: 8px;
        }
        .breadcrumb a {
            padding: 7px 9px;
            font-size: 13px;
        }
        .actions-bar {
            margin-top: 14px;
        }
        .toolbar-shell {
            padding: 14px;
            gap: 12px;
        }
        .toolbar-actions,
        .toolbar-meta {
            gap: 10px;
        }
        .toolbar-search {
            min-width: 240px;
            min-height: 42px;
            padding: 10px 12px;
        }
        .toolbar-count {
            min-height: 42px;
            padding: 8px 12px;
            font-size: 13px;
        }
        .btn {
            min-height: 42px;
            padding: 10px 14px;
            border-radius: 12px;
            font-size: 14px;
        }
        .file-list {
            padding: 14px 18px 14px;
        }
        .file-list table {
            overflow: hidden;
            background: #ffffff;
        }
        thead {
            background: #f8fafc;
        }
        th {
            padding: 14px 12px;
            font-size: 11px;
            letter-spacing: 0.06em;
        }
        td {
            padding: 14px 12px;
        }
        .file-row:hover {
            background: #f8fbff;
        }
        .file-name-cell {
            gap: 10px;
        }
        .file-icon-badge {
            width: 36px;
            height: 36px;
            flex: 0 0 36px;
            font-size: 17px;
            border-radius: 10px;
        }
        .file-title-link,
        .file-title-text {
            font-size: 14px;
            line-height: 1.35;
        }
        .file-meta-row {
            margin-top: 5px;
        }
        .permission-badge {
            padding: 4px 8px;
            font-size: 10px;
        }
        .action-group {
            gap: 6px;
            justify-content: flex-end;
        }
        .action-group .btn {
            min-width: 40px;
            min-height: 38px;
            padding: 8px 10px;
            font-size: 13px;
        }
        .status-bar {
            padding: 0 18px 18px;
            gap: 8px;
        }
        .status-pill {
            padding: 8px 10px;
            font-size: 12px;
        }
        .bulk-actions {
            border-radius: 18px;
            box-shadow: 0 18px 42px rgba(15,23,42,0.18);
        }
        .terminal-container {
            box-shadow: 0 -18px 42px rgba(15,23,42,0.42);
        }
        @media (max-width: 900px) {
            .overview-strip {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
            .header-right {
                width: 100%;
                margin-left: 0;
            }
        }
        @media (max-width: 768px) {
            body {
                padding: 10px;
            }
            .container {
                border-radius: 20px;
            }
            .header {
                padding: 16px 14px;
            }
            .header-subtitle {
                font-size: 11px;
                max-width: none;
            }
            .header-right {
                width: 100%;
                justify-content: space-between;
                align-items: center;
                gap: 10px;
            }
            .session-info {
                flex: 1 1 180px;
                min-width: 0;
                font-size: 12px;
                line-height: 1.4;
            }
            .btn-logout {
                min-width: 116px;
                padding: 10px 14px;
            }
            .overview-strip {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 10px;
                padding: 12px 14px 0;
            }
            .overview-card {
                min-height: 66px;
                padding: 10px 12px;
            }
            .overview-icon {
                width: 34px;
                height: 34px;
                flex-basis: 34px;
                font-size: 16px;
            }
            .overview-value {
                font-size: 20px;
            }
            .path-bar,
            .actions-bar,
            .file-list,
            .status-bar {
                margin-left: 14px;
                margin-right: 14px;
            }
            .path-top-row,
            .path-controls,
            .toolbar-meta {
                flex-direction: column;
                align-items: stretch;
            }
            .path-chip-row {
                width: 100%;
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
            .path-chip {
                justify-content: center;
                text-align: center;
            }
            .path-input,
            .path-controls .btn,
            .toolbar-search,
            .toolbar-count {
                width: 100%;
                min-width: 0;
            }
            .toolbar-shell {
                padding: 12px;
            }
            .toolbar-actions {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                width: 100%;
                gap: 8px;
            }
            .toolbar-actions .btn {
                width: 100%;
                justify-content: center;
            }
            .toolbar-meta {
                width: 100%;
                margin-left: 0;
            }
            .breadcrumb {
                overflow-x: auto;
                white-space: nowrap;
                flex-wrap: nowrap;
                -webkit-overflow-scrolling: touch;
            }
            .breadcrumb a {
                flex: 0 0 auto;
            }
            .file-list table,
            .file-list thead,
            .file-list tbody,
            .file-list tr,
            .file-list th,
            .file-list td {
                display: block;
                width: 100%;
            }
            .file-list thead {
                display: none;
            }
            .file-row {
                border: 1px solid #e6e9f2;
                border-radius: 16px;
                background: #ffffff;
                margin-bottom: 12px;
                padding: 12px 14px;
                box-shadow: 0 6px 18px rgba(30,41,59,0.05);
            }
            .file-row td {
                border: none;
                padding: 7px 0;
            }
            .file-row td[data-label]::before {
                content: attr(data-label);
                display: block;
                margin-bottom: 4px;
                font-size: 10px;
                color: #667085;
                text-transform: uppercase;
                letter-spacing: 0.05em;
                font-weight: 700;
            }
            .file-row td[data-label="Select"]::before {
                display: none;
            }
            .action-group {
                justify-content: flex-start;
            }
            .action-group .btn {
                flex: 1 1 calc(25% - 6px);
            }
            .status-bar {
                display: none;
            }
        }
        @media (max-width: 420px) {
            .header h1 {
                font-size: 20px;
            }
            .header-right {
                flex-direction: column;
                align-items: stretch;
            }
            .btn-logout {
                width: 100%;
                justify-content: center;
            }
            .overview-strip {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
            .path-title {
                font-size: 18px;
            }
            .path-caption {
                font-size: 12px;
            }
            .path-chip-row {
                grid-template-columns: 1fr;
            }
            .toolbar-actions {
                grid-template-columns: 1fr 1fr;
            }
            .action-group .btn {
                flex: 1 1 calc(50% - 6px);
            }
        }
    </style>

    <style>
        /* Classic professional theme */
        :root {
            --classic-bg: #e9edf2;
            --classic-surface: #ffffff;
            --classic-surface-soft: #f7f8fa;
            --classic-border: #d7dce4;
            --classic-border-strong: #c5ccd6;
            --classic-text: #1e293b;
            --classic-muted: #667085;
            --classic-primary: #243b63;
            --classic-primary-soft: #edf2f8;
            --classic-primary-strong: #1b2f52;
            --classic-success: #2f5d50;
            --classic-warning: #9a6700;
            --classic-danger: #8f2d2d;
            --classic-shadow: 0 12px 28px rgba(15, 23, 42, 0.08);
            --classic-shadow-soft: 0 6px 16px rgba(15, 23, 42, 0.05);
            --classic-radius-lg: 16px;
            --classic-radius-md: 12px;
            --classic-radius-sm: 10px;
        }

        body {
            background: linear-gradient(180deg, #f1f4f8 0%, #e6ebf1 100%) !important;
            color: var(--classic-text);
            font-family: 'Segoe UI', Tahoma, Arial, sans-serif;
        }
        .container,
        .login-container {
            background: var(--classic-surface) !important;
            border: 1px solid var(--classic-border);
            box-shadow: 0 20px 50px rgba(15, 23, 42, 0.10) !important;
        }
        .container {
            border-radius: 22px !important;
            overflow: hidden;
        }
        .header {
            background: linear-gradient(180deg, #2f4367 0%, #243b63 100%) !important;
            border-bottom: 1px solid rgba(255,255,255,0.08);
            box-shadow: inset 0 -1px 0 rgba(255,255,255,0.06);
        }
        .header h1,
        .header-brand,
        .header-subtitle,
        .session-info,
        #deviceTypeBadge,
        #osTypeBadge {
            color: #f8fafc !important;
        }
        .session-info {
            background: rgba(255,255,255,0.08) !important;
            border: 1px solid rgba(255,255,255,0.14);
            border-radius: 14px !important;
            box-shadow: none !important;
        }
        .btn,
        .btn-logout,
        .terminal-btn,
        .page-btn {
            border-radius: var(--classic-radius-sm) !important;
            border: 1px solid transparent;
            box-shadow: none !important;
            font-weight: 600;
            transition: background-color 0.18s ease, border-color 0.18s ease, color 0.18s ease, transform 0.18s ease;
        }
        .btn:hover,
        .btn-logout:hover,
        .terminal-btn:hover,
        .page-btn:hover {
            transform: translateY(-1px);
        }
        .btn-primary,
        .btn-logout,
        .page-btn {
            background: var(--classic-primary) !important;
            color: #ffffff !important;
            border-color: var(--classic-primary-strong) !important;
        }
        .btn-primary:hover,
        .btn-logout:hover,
        .page-btn:hover {
            background: var(--classic-primary-strong) !important;
        }
        .btn-success {
            background: var(--classic-success) !important;
            color: #ffffff !important;
            border-color: #294f45 !important;
        }
        .btn-warning {
            background: #b38728 !important;
            color: #ffffff !important;
            border-color: #916b1e !important;
        }
        .btn-danger {
            background: var(--classic-danger) !important;
            color: #ffffff !important;
            border-color: #792626 !important;
        }
        .btn-secondary,
        .terminal-btn {
            background: #eef1f5 !important;
            color: var(--classic-text) !important;
            border-color: var(--classic-border) !important;
        }
        .overview-strip {
            background: #eef2f6 !important;
        }
        .overview-card,
        .path-bar,
        .toolbar-shell,
        .file-list-shell,
        .status-pill,
        .bulk-actions,
        .modal-content,
        .upload-modal-content {
            background: var(--classic-surface) !important;
            border: 1px solid var(--classic-border) !important;
            box-shadow: var(--classic-shadow-soft) !important;
        }
        .overview-card,
        .path-bar,
        .toolbar-shell,
        .file-list-shell,
        .bulk-actions,
        .modal-content,
        .upload-modal-content {
            border-radius: var(--classic-radius-lg) !important;
        }
        .overview-icon,
        .file-icon-badge {
            background: linear-gradient(180deg, #f7f9fb 0%, #edf2f7 100%) !important;
            border: 1px solid var(--classic-border);
            color: var(--classic-primary);
        }
        .overview-label,
        .path-caption,
        .file-list-subtitle,
        .editor-meta,
        .help-text,
        .table-entries-info,
        .path-chip,
        .status-pill,
        .list-stat {
            color: var(--classic-muted) !important;
        }
        .overview-value,
        .path-title,
        .file-list-title,
        .file-title-link,
        .file-title-text,
        .list-stat strong,
        .toolbar-count {
            color: var(--classic-text) !important;
        }
        .path-chip,
        .toolbar-count,
        .list-stat,
        .file-type-badge,
        .file-ext-badge,
        .permission-badge {
            border-radius: 999px !important;
        }
        .path-chip,
        .toolbar-count,
        .list-stat {
            background: var(--classic-surface-soft) !important;
            border: 1px solid var(--classic-border) !important;
        }
        .path-input,
        .toolbar-search,
        .chmod-input,
        .editor-textarea,
        .terminal-input {
            background: #fbfcfd !important;
            border: 1px solid var(--classic-border-strong) !important;
            border-radius: var(--classic-radius-sm) !important;
            color: var(--classic-text) !important;
        }
        .path-input:focus,
        .toolbar-search:focus,
        .chmod-input:focus,
        .editor-textarea:focus,
        .terminal-input:focus {
            outline: none;
            border-color: var(--classic-primary) !important;
            box-shadow: 0 0 0 3px rgba(36, 59, 99, 0.10) !important;
        }
        .file-list-header {
            background: linear-gradient(180deg, #fafbfd 0%, #f5f7fa 100%) !important;
            border-bottom: 1px solid var(--classic-border) !important;
        }
        .file-list thead th {
            background: #f3f5f8 !important;
            color: #334155 !important;
            border-bottom: 1px solid var(--classic-border) !important;
            font-size: 12.5px;
            letter-spacing: 0.03em;
            text-transform: uppercase;
        }
        .file-row:nth-child(even) {
            background: #fbfcfe !important;
        }
        .file-row:hover {
            background: #f4f7fb !important;
        }
        .file-row.selected {
            background: #edf3fb !important;
            box-shadow: inset 4px 0 0 #3a5a8a !important;
        }
        .file-row td,
        .file-list th {
            border-color: #e7ebf1 !important;
        }
        .file-type-dir {
            background: #edf3fb !important;
            color: #28456d !important;
        }
        .file-type-zip {
            background: #fdf2e3 !important;
            color: #975a16 !important;
        }
        .file-type-file,
        .file-ext-badge {
            background: #f5f6f8 !important;
            color: #475467 !important;
            border: 1px solid var(--classic-border) !important;
        }
        .badge-r {
            background: #eaf4ef !important;
            color: #215c47 !important;
        }
        .badge-w {
            background: #edf2fb !important;
            color: #274c84 !important;
        }
        .badge-denied {
            background: #fbeaea !important;
            color: #8f2d2d !important;
        }
        .progress-bar {
            background: #e8edf3 !important;
            border-radius: 999px !important;
            border: 1px solid var(--classic-border);
        }
        .progress-fill {
            background: linear-gradient(90deg, #314d79 0%, #243b63 100%) !important;
        }
        .terminal-container {
            border-top: 3px solid #314d79 !important;
            box-shadow: 0 -16px 36px rgba(15, 23, 42, 0.22) !important;
        }
        .terminal-header,
        .terminal-input-container {
            background: #1f2937 !important;
        }
        .terminal-output,
        .terminal-input {
            background: #111827 !important;
        }
        .terminal-prompt,
        .terminal-cwd {
            color: #9fb6da !important;
        }
        .table-footer-bar,
        .table-pagination {
            position: relative;
            z-index: 6;
        }
        .table-pagination .page-btn {
            pointer-events: auto;
            user-select: none;
            -webkit-user-select: none;
            touch-action: manipulation;
        }
        .page-btn:disabled {
            opacity: 0.55;
            cursor: not-allowed;
            transform: none !important;
            pointer-events: none;
        }
        .empty-search {
            background: #fafbfd !important;
            border: 1px dashed var(--classic-border-strong) !important;
            color: var(--classic-muted) !important;
        }
        .upload-zone {
            background: rgba(36, 59, 99, 0.94) !important;
        }
        @media (max-width: 768px) {
            .container {
                border-radius: 18px !important;
            }
            .header,
            .path-bar,
            .toolbar-shell,
            .file-list-shell {
                border-radius: 14px !important;
            }
            .list-stat {
                min-height: 42px;
            }
            .file-row {
                border-radius: 14px !important;
                border: 1px solid var(--classic-border) !important;
                box-shadow: var(--classic-shadow-soft) !important;
            }
        }
    </style>

    <style>
        :root {
            color-scheme: light;
            --login-surface: rgba(255, 255, 255, 0.96);
            --login-border: rgba(148, 163, 184, 0.24);
            --login-shadow: 0 30px 70px rgba(15, 23, 42, 0.32);
            --login-text: #0f172a;
            --login-muted: #64748b;
            --login-accent: linear-gradient(135deg, #2563eb 0%, #4f46e5 55%, #7c3aed 100%);
        }
        body {
            background:
                radial-gradient(circle at top, rgba(96, 165, 250, 0.20), transparent 34%),
                radial-gradient(circle at bottom right, rgba(167, 139, 250, 0.18), transparent 28%),
                linear-gradient(180deg, #0f172a 0%, #111827 48%, #0b1120 100%);
        }
        .login-container {
            position: relative;
            max-width: 460px;
            padding: 34px 32px;
            border: 1px solid var(--login-border);
            background: var(--login-surface);
            box-shadow: var(--login-shadow);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
        }
        .login-container::before {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: inherit;
            padding: 1px;
            background: linear-gradient(135deg, rgba(255,255,255,0.72), rgba(148,163,184,0.14));
            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
            pointer-events: none;
        }
        .login-header h1 {
            color: var(--login-text);
            font-size: clamp(1.6rem, 4vw, 2rem);
            letter-spacing: -0.03em;
        }
        .login-header p,
        .security-info {
            color: var(--login-muted);
        }
        .login-header .icon {
            width: 78px;
            height: 78px;
            margin: 0 auto 16px;
            display: grid;
            place-items: center;
            border-radius: 24px;
            background: linear-gradient(135deg, rgba(37,99,235,0.14), rgba(124,58,237,0.12));
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.8);
        }
        .form-group input {
            min-height: 50px;
            border-radius: 14px;
            border-color: #cbd5e1;
            background: #f8fafc;
            color: var(--login-text);
        }
        .form-group input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
        }
        .btn-login {
            min-height: 50px;
            border-radius: 14px;
            background: var(--login-accent);
            box-shadow: 0 18px 36px rgba(79, 70, 229, 0.26);
        }
        .btn-login:hover {
            box-shadow: 0 22px 38px rgba(79, 70, 229, 0.32);
        }
        .error-message,
        .security-info {
            border-radius: 14px;
        }
        .security-info {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            line-height: 1.6;
        }
        @media (max-width: 768px) {
            body {
                padding: 16px;
            }
            .login-container {
                padding: 28px 22px;
                border-radius: 22px;
            }
            .toggle-password {
                top: 40px;
            }
        }
        @media (max-width: 480px) {
            body {
                padding: 10px;
            }
            .login-container {
                padding: 24px 18px;
                border-radius: 18px;
            }
            .login-header .icon {
                width: 68px;
                height: 68px;
                font-size: 48px;
            }
            .form-group label {
                font-size: 13px;
            }
        }
    </style>
</head>
    <body class="<?php echo htmlspecialchars($deviceCssClass); ?>">
        <div class="login-container">
            <div class="login-header">
                <div class="icon">🔐</div>
                <h1>Secure File Manager</h1>
                <p>Masukkan password untuk mengakses file manager</p>
            </div>
            
            <?php if (isset($login_error)): ?>
                <div class="error-message">⚠️ <?php echo htmlspecialchars($login_error); ?></div>
            <?php endif; ?>
            
            <form method="POST" class="login-form">
                <input type="hidden" name="action" value="login">
                <div class="form-group">
                    <label for="password">🔑 Password</label>
                    <input type="password" id="password" name="password" placeholder="Masukkan password" required autofocus>
                    <span class="toggle-password" onclick="togglePassword()">👁️</span>
                </div>
                <button type="submit" class="btn-login">🚀 Login</button>
            </form>
            
            <div class="security-info">
                <strong>⚠️ Catatan Keamanan:</strong>
                Hash password aktif dengan <code>password_verify()</code><br>
                Password awal bawaan: <code>admin123</code><br>
                <strong style="color: #c33;">WAJIB GANTI HASH</strong> sebelum deploy!
            </div>
        </div>
        
        <script>
            function togglePassword() {
                const input = document.getElementById('password');
                const toggle = document.querySelector('.toggle-password');
                if (input.type === 'password') {
                    input.type = 'text';
                    toggle.textContent = '🙈';
                } else {
                    input.type = 'password';
                    toggle.textContent = '👁️';
                }
            }
        </script>
    </body>
    </html>
    <?php
    exit;
}

// ========================================
// FILE MANAGER CODE (Protected Area)
// ========================================

ini_set('memory_limit', MEMORY_LIMIT);
ini_set('max_execution_time', '300');
set_time_limit(300);

$defaultStartDir = detectDefaultStartDir();
$currentDirInput = isset($_GET['dir']) ? $_GET['dir'] : $defaultStartDir;
$currentDir = resolveExistingPath($currentDirInput, $defaultStartDir);

// Security/path validation
if (!isPathAllowedValue($currentDir)) {
    $currentDir = $defaultStartDir;
}

// ========================================
// COMPRESS HANDLER (FIXED - Using Session Storage)
// ========================================

if (isset($_GET['action']) && $_GET['action'] === 'compress_prepare') {
    prepareApiOutput('application/json; charset=UTF-8');
    
    $items = isset($_POST['items']) ? json_decode($_POST['items'], true) : array();
    $baseDir = resolveExistingPath(arrayGetValue($_POST, 'base_dir', $currentDir), $currentDir);
    
    if (empty($items)) {
        echo json_encode(array('error' => 'No items selected'));
        exit;
    }
    
    // Store in session for SSE to access
    $taskId = uniqid('compress_');
    $_SESSION['compress_tasks'][$taskId] = array(
        'items' => $items,
        'base_dir' => $baseDir,
        'status' => 'pending'
    );
    
    echo json_encode(array('success' => true, 'task_id' => $taskId));
    exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'compress_execute') {
    $taskId = arrayGetValue($_GET, 'task_id', '');
    
    if (empty($taskId) || !isset($_SESSION['compress_tasks'][$taskId])) {
        prepareApiOutput('text/event-stream');
        echo "data: " . json_encode(array('error' => 'Invalid task ID')) . "\n\n";
        flush();
        exit;
    }
    
    $task = $_SESSION['compress_tasks'][$taskId];
    $items = $task['items'];
    $baseDir = resolveExistingPath($task['base_dir'], $currentDir);
    
    prepareApiOutput('text/event-stream');
    header('Cache-Control: no-cache');
    header('Connection: keep-alive');
    header('X-Accel-Buffering: no');
    
    // Clear all output buffers
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    
    // Disable output buffering
    if (function_exists('apache_setenv')) {
        @apache_setenv('no-gzip', '1');
    }
    @ini_set('zlib.output_compression', '0');
    @ini_set('implicit_flush', '1');
    
    try {
        $timestamp = date('Y-m-d_H-i-s');
        $zipName = (count($items) === 1) ? basename($items[0]) : 'bulk_compress_' . $timestamp;
        $zipName = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $zipName);
        $zipName = preg_replace('/\.zip$/i', '', $zipName);
        
        $zipPath = joinPathValue($baseDir, $zipName . '.zip');
        $counter = 1;
        while (file_exists($zipPath)) {
            $zipPath = joinPathValue($baseDir, $zipName . '_' . $counter . '.zip');
            $counter++;
        }
        
        if (getArchiveCompressMethod() !== 'ziparchive') {
            echo "data: " . json_encode(array(
                'current' => 0,
                'total' => max(1, count($items)),
                'percent' => 5,
                'speed' => 0,
                'file' => 'Preparing archive...'
            )) . "\n\n";
            @flush();

            $compressError = '';
            $compressed = runArchiveCompressionFallback($baseDir, $items, $zipPath, $compressError);
            if (!$compressed) {
                echo "data: " . json_encode(array('error' => 'Gagal membuat ZIP: ' . ($compressError !== '' ? $compressError : 'compression method not available'))) . "\n\n";
                @flush();
                exit;
            }

            echo "data: " . json_encode(array(
                'current' => max(1, count($items)),
                'total' => max(1, count($items)),
                'percent' => 100,
                'speed' => 0,
                'file' => basename($zipPath),
                'complete' => true,
                'message' => 'Compression complete!',
                'zipFile' => basename($zipPath),
                'zipSize' => formatSize(filesize($zipPath))
            )) . "\n\n";
            @flush();
            unset($_SESSION['compress_tasks'][$taskId]);
            exit;
        }

        $zip = new ZipArchive();
        $zipResult = $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        
        if ($zipResult !== true) {
            echo "data: " . json_encode(array('error' => 'Cannot create ZIP file: ' . $zipResult)) . "\n\n";
            flush();
            exit;
        }
        
        // Collect all files
        $allFiles = array();
        foreach ($items as $item) {
            $fullPath = joinPathValue($baseDir, $item);
            
            if (!file_exists($fullPath)) {
                continue;
            }
            
            if (is_file($fullPath)) {
                $allFiles[] = array(
                    'path' => $fullPath,
                    'relative' => $item
                );
            } elseif (is_dir($fullPath)) {
                $iterator = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($fullPath, RecursiveDirectoryIterator::SKIP_DOTS),
                    RecursiveIteratorIterator::SELF_FIRST
                );
                
                foreach ($iterator as $file) {
                    if ($file->isFile()) {
                        $filePath = $file->getPathname();
                        $relativePath = $item . DIRECTORY_SEPARATOR . substr($filePath, strlen($fullPath) + 1);
                        $allFiles[] = array(
                            'path' => $filePath,
                            'relative' => $relativePath
                        );
                    }
                }
            }
        }
        
        $totalFiles = count($allFiles);
        
        if ($totalFiles === 0) {
            $zip->close();
            @unlink($zipPath);
            echo "data: " . json_encode(array('error' => 'No files to compress')) . "\n\n";
            flush();
            exit;
        }
        
        $current = 0;
        $startTime = microtime(true);
        
        foreach ($allFiles as $fileInfo) {
            try {
                $filePath = $fileInfo['path'];
                $relativePath = $fileInfo['relative'];
                
                if (!file_exists($filePath) || !is_readable($filePath)) {
                    continue;
                }
                
                $fileSize = @filesize($filePath);
                if ($fileSize === false || $fileSize > MAX_FILE_SIZE) {
                    continue;
                }
                
                // Normalize path separators
                $relativePath = str_replace('\\', '/', $relativePath);
                
                $addResult = $zip->addFile($filePath, $relativePath);
                
                if (!$addResult) {
                    continue;
                }
                
                $current++;
                $elapsed = microtime(true) - $startTime;
                $speed = $elapsed > 0 ? $current / $elapsed : 0;
                
                if ($current % 5 === 0 || $current === $totalFiles) {
                    $percent = round(($current / $totalFiles) * 100, 1);
                    
                    echo "data: " . json_encode(array(
                        'current' => $current,
                        'total' => $totalFiles,
                        'percent' => $percent,
                        'speed' => round($speed, 1),
                        'file' => basename($filePath)
                    )) . "\n\n";
                    
                    @flush();
                    @ob_flush();
                    usleep(1000);
                }
            } catch (Exception $e) {
                continue;
            }
        }
        
        $zip->close();
        
        // Verify ZIP was created
        if (!file_exists($zipPath) || filesize($zipPath) === 0) {
            @unlink($zipPath);
            echo "data: " . json_encode(array('error' => 'Failed to create ZIP file')) . "\n\n";
            flush();
            exit;
        }
        
        echo "data: " . json_encode(array(
            'complete' => true,
            'message' => 'Compression complete!',
            'zipFile' => basename($zipPath),
            'zipSize' => formatSize(filesize($zipPath))
        )) . "\n\n";
        flush();
        
        // Clean up session task
        unset($_SESSION['compress_tasks'][$taskId]);
        
    } catch (Exception $e) {
        echo "data: " . json_encode(array('error' => 'Exception: ' . $e->getMessage())) . "\n\n";
        flush();
    }
    
    exit;
}

// ========================================
// UPLOAD HANDLER
// ========================================

if (isset($_FILES['upload_files']) && UPLOAD_ENABLED) {
    prepareApiOutput('application/json; charset=UTF-8');
    
    $uploadDir = resolveExistingPath(arrayGetValue($_POST, 'upload_dir', $currentDir), $currentDir);
    $uploadResults = array('success' => array(), 'failed' => array());
    
    $files = $_FILES['upload_files'];
    $fileCount = count($files['name']);
    
    for ($i = 0; $i < $fileCount; $i++) {
        $fileName = $files['name'][$i];
        $tmpName = $files['tmp_name'][$i];
        $fileSize = $files['size'][$i];
        $fileError = $files['error'][$i];
        
        if ($fileError !== UPLOAD_ERR_OK) {
            $uploadResults['failed'][] = array(
                'name' => $fileName,
                'reason' => 'Upload error code: ' . $fileError
            );
            continue;
        }
        
        if ($fileSize > MAX_UPLOAD_SIZE) {
            $uploadResults['failed'][] = array(
                'name' => $fileName,
                'reason' => 'File too large (max ' . formatSize(MAX_UPLOAD_SIZE) . ')'
            );
            continue;
        }
        
        // Check extension if not wildcard
        if (firstArrayValue(ALLOWED_EXTENSIONS, '*') !== '*') {
            $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            if (!in_array($ext, ALLOWED_EXTENSIONS)) {
                $uploadResults['failed'][] = array(
                    'name' => $fileName,
                    'reason' => 'File type not allowed'
                );
                continue;
            }
        }
        
        // Generate unique filename if exists
        $targetPath = joinPathValue($uploadDir, $fileName);
        $counter = 1;
        $baseName = pathinfo($fileName, PATHINFO_FILENAME);
        $extension = pathinfo($fileName, PATHINFO_EXTENSION);
        
        while (file_exists($targetPath)) {
            $newName = $baseName . '_' . $counter . '.' . $extension;
            $targetPath = joinPathValue($uploadDir, $newName);
            $counter++;
        }
        
        if (move_uploaded_file($tmpName, $targetPath)) {
            $uploadResults['success'][] = array(
                'name' => basename($targetPath),
                'size' => $fileSize
            );
        } else {
            $uploadResults['failed'][] = array(
                'name' => $fileName,
                'reason' => 'Failed to move uploaded file'
            );
        }
    }
    
    echo json_encode($uploadResults);
    exit;
}

// ========================================
// TERMINAL HANDLER
// ========================================

if (isset($_POST['terminal_action'])) {
    prepareApiOutput('application/json; charset=UTF-8');
    
    if ($_POST['terminal_action'] === 'execute') {
        if (!TERMINAL_ENABLED) {
            echo json_encode(array('error' => 'Terminal is disabled'));
            exit;
        }

        $command = trim(arrayGetValue($_POST, 'command', ''));
        $cwd = resolveExistingPath(arrayGetValue($_SESSION, 'terminal_cwd', $defaultStartDir), $defaultStartDir);

        if (!is_dir($cwd) || !is_readable($cwd)) {
            $cwd = $defaultStartDir;
            $_SESSION['terminal_cwd'] = $cwd;
        }

        if ($command === '') {
            echo json_encode(array('output' => '', 'cwd' => $cwd, 'success' => true));
            exit;
        }

        // Handle cd command
        if (preg_match('/^cd(?:\s+(.*))?$/i', $command, $matches)) {
            $newDirInput = isset($matches[1]) ? trim($matches[1]) : '';

            if ($newDirInput === '' || $newDirInput === '~') {
                $candidateDir = isWindowsOS() ? getEnvValue('USERPROFILE', $defaultStartDir) : getEnvValue('HOME', $defaultStartDir);
            } elseif ($newDirInput === '..') {
                $candidateDir = getParentPathValue($cwd);
            } elseif (!isAbsolutePathValue($newDirInput)) {
                $candidateDir = joinPathValue($cwd, $newDirInput);
            } else {
                $candidateDir = $newDirInput;
            }

            $resolvedDir = realpath($candidateDir);
            if ($resolvedDir !== false) {
                $resolvedDir = normalizePathValue($resolvedDir);
            } elseif (isWindowsOS() && hasWindowsDrive($candidateDir) && @is_dir($candidateDir)) {
                $resolvedDir = normalizePathValue($candidateDir);
            } else {
                $resolvedDir = false;
            }

            if ($resolvedDir !== false && is_dir($resolvedDir) && isPathAllowedValue($resolvedDir)) {
                $_SESSION['terminal_cwd'] = $resolvedDir;
                echo json_encode(array('output' => '', 'cwd' => $resolvedDir, 'success' => true));
            } else {
                echo json_encode(array('output' => 'cd: path not found or not allowed', 'cwd' => $cwd, 'success' => false));
            }
            exit;
        }

        if ($command === 'pwd' || (isWindowsOS() && strtolower($command) === 'cd')) {
            echo json_encode(array('output' => $cwd, 'cwd' => $cwd, 'success' => true));
            exit;
        }

        if ($command === 'clear' || strtolower($command) === 'cls') {
            echo json_encode(array('output' => '', 'cwd' => $cwd, 'success' => true, 'clear' => true));
            exit;
        }

        if (strtolower($command) === 'exit') {
            echo json_encode(array('output' => 'Terminal closed', 'cwd' => $cwd, 'success' => true, 'close_terminal' => true));
            exit;
        }

        $executionResult = executeTerminalCommand($command, $cwd);
        echo json_encode($executionResult);
        exit;
    }
    
    if ($_POST['terminal_action'] === 'get_cwd') {
        echo json_encode(array('cwd' => arrayGetValue($_SESSION, 'terminal_cwd', getcwd())));
        exit;
    }
}

// ========================================
// OTHER AJAX HANDLERS
// ========================================

if (isset($_GET['action'])) {
    $action = $_GET['action'];
    
    // Extract / unzip with progress
    if ($action === 'extract' || $action === 'unzip') {
        prepareApiOutput('text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no');

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        $zipFile = arrayGetValue($_POST, 'zip_file', arrayGetValue($_GET, 'zip_file', ''));
        $baseDir = resolveExistingPath(arrayGetValue($_POST, 'base_dir', arrayGetValue($_GET, 'base_dir', $currentDir)), $currentDir);
        $zipPath = resolveManagedItemPath($baseDir, $zipFile);

        if ($zipPath === false || !file_exists($zipPath) || !is_file($zipPath)) {
            echo "data: " . json_encode(array('error' => 'File ZIP tidak ditemukan')) . "\n\n";
            @flush();
            exit;
        }

        if (strtolower(pathinfo($zipPath, PATHINFO_EXTENSION)) !== 'zip') {
            echo "data: " . json_encode(array('error' => 'Hanya file ZIP yang dapat di-unzip')) . "\n\n";
            @flush();
            exit;
        }

        $extractPath = createUniqueExtractDirectory($baseDir, $zipFile);
        if (!@mkdir($extractPath, 0755, true)) {
            echo "data: " . json_encode(array('error' => 'Folder hasil unzip tidak dapat dibuat')) . "\n\n";
            @flush();
            exit;
        }

        $startTime = microtime(true);
        $processed = 0;
        $extractedCount = 0;

        if (class_exists('ZipArchive')) {
            $extractError = '';
            $ok = extractZipArchiveSafely($zipPath, $extractPath, $extractError, $extractedCount, 'emitUnzipSseProgress');
            if (!$ok) {
                cleanupExtractionDirectory($extractPath);
                echo "data: " . json_encode(array('error' => ($extractError !== '' ? $extractError : 'Gagal mengekstrak file ZIP'))) . "\n\n";
                @flush();
                exit;
            }
            $processed = max(1, $extractedCount);
        } else {
            echo "data: " . json_encode(array(
                'current' => 0,
                'total' => 1,
                'percent' => 10,
                'speed' => 0,
                'file' => basename($zipFile)
            )) . "\n\n";
            @flush();

            $extractError = '';
            $ok = runArchiveExtractionFallback($zipPath, $extractPath, $extractError, $extractedCount);
            if (!$ok) {
                cleanupExtractionDirectory($extractPath);
                echo "data: " . json_encode(array('error' => 'Fitur unzip server tidak tersedia: ' . ($extractError !== '' ? $extractError : 'unknown error'))) . "\n\n";
                @flush();
                exit;
            }

            $processed = max(1, $extractedCount);
            echo "data: " . json_encode(array(
                'current' => 1,
                'total' => 1,
                'percent' => 100,
                'speed' => 0,
                'file' => basename($zipFile)
            )) . "\n\n";
            @flush();
            @ob_flush();
        }

        if ($extractedCount <= 0) {
            cleanupExtractionDirectory($extractPath);
            echo "data: " . json_encode(array('error' => 'Tidak ada file valid yang berhasil diekstrak dari ZIP')) . "\n\n";
            @flush();
            exit;
        }

        echo "data: " . json_encode(array(
            'complete' => true,
            'message' => 'Unzip selesai',
            'folder' => basename($extractPath),
            'extracted' => $extractedCount
        )) . "\n\n";
        @flush();
        exit;
    }
    
    if ($action === 'unzip_simple') {
        prepareApiOutput('application/json; charset=UTF-8');

        $zipFile = arrayGetValue($_POST, 'zip_file', arrayGetValue($_GET, 'zip_file', ''));
        $baseDir = resolveExistingPath(arrayGetValue($_POST, 'base_dir', arrayGetValue($_GET, 'base_dir', $currentDir)), $currentDir);
        $zipPath = resolveManagedItemPath($baseDir, $zipFile);

        if ($zipPath === false || !file_exists($zipPath) || !is_file($zipPath)) {
            respondJson(array('success' => false, 'error' => 'File ZIP tidak ditemukan'));
        }

        if (strtolower(pathinfo($zipPath, PATHINFO_EXTENSION)) !== 'zip') {
            respondJson(array('success' => false, 'error' => 'Hanya file ZIP yang dapat di-unzip'));
        }

        $extractPath = createUniqueExtractDirectory($baseDir, $zipFile);
        if (!@mkdir($extractPath, 0755, true)) {
            respondJson(array('success' => false, 'error' => 'Folder hasil unzip tidak dapat dibuat'));
        }

        $extractedCount = 0;

        if (class_exists('ZipArchive')) {
            $extractError = '';
            $ok = extractZipArchiveSafely($zipPath, $extractPath, $extractError, $extractedCount);
            if (!$ok) {
                cleanupExtractionDirectory($extractPath);
                respondJson(array('success' => false, 'error' => ($extractError !== '' ? $extractError : 'Gagal mengekstrak file ZIP')));
            }
        } else {
            $extractError = '';
            $ok = runArchiveExtractionFallback($zipPath, $extractPath, $extractError, $extractedCount);
            if (!$ok) {
                cleanupExtractionDirectory($extractPath);
                respondJson(array('success' => false, 'error' => 'Fitur unzip server tidak tersedia: ' . ($extractError !== '' ? $extractError : 'unknown error')));
            }
        }

        if ($extractedCount <= 0) {
            cleanupExtractionDirectory($extractPath);
            respondJson(array('success' => false, 'error' => 'Tidak ada file valid yang berhasil diekstrak dari ZIP'));
        }

        respondJson(array(
            'success' => true,
            'message' => 'Unzip selesai',
            'folder' => basename($extractPath),
            'extracted' => $extractedCount
        ));
    }

    // Delete
    if ($action === 'delete') {
        $items = isset($_POST['items']) ? json_decode($_POST['items'], true) : array();
        $baseDir = arrayGetValue($_POST, 'base_dir', $currentDir);
        $results = array('success' => array(), 'failed' => array());
        
        foreach ($items as $item) {
            $path = $baseDir . DIRECTORY_SEPARATOR . $item;
            if (file_exists($path)) {
                if (is_dir($path)) {
                    if (deleteDirectory($path)) {
                        $results['success'][] = $item;
                    } else {
                        $results['failed'][] = $item;
                    }
                } else {
                    if (unlink($path)) {
                        $results['success'][] = $item;
                    } else {
                        $results['failed'][] = $item;
                    }
                }
            }
        }
        
        prepareApiOutput('application/json; charset=UTF-8');
        echo json_encode($results);
        exit;
    }
    
    // Download
    if ($action === 'download') {
        $file = arrayGetValue($_GET, 'file', '');
        $filePath = $currentDir . DIRECTORY_SEPARATOR . $file;
        
        if (file_exists($filePath) && is_file($filePath)) {
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
            header('Content-Length: ' . filesize($filePath));
            readfile($filePath);
            exit;
        }
    }
    

    // View file
    if ($action === 'view') {
        $file = arrayGetValue($_GET, 'file', '');
        $filePath = resolveManagedItemPath($currentDir, $file);

        if ($filePath === false || !file_exists($filePath) || !is_file($filePath)) {
            respondJson(array('success' => false, 'error' => 'File tidak ditemukan'));
        }

        $fileSize = safeFileSize($filePath);
        if ($fileSize > MAX_VIEW_FILE_SIZE) {
            respondJson(array('success' => false, 'error' => 'File terlalu besar untuk dibuka di editor (maks 2MB)'));
        }

        if (!@is_readable($filePath)) {
            respondJson(array('success' => false, 'error' => 'File tidak dapat dibaca'));
        }

        $content = @file_get_contents($filePath);
        if ($content === false) {
            respondJson(array('success' => false, 'error' => 'Gagal membaca file'));
        }

        if (strpos($content, "\0") !== false) {
            respondJson(array('success' => false, 'error' => 'File biner tidak dapat ditampilkan di editor teks'));
        }

        respondJson(array(
            'success' => true,
            'name' => basename($filePath),
            'size' => formatSize($fileSize),
            'permissions' => getPathPermissions($filePath),
            'editable' => isEditableTextFile($filePath) && @is_writable($filePath),
            'content' => $content
        ));
    }

    // Save file
    if ($action === 'save_file') {
        $baseDir = arrayGetValue($_POST, 'base_dir', $currentDir);
        $file = arrayGetValue($_POST, 'file', '');
        $content = arrayGetValue($_POST, 'content', '');
        $filePath = resolveManagedItemPath($baseDir, $file);

        if ($filePath === false || !file_exists($filePath) || !is_file($filePath)) {
            respondJson(array('success' => false, 'error' => 'File tidak ditemukan'));
        }

        if (!isEditableTextFile($filePath)) {
            respondJson(array('success' => false, 'error' => 'File ini tidak didukung untuk edit teks'));
        }

        if (!@is_writable($filePath)) {
            respondJson(array('success' => false, 'error' => 'File tidak writable'));
        }

        $result = @file_put_contents($filePath, $content, LOCK_EX);
        if ($result === false) {
            respondJson(array('success' => false, 'error' => 'Gagal menyimpan file'));
        }

        respondJson(array(
            'success' => true,
            'message' => 'File berhasil disimpan',
            'permissions' => getPathPermissions($filePath)
        ));
    }

    // CHMOD
    if ($action === 'chmod') {
        if (isWindowsOS()) {
            respondJson(array('success' => false, 'error' => 'CHMOD tidak didukung secara penuh di Windows'));
        }

        $baseDir = arrayGetValue($_POST, 'base_dir', $currentDir);
        $target = arrayGetValue($_POST, 'target', '');
        $mode = preg_replace('/[^0-7]/', '', arrayGetValue($_POST, 'mode', ''));
        $targetPath = resolveManagedItemPath($baseDir, $target);

        if ($targetPath === false || !file_exists($targetPath)) {
            respondJson(array('success' => false, 'error' => 'Target tidak ditemukan'));
        }

        if ($mode === '' || strlen($mode) < 3 || strlen($mode) > 4) {
            respondJson(array('success' => false, 'error' => 'Format permission tidak valid'));
        }

        $chmodResult = @chmod($targetPath, octdec($mode));
        clearstatcache();

        if (!$chmodResult) {
            respondJson(array('success' => false, 'error' => 'Gagal mengubah permission'));
        }

        respondJson(array(
            'success' => true,
            'permissions' => getPathPermissions($targetPath)
        ));
    }

    // Create folder
    if ($action === 'create_folder') {
        $folderName = arrayGetValue($_POST, 'folder_name', '');
        $baseDir = arrayGetValue($_POST, 'base_dir', $currentDir);
        
        if (!empty($folderName)) {
            $folderPath = $baseDir . DIRECTORY_SEPARATOR . $folderName;
            if (mkdir($folderPath, 0755, true)) {
                prepareApiOutput('application/json; charset=UTF-8');
                echo json_encode(array('success' => true));
                exit;
            }
        }
        
        prepareApiOutput('application/json; charset=UTF-8');
        echo json_encode(array('success' => false));
        exit;
    }
}

// Helper functions
function deleteDirectory($dir) {
    if (!is_dir($dir)) return false;
    $items = scandir($dir);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        $path = $dir . DIRECTORY_SEPARATOR . $item;
        is_dir($path) ? deleteDirectory($path) : unlink($path);
    }
    return rmdir($dir);
}

function formatSize($bytes) {
    if ($bytes >= 1073741824) return number_format($bytes / 1073741824, 2) . ' GB';
    if ($bytes >= 1048576) return number_format($bytes / 1048576, 2) . ' MB';
    if ($bytes >= 1024) return number_format($bytes / 1024, 2) . ' KB';
    return $bytes . ' B';
}

function safeIsDir($path) {
    return file_exists($path) && @is_dir($path);
}

function safeIsFile($path) {
    return file_exists($path) && @is_file($path);
}

function safeFileSize($path) {
    if (!safeIsFile($path) || !@is_readable($path)) {
        return 0;
    }
    $size = @filesize($path);
    return ($size === false) ? 0 : $size;
}

function safeFileMTime($path) {
    if (!file_exists($path)) {
        return 0;
    }
    $mtime = @filemtime($path);
    return ($mtime === false) ? 0 : $mtime;
}

function getPathPermissions($path) {
    if (!file_exists($path)) {
        return '----';
    }

    $perms = @fileperms($path);
    if ($perms === false) {
        return '----';
    }

    return substr(sprintf('%o', $perms), -4);
}

function resolveManagedItemPath($baseDir, $itemName) {
    $itemName = trim((string)$itemName);
    if ($itemName === '' || $itemName === '.' || $itemName === '..') {
        return false;
    }

    if (strpos($itemName, '/') !== false || strpos($itemName, '\\') !== false) {
        return false;
    }

    $path = joinPathValue($baseDir, $itemName);
    return normalizePathValue($path);
}

function isEditableTextFile($path) {
    if (!is_file($path) || !@is_readable($path)) {
        return false;
    }

    $size = @filesize($path);
    if ($size === false || $size > MAX_VIEW_FILE_SIZE) {
        return false;
    }

    $textExtensions = array('txt', 'log', 'md', 'json', 'xml', 'csv', 'ini', 'conf', 'config', 'env', 'yml', 'yaml', 'htaccess', 'php', 'phtml', 'html', 'htm', 'css', 'js', 'mjs', 'ts', 'tsx', 'jsx', 'py', 'java', 'c', 'cpp', 'h', 'hpp', 'sql', 'sh', 'bat', 'cmd', 'ps1', 'rb', 'go', 'rs', 'swift', 'kt', 'vue', 'gitignore');
    $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

    if (in_array($extension, $textExtensions)) {
        return true;
    }

    $sample = @file_get_contents($path, false, null, 0, 4096);
    if ($sample === false) {
        return false;
    }

    return strpos($sample, "\0") === false;
}

// Get directory listing
$items = array();
if (is_dir($currentDir) && is_readable($currentDir)) {
    $scan = @scandir($currentDir);
    if (is_array($scan)) {
        foreach ($scan as $item) {
            if ($item === '.') continue;
            $fullPath = $currentDir . DIRECTORY_SEPARATOR . $item;

            $isDir = safeIsDir($fullPath);
            $isFile = safeIsFile($fullPath);
            $isRead = @is_readable($fullPath);
            $isWrite = @is_writable($fullPath);
            $modified = safeFileMTime($fullPath);
            $size = $isFile ? safeFileSize($fullPath) : 0;

            $items[] = array(
                'name' => $item,
                'type' => $isDir ? 'dir' : 'file',
                'size' => $size,
                'modified' => $modified,
                'readable' => $isRead,
                'writable' => $isWrite,
                'is_zip' => $isFile && strtolower(pathinfo($fullPath, PATHINFO_EXTENSION)) === 'zip',
                'permissions' => getPathPermissions($fullPath),
                'editable' => $isFile && isEditableTextFile($fullPath)
            );
        }
    }
}

function compareFileManagerItems($a, $b) {
    if ($a['type'] !== $b['type']) {
        return $a['type'] === 'dir' ? -1 : 1;
    }
    return strcasecmp($a['name'], $b['name']);
}

usort($items, 'compareFileManagerItems');

$sessionRemaining = SESSION_TIMEOUT - (time() - $_SESSION['last_activity']);
$totalItemsCount = count($items);
$totalFolderCount = 0;
$totalFileCount = 0;
$totalZipCount = 0;
$totalReadableCount = 0;
$totalWritableCount = 0;

foreach ($items as $summaryItem) {
    if ($summaryItem['name'] === '..') {
        continue;
    }

    if ($summaryItem['type'] === 'dir') {
        $totalFolderCount++;
    } else {
        $totalFileCount++;
    }

    if (!empty($summaryItem['is_zip'])) {
        $totalZipCount++;
    }

    if (!empty($summaryItem['readable'])) {
        $totalReadableCount++;
    }

    if (!empty($summaryItem['writable'])) {
        $totalWritableCount++;
    }
}

$currentDirDisplayName = trim(basename(rtrim(str_replace('\\', '/', $currentDir), '/')));
if ($currentDirDisplayName === '') {
    $currentDirDisplayName = isWindowsOS() ? 'Drive Root' : 'Root';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🔒 Secure File Manager</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        .header h1 { font-size: 24px; display: flex; align-items: center; gap: 10px; }
        .header-right { display: flex; align-items: center; gap: 20px; flex-wrap: wrap; }
        .session-info { font-size: 13px; opacity: 0.9; }
        .btn-logout {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 2px solid white;
            padding: 8px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-logout:hover { background: white; color: #667eea; }
        .path-bar {
            padding: 15px 30px;
            background: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }
        .path-input {
            flex: 1;
            padding: 8px 12px;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            min-width: 200px;
        }
        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 14px;
            flex-wrap: wrap;
        }
        .breadcrumb a {
            color: #667eea;
            text-decoration: none;
            padding: 4px 8px;
            border-radius: 4px;
            transition: all 0.2s;
        }
        .breadcrumb a:hover { background: #667eea; color: white; }
        .actions-bar {
            padding: 15px 30px;
            background: white;
            border-bottom: 1px solid #dee2e6;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .btn-success { background: #28a745; color: white; }
        .btn-danger { background: #dc3545; color: white; }
        .btn-warning { background: #ffc107; color: #212529; }
        .btn-secondary { background: #6c757d; color: white; }
        .btn-terminal { background: #212529; color: #00ff00; }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15); }
        .bulk-actions {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            display: none;
            z-index: 1000;
        }
        .bulk-actions.active { display: block; animation: slideUp 0.3s ease; }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .file-list { padding: 30px; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        thead { background: #f8f9fa; }
        th {
            padding: 12px;
            text-align: left;
            font-weight: 600;
            color: #495057;
            border-bottom: 2px solid #dee2e6;
        }
        td { padding: 12px; border-bottom: 1px solid #dee2e6; }
        tr:hover { background: #f8f9fa; }
        tr.selected { background: #e7f3ff; }
        .file-icon { font-size: 24px; margin-right: 8px; }
        .permission-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
            margin-left: 5px;
        }
        .badge-r { background: #d1ecf1; color: #0c5460; }
        .badge-w { background: #d4edda; color: #155724; }
        .badge-denied { background: #f8d7da; color: #721c24; }
        
        /* Upload Zone */
        .upload-zone {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(102, 126, 234, 0.95);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 2500;
        }
        .upload-zone.active { display: flex; }
        .upload-zone-content {
            text-align: center;
            color: white;
            padding: 40px;
            border: 4px dashed white;
            border-radius: 20px;
            max-width: 600px;
        }
        .upload-zone-content h2 { font-size: 36px; margin-bottom: 20px; }
        .upload-zone-content p { font-size: 18px; opacity: 0.9; }
        
        /* Upload Modal */
        .upload-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 3000;
            align-items: center;
            justify-content: center;
        }
        .upload-modal.active { display: flex; }
        .upload-modal-content {
            background: white;
            border-radius: 15px;
            padding: 30px;
            max-width: 600px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
        }
        .upload-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .upload-modal-close {
            background: #dc3545;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
        }
        .upload-file-list { max-height: 300px; overflow-y: auto; margin: 20px 0; }
        .upload-file-item {
            padding: 10px;
            background: #f8f9fa;
            margin-bottom: 10px;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .upload-file-item.success { background: #d4edda; border-left: 4px solid #28a745; }
        .upload-file-item.failed { background: #f8d7da; border-left: 4px solid #dc3545; }
        
        /* Terminal */
        .terminal-container {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 400px;
            background: #1e1e1e;
            border-top: 3px solid #667eea;
            display: none;
            flex-direction: column;
            z-index: 1500;
            box-shadow: 0 -10px 40px rgba(0, 0, 0, 0.5);
        }
        .terminal-container.active { display: flex; animation: slideUpTerminal 0.3s ease; }
        @keyframes slideUpTerminal {
            from { transform: translateY(100%); }
            to { transform: translateY(0); }
        }
        .terminal-header {
            background: #2d2d2d;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #3d3d3d;
        }
        .terminal-title {
            color: #00ff00;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            font-family: 'Courier New', monospace;
        }
        .terminal-controls { display: flex; gap: 10px; }
        .terminal-btn {
            background: transparent;
            border: 1px solid #555;
            color: #aaa;
            padding: 5px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            transition: all 0.2s;
        }
        .terminal-btn:hover { background: #555; color: white; }
        .terminal-output {
            flex: 1;
            overflow-y: auto;
            padding: 15px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            color: #00ff00;
            background: #1e1e1e;
        }
        .terminal-output::-webkit-scrollbar { width: 8px; }
        .terminal-output::-webkit-scrollbar-track { background: #2d2d2d; }
        .terminal-output::-webkit-scrollbar-thumb { background: #555; border-radius: 4px; }
        .terminal-line { margin-bottom: 5px; white-space: pre-wrap; word-wrap: break-word; }
        .terminal-prompt { color: #667eea; font-weight: 600; }
        .terminal-command { color: #fff; }
        .terminal-error { color: #ff5555; }
        .terminal-input-container {
            padding: 15px;
            background: #2d2d2d;
            border-top: 1px solid #3d3d3d;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .terminal-cwd { color: #667eea; font-family: 'Courier New', monospace; font-weight: 600; }
        .terminal-input {
            flex: 1;
            background: #1e1e1e;
            border: 1px solid #3d3d3d;
            color: #00ff00;
            padding: 8px 12px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
        }
        .terminal-input:focus { outline: none; border-color: #667eea; }
        
        .editor-modal-body { display: flex; flex-direction: column; gap: 12px; }
        .editor-meta { display: flex; gap: 10px; flex-wrap: wrap; color: #666; font-size: 13px; }
        .editor-textarea { width: 100%; min-height: 360px; padding: 14px; border: 1px solid #d0d7de; border-radius: 10px; font-family: Consolas, 'Courier New', monospace; font-size: 14px; resize: vertical; }
        .editor-textarea[readonly] { background: #f8f9fa; color: #555; }
        .modal-actions { display: flex; gap: 10px; justify-content: flex-end; flex-wrap: wrap; margin-top: 14px; }
        .chmod-input { width: 100%; padding: 12px 14px; border: 1px solid #d0d7de; border-radius: 10px; font-size: 16px; }
        .help-text { color: #666; font-size: 13px; line-height: 1.5; }
        .action-group { display: flex; gap: 6px; flex-wrap: wrap; align-items: center; }
        .action-group .btn { padding: 7px 10px; min-width: 44px; justify-content: center; }
        .path-label { font-weight: 700; white-space: nowrap; }

        /* Progress Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 2000;
            align-items: center;
            justify-content: center;
        }
        .modal.active { display: flex; }
        .modal-content {
            background: white;
            border-radius: 15px;
            padding: 30px;
            max-width: 500px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }
        .progress-bar {
            width: 100%;
            height: 30px;
            background: #e9ecef;
            border-radius: 15px;
            overflow: hidden;
            margin: 20px 0;
        }
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #667eea, #764ba2);
            transition: width 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }
        
        body.touch-device .btn,
        body.touch-device .btn-logout,
        body.touch-device .terminal-btn,
        body.touch-device .path-input {
            min-height: 44px;
            font-size: 16px;
        }
        body.device-desktop .container {
            max-width: 1340px;
        }
        body.device-desktop .header,
        body.device-desktop .path-bar,
        body.device-desktop .actions-bar,
        body.device-desktop .file-list,
        body.device-desktop .status-bar {
            margin-left: auto;
            margin-right: auto;
        }
        body.device-desktop .toolbar-actions {
            justify-content: flex-start;
        }
        body.device-desktop .file-list table {
            min-width: 100%;
        }
        body.device-mobile .actions-bar,
        body.device-tablet .actions-bar {
            position: sticky;
            top: 0;
            z-index: 25;
        }
        body.device-mobile table,
        body.device-tablet table {
            min-width: 760px;
        }
        body.device-mobile .terminal-container {
            height: 58vh;
        }
        body.device-tablet .terminal-container {
            height: 48vh;
        }
        body.device-mobile .header-right,
        body.device-tablet .header-right {
            width: 100%;
            justify-content: space-between;
        }
        body.device-mobile .breadcrumb,
        body.device-tablet .breadcrumb {
            display: block;
            width: 100%;
            overflow-x: auto;
            white-space: nowrap;
            -webkit-overflow-scrolling: touch;
        }
        body.device-mobile .file-list,
        body.device-tablet .file-list {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        body.device-mobile .actions-bar .btn,
        body.device-mobile .actions-bar .btn-terminal,
        body.device-mobile .actions-bar .btn-success {
            flex: 1 1 calc(50% - 10px);
            text-align: center;
            justify-content: center;
        }
        body.device-tablet .actions-bar .btn,
        body.device-tablet .actions-bar .btn-terminal,
        body.device-tablet .actions-bar .btn-success {
            flex: 1 1 calc(33.33% - 10px);
            text-align: center;
            justify-content: center;
        }
        body.device-mobile .path-bar,
        body.device-tablet .path-bar {
            gap: 12px;
        }
        body.device-mobile .modal-content,
        body.device-mobile .upload-modal-content,
        body.device-tablet .modal-content,
        body.device-tablet .upload-modal-content {
            width: calc(100% - 24px);
            padding: 24px 18px;
        }
        @media (max-width: 768px) {
            .container { border-radius: 12px; }
            .header { padding: 18px; }
            .header h1 { font-size: 18px; }
            .header-right { width: 100%; flex-direction: column; align-items: stretch; gap: 12px; }
            .session-info { font-size: 12px; line-height: 1.5; }
            .path-bar { flex-direction: column; align-items: stretch; padding: 16px 18px; }
            .path-label { margin-bottom: -4px; }
            .actions-bar { padding: 16px 18px; gap: 8px; }
            .actions-bar .btn, .actions-bar .btn-terminal, .actions-bar .btn-success, .actions-bar .btn-primary { flex: 1 1 calc(50% - 8px); }
            .bulk-actions { left: 12px; right: 12px; bottom: 12px; padding: 16px; }
            .file-list-shell { margin: 14px 14px 0; border-radius: 18px; }
            .file-list-header { padding: 14px; }
            .file-list-stats { width: 100%; }
            .list-stat { flex: 1 1 calc(50% - 8px); justify-content: center; }
            .file-list { padding: 14px; }
            .table-scroll-shell { overflow: visible; }
            .file-list table, .file-list thead, .file-list tbody, .file-list tr, .file-list th, .file-list td { display: block; width: 100%; }
            .file-list thead { display: none; }
            .file-row { background: #fff; border: 1px solid #e9ecef; border-radius: 14px; margin-bottom: 14px; padding: 8px 12px; box-shadow: 0 6px 18px rgba(0,0,0,0.05); }
            .file-row td { border: none; padding: 8px 0; }
            .file-row td[data-label]::before { content: attr(data-label); display: block; font-size: 12px; font-weight: 700; color: #6c757d; margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.04em; }
            .file-row td[data-label="Select"]::before { display: none; }
            .file-row td:first-child { padding-top: 0; }
            .file-row td:last-child { padding-bottom: 0; }
            .action-group { gap: 8px; }
            .action-group .btn { flex: 1 1 calc(33.33% - 8px); }
            .terminal-container { height: 58vh; }
            .terminal-header { padding: 10px 14px; flex-wrap: wrap; gap: 10px; }
            .terminal-controls { width: 100%; justify-content: flex-end; }
            .terminal-input-container { flex-wrap: wrap; align-items: stretch; }
            .terminal-cwd { width: 100%; overflow-x: auto; white-space: nowrap; }
            .terminal-input { width: 100%; min-height: 44px; }
            .editor-textarea { min-height: 260px; }
            .modal-actions .btn { flex: 1 1 calc(50% - 10px); justify-content: center; }
        }
        @media (max-width: 480px) {
            body { padding: 10px; }
            .header, .path-bar, .actions-bar { padding-left: 14px; padding-right: 14px; }
            .actions-bar .btn, .actions-bar .btn-terminal, .actions-bar .btn-success, .actions-bar .btn-primary { flex: 1 1 100%; }
            .action-group .btn { flex: 1 1 calc(50% - 8px); }
            .modal-content, .upload-modal-content { width: calc(100% - 12px) !important; padding: 18px 14px !important; }
            .editor-textarea { min-height: 220px; font-size: 13px; }
        }
    </style>

    <style>
        /* Main page professional layout fix */
        .header-brand {
            display: flex;
            flex-direction: column;
            gap: 6px;
            min-width: 0;
        }
        .header-subtitle {
            color: rgba(255, 255, 255, 0.84);
            font-size: 13px;
            line-height: 1.35;
            letter-spacing: 0.01em;
        }
        .header-right {
            align-items: center;
        }
        .session-info {
            background: rgba(255,255,255,0.12);
            padding: 10px 12px;
            border-radius: 12px;
            line-height: 1.5;
        }
        .overview-strip {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 12px;
            padding: 16px 18px 0;
            background: #f7f8fc;
        }
        .overview-card {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
            padding: 14px 16px;
            border-radius: 16px;
            background: #ffffff;
            border: 1px solid #e7eaf3;
            box-shadow: 0 8px 24px rgba(30, 41, 59, 0.06);
        }
        .overview-icon {
            width: 42px;
            height: 42px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 42px;
            border-radius: 12px;
            background: linear-gradient(135deg, #eef2ff 0%, #e0e7ff 100%);
            font-size: 20px;
        }
        .overview-label {
            font-size: 12px;
            color: #64748b;
            line-height: 1.2;
        }
        .overview-value {
            font-size: 24px;
            font-weight: 700;
            color: #111827;
            line-height: 1.15;
            margin-top: 3px;
        }
        .path-bar {
            margin: 16px 18px 0;
            padding: 16px;
            background: #ffffff;
            border: 1px solid #e7eaf3;
            border-radius: 18px;
            box-shadow: 0 8px 24px rgba(30, 41, 59, 0.05);
            display: block;
        }
        .path-top-row,
        .path-controls,
        .path-chip-row {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
        }
        .path-top-row {
            justify-content: space-between;
            margin-bottom: 12px;
        }
        .path-title-block {
            min-width: 0;
            flex: 1 1 280px;
        }
        .path-title {
            font-size: 18px;
            font-weight: 700;
            color: #111827;
            line-height: 1.2;
            margin-bottom: 4px;
        }
        .path-caption {
            color: #667085;
            line-height: 1.45;
        }
        .path-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 12px;
            border-radius: 999px;
            background: #f3f6ff;
            color: #475467;
            font-size: 13px;
            line-height: 1.25;
            white-space: normal;
        }
        .path-input {
            flex: 1 1 280px;
            min-width: 0;
            min-height: 46px;
            border-radius: 12px;
            border: 1px solid #d6dae5;
            background: #fafbff;
        }
        .breadcrumb {
            margin-top: 12px;
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            align-items: center;
            overflow: visible;
            white-space: normal;
        }
        .breadcrumb a {
            display: inline-flex;
            align-items: center;
            max-width: 100%;
            word-break: break-word;
        }
        .actions-bar {
            margin: 16px 18px 0;
            padding: 0;
            background: transparent;
            border: 0;
        }
        .toolbar-shell {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            padding: 16px;
            background: #ffffff;
            border: 1px solid #e7eaf3;
            border-radius: 18px;
            box-shadow: 0 8px 24px rgba(30, 41, 59, 0.05);
        }
        .toolbar-actions,
        .toolbar-meta {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
        }
        .toolbar-meta {
            margin-left: auto;
        }
        .toolbar-search {
            min-height: 44px;
            min-width: 220px;
            padding: 10px 12px;
            border-radius: 12px;
            border: 1px solid #d6dae5;
            background: #fafbff;
        }
        .toolbar-count {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            min-height: 44px;
            padding: 9px 12px;
            border-radius: 12px;
            background: #eef4ff;
            color: #3b5bcc;
            font-weight: 600;
        }
        .file-list-shell {
            margin: 16px 18px 0;
            background: #ffffff;
            border: 1px solid #e7eaf3;
            border-radius: 22px;
            box-shadow: 0 12px 32px rgba(30, 41, 59, 0.06);
            overflow: hidden;
        }
        .file-list-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 16px;
            flex-wrap: wrap;
            padding: 18px;
            border-bottom: 1px solid #eef2f7;
            background: linear-gradient(180deg, #fbfcff 0%, #f8fbff 100%);
        }
        .file-list-title {
            font-size: 17px;
            font-weight: 700;
            color: #111827;
            line-height: 1.2;
        }
        .file-list-subtitle {
            margin-top: 5px;
            color: #667085;
            line-height: 1.45;
            max-width: 760px;
        }
        .file-list-stats {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            align-items: center;
        }
        .list-stat {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            min-height: 40px;
            padding: 8px 12px;
            border-radius: 999px;
            background: #ffffff;
            border: 1px solid #e4e7ec;
            color: #475467;
            font-size: 13px;
            white-space: nowrap;
        }
        .list-stat strong {
            color: #111827;
        }
        .table-scroll-shell {
            overflow-x: auto;
            overflow-y: visible;
        }
        .file-list {
            padding: 0;
        }
        .file-list table {
            width: 100%;
            min-width: 980px;
            border-collapse: separate;
            border-spacing: 0;
        }
        .file-list thead th {
            position: sticky;
            top: 0;
            z-index: 5;
            background: rgba(248, 250, 252, 0.96);
            backdrop-filter: blur(10px);
            box-shadow: inset 0 -1px 0 #e5e7eb;
        }
        .col-select {
            width: 54px;
            text-align: center;
        }
        .col-size,
        .col-modified {
            width: 160px;
        }
        .col-actions {
            width: 240px;
        }
        .file-row:nth-child(even) {
            background: #fcfdff;
        }
        .file-row:hover {
            background: #f7faff;
        }
        .file-row.selected {
            background: #eef4ff;
            box-shadow: inset 4px 0 0 #4f46e5;
        }
        .file-type-badge,
        .file-ext-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 9px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
            line-height: 1;
            letter-spacing: 0.02em;
        }
        .file-type-dir {
            background: #eef4ff;
            color: #3154c5;
        }
        .file-type-zip {
            background: #fff5eb;
            color: #b54708;
        }
        .file-type-file {
            background: #f3f4f6;
            color: #344054;
        }
        .file-ext-badge {
            background: #f8fafc;
            color: #475467;
            border: 1px solid #e4e7ec;
        }
        .file-secondary {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
            margin-top: 8px;
        }
        .file-size-cell,
        .file-date-cell {
            white-space: nowrap;
            color: #475467;
            font-variant-numeric: tabular-nums;
        }
        .file-title-link:hover {
            color: #3b5bcc;
        }
        .action-group .btn {
            min-width: 40px;
            justify-content: center;
        }
        .file-name-cell {
            display: flex;
            gap: 12px;
            align-items: flex-start;
        }
        .file-icon-badge {
            width: 40px;
            height: 40px;
            flex: 0 0 40px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            background: linear-gradient(135deg, #eef2ff 0%, #e0e7ff 100%);
            font-size: 19px;
        }
        .file-main {
            min-width: 0;
            flex: 1 1 auto;
        }
        .file-title-link,
        .file-title-text {
            display: block;
            color: #111827;
            font-weight: 600;
            line-height: 1.35;
            word-break: break-word;
            text-decoration: none;
        }
        .file-meta-row {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
            margin-top: 6px;
        }
        .permission-badge {
            margin-left: 0;
            border-radius: 999px;
        }
        .action-group {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            align-items: center;
        }
        .status-bar {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            padding: 0 18px 18px;
        }
        .status-pill {
            display: inline-flex;
            align-items: center;
            padding: 9px 12px;
            border-radius: 999px;
            background: #ffffff;
            border: 1px solid #e7eaf3;
            color: #475467;
            line-height: 1.3;
        }
        .empty-search {
            display: none;
            margin-top: 12px;
            padding: 18px 16px;
            text-align: center;
            background: #ffffff;
            border: 1px dashed #d6dae5;
            border-radius: 16px;
            color: #667085;
        }
        .empty-search.active {
            display: block;
        }
        @media (max-width: 1024px) {
            .overview-strip {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }
        @media (max-width: 768px) {
            body {
                padding: 10px;
            }
            .container {
                border-radius: 18px;
            }
            .header {
                padding: 18px 16px;
            }
            .header h1 {
                font-size: 18px;
                line-height: 1.25;
                flex-wrap: wrap;
            }
            .header-right {
                width: 100%;
                flex-direction: column;
                align-items: stretch;
                gap: 12px;
            }
            .btn-logout {
                width: 100%;
                justify-content: center;
            }
            .overview-strip {
                grid-template-columns: 1fr 1fr;
                padding: 14px 14px 0;
            }
            .overview-card {
                min-height: 74px;
                padding: 12px;
            }
            .path-bar,
            .actions-bar,
            .file-list,
            .status-bar {
                margin-left: 14px;
                margin-right: 14px;
            }
            .path-top-row,
            .path-controls,
            .path-chip-row,
            .toolbar-shell,
            .toolbar-actions,
            .toolbar-meta {
                flex-direction: column;
                align-items: stretch;
            }
            .toolbar-meta {
                margin-left: 0;
                width: 100%;
            }
            .toolbar-search,
            .toolbar-count,
            .toolbar-actions .btn,
            .path-controls .btn,
            .path-input {
                width: 100%;
                min-width: 0;
            }
            .toolbar-count {
                justify-content: center;
            }
            .breadcrumb {
                overflow-x: auto;
                white-space: nowrap;
                flex-wrap: nowrap;
                padding-bottom: 2px;
            }
            .breadcrumb a {
                flex: 0 0 auto;
            }
            .file-list table,
            .file-list thead,
            .file-list tbody,
            .file-list tr,
            .file-list th,
            .file-list td {
                display: block;
                width: 100%;
            }
            .file-list thead {
                display: none;
            }
            .file-row {
                padding: 12px 14px;
                margin-bottom: 12px;
                border: 1px solid #e7eaf3;
                border-radius: 16px;
                background: #ffffff;
                box-shadow: 0 6px 18px rgba(30, 41, 59, 0.05);
            }
            .file-row td {
                border: 0;
                padding: 7px 0;
            }
            .file-row td[data-label]::before {
                content: attr(data-label);
                display: block;
                margin-bottom: 5px;
                color: #667085;
                font-size: 11px;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.05em;
            }
            .file-row td[data-label="Select"]::before {
                display: none;
            }
            .action-group .btn {
                flex: 1 1 calc(33.333% - 8px);
                justify-content: center;
            }
            .status-pill {
                width: 100%;
                justify-content: center;
                text-align: center;
            }
        }
        @media (max-width: 480px) {
            .overview-strip {
                grid-template-columns: 1fr;
            }
            .overview-card {
                min-height: 68px;
            }
            .header-subtitle {
                font-size: 12px;
            }
            .path-title {
                font-size: 17px;
            }
            .path-caption,
            .session-info,
            .path-chip,
            .status-pill {
                font-size: 12px;
            }
            .action-group .btn {
                flex: 1 1 calc(50% - 8px);
            }
        }
    </style>
    <style>
        :root {
            --ui-bg: #eef2ff;
            --ui-surface: rgba(255, 255, 255, 0.96);
            --ui-surface-soft: #f8fafc;
            --ui-border: #dbe4f0;
            --ui-text: #0f172a;
            --ui-muted: #64748b;
            --ui-shadow: 0 18px 50px rgba(15, 23, 42, 0.08);
            --ui-shadow-strong: 0 28px 70px rgba(15, 23, 42, 0.14);
            --ui-primary: linear-gradient(135deg, #2563eb 0%, #4f46e5 100%);
            --ui-success: linear-gradient(135deg, #059669 0%, #10b981 100%);
            --ui-warning: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%);
            --ui-danger: linear-gradient(135deg, #dc2626 0%, #ef4444 100%);
            --ui-terminal: linear-gradient(135deg, #0f172a 0%, #111827 100%);
        }
        body {
            background:
                radial-gradient(circle at top left, rgba(37, 99, 235, 0.16), transparent 28%),
                radial-gradient(circle at top right, rgba(124, 58, 237, 0.13), transparent 28%),
                linear-gradient(180deg, #f1f5f9 0%, #e2e8f0 100%);
            color: var(--ui-text);
        }
        .container {
            border: 1px solid rgba(148, 163, 184, 0.22);
            box-shadow: var(--ui-shadow-strong);
            background: rgba(248, 250, 252, 0.92);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
        }
        .header {
            background:
                radial-gradient(circle at top left, rgba(96, 165, 250, 0.16), transparent 32%),
                linear-gradient(135deg, #0f172a 0%, #111827 55%, #1e293b 100%);
        }
        .path-bar,
        .toolbar-shell,
        .file-list-shell,
        .bulk-actions,
        .modal-content,
        .upload-modal-content {
            background: var(--ui-surface);
            border-color: rgba(148, 163, 184, 0.18);
            box-shadow: var(--ui-shadow);
        }
        .session-info,
        .path-chip,
        .toolbar-count,
        .list-stat,
        .page-badge {
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }
        .toolbar-count {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            min-height: 44px;
            padding: 10px 12px;
            border-radius: 12px;
            background: #eff6ff;
            color: #1d4ed8;
            font-weight: 700;
        }
        .toolbar-count strong {
            font-size: 1rem;
        }
        .toolbar-meta {
            align-items: center;
        }
        .entries-control label,
        .search-control label {
            font-weight: 700;
            color: #475569;
        }
        .rows-select,
        .toolbar-search,
        .path-input,
        .chmod-input,
        .terminal-input,
        .editor-textarea {
            border-radius: 14px;
        }
        .rows-select,
        .toolbar-search,
        .path-input,
        .chmod-input {
            min-height: 46px;
            border: 1px solid #cbd5e1;
            background: #f8fafc;
        }
        .rows-select:focus,
        .toolbar-search:focus,
        .path-input:focus,
        .chmod-input:focus,
        .terminal-input:focus,
        .editor-textarea:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
        }
        .btn {
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08);
        }
        .btn-primary { background: var(--ui-primary); }
        .btn-success { background: var(--ui-success); }
        .btn-warning { background: var(--ui-warning); }
        .btn-danger { background: var(--ui-danger); }
        .btn-terminal { background: var(--ui-terminal); color: #bbf7d0; }
        .btn-logout {
            min-height: 46px;
            border-radius: 14px;
            padding-inline: 18px;
        }
        .file-list-shell,
        .file-list table,
        .file-row,
        .overview-card,
        .path-bar,
        .toolbar-shell {
            border-radius: 20px;
        }
        .file-table thead th {
            position: sticky;
            top: 0;
            z-index: 3;
            background: rgba(248, 250, 252, 0.96);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }
        .file-row {
            transition: transform 0.18s ease, box-shadow 0.18s ease, background-color 0.18s ease;
        }
        .file-row:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 22px rgba(15, 23, 42, 0.06);
        }
        .file-title-link,
        .file-title-text {
            word-break: break-word;
        }
        .file-secondary,
        .file-meta-row,
        .session-info,
        .path-caption,
        .file-list-subtitle {
            line-height: 1.55;
        }
        .bulk-actions {
            border: 1px solid rgba(148, 163, 184, 0.18);
        }
        .terminal-container {
            border-top: 1px solid rgba(96, 165, 250, 0.25);
            box-shadow: 0 -22px 48px rgba(15, 23, 42, 0.28);
        }
        .terminal-header,
        .terminal-input-container {
            background: rgba(15, 23, 42, 0.98);
        }
        .terminal-output {
            background: #020617;
        }
        @media (max-width: 1200px) {
            .overview-strip {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
            .toolbar-shell {
                align-items: stretch;
            }
            .toolbar-meta {
                width: 100%;
                margin-left: 0;
                justify-content: space-between;
            }
        }
        @media (max-width: 992px) {
            body {
                padding: 16px;
            }
            .container {
                border-radius: 22px;
            }
            .header {
                padding: 22px 20px;
            }
            .overview-strip {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                padding-inline: 16px;
            }
            .path-bar,
            .actions-bar,
            .file-list,
            .table-footer-bar {
                margin-inline: 16px;
            }
            .toolbar-actions,
            .toolbar-meta {
                width: 100%;
            }
            .search-control,
            .toolbar-search {
                width: 100%;
            }
        }
        @media (max-width: 768px) {
            body {
                padding: 12px;
            }
            .container {
                border-radius: 18px;
            }
            .overview-strip {
                grid-template-columns: 1fr;
                gap: 10px;
                padding-top: 14px;
            }
            .path-top-row,
            .path-controls,
            .toolbar-shell,
            .toolbar-meta,
            .toolbar-actions,
            .file-list-header,
            .table-footer-bar {
                gap: 10px;
            }
            .toolbar-count,
            .entries-control,
            .search-control,
            .rows-select,
            .toolbar-search,
            .path-input,
            .path-controls .btn,
            .toolbar-actions .btn,
            .toolbar-actions .btn-terminal,
            .modal-actions .btn {
                width: 100%;
            }
            .session-info {
                font-size: 12px;
            }
            .file-row {
                padding: 10px 12px;
            }
            .action-group .btn {
                min-width: calc(50% - 8px);
            }
            .bulk-actions {
                left: 12px;
                right: 12px;
                bottom: 12px;
                padding: 14px;
            }
        }
        @media (max-width: 520px) {
            .header h1 {
                font-size: 1.35rem;
            }
            .header-subtitle,
            .path-caption,
            .file-list-subtitle {
                font-size: 12px;
            }
            .path-bar,
            .file-list-shell,
            .toolbar-shell,
            .overview-card {
                border-radius: 16px;
            }
            .action-group .btn {
                flex: 1 1 100%;
            }
            .modal-content,
            .upload-modal-content {
                border-radius: 16px;
            }
        }
    </style>
</head>
<body class="<?php echo htmlspecialchars($deviceCssClass); ?>">
    <div class="container">
        <div class="header">
            <div class="header-brand">
                <div class="header-subtitle">Professional file manager interface</div>
                <h1><span>File Manager App</span></h1>
            </div>
            <div class="header-right">
                <div class="session-info"><span id="deviceTypeBadge"><?php echo $deviceIcon; ?> <?php echo htmlspecialchars($deviceLabel); ?></span> | <span id="osTypeBadge">Client OS</span> | <span id="serverOsBadge">Server: <?php echo htmlspecialchars(getServerOsLabel()); ?></span> | <span>🐘 PHP <?php echo htmlspecialchars(PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION); ?></span> | ⏱️ Session: <?php echo gmdate('i:s', max(0, $sessionRemaining)); ?> remaining</div>
                <form method="POST" style="margin: 0;">
                    <input type="hidden" name="action" value="logout">
                    <button type="submit" class="btn-logout">🚪 Logout</button>
                </form>
            </div>
        </div>

        <div class="overview-strip">
            <div class="overview-card"><div class="overview-icon">📁</div><div><div class="overview-label">Folders</div><div class="overview-value"><?php echo $totalFolderCount; ?></div></div></div>
            <div class="overview-card"><div class="overview-icon">📄</div><div><div class="overview-label">Files</div><div class="overview-value"><?php echo $totalFileCount; ?></div></div></div>
            <div class="overview-card"><div class="overview-icon">🗜️</div><div><div class="overview-label">ZIP</div><div class="overview-value"><?php echo $totalZipCount; ?></div></div></div>
            <div class="overview-card"><div class="overview-icon">👁️</div><div><div class="overview-label">Readable</div><div class="overview-value"><?php echo $totalReadableCount; ?></div></div></div>
            <div class="overview-card"><div class="overview-icon">✍️</div><div><div class="overview-label">Writable</div><div class="overview-value"><?php echo $totalWritableCount; ?></div></div></div>
        </div>
        
        <div class="path-bar">
            <div class="path-top-row">
                <div class="path-title-block">
                    <div class="path-title">📂 <?php echo htmlspecialchars($currentDirDisplayName); ?></div>
                    <div class="path-caption">Browse files and folders from the current working directory.</div>
                </div>
                <div class="path-chip-row">
                    <span class="path-chip">🧾 <?php echo $totalItemsCount; ?> items</span>
                    <span class="path-chip">⏱️ <?php echo gmdate('i:s', max(0, $sessionRemaining)); ?></span>
                    <span class="path-chip">💻 <?php echo htmlspecialchars(getServerOsLabel()); ?></span>
                </div>
            </div>
            <div class="path-controls">
                <input type="text" class="path-input" id="customPath" value="<?php echo htmlspecialchars($currentDir); ?>" placeholder="Enter path...">
                <button class="btn btn-primary" onclick="navigateToPath()">Open</button>
            </div>
            <div class="breadcrumb">
                <?php
                $breadcrumbs = buildBreadcrumbs($currentDir);
                foreach ($breadcrumbs as $index => $crumb) {
                    $icon = ($index === 0) ? '🏠 ' : '';
                    echo '<a href="?dir=' . urlencode($crumb['path']) . '">' . $icon . htmlspecialchars($crumb['label']) . '</a>';
                    if ($index < count($breadcrumbs) - 1) echo ' > ';
                }
                ?>
            </div>
        </div>
        
        <div class="actions-bar">
            <div class="toolbar-shell">
                <div class="toolbar-actions">
                    <?php if (UPLOAD_ENABLED): ?>
                    <button class="btn btn-success" onclick="document.getElementById('fileInput').click()">+ Add File</button>
                    <input type="file" id="fileInput" multiple style="display: none;" onchange="handleFileSelect(event)">
                    <?php endif; ?>
                    <button class="btn btn-primary" onclick="showCreateFolderModal()">+ New Folder</button>
                    <button class="btn btn-primary" onclick="selectAll()">Select All</button>
                    <button class="btn btn-primary" onclick="deselectAll()">Clear</button>
                    <?php if (TERMINAL_ENABLED): ?>
                    <button class="btn btn-terminal" onclick="toggleTerminal()">Terminal</button>
                    <?php endif; ?>
                </div>
                <div class="toolbar-meta datatable-controls">
                    <div class="entries-control">
                        <label for="rowsPerPage">Show
                            <select id="rowsPerPage" class="rows-select">
                                <option value="10" selected>10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                            entries
                        </label>
                    </div>
                    <div class="toolbar-count">Selected <strong id="toolbarSelectedCount">0</strong></div>
                    <div class="search-control">
                        <label for="toolbarSearch">Search:</label>
                        <input type="search" class="toolbar-search" id="toolbarSearch" placeholder="Cari file atau folder..." autocomplete="off">
                    </div>
                </div>
            </div>
        </div>
        
        <div class="file-list-shell">
            <div class="file-list-header">
                <div>
                    <div class="file-list-title">📂 Daftar File & Folder</div>
                    <div class="file-list-subtitle">Tampilan list file dibuat lebih rapi, lebih mudah dibaca, dengan sticky header, statistik tampilan, dan informasi file yang lebih jelas.</div>
                </div>
                <div class="file-list-stats">
                    <span class="list-stat">📦 Total <strong id="listTotalCount"><?php echo count($items); ?></strong></span>
                    <span class="list-stat">👁️ Tampil <strong id="listVisibleCount"><?php echo count($items); ?></strong></span>
                    <span class="list-stat">📄 Halaman <strong id="listCurrentPage">1</strong>/<strong id="listTotalPages">1</strong></span>
                </div>
            </div>
            <div class="file-list">
                <div class="table-scroll-shell">
                    <table class="file-table">
                        <thead>
                            <tr>
                                <th class="col-select"><input type="checkbox" id="selectAllCheckbox" onchange="toggleSelectAll(this)"></th>
                                <th>Nama</th>
                                <th class="col-size">Ukuran</th>
                                <th class="col-modified">Diubah</th>
                                <th class="col-actions">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                            <tr class="file-row" data-name="<?php echo htmlspecialchars($item['name']); ?>" data-kind="<?php echo htmlspecialchars($item['type']); ?>" data-zip="<?php echo $item['is_zip'] ? '1' : '0'; ?>">
                                <td data-label="Pilih">
                                    <input type="checkbox" class="item-checkbox" value="<?php echo htmlspecialchars($item['name']); ?>" onchange="updateBulkActions()">
                                </td>
                                <td data-label="Nama">
                                    <div class="file-name-cell">
                                        <span class="file-icon-badge">
                                            <?php
                                            if ($item['name'] === '..') {
                                                echo '⬆️';
                                            } elseif ($item['type'] === 'dir') {
                                                echo '📁';
                                            } elseif ($item['is_zip']) {
                                                echo '📦';
                                            } else {
                                                echo '📄';
                                            }
                                            ?>
                                        </span>
                                        <div class="file-main">
                                            <?php if ($item['type'] === 'dir'): ?>
                                                <a class="file-title-link" title="<?php echo htmlspecialchars($item['name']); ?>" href="?dir=<?php echo urlencode(resolveExistingPath(joinPathValue($currentDir, $item['name']), $currentDir)); ?>">
                                                    <?php echo htmlspecialchars($item['name']); ?>
                                                </a>
                                            <?php else: ?>
                                                <span class="file-title-text" title="<?php echo htmlspecialchars($item['name']); ?>"><?php echo htmlspecialchars($item['name']); ?></span>
                                            <?php endif; ?>
                                            <div class="file-secondary">
                                                <?php if ($item['name'] === '..'): ?>
                                                    <span class="file-type-badge file-type-dir">Kembali</span>
                                                <?php elseif ($item['type'] === 'dir'): ?>
                                                    <span class="file-type-badge file-type-dir">Folder</span>
                                                <?php elseif ($item['is_zip']): ?>
                                                    <span class="file-type-badge file-type-zip">ZIP Archive</span>
                                                <?php else: ?>
                                                    <span class="file-type-badge file-type-file">File</span>
                                                <?php endif; ?>
                                                <?php if ($item['type'] === 'file' && !$item['is_zip']): ?>
                                                    <?php $itemExtension = strtolower(pathinfo($item['name'], PATHINFO_EXTENSION)); ?>
                                                    <?php if ($itemExtension !== ''): ?>
                                                        <span class="file-ext-badge">.<?php echo htmlspecialchars($itemExtension); ?></span>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </div>
                                            <div class="file-meta-row">
                                                <?php if ($item['readable']): ?>
                                                    <span class="permission-badge badge-r">Readable</span>
                                                <?php endif; ?>
                                                <?php if ($item['writable']): ?>
                                                    <span class="permission-badge badge-w">Writable</span>
                                                <?php endif; ?>
                                                <span class="permission-badge" style="background:#fff7ed;color:#9a3412;"><?php echo htmlspecialchars($item['permissions']); ?></span>
                                                <?php if (!$item['readable'] && !$item['writable']): ?>
                                                    <span class="permission-badge badge-denied">Denied</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="file-size-cell" data-label="Ukuran"><?php echo $item['type'] === 'dir' ? '-' : formatSize($item['size']); ?></td>
                                <td class="file-date-cell" data-label="Diubah"><?php echo $item['modified'] > 0 ? date('Y-m-d H:i:s', $item['modified']) : '-'; ?></td>
                                <td data-label="Aksi">
                                    <div class="action-group">
                            <?php if ($item['name'] !== '..'): ?>
                                <?php if ($item['type'] === 'file' && !$item['is_zip']): ?>
                                    <button class="btn btn-primary" title="View" onclick='viewFile(<?php echo json_encode($item['name']); ?>)'>👁️</button>
                                    <?php if ($item['editable']): ?>
                                        <button class="btn btn-success" title="Edit" onclick='editFile(<?php echo json_encode($item['name']); ?>)'>✏️</button>
                                    <?php endif; ?>
                                    <button class="btn btn-warning" title="CHMOD" onclick='showChmodModal(<?php echo json_encode($item['name']); ?>, <?php echo json_encode($item['permissions']); ?>)'>🔐</button>
                                    <button class="btn btn-primary" title="Download" onclick='downloadFile(<?php echo json_encode($item['name']); ?>)'>⬇️</button>
                                    <button class="btn btn-primary" title="ZIP" onclick='compressSingle(<?php echo json_encode($item['name']); ?>)'>🗜️</button>
                                <?php elseif ($item['is_zip']): ?>
                                    <button class="btn btn-warning" title="CHMOD" onclick='showChmodModal(<?php echo json_encode($item['name']); ?>, <?php echo json_encode($item['permissions']); ?>)'>🔐</button>
                                    <button class="btn btn-primary" title="Download" onclick='downloadFile(<?php echo json_encode($item['name']); ?>)'>⬇️</button>
                                    <button class="btn btn-success" title="Unzip" onclick='extractZip(<?php echo json_encode($item['name']); ?>)'>📂 Unzip</button>
                                <?php elseif ($item['type'] === 'dir'): ?>
                                    <button class="btn btn-warning" title="CHMOD" onclick='showChmodModal(<?php echo json_encode($item['name']); ?>, <?php echo json_encode($item['permissions']); ?>)'>🔐</button>
                                    <button class="btn btn-primary" title="ZIP" onclick='compressSingle(<?php echo json_encode($item['name']); ?>)'>🗜️</button>
                                <?php endif; ?>
                            <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="empty-search" id="emptySearchState">Tidak ada file atau folder yang cocok dengan pencarian saat ini.</div>
            </div>
        </div>

        <div class="table-footer-bar">
            <div class="table-entries-info" id="tableEntriesInfo">Menampilkan 0 sampai 0 dari 0 item</div>
            <div class="table-pagination" role="navigation" aria-label="Pagination daftar file">
                <button type="button" class="page-btn" id="tablePrevBtn" data-page-action="prev" aria-label="Halaman sebelumnya">Sebelumnya</button>
                <span class="page-badge" id="tablePageBadge">1 / 1</span>
                <button type="button" class="page-btn" id="tableNextBtn" data-page-action="next" aria-label="Halaman berikutnya">Berikutnya</button>
            </div>
        </div>
    </div>
    
    <!-- Bulk Actions Panel -->
    <div class="bulk-actions" id="bulkActions">
        <h3 style="margin-bottom: 15px;"><span id="selectedCount">0</span> items selected</h3>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <button class="btn btn-primary" onclick="compressSelected()">🗜️ Compress</button>
            <button class="btn btn-success" onclick="extractSelectedZips()">📂 Unzip ZIP</button>
            <button class="btn btn-danger" onclick="deleteSelected()">🗑️ Delete</button>
            <button class="btn" onclick="deselectAll()" style="background: #6c757d; color: white;">❌ Cancel</button>
        </div>
    </div>
    
    <!-- Upload Drag & Drop Zone -->
    <div class="upload-zone" id="uploadZone">
        <div class="upload-zone-content">
            <h2>📤 Drop Files Here</h2>
            <p>Release to upload files to current directory</p>
        </div>
    </div>
    
    <!-- Upload Progress Modal -->
    <div class="upload-modal" id="uploadModal">
        <div class="upload-modal-content">
            <div class="upload-modal-header">
                <h2>📤 Uploading Files...</h2>
                <button class="upload-modal-close" onclick="closeUploadModal()">Close</button>
            </div>
            <div class="upload-file-list" id="uploadFileList"></div>
            <div id="uploadSummary" style="margin-top: 20px; font-weight: 600;"></div>
        </div>
    </div>
    
    <!-- View/Edit Modal -->
    <div class="modal" id="editorModal">
        <div class="modal-content" style="max-width: 900px;">
            <h2 id="editorTitle" style="margin-bottom: 15px; color: #667eea;">👁️ View File</h2>
            <div class="editor-modal-body">
                <div class="editor-meta">
                    <span><strong>File:</strong> <span id="editorFileName">-</span></span>
                    <span><strong>Size:</strong> <span id="editorFileSize">-</span></span>
                    <span><strong>Perm:</strong> <span id="editorFilePerms">-</span></span>
                </div>
                <textarea id="editorContent" class="editor-textarea" readonly></textarea>
                <div class="modal-actions">
                    <button class="btn btn-secondary" onclick="closeEditorModal()">Close</button>
                    <button class="btn btn-success" id="saveEditorBtn" onclick="saveEditorFile()" style="display:none;">💾 Save</button>
                </div>
            </div>
        </div>
    </div>

    <!-- CHMOD Modal -->
    <div class="modal" id="chmodModal">
        <div class="modal-content" style="max-width: 520px;">
            <h2 style="margin-bottom: 15px; color: #667eea;">🔐 CHMOD</h2>
            <div class="editor-modal-body">
                <div><strong>Target:</strong> <span id="chmodTarget">-</span></div>
                <input type="text" id="chmodInput" class="chmod-input" maxlength="4" placeholder="755 atau 0644">
                <div class="help-text">Gunakan 3 atau 4 digit oktal, contoh <strong>755</strong>, <strong>644</strong>, <strong>0755</strong>. Pada Windows, fitur chmod biasanya tidak berpengaruh.</div>
                <div class="modal-actions">
                    <button class="btn btn-secondary" onclick="closeChmodModal()">Close</button>
                    <button class="btn btn-warning" onclick="applyChmod()">Apply</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Terminal -->
    <?php if (TERMINAL_ENABLED): ?>
    <div class="terminal-container" id="terminal">
        <div class="terminal-header">
            <div class="terminal-title">
                <span>💻</span>
                <span>Terminal</span>
                <span style="opacity: 0.7; font-size: 12px;">(Press Ctrl+` to toggle)</span>
            </div>
            <div class="terminal-controls">
                <button class="terminal-btn" onclick="clearTerminal()">Clear</button>
                <button class="terminal-btn" onclick="toggleTerminal()">Hide</button>
            </div>
        </div>
        <div class="terminal-output" id="terminalOutput">
            <div class="terminal-line"><span style="color: #667eea;">Welcome to Secure File Manager Terminal!</span></div>
            <div class="terminal-line"><span style="color: #aaa;">Type commands and press Enter.</span></div>
            <div class="terminal-line"><span style="color: #aaa;">Current: <?php echo htmlspecialchars(arrayGetValue($_SESSION, 'terminal_cwd', getcwd())); ?></span></div>
            <div class="terminal-line"></div>
        </div>
        <div class="terminal-input-container">
            <span class="terminal-cwd" id="terminalCwd"><?php echo htmlspecialchars(arrayGetValue($_SESSION, 'terminal_cwd', getcwd())); ?></span>
            <span style="color: #00ff00;" id="terminalPromptSymbol"><?php echo htmlspecialchars(getTerminalPromptSymbol()); ?></span>
            <input type="text" class="terminal-input" id="terminalInput" placeholder="Enter command..." autocomplete="off">
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Progress Modal -->
    <div class="modal" id="progressModal">
        <div class="modal-content">
            <h2 id="progressTitle" style="margin-bottom: 20px; color: #667eea;">🗜️ Processing...</h2>
            <div class="progress-bar">
                <div class="progress-fill" id="progressFill" style="width: 0%">0%</div>
            </div>
            <div id="progressInfo" style="text-align: center; color: #666;">
                <p><strong id="progressCurrent">0</strong> / <strong id="progressTotal">0</strong> files</p>
                <p><span id="progressSpeed">0</span> files/s</p>
                <p style="font-size: 14px; margin-top: 10px;">Current: <strong id="progressFile">-</strong></p>
            </div>
        </div>
    </div>
    
    <script>
        const currentDir = <?php echo json_encode($currentDir); ?>;
        let commandHistory = [];
        let historyIndex = -1;
        let dragCounter = 0;
        
        function detectRuntimeDeviceInfo() {
            var width = window.innerWidth || document.documentElement.clientWidth || screen.width || 1200;
            var ua = (navigator.userAgent || '').toLowerCase();
            var maxTouchPoints = navigator.maxTouchPoints || navigator.msMaxTouchPoints || 0;
            var isTouch = ('ontouchstart' in window) || maxTouchPoints > 0;
            var type = 'desktop';

            if (width <= 768 || /iphone|ipod|android.+mobile|blackberry|opera mini|windows phone/.test(ua)) {
                type = 'mobile';
            } else if (width <= 1024 || /ipad|tablet|playbook|kindle|silk/.test(ua) || (/android/.test(ua) && !/mobile/.test(ua))) {
                type = 'tablet';
            } else if (isTouch && width < 1180) {
                type = 'tablet';
            }

            return { type: type, touch: isTouch };
        }

        function closeAllModals() {
            ['progressModal', 'uploadModal', 'editorModal', 'chmodModal'].forEach(function(id) {
                var el = document.getElementById(id);
                if (el) {
                    el.classList.remove('active');
                }
            });
        }

        function detectRuntimeOsLabel() {
            var ua = (navigator.userAgent || '').toLowerCase();
            if (ua.indexOf('windows') !== -1) return 'Windows';
            if (ua.indexOf('android') !== -1) return 'Android';
            if (ua.indexOf('iphone') !== -1 || ua.indexOf('ipad') !== -1 || ua.indexOf('ipod') !== -1) return 'iOS';
            if (ua.indexOf('mac os') !== -1 || ua.indexOf('macintosh') !== -1) return 'macOS';
            if (ua.indexOf('linux') !== -1) return 'Linux';
            return 'Unknown OS';
        }

        function applyResponsiveDeviceMode() {
            var info = detectRuntimeDeviceInfo();
            var body = document.body;
            body.classList.remove('device-desktop', 'device-tablet', 'device-mobile', 'touch-device', 'no-touch-device');
            body.classList.add('device-' + info.type);
            body.classList.add(info.touch ? 'touch-device' : 'no-touch-device');

            var badge = document.getElementById('deviceTypeBadge');
            if (badge) {
                var labelMap = { desktop: '🖥️ Desktop', tablet: '📲 Tablet', mobile: '📱 Mobile' };
                badge.textContent = labelMap[info.type] || '🖥️ Desktop';
            }

            var osBadge = document.getElementById('osTypeBadge');
            if (osBadge) {
                osBadge.textContent = detectRuntimeOsLabel();
            }
        }

        function parseJsonResponse(response) {
            return response.text().then(function(text) {
                try {
                    return JSON.parse(text);
                } catch (error) {
                    var cleanText = text.replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim();
                    throw new Error(cleanText || 'Server returned invalid JSON');
                }
            });
        }

        function navigateToPath() {
            const path = document.getElementById('customPath').value;
            window.location.href = '?dir=' + encodeURIComponent(path);
        }
        
        function selectAll() {
            document.querySelectorAll('.item-checkbox').forEach(cb => cb.checked = true);
            document.querySelectorAll('.file-row').forEach(row => row.classList.add('selected'));
            const masterCheckbox = document.getElementById('selectAllCheckbox');
            if (masterCheckbox) {
                masterCheckbox.checked = true;
                masterCheckbox.indeterminate = false;
            }
            updateBulkActions();
        }
        
        function deselectAll() {
            document.querySelectorAll('.item-checkbox').forEach(cb => cb.checked = false);
            document.querySelectorAll('.file-row').forEach(row => row.classList.remove('selected'));
            const masterCheckbox = document.getElementById('selectAllCheckbox');
            if (masterCheckbox) {
                masterCheckbox.checked = false;
                masterCheckbox.indeterminate = false;
            }
            updateBulkActions();
        }
        
        function toggleSelectAll(checkbox) {
            if (checkbox.checked) { selectAll(); } else { deselectAll(); }
        }
        
        function updateBulkActions() {
            const selected = document.querySelectorAll('.item-checkbox:checked');
            const bulkActions = document.getElementById('bulkActions');
            const selectedCount = document.getElementById('selectedCount');
            
            if (selectedCount) {
                selectedCount.textContent = selected.length;
            }
            updateToolbarSelection();
            
            if (bulkActions) {
                if (selected.length > 0) {
                    bulkActions.classList.add('active');
                } else {
                    bulkActions.classList.remove('active');
                }
            }
            
            document.querySelectorAll('.file-row').forEach(row => {
                const checkbox = row.querySelector('.item-checkbox');
                if (checkbox && checkbox.checked) {
                    row.classList.add('selected');
                } else {
                    row.classList.remove('selected');
                }
            });
            
            const allCheckboxes = document.querySelectorAll('.item-checkbox');
            const checkedCheckboxes = document.querySelectorAll('.item-checkbox:checked');
            const selectAllCheckbox = document.getElementById('selectAllCheckbox');
            if (!selectAllCheckbox) {
                return;
            }
            
            if (checkedCheckboxes.length === 0) {
                selectAllCheckbox.checked = false;
                selectAllCheckbox.indeterminate = false;
            } else if (checkedCheckboxes.length === allCheckboxes.length) {
                selectAllCheckbox.checked = true;
                selectAllCheckbox.indeterminate = false;
            } else {
                selectAllCheckbox.checked = false;
                selectAllCheckbox.indeterminate = true;
            }
        }
        
        function getSelectedItems() {
            return Array.from(document.querySelectorAll('.item-checkbox:checked')).map(cb => cb.value);
        }

        function updateToolbarSelection() {
            const chip = document.getElementById('toolbarSelectedCount');
            if (chip) {
                chip.textContent = document.querySelectorAll('.item-checkbox:checked').length;
            }
        }

        function syncToolbarSearchKeyword(keyword) {
            const searchInput = document.getElementById('toolbarSearch');
            if (searchInput && typeof keyword === 'string' && searchInput.value !== keyword) {
                searchInput.value = keyword;
            }
            updateProfessionalTableView(true);
        }
        
        function showProgressModal(title) {
            const modal = document.getElementById('progressModal');
            document.getElementById('progressTitle').textContent = title;
            document.getElementById('progressFill').style.width = '0%';
            document.getElementById('progressFill').textContent = '0%';
            document.getElementById('progressCurrent').textContent = '0';
            document.getElementById('progressTotal').textContent = '0';
            document.getElementById('progressSpeed').textContent = '0';
            document.getElementById('progressFile').textContent = '-';
            modal.classList.add('active');
        }
        
        function hideProgressModal() {
            document.getElementById('progressModal').classList.remove('active');
        }
        
        function updateProgress(data) {
            if (data.error) {
                alert('Error: ' + data.error);
                hideProgressModal();
                return;
            }
            
            if (data.complete) {
                document.getElementById('progressFill').style.width = '100%';
                document.getElementById('progressFill').textContent = '100%';
                setTimeout(() => {
                    hideProgressModal();
                    location.reload();
                }, 2000);
                return;
            }
            
            document.getElementById('progressFill').style.width = data.percent + '%';
            document.getElementById('progressFill').textContent = data.percent + '%';
            document.getElementById('progressCurrent').textContent = data.current;
            document.getElementById('progressTotal').textContent = data.total;
            document.getElementById('progressSpeed').textContent = data.speed;
            document.getElementById('progressFile').textContent = data.file || '-';
        }
        
        function compressSingle(item) {
            compressItems([item]);
        }
        
        function compressSelected() {
            const items = getSelectedItems();
            if (items.length === 0) {
                alert('Please select items to compress');
                return;
            }
            compressItems(items);
        }
        
        function compressItems(items) {
            showProgressModal('🗜️ Compressing...');
            
            // Step 1: Prepare compress task
            const formData = new FormData();
            formData.append('items', JSON.stringify(items));
            formData.append('base_dir', currentDir);
            
            fetch('?action=compress_prepare', {
                method: 'POST',
                body: formData
            })
            .then(parseJsonResponse)
            .then(data => {
                if (data.error) {
                    alert('Error: ' + data.error);
                    hideProgressModal();
                    return;
                }
                
                // Step 2: Execute compress with SSE
                const taskId = data.task_id;
                const eventSource = new EventSource('?action=compress_execute&task_id=' + taskId);
                
                eventSource.onmessage = function(event) {
                    const progressData = JSON.parse(event.data);
                    updateProgress(progressData);
                    
                    if (progressData.complete || progressData.error) {
                        eventSource.close();
                    }
                };
                
                eventSource.onerror = function() {
                    eventSource.close();
                    hideProgressModal();
                    alert('Connection error');
                };
            })
            .catch(error => {
                hideProgressModal();
                alert('Error: ' + error.message);
            });
        }
        
        function postJsonWithXhr(url, body) {
            return new Promise(function(resolve, reject) {
                var xhr = new XMLHttpRequest();
                xhr.open('POST', url, true);
                xhr.withCredentials = true;
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                xhr.timeout = 120000;

                xhr.onload = function() {
                    if (xhr.status >= 200 && xhr.status < 300) {
                        try {
                            resolve(JSON.parse(xhr.responseText || '{}'));
                        } catch (error) {
                            reject(new Error('Server returned invalid JSON'));
                        }
                    } else {
                        reject(new Error('HTTP ' + xhr.status));
                    }
                };

                xhr.onerror = function() {
                    reject(new Error('XHR request failed'));
                };

                xhr.ontimeout = function() {
                    reject(new Error('Request timeout'));
                };

                xhr.send(body);
            });
        }

        function extractZipFallback(zipFile) {
            const fallbackUrl = window.location.pathname + '?action=unzip_simple';
            const body = new URLSearchParams({
                zip_file: zipFile,
                base_dir: currentDir
            }).toString();

            return fetch(fallbackUrl, {
                method: 'POST',
                credentials: 'same-origin',
                cache: 'no-store',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: body
            })
            .then(parseJsonResponse)
            .catch(function() {
                return postJsonWithXhr(fallbackUrl, body);
            })
            .then(data => {
                if (!data.success) {
                    throw new Error(data.error || 'Gagal unzip file');
                }

                updateProgress({
                    current: data.extracted || 1,
                    total: data.extracted || 1,
                    percent: 100,
                    speed: 0,
                    file: zipFile,
                    complete: true
                });

                setTimeout(function() {
                    alert((data.message || 'Unzip selesai') + (data.folder ? '\nFolder: ' + data.folder : ''));
                    location.reload();
                }, 200);
            });
        }

        function extractZip(zipFile) {
            showProgressModal('📂 Unzipping...');

            let finished = false;
            let receivedProgress = false;
            let eventSource;

            try {
                eventSource = new EventSource('?action=unzip&zip_file=' + encodeURIComponent(zipFile) + '&base_dir=' + encodeURIComponent(currentDir));
            } catch (error) {
                extractZipFallback(zipFile)
                    .catch(function(fallbackError) {
                        hideProgressModal();
                        alert('Error: ' + fallbackError.message);
                    });
                return;
            }

            eventSource.onmessage = function(event) {
                const data = JSON.parse(event.data);
                receivedProgress = true;
                updateProgress(data);

                if (data.complete) {
                    finished = true;
                    eventSource.close();
                    setTimeout(function() {
                        alert((data.message || 'Unzip selesai') + (data.folder ? '\nFolder: ' + data.folder : ''));
                        location.reload();
                    }, 250);
                    return;
                }

                if (data.error) {
                    finished = true;
                    eventSource.close();
                    hideProgressModal();
                    alert('Error: ' + data.error);
                }
            };

            eventSource.onerror = function() {
                if (finished) {
                    return;
                }

                eventSource.close();

                if (receivedProgress) {
                    hideProgressModal();
                    alert('Koneksi SSE terputus saat unzip. Silakan refresh halaman untuk mengecek hasil extract.');
                    return;
                }

                extractZipFallback(zipFile)
                    .catch(function(fallbackError) {
                        hideProgressModal();
                        alert('Error: ' + fallbackError.message);
                    });
            };
        }

        function extractSelectedZips() {
            const zipItems = getSelectedItems().filter(function(name) {
                return /\.zip$/i.test(name || '');
            });

            if (zipItems.length === 0) {
                alert('Pilih minimal satu file ZIP untuk di-unzip');
                return;
            }

            if (zipItems.length > 1) {
                alert('Saat ini unzip massal diproses satu per satu. Silakan mulai dari satu file ZIP terlebih dahulu.');
                extractZip(zipItems[0]);
                return;
            }

            extractZip(zipItems[0]);
        }
        
        function deleteSelected() {
            const items = getSelectedItems();
            if (items.length === 0) {
                alert('Please select items to delete');
                return;
            }
            
            if (!confirm(`Are you sure you want to delete ${items.length} item(s)?`)) {
                return;
            }
            
            const formData = new FormData();
            formData.append('items', JSON.stringify(items));
            formData.append('base_dir', currentDir);
            
            fetch('?action=delete', {
                method: 'POST',
                body: formData
            })
            .then(parseJsonResponse)
            .then(data => {
                alert(`Deleted: ${data.success.length}\\nFailed: ${data.failed.length}`);
                location.reload();
            });
        }
        
        let activeEditorFile = '';

        function downloadFile(file) {
            window.location.href = '?action=download&file=' + encodeURIComponent(file) + '&dir=' + encodeURIComponent(currentDir);
        }

        function openEditorFromServer(file, allowEdit) {
            fetch('?action=view&file=' + encodeURIComponent(file) + '&dir=' + encodeURIComponent(currentDir))
            .then(parseJsonResponse)
            .then(data => {
                if (!data.success) {
                    throw new Error(data.error || 'Failed to open file');
                }

                activeEditorFile = file;
                document.getElementById('editorFileName').textContent = data.name || file;
                document.getElementById('editorFileSize').textContent = data.size || '-';
                document.getElementById('editorFilePerms').textContent = data.permissions || '-';
                document.getElementById('editorContent').value = data.content || '';

                const canEdit = allowEdit && data.editable;
                document.getElementById('editorTitle').textContent = canEdit ? '✏️ Edit File' : '👁️ View File';
                document.getElementById('editorContent').readOnly = !canEdit;
                document.getElementById('saveEditorBtn').style.display = canEdit ? 'inline-flex' : 'none';
                document.getElementById('editorModal').classList.add('active');

                if (allowEdit && !data.editable) {
                    alert('File ini hanya bisa dibuka dalam mode view / read-only.');
                }
            })
            .catch(error => {
                alert('Error: ' + error.message);
            });
        }

        function viewFile(file) {
            openEditorFromServer(file, false);
        }

        function editFile(file) {
            openEditorFromServer(file, true);
        }

        function closeEditorModal() {
            document.getElementById('editorModal').classList.remove('active');
        }

        function saveEditorFile() {
            if (!activeEditorFile) {
                alert('No file selected');
                return;
            }

            const formData = new FormData();
            formData.append('file', activeEditorFile);
            formData.append('base_dir', currentDir);
            formData.append('content', document.getElementById('editorContent').value);

            fetch('?action=save_file', {
                method: 'POST',
                body: formData
            })
            .then(parseJsonResponse)
            .then(data => {
                if (!data.success) {
                    throw new Error(data.error || 'Failed to save file');
                }
                alert('File berhasil disimpan');
                closeEditorModal();
                location.reload();
            })
            .catch(error => {
                alert('Error: ' + error.message);
            });
        }

        function showChmodModal(target, currentMode) {
            document.getElementById('chmodTarget').textContent = target;
            document.getElementById('chmodInput').value = currentMode || '';
            document.getElementById('chmodModal').dataset.target = target;
            document.getElementById('chmodModal').classList.add('active');
        }

        function closeChmodModal() {
            document.getElementById('chmodModal').classList.remove('active');
        }

        function applyChmod() {
            const modal = document.getElementById('chmodModal');
            const target = modal.dataset.target || '';
            const mode = document.getElementById('chmodInput').value.trim();

            if (!target || !mode) {
                alert('Target dan permission wajib diisi');
                return;
            }

            const formData = new FormData();
            formData.append('target', target);
            formData.append('base_dir', currentDir);
            formData.append('mode', mode);

            fetch('?action=chmod', {
                method: 'POST',
                body: formData
            })
            .then(parseJsonResponse)
            .then(data => {
                if (!data.success) {
                    throw new Error(data.error || 'Failed to chmod');
                }
                alert('Permission berhasil diubah menjadi ' + data.permissions);
                closeChmodModal();
                location.reload();
            })
            .catch(error => {
                alert('Error: ' + error.message);
            });
        }
        
        function showCreateFolderModal() {
            const folderName = prompt('Enter folder name:');
            if (!folderName) return;
            
            const formData = new FormData();
            formData.append('folder_name', folderName);
            formData.append('base_dir', currentDir);
            
            fetch('?action=create_folder', {
                method: 'POST',
                body: formData
            })
            .then(parseJsonResponse)
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Failed to create folder');
                }
            });
        }
        
        // Upload Functions
        function handleFileSelect(event) {
            const files = event.target.files;
            if (files.length > 0) {
                uploadFiles(files);
            }
        }
        
        function uploadFiles(files) {
            const modal = document.getElementById('uploadModal');
            const fileList = document.getElementById('uploadFileList');
            const summary = document.getElementById('uploadSummary');
            
            modal.classList.add('active');
            fileList.innerHTML = '';
            summary.innerHTML = 'Uploading...';
            
            const formData = new FormData();
            formData.append('upload_dir', currentDir);
            
            for (let i = 0; i < files.length; i++) {
                formData.append('upload_files[]', files[i]);
                
                const item = document.createElement('div');
                item.className = 'upload-file-item';
                item.innerHTML = `<span>📄 ${files[i].name}</span><span>${formatBytes(files[i].size)}</span>`;
                fileList.appendChild(item);
            }
            
            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(parseJsonResponse)
            .then(data => {
                const items = fileList.querySelectorAll('.upload-file-item');
                
                data.success.forEach(file => {
                    for (let item of items) {
                        if (item.textContent.includes(file.name)) {
                            item.classList.add('success');
                            break;
                        }
                    }
                });
                
                data.failed.forEach(file => {
                    for (let item of items) {
                        if (item.textContent.includes(file.name)) {
                            item.classList.add('failed');
                            item.innerHTML += `<br><small>${file.reason}</small>`;
                            break;
                        }
                    }
                });
                
                summary.innerHTML = `
                    <span style="color: #28a745;">✓ Success: ${data.success.length}</span> | 
                    <span style="color: #dc3545;">✗ Failed: ${data.failed.length}</span>
                `;
                
                setTimeout(() => {
                    if (data.success.length > 0) {
                        location.reload();
                    }
                }, 2000);
            })
            .catch(error => {
                summary.innerHTML = `<span style="color: #dc3545;">Error: ${error.message}</span>`;
            });
        }
        
        function closeUploadModal() {
            document.getElementById('uploadModal').classList.remove('active');
        }
        
        function formatBytes(bytes) {
            if (bytes >= 1073741824) return (bytes / 1073741824).toFixed(2) + ' GB';
            if (bytes >= 1048576) return (bytes / 1048576).toFixed(2) + ' MB';
            if (bytes >= 1024) return (bytes / 1024).toFixed(2) + ' KB';
            return bytes + ' B';
        }
        
        // Drag & Drop
        document.addEventListener('dragenter', function(e) {
            e.preventDefault();
            dragCounter++;
            if (dragCounter === 1) {
                document.getElementById('uploadZone').classList.add('active');
            }
        });
        
        document.addEventListener('dragleave', function(e) {
            dragCounter--;
            if (dragCounter === 0) {
                document.getElementById('uploadZone').classList.remove('active');
            }
        });
        
        document.addEventListener('dragover', function(e) {
            e.preventDefault();
        });
        
        document.addEventListener('drop', function(e) {
            e.preventDefault();
            dragCounter = 0;
            document.getElementById('uploadZone').classList.remove('active');
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                uploadFiles(files);
            }
        });
        
        // Terminal Functions
        function toggleTerminal() {
            const terminal = document.getElementById('terminal');
            terminal.classList.toggle('active');
            if (terminal.classList.contains('active')) {
                document.getElementById('terminalInput').focus();
                updateTerminalCwd();
            }
        }
        
        function clearTerminal() {
            document.getElementById('terminalOutput').innerHTML = '';
        }
        
        function updateTerminalCwd() {
            fetch('', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'terminal_action=get_cwd'
            })
            .then(parseJsonResponse)
            .then(data => {
                document.getElementById('terminalCwd').textContent = data.cwd;
            });
        }
        
        function executeCommand(command) {
            if (!command.trim()) return;
            
            commandHistory.unshift(command);
            historyIndex = -1;
            
            const output = document.getElementById('terminalOutput');
            const cmdLine = document.createElement('div');
            cmdLine.className = 'terminal-line';
            const promptSymbol = document.getElementById('terminalPromptSymbol') ? document.getElementById('terminalPromptSymbol').textContent : '$';
            cmdLine.innerHTML = `<span class="terminal-prompt">${document.getElementById('terminalCwd').textContent} ${escapeHtml(promptSymbol)}</span> <span class="terminal-command">${escapeHtml(command)}</span>`;
            output.appendChild(cmdLine);
            
            const formData = new FormData();
            formData.append('terminal_action', 'execute');
            formData.append('command', command);
            
            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(parseJsonResponse)
            .then(data => {
                if (data.clear) {
                    clearTerminal();
                    return;
                }
                
                if (data.output) {
                    const outputLine = document.createElement('div');
                    outputLine.className = 'terminal-line' + (data.success ? '' : ' terminal-error');
                    outputLine.textContent = data.output;
                    output.appendChild(outputLine);
                }
                
                if (data.cwd) {
                    document.getElementById('terminalCwd').textContent = data.cwd;
                }

                if (data.close_terminal) {
                    setTimeout(function() {
                        document.getElementById('terminal').classList.remove('active');
                    }, 300);
                }
                
                output.scrollTop = output.scrollHeight;
            })
            .catch(error => {
                const errorLine = document.createElement('div');
                errorLine.className = 'terminal-line terminal-error';
                errorLine.textContent = 'Error: ' + error.message;
                output.appendChild(errorLine);
                output.scrollTop = output.scrollHeight;
            });
        }
        
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        var terminalInput = document.getElementById('terminalInput');
        if (terminalInput) {
            terminalInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    const command = this.value;
                    executeCommand(command);
                    this.value = '';
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    if (historyIndex < commandHistory.length - 1) {
                        historyIndex++;
                        this.value = commandHistory[historyIndex];
                    }
                } else if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    if (historyIndex > 0) {
                        historyIndex--;
                        this.value = commandHistory[historyIndex];
                    } else if (historyIndex === 0) {
                        historyIndex = -1;
                        this.value = '';
                    }
                }
            });
        }
        
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === '`') {
                e.preventDefault();
                toggleTerminal();
            }

            if (e.key === 'Escape') {
                closeAllModals();
            }
        });

        document.querySelectorAll('.modal, .upload-modal').forEach(function(modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    modal.classList.remove('active');
                }
            });
        });

        let tableCurrentPage = 1;

        function getPaginationButton(action) {
            if (action === 'prev') {
                return document.getElementById('tablePrevBtn') || document.querySelector('[data-page-action="prev"]');
            }
            if (action === 'next') {
                return document.getElementById('tableNextBtn') || document.querySelector('[data-page-action="next"]');
            }
            return null;
        }

        function getRowsPerPageValue() {
            const rowsSelect = document.getElementById('rowsPerPage');
            return Math.max(1, parseInt(rowsSelect ? rowsSelect.value : '10', 10) || 10);
        }

        function getProfessionalVisibleRows() {
            const searchInput = document.getElementById('toolbarSearch');
            const query = (searchInput ? searchInput.value : '').toLowerCase().trim();
            return Array.from(document.querySelectorAll('.file-row')).filter(function(row) {
                return query === '' || row.textContent.toLowerCase().indexOf(query) !== -1;
            });
        }

        function getProfessionalTotalPages() {
            const totalRows = getProfessionalVisibleRows().length;
            return Math.max(1, Math.ceil(totalRows / getRowsPerPageValue()));
        }

        function setProfessionalTablePage(nextPage, resetPage) {
            if (resetPage) {
                tableCurrentPage = 1;
            } else {
                const totalPages = getProfessionalTotalPages();
                tableCurrentPage = Math.max(1, Math.min(totalPages, parseInt(nextPage, 10) || 1));
            }
            updateProfessionalTableView(false);
        }

        function changeProfessionalTablePage(delta) {
            setProfessionalTablePage(tableCurrentPage + delta, false);
        }

        function bindProfessionalTableControls() {
            const toolbarSearch = document.getElementById('toolbarSearch');
            if (toolbarSearch && toolbarSearch.dataset.paginationBound !== '1') {
                toolbarSearch.addEventListener('input', function() {
                    setProfessionalTablePage(1, true);
                });
                toolbarSearch.dataset.paginationBound = '1';
            }

            const rowsPerPage = document.getElementById('rowsPerPage');
            if (rowsPerPage && rowsPerPage.dataset.paginationBound !== '1') {
                rowsPerPage.addEventListener('change', function() {
                    setProfessionalTablePage(1, true);
                });
                rowsPerPage.dataset.paginationBound = '1';
            }

            const prevBtn = getPaginationButton('prev');
            if (prevBtn && prevBtn.dataset.paginationBound !== '1') {
                prevBtn.addEventListener('click', function(event) {
                    event.preventDefault();
                    if (prevBtn.disabled) {
                        return;
                    }
                    changeProfessionalTablePage(-1);
                });
                prevBtn.dataset.paginationBound = '1';
            }

            const nextBtn = getPaginationButton('next');
            if (nextBtn && nextBtn.dataset.paginationBound !== '1') {
                nextBtn.addEventListener('click', function(event) {
                    event.preventDefault();
                    if (nextBtn.disabled) {
                        return;
                    }
                    changeProfessionalTablePage(1);
                });
                nextBtn.dataset.paginationBound = '1';
            }

            if (!document.body.dataset.paginationKeyboardBound) {
                document.addEventListener('keydown', function(event) {
                    const tag = event.target && event.target.tagName ? event.target.tagName.toLowerCase() : '';
                    const typing = tag === 'input' || tag === 'textarea' || tag === 'select' || (event.target && event.target.isContentEditable);
                    if (typing) {
                        return;
                    }
                    if (event.key === 'ArrowLeft') {
                        const prev = getPaginationButton('prev');
                        if (prev && !prev.disabled) {
                            changeProfessionalTablePage(-1);
                        }
                    } else if (event.key === 'ArrowRight') {
                        const next = getPaginationButton('next');
                        if (next && !next.disabled) {
                            changeProfessionalTablePage(1);
                        }
                    }
                });
                document.body.dataset.paginationKeyboardBound = '1';
            }
        }

        function updateListCounters(totalRows, totalPages) {
            const totalCountEl = document.getElementById('listTotalCount');
            const visibleCountEl = document.getElementById('listVisibleCount');
            const currentPageEl = document.getElementById('listCurrentPage');
            const totalPagesEl = document.getElementById('listTotalPages');
            const allRowsCount = document.querySelectorAll('.file-row').length;

            if (totalCountEl) {
                totalCountEl.textContent = String(allRowsCount);
            }
            if (visibleCountEl) {
                visibleCountEl.textContent = String(totalRows);
            }
            if (currentPageEl) {
                currentPageEl.textContent = String(totalRows === 0 ? 0 : tableCurrentPage);
            }
            if (totalPagesEl) {
                totalPagesEl.textContent = String(totalRows === 0 ? 0 : totalPages);
            }
        }

        function updateProfessionalTableView(resetPage) {
            const rowsSelect = document.getElementById('rowsPerPage');
            const pageBadge = document.getElementById('tablePageBadge');
            const infoEl = document.getElementById('tableEntriesInfo');
            const prevBtn = document.getElementById('tablePrevBtn');
            const nextBtn = document.getElementById('tableNextBtn');
            const emptyState = document.getElementById('emptySearchState');
            const allRows = Array.from(document.querySelectorAll('.file-row'));
            const filteredRows = getProfessionalVisibleRows();
            const totalRows = filteredRows.length;
            const rowsPerPage = getRowsPerPageValue();
            const totalPages = Math.max(1, Math.ceil(totalRows / rowsPerPage));

            if (resetPage) {
                tableCurrentPage = 1;
            }
            if (tableCurrentPage > totalPages) {
                tableCurrentPage = totalPages;
            }
            if (tableCurrentPage < 1) {
                tableCurrentPage = 1;
            }

            const startIndex = totalRows === 0 ? 0 : (tableCurrentPage - 1) * rowsPerPage;
            const endIndex = Math.min(startIndex + rowsPerPage, totalRows);
            const visibleSlice = filteredRows.slice(startIndex, endIndex);

            allRows.forEach(function(row) {
                row.style.display = visibleSlice.indexOf(row) !== -1 ? '' : 'none';
            });

            if (emptyState) {
                emptyState.classList.toggle('active', totalRows === 0);
            }
            if (infoEl) {
                if (totalRows === 0) {
                    infoEl.textContent = 'Menampilkan 0 sampai 0 dari 0 item';
                } else {
                    infoEl.textContent = 'Menampilkan ' + (startIndex + 1) + ' sampai ' + endIndex + ' dari ' + totalRows + ' item';
                }
            }
            if (pageBadge) {
                pageBadge.textContent = totalRows === 0 ? '0 / 0' : (String(tableCurrentPage) + ' / ' + String(totalPages));
                pageBadge.title = totalRows === 0 ? 'Tidak ada halaman' : ('Halaman ' + tableCurrentPage + ' dari ' + totalPages);
            }
            updateListCounters(totalRows, totalPages);
            if (prevBtn) {
                prevBtn.disabled = tableCurrentPage <= 1 || totalRows === 0;
            }
            if (nextBtn) {
                nextBtn.disabled = tableCurrentPage >= totalPages || totalRows === 0;
            }
        }

        function filterFileRows(keyword) {
            const searchInput = document.getElementById('toolbarSearch');
            if (searchInput && typeof keyword === 'string' && searchInput.value !== keyword) {
                searchInput.value = keyword;
            }
            updateProfessionalTableView(true);
        }

        bindProfessionalTableControls();
        applyResponsiveDeviceMode();
        updateToolbarSelection();
        updateProfessionalTableView(true);
        window.addEventListener('resize', applyResponsiveDeviceMode);
        window.addEventListener('orientationchange', applyResponsiveDeviceMode);
        if (window.visualViewport) {
            window.visualViewport.addEventListener('resize', applyResponsiveDeviceMode);
        }
    </script>
</body>
</html>
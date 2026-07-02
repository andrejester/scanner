<?php

use App\Http\Controllers\Auth\Google2FAController;
use App\Http\Controllers\Auth\TwoFactorController;
use App\Http\Controllers\Backend\CkeditorController;
use App\Http\Controllers\Backend\FileScannerController;
use App\Http\Controllers\HomeController;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Route;
use RealRashid\SweetAlert\Facades\Alert;
use UniSharp\LaravelFilemanager\Lfm;

Carbon::setLocale('id');

Route::get('/', function () {
    return redirect('/log-adm');
});

Route::get('/welcome', function () {
    return redirect('/log-adm');
});

Route::get('/some-non-existing-page', function () {
    abort(404);
});

Route::get('/error', function () {
    abort(500);
});


// ADMIN
Route::middleware(['auth', 'role:Super Admin|Admin|Guest', '2fa'])->group(function () {

    Route::post('/backupDb', function () {
        Artisan::call('backup:run --only-db');
        Alert::info('Info Title', 'Database backup completed!');
        return redirect()->route('backup.index');
    })->name('backupDb');

    Route::get('backup', [HomeController::class, 'backup'])->name('backup.index');

    Route::get('/admin/clear-cache', [HomeController::class, 'clearCache'])->name('admin.clearCache');
    Route::get('/admin/storage-link', [HomeController::class, 'storageLink'])->name('admin.storageLink');

    Route::post('/ckeditor/upload', [CkeditorController::class, 'upload'])->name('ckeditor.upload');
    Route::get('/ckeditor/files', function () {
        $files = glob(public_path('uploads/ckeditor/*'));

        $list = [];
        foreach ($files as $file) {
            $list[] = asset('uploads/ckeditor/' . basename($file));
        }

        return view('ckeditor.files', ['files' => $list]);
    })->name('ckeditor.files');

    Route::get('file-manager', [HomeController::class, 'fileManager'])->name('file-manager');

    Route::get('/dashboard', [HomeController::class, 'show'])->name('dashboard');


    // File Scanner Routes
    Route::get('filescanner', [FileScannerController::class, 'index'])->name('filescanner.index');
    Route::get('filescanner/folders', [FileScannerController::class, 'getFolders'])->name('filescanner.folders');
    Route::post('filescanner/scan', [FileScannerController::class, 'scan'])->name('filescanner.scan');
    Route::delete('filescanner/clear-all', [FileScannerController::class, 'clearAll'])->name('filescanner.clearAll');
    Route::get('filescanner/{id}', [FileScannerController::class, 'show'])->name('filescanner.show');
    Route::post('filescanner/{id}/quarantine', [FileScannerController::class, 'quarantine'])->name('filescanner.quarantine');
    Route::post('filescanner/{id}/restore', [FileScannerController::class, 'restore'])->name('filescanner.restore');
    Route::delete('filescanner/{id}', [FileScannerController::class, 'destroy'])->name('filescanner.destroy');

    // Database Reset Routes (Development Only)
    Route::middleware(['env:local,development'])->prefix('system')->name('system.')->group(function () {
        Route::get('database-reset', [\App\Http\Controllers\System\DatabaseResetController::class, 'index'])->name('database-reset.index');
        Route::post('database-reset/truncate', [\App\Http\Controllers\System\DatabaseResetController::class, 'truncate'])->name('database-reset.truncate');
        Route::post('database-reset/truncate-all', [\App\Http\Controllers\System\DatabaseResetController::class, 'truncateAll'])->name('database-reset.truncate-all');

        // Database Conversion Routes
        Route::get('database-convert', [\App\Http\Controllers\System\DatabaseConvertController::class, 'index'])->name('database-convert.index');
        Route::post('database-convert/save-config', [\App\Http\Controllers\System\DatabaseConvertController::class, 'saveConfig'])->name('database-convert.save-config');
        Route::post('database-convert/source-columns', [\App\Http\Controllers\System\DatabaseConvertController::class, 'getSourceColumns'])->name('database-convert.source-columns');
        Route::post('database-convert/target-columns', [\App\Http\Controllers\System\DatabaseConvertController::class, 'getTargetColumns'])->name('database-convert.target-columns');
        Route::post('database-convert/preview-source', [\App\Http\Controllers\System\DatabaseConvertController::class, 'previewSource'])->name('database-convert.preview-source');
        Route::post('database-convert/convert', [\App\Http\Controllers\System\DatabaseConvertController::class, 'convert'])->name('database-convert.convert');
    });

    Route::get('/logout', function (Illuminate\Http\Request $request) {

        Auth::logout();

        // Hapus semua session
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Hapus cookie tambahan (opsional)
        foreach ($request->cookies->all() as $cookieName => $cookieValue) {
            Cookie::queue(Cookie::forget($cookieName));
        }

        return redirect()->route('log-adm');
    })->name('logout.get');
});

require __DIR__ . '/auth.php';

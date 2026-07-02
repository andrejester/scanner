<?php

use App\Http\Controllers\Auth\Google2FAController;
use App\Http\Controllers\Auth\TwoFactorController;
use App\Http\Controllers\Backend\AlumniTmpController;
use App\Http\Controllers\Backend\BannerAdsController;
use App\Http\Controllers\Backend\BannerController;
use App\Http\Controllers\Backend\BlogController;
use App\Http\Controllers\Backend\BookingAdminOutbondController;
use App\Http\Controllers\Backend\BookingController;
use App\Http\Controllers\Backend\CkeditorController;
use App\Http\Controllers\Backend\CommentsController;
use App\Http\Controllers\Backend\FileScannerController;
use App\Http\Controllers\Backend\MasterAkreditasiController;
use App\Http\Controllers\Backend\MasterAlumniController;
use App\Http\Controllers\Backend\MasterBeasiswaController;
use App\Http\Controllers\Backend\MasterDosenController;
use App\Http\Controllers\Backend\MasterDownloadController;
use App\Http\Controllers\Backend\MasterEventController;
use App\Http\Controllers\Backend\MasterFaqController;
use App\Http\Controllers\Backend\MasterGaleriController;
use App\Http\Controllers\Backend\MasterGaleriKampusController;
use App\Http\Controllers\Backend\MasterHerosectionController;
use App\Http\Controllers\Backend\MasterJurnalController;
use App\Http\Controllers\Backend\MasterKalenderAkademikController;
use App\Http\Controllers\Backend\MasterKategoriBeritaController;
use App\Http\Controllers\Backend\MasterKategoriDownloadController;
use App\Http\Controllers\Backend\MasterKategoriJurnalController;
use App\Http\Controllers\Backend\MasterKegiatanMahasiswaController;
use App\Http\Controllers\Backend\MasterLayananController;
use App\Http\Controllers\Backend\MasterPaketOutbondController;
use App\Http\Controllers\Backend\MasterPelatihanController;
use App\Http\Controllers\Backend\MasterPendaftaranProdiController;
use App\Http\Controllers\Backend\MasterPengumumanController;
use App\Http\Controllers\Backend\MasterPersyaratanUjianController;
use App\Http\Controllers\Backend\MasterPrivacyPolicyController;
use App\Http\Controllers\Backend\MasterProfilKampusController;
use App\Http\Controllers\Backend\MasterProgramStudiController;
use App\Http\Controllers\Backend\MasterSambutanDirekturController;
use App\Http\Controllers\Backend\MasterSupportteamController;
use App\Http\Controllers\Backend\MasterTaglineController;
use App\Http\Controllers\Backend\MasterVideoCategoryController;
use App\Http\Controllers\Backend\MasterVideoController;
use App\Http\Controllers\Backend\NoteController;
use App\Http\Controllers\Backend\PelatihanController;
use App\Http\Controllers\Backend\VisitorStatistikController;
use App\Http\Controllers\Frontend\DosenController;
use App\Http\Controllers\FrontendController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InboxController;
use App\Http\Controllers\PushSubscriptionController;
use App\Http\Controllers\System\PermissionController;
use App\Http\Controllers\System\ProfileController;
use App\Http\Controllers\System\SettingController;
use App\Http\Controllers\System\UserController;
use App\Models\Backend\Booking;
use App\Models\Backend\Inbox;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Route;
use RealRashid\SweetAlert\Facades\Alert;
use UniSharp\LaravelFilemanager\Lfm;

Carbon::setLocale('id');

Route::get('/welcome', function () {
    return view('welcome');
});

Route::get('/some-non-existing-page', function () {
    abort(404);
});

Route::get('/error', function () {
    abort(500);
});


Route::get('/choose-2fa', [TwoFactorController::class, 'choose'])->name('2fa.choose');
Route::post('/choose-2fa', [TwoFactorController::class, 'choosePost'])->name('2fa.choose.post');

Route::get('/2fa-otp', [TwoFactorController::class, 'showVerifyForm'])->name('otp.form');
Route::post('/2fa-otp', [TwoFactorController::class, 'verifyOtp'])->name('otp.verify');

Route::get('/2fa/google/setup', [Google2FAController::class, 'showSetup'])->name('2fa.google.setup');
Route::post('/2fa/google/verify', [Google2FAController::class, 'verify'])->name('2fa.google.verify');

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

    // Master
    Route::resource('banner', BannerController::class);
    Route::resource('banner-ads', BannerAdsController::class);
    Route::resource('mastergaleri', MasterGaleriController::class);
    Route::resource('mastersambutandirektur', MasterSambutanDirekturController::class);
    Route::resource('mastervideo', MasterVideoController::class);
    Route::resource('mastervideocategory', MasterVideoCategoryController::class);

    Route::resource('masterkategoridownload', MasterKategoriDownloadController::class);
    Route::resource('masterdownload', MasterDownloadController::class);

    // Comments Management
    Route::get('comments', [CommentsController::class, 'index'])->name('comments.index');
    Route::get('comments/{id}', [CommentsController::class, 'show'])->name('comments.show');
    Route::put('comments/{id}/status', [CommentsController::class, 'updateStatus'])->name('comments.updateStatus');
    Route::delete('comments/{comment}', [CommentsController::class, 'destroy'])->name('comments.destroy');

    Route::resource('mastersupportteam', MasterSupportteamController::class);
    Route::resource('masterlayanan', MasterLayananController::class);
    Route::resource('masterkategoriberita', MasterKategoriBeritaController::class);
    Route::resource('mastertagline', MasterTaglineController::class);
    Route::Resource('masterfaq', MasterFaqController::class);
    Route::Resource('masterprivacypolicy', MasterPrivacyPolicyController::class);

    Route::get('backup', [HomeController::class, 'backup'])->name('backup.index');
    Route::get('versi', [HomeController::class, 'versi'])->name('versi.index');

    Route::resource('user', UserController::class);
    Route::resource('permission', PermissionController::class);
    Route::resource('profile-user', ProfileController::class);
    Route::resource('setting', SettingController::class);

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('inbox', InboxController::class);

    // Push Notification Subscription
    Route::post('/push/subscribe', [PushSubscriptionController::class, 'store'])->name('push.subscribe');
    Route::post('/push/unsubscribe', [PushSubscriptionController::class, 'destroy'])->name('push.unsubscribe');
    Route::resource('notes', NoteController::class);
    Route::get('/notes/edit/{id}', [NoteController::class, 'edit'])->name('notes.edit');
    Route::post('/notes/{id}/position', [NoteController::class, 'updatePosition'])->name('notes.updatePosition');
    Route::delete('notes/{note}', [NoteController::class, 'destroy'])->name('notes.destroy');

    Route::Resource('blogadmin', BlogController::class);

    Route::get('/statistik/monthly/{year}', [VisitorStatistikController::class, 'monthly']);
    Route::get('/statistik/monthly-activity/{year}', [VisitorStatistikController::class, 'monthlyActivity']);

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

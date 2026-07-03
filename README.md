# Laravel File Scanner

Aplikasi web berbasis Laravel 11 untuk memindai dan mendeteksi file berbahaya (webshell, backdoor, malware) pada direktori upload. Dirancang untuk membantu administrator server mengidentifikasi ancaman keamanan pada file yang diunggah pengguna.

## Fitur Utama

- **22 Kategori Pemeriksaan** – mencakup signature scanner, obfuscation, dynamic include, remote include, hidden upload, image shell, IOC (Indicators of Compromise), dan lainnya
- **Threat Scoring** – setiap file mendapat skor ancaman dan dikategorikan ke level: `critical`, `high`, `medium`, `low`, atau `safe`
- **Karantina File** – file berbahaya dapat dipindahkan ke folder karantina terpisah, serta dipulihkan kembali jika diperlukan
- **Scan Berulang** – hasil scan lama dihapus otomatis sebelum scan ulang agar data selalu segar
- **Detail Temuan** – setiap file menampilkan isi konten dan daftar pola mencurigakan yang terdeteksi, dikelompokkan per kategori
- **DataTable Interaktif** – tabel hasil scan dengan filter, sorting, dan badge visual per level ancaman
- **Role & Permission** – akses dikontrol via Spatie Permission (`filescanner_read`, `filescanner_write`, `filescanner_delete`)
- **IOC Database** – mendeteksi tanda tangan webshell terkenal: WSO, C99, R57, B374K, China Chopper, IndoXploit, SMokWSO, Secure PHP File Manager, Adminer, dan lainnya

## Kategori Deteksi

| Kategori | Contoh |
|---|---|
| Signature Scanner | `eval()`, `exec()`, `shell_exec()`, `base64_decode()` |
| Dangerous Combination | `eval(base64_decode(...))`, `system($_GET[...])` |
| Superglobal Input | `$_GET`, `$_POST`, `$_REQUEST`, `php://input` |
| Obfuscation | `chr()`, `str_rot13()`, `strrev()`, `pack()` |
| Encoded String | Base64 panjang (>500 char), eval+base64 embedded |
| Hex String | `\x41`, `0x4141` |
| Very Long Line | Baris >1000 karakter |
| Suspicious Variable | `$$var`, `$GLOBALS`, dynamic variable |
| Dynamic Function Call | `call_user_func()`, `create_function()`, `preg_replace /e` |
| Dynamic/Remote Include | `include($var)`, `include('http://...')` |
| Hidden Upload | `move_uploaded_file()`, `file_put_contents()`, `fwrite()` |
| Image Shell | Ekstensi ganda `.jpg.php`, ekstensi `phtml/phar` |
| Fake Image | Tag `<?php` di dalam file gambar |
| Suspicious Filename | `shell.php`, `cmd.php`, `backdoor`, `c99`, `r57`, dll |
| Permission Scanner | `chmod(0777)` |
| File Manager Detection | File dengan ≥4 fungsi FM: `scandir`, `unlink`, `rename`, dll |
| IOC Scanner | WSO, C99, R57, B374K, Adminer, Telegram exfiltration, dll |

## Stack Teknologi

- **Framework**: Laravel 11 (PHP ^8.2)
- **Frontend**: Livewire 3, Inertia.js, Vite
- **DataTable**: Yajra Laravel DataTables 11
- **Auth & Permission**: Spatie Laravel Permission 6
- **File Storage**: Laravel `storage/app/public/files/2/`
- **Database**: MySQL
- **Alert**: RealRashid SweetAlert
- **Image Processing**: Intervention Image 3
- **PDF**: barryvdh/laravel-dompdf

## Instalasi

```bash
# Clone repo
git clone <repo-url>
cd scanner

# Install dependencies
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Database
php artisan migrate
php artisan db:seed   # jika tersedia

# Storage link
php artisan storage:link

# Build assets
npm run build
```

## Konfigurasi `.env`

```dotenv
APP_NAME="Scanner"
APP_URL=http://localhost
APP_TIMEZONE=Asia/Jakarta

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nama_database
DB_USERNAME=username
DB_PASSWORD=password
```

## Cara Penggunaan

1. Login sebagai user dengan permission `filescanner_read` / `filescanner_write`
2. Buka menu **File Scanner** di sidebar
3. Masukkan nama folder atau prefix folder target (misal: `assist-bpr.net`)
4. Tentukan kedalaman scan (default: 10 level)
5. Klik **Scan** — sistem akan memindai semua file di dalam folder tersebut
6. Hasil tampil di tabel, diurutkan dari level ancaman tertinggi
7. Klik **Detail** untuk melihat isi file dan daftar deteksi lengkap
8. Gunakan tombol **Karantina** untuk mengamankan file berbahaya

## Struktur Direktori Relevan

```
app/
├── Http/Controllers/Backend/FileScannerController.php  # Core scanner engine
├── DataTables/FileScannerDataTable.php                 # Tabel hasil scan
├── Models/Backend/FileScanner.php                      # Model hasil scan
└── Console/Commands/TestFileScannerCommand.php         # Artisan test command

storage/app/
├── public/files/2/    # Direktori target scan (file upload)
└── quarantine/        # File yang dikarantina
```

## Permission

| Permission | Akses |
|---|---|
| `filescanner_read` | Melihat hasil scan dan detail file |
| `filescanner_write` | Menjalankan scan, karantina, restore |
| `filescanner_delete` | Hapus log scan |

## Lisensi

MIT

## Menjalankan Aplikasi

```bash
php artisan serve --host=0.0.0.0 --port=8000
```


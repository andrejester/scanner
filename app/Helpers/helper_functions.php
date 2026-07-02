<?php

use App\Models\System\Config;
use App\Models\System\Invoice;
use App\Models\System\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

function Number2String($nNumber, $nDecimals = 2)
{
    $nNumber = floatval(String2Number($nNumber));
    return number_format($nNumber, $nDecimals, ".", ",");
}

function ZFormat($nValue, $nDec = false)
{
    $cChar = "";
    if ($nValue < 0) $cChar = "()";
    if (!$nDec) $nDec = 2;
    $cRetval = number_format(abs($nValue), $nDec);
    if ($cRetval == number_format(0, $nDec)) {
        $cChar = "";
    }
    $cRetval = substr($cChar, 0, 1) . $cRetval . substr($cChar, 1, 1);

    return $cRetval;
}

function menu()
{

    $data = array(
        // Note
        "Note" => array("notes" => ["Read"]),

        // Beranda
        "Sambutan Direktur" => array("mastersambutandirektur" => ["Write", "Read", "Update", "Delete"]),
        "Profil Kampus" => array("masterprofilkampus" => ["Write", "Read", "Update", "Delete"]),
        "Banner Utama" => array("banner" => ["Write", "Read", "Update", "Delete"]),
        "Banner Iklan" => array("banner" => ["Write", "Read", "Update", "Delete"]),
        "Tagline" => array("mastertagline" => ["Write", "Read", "Update", "Delete"]),

        // Akademik
        "Program S2" => array("masterprogramstudis2" => ["Write", "Read", "Update", "Delete"]),
        "Program S3" => array("masterprogramstudis3" => ["Write", "Read", "Update", "Delete"]),
        "Pendaftaran S2" => array("masterpendaftaranprodis2" => ["Write", "Read", "Update", "Delete"]),
        "Pendaftaran S3" => array("masterpendaftaranprodis3" => ["Write", "Read", "Update", "Delete"]),
        "Persyaratan Ujian" => array("masterpersyaratanujian" => ["Write", "Read", "Update", "Delete"]),
        "Akreditasi" => array("masterakreditasi" => ["Write", "Read", "Update", "Delete"]),

        // Informasi
        "Kegiatan Mahasiswa" => array("masterkegiatanmahasiswa" => ["Write", "Read", "Update", "Delete"]),
        "Alumni" => array("masteralumni" => ["Write", "Read", "Update", "Delete"]),
        "Pendaftaran Alumni" => array("alumnitmp" => ["Write", "Read", "Update", "Delete"]),
        "Layanan Akademik" => array("masterlayanan" => ["Write", "Read", "Update", "Delete"]),
        "Daftar Jurnal" => array("masterjurnal" => ["Write", "Read", "Update", "Delete"]),
        "Kategori Jurnal" => array("masterkategorijurnal" => ["Write", "Read", "Update", "Delete"]),
        "Kalender Akademik" => array("masterkalenderakademik" => ["Write", "Read", "Update", "Delete"]),
        "Master Beasiswa" => array("masterbeasiswa" => ["Write", "Read", "Update", "Delete"]),
        "Pengumuman" => array("masterpengumuman" => ["Write", "Read", "Update", "Delete"]),
        "Comments" => array("comments" => ["Write", "Read", "Update", "Delete"]),
        "FAQ" => array("masterfaq" => ["Write", "Read", "Update", "Delete"]),

        // Berita
        "Berita Kampus" => array("blogadmin" => ["Write", "Read", "Update", "Delete"]),
        "Kategori Berita" => array("masterkategoriberita" => ["Write", "Read", "Update", "Delete"]),
        "Event" => array("masterevent" => ["Write", "Read", "Update", "Delete"]),

        // Manajemen Konten
        "Inbox" => array("inbox" => ["Write", "Read", "Update", "Delete"]),

        // User Role & Permission
        "User" => array("user" => ["Write", "Read", "Update", "Delete"]),
        "Permission" => array("permission" => ["Write", "Read", "Update", "Delete"]),

        // System
        "Backup Database" => array("backup" => ["Read"]),
        "Versi Aplikasi" => array("versi" => ["Write", "Read", "Update", "Delete"]),
    );
    return $data;
}

if (!function_exists('String2Number')) {
    function String2Number($cKey)
    {
        return str_replace(".", "", $cKey);
    }
}
if (!function_exists('format_currency')) {
    function format_currency($value, $format = true)
    {
        if (!$format) {
            return $value;
        }

        $settings = settings();
        $position = $settings->default_currency_position;
        $symbol = $settings->currency->symbol;
        $decimal_separator = $settings->currency->decimal_separator;
        $thousand_separator = $settings->currency->thousand_separator;
        if ($position == 'prefix') {
            $formatted_value = $symbol . number_format($value, 2, $decimal_separator, $thousand_separator);
        } else {
            $formatted_value = number_format($value, 2, $decimal_separator, $thousand_separator) . $symbol;
        }

        return $formatted_value;
    }
}

if (!function_exists('convertRupiahToNumber')) {
    function convertRupiahToNumber($rupiah)
    {
        // Hapus awalan 'Rp ' dan tanda pemisah ribuan
        $number = str_replace(['Rp ', ',', '.'], '', $rupiah);

        // Ubah string menjadi angka (float)
        $number = (float) $number;

        return $number;
    }
}
if (!function_exists('settings')) {
    function settings()
    {
        $settings = cache()->remember('settings', 24 * 60, function () {
            return  Setting::firstOrFail();
        });

        return $settings;
    }
}

if (!function_exists('portfolioCategories')) {
    function portfolioCategories()
    {
        return cache()->remember('portfolio_categories', 60, function () {
            return \App\Models\Master\MasterPortofolioCategory::where('status', 'active')
                ->orderBy('title')
                ->get();
        });
    }
}


if (!function_exists('make_reference_id')) {
    function make_reference_id($prefix, $number)
    {
        $padded_text = $prefix . '-' . str_pad($number, 5, 0, STR_PAD_LEFT);

        return $padded_text;
    }
}

if (!function_exists('log_custom')) {
    function log_custom($info, $data = [])
    {
        //$userName = Auth::user()->name;
        $ip = $_SERVER['REMOTE_ADDR'];
        Log::info("$ip  $info", $data);
    }
}


function differenceDay($date_start, $date_end, $month = false)
{
    $date_start = Carbon::createFromFormat('Y-m-d', $date_start);
    $date_end = Carbon::createFromFormat('Y-m-d', $date_end);

    if ($month) {
        return  $date_start->diffInMonths($date_end);
    }
    return  $date_start->diffInDays($date_end);
}

function getKeterangan($id, $table, $field = 'name', $where = '')
{
    $cResult = "";
    $where = ($where == '') ? 'Kode' : $where;
    $data = DB::select("select $field name from $table where $where ='$id'");
    foreach ($data as $key => $value) {
        $cResult = $value->name;
    }
    return $cResult;
}


function getConfig($code, $default = '')
{
    $data = Config::where('code', $code)->first();
    $value = $data ? $data->value : $default;
    return $value;
}

function getTgl($user_id = '')
{
    return date("Y-m-d");
    $user_id = ($user_id == "") ? Auth::user()->id : $user_id;
    $dTgl = date("Y-m-d");
    $dateNow = Date("Y-m-d H:i:s");
    $data = getDataTable("date", "user_dates", "", "user_id = '$user_id' and date_end > '$dateNow' ", "", "id desc", "1");
    $arr = (array)$data;
    if ($arr) {
        $dTgl = $data[0]->date;
    }
    return $dTgl;
}

function getDataTable($field, $table, $join = '', $where = '', $group = '', $order = '', $limit = '')
{
    $where = $where != '' ? "where " . $where : $where;
    $group = $group != '' ? "group by " . $group : $group;
    $order = $order != '' ? "order by " . $order : $order;
    $limit = $limit != '' ? "limit  " . $limit : $limit;
    $data = DB::select("select $field from $table $join $where $group $order $limit");
    return $data;
}

function tanggalIndonesia($tgl, $format = "d M, Y")
{
    return Carbon::parse($tgl)->format($format);
}

function nextMonth($dateStart, $interval_bulan)
{
    // Tanggal awal
    $dateStart = Carbon::parse($dateStart);
    $dateEnd = $dateStart->copy()->addMonthsNoOverflow(intval($interval_bulan));

    // Format tanggal ke dalam format yang diinginkan
    $dateEnd = $dateEnd->toDateString();
    return $dateEnd;
}

function getKe($date_start, $n, $date)
{

    $ke = 0;
    for ($start = 1; $start <= $n; $start++) {
        $jthtmp = nextMonth($date_start, $start, true);
        if (strtotime($jthtmp) <= strtotime($date)) {
            $ke++;
        }
    }
    return $ke;
}

function incrementVersion($version)
{
    // Pisahkan versi ke dalam array
    $parts = explode('.', $version);

    // Lakukan increment untuk setiap bagian versi
    foreach ($parts as $key => $part) {
        // Convert bagian versi menjadi integer
        $parts[$key] = (int)$part;

        // Tingkatkan versi terakhir dan keluar dari loop
        if ($key === count($parts) - 1) {
            $parts[$key]++;
            break;
        }
    }

    // Gabungkan kembali versi ke dalam string
    $newVersion = implode('.', $parts);

    // Kembalikan versi yang baru
    return $newVersion;
}

function GetSize($nSize)
{
    $cRetval = number_format($nSize, 2) . " B";

    $vaSize = array("KB", "MB", "GB", "TB");
    $n = 1024;
    foreach ($vaSize as $key => $value) {
        if ($nSize > $n) {
            $nSize = $nSize / $n;
            $cRetval = number_format($nSize, 2) . " " . $value;
        }
        $n = 1000;
    }
    return $cRetval;
}

function String2SQL($cChar)
{
    $cChar = str_replace("\\", "\\\\", $cChar);
    $cChar = str_replace("'", "\'", $cChar);
    $cChar = str_replace('"', '\"', $cChar);
    $cChar = str_replace("\n", "//n", $cChar);
    return $cChar;
}

function SQL2String($cChar)
{
    $cChar = str_replace("//n", "\n", $cChar);
    return $cChar;
}

function NextDay($nTime, $nNextDay)
{
    $nDay = date("d", $nTime);
    $nMonth = date("m", $nTime);
    $nYear = date("Y", $nTime);

    $n = mktime(0, 0, 0, $nMonth, $nDay + $nNextDay, $nYear);
    return $n;
}

function NextWeek($nTime, $nNextWeek)
{
    return NextDay($nTime, $nNextWeek * 7);
}

function Date2String($dTgl)
{
    $cRetval = substr($dTgl, 0, 10);
    $va = explode("-", $dTgl);
    // Jika Array 1 Bukan Tahun maka akan berisi 2 Digit
    if (strlen($va[0]) == 2) {
        $cRetval = $va[2] . "-" . $va[1] . "-" . $va[0];
    }
    return $cRetval;
}

function String2Date($cString)
{
    $cRetval = substr($cString, 0, 10);
    $va = explode("-", $cString);
    // Jika Array 1 Tahun maka akan berisi 4 Digit
    if (strlen($va[0]) == 4) {
        $cRetval = $va[2] . "-" . $va[1] . "-" . $va[0];
    }
    return $cRetval;
}

function extractYouTubeVideoId($url)
{
    preg_match('/(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/|\S*?[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $url, $matches);
    return $matches[1] ?? null;  // Return the video ID or null if not found
}

function extractInstagramEmbedUrl($url)
{
    if (preg_match('#instagram\.com\/(?:p|reel|tv)\/([^\/\?\&]+)#i', $url, $matches)) {
        return 'https://www.instagram.com/' . $matches[1] . '/embed/';
    }
    return null;
}

function extractTikTokEmbedUrl($url)
{
    $params = '?autoplay=1&mute=1&loop=1';

    // Jika input adalah blockquote embed code
    if (str_contains($url, '<blockquote') && str_contains($url, 'tiktok-embed')) {
        return ['type' => 'blockquote', 'content' => $url];
    }

    // Jika input adalah URL biasa
    if (preg_match('/tiktok\.com\/@[^\/]+\/video\/(\d+)/i', $url, $matches)) {
        return ['type' => 'iframe', 'url' => 'https://www.tiktok.com/embed/v2/' . $matches[1] . $params];
    }

    if (preg_match('/tiktok\.com\/(?:v|embed)\/(\d+)/i', $url, $matches)) {
        return ['type' => 'iframe', 'url' => 'https://www.tiktok.com/embed/v2/' . $matches[1] . $params];
    }

    if (preg_match('/(?:vm|m)\.tiktok\.com\/([A-Za-z0-9]+)/i', $url, $matches)) {
        return ['type' => 'iframe', 'url' => 'https://www.tiktok.com/embed/' . $matches[1] . $params];
    }

    return null;
}

function youtube($url)
{
    $link = str_replace('http://www.youtube.com/watch?v=', '', $url);
    $link = str_replace('https://www.youtube.com/watch?v=', '', $link);
    $data = '<object class="video embed-responsive-item" width="100%" height="250px"  data="http://www.youtube.com/v/' . $link . '" type="application/x-shockwave-flash">
	<param name="src" value="http://www.youtube.com/v/' . $link . '" />
	</object>';
    return $data;
}
function kategoriyoutube($url)
{
    $link = str_replace('http://www.youtube.com/watch?v=', '', $url);
    $link = str_replace('https://www.youtube.com/watch?v=', '', $link);
    $data = '<object class="kat-video" width="325" height="200" data="http://www.youtube.com/v/' . $link . '" type="application/x-shockwave-flash">
	<param name="src" value="http://www.youtube.com/v/' . $link . '" />
	</object>';
    return $data;
}
function detailyoutube($url)
{
    $link = str_replace('http://www.youtube.com/watch?v=', '', $url);
    $link = str_replace('https://www.youtube.com/watch?v=', '', $link);
    $data = '<object width="800" data="http://www.youtube.com/v/' . $link . '" type="application/x-shockwave-flash">
	<param name="src" value="http://www.youtube.com/v/' . $link . '" />
	</object>';
    return $data;
}

if (! function_exists('get_keteranganID')) {
    function get_keteranganID($cID, $cField, $cTable, $Kode = "")
    {
        $cTable = strtolower($cTable);
        $cKeterangan = "";

        // Define the where condition based on the optional Kode
        if (!empty($Kode) && trim($Kode) !== "") {
            $cWhere = $Kode . " = ?";
            $dbData = DB::table($cTable)->whereRaw($cWhere, [$cID])->first();
        } else {
            $dbData = DB::table($cTable)->where('ID', $cID)->first();
        }

        // If a result is found, return the specified field value
        if ($dbData) {
            $cKeterangan = $dbData->$cField;
        }

        return $cKeterangan;
    }

    //membuat fungsi untuk merubah json supaya bisa menjadi string , lalu jika ada lebih dari satu data maka akan di pisahkan dengan &, contoh : SLF&PBG
    function jsonToString($json)
    {
        $data = json_decode($json);
        $cRetval = "";
        if (count($data) > 1) {
            foreach ($data as $key => $value) {
                $cRetval .= $value . " & ";
            }
            $cRetval = substr($cRetval, 0, -2);
        } else {
            $cRetval = $data[0];
        }
        return $cRetval;
    }

    //membuat fungsi untuk menjadikan timestamp menjadi format tanggal
    function timestampToDate($timestamp)
    {
        return date('d-m-Y', strtotime($timestamp));
    }

    //membuat option value untuk select tahun di html
    function optionTahun($start, $end)
    {
        $option = "";
        for ($i = $start; $i <= $end; $i++) {
            $option .= "<option value='$i'>$i</option>";
        }
        return $option;
    }
    if (!function_exists('get_setting')) {
        /**
         * Mendapatkan nilai setting dari session
         *
         * @param string $cKey Kunci setting
         * @param mixed $cDefault Nilai default
         * @return mixed
         */
        function get_setting(string $cKey, $cDefault = '')
        {
            $key = generate_session_key($cKey);

            if (!session()->has($key)) {
                session([$key => $cDefault]);
            }

            return session($key);
        }
    }

    if (!function_exists('save_setting')) {
        /**
         * Menyimpan nilai setting ke session
         *
         * @param string $cKey Kunci setting
         * @param mixed $cValue Nilai yang disimpan
         */
        function save_setting(string $cKey, $cValue): void
        {
            session([generate_session_key($cKey) => $cValue]);
        }
    }

    if (!function_exists('generate_session_key')) {
        /**
         * Generate unique session key
         *
         * @param string $cKey
         * @return string
         */
        function generate_session_key(string $cKey): string
        {
            return md5(
                session()->getId() .
                    __FILE__ .
                    strtolower($cKey)
            );
        }
    }
}

if (!function_exists('showLastLoginInfo')) {
    /**
     * Ambil aktivitas login terakhir berdasarkan email & IP dari log terbaru
     *
     * @param string $email
     * @param string $ip
     * @return string|null
     */
    function showLastLoginInfo($email, $ip)
    {
        $lastLogin = null;
        $logDir = storage_path('logs');

        try {
            // Ambil semua file log, termasuk customlog.log & harian
            $logFiles = collect(File::files($logDir))
                ->filter(fn($f) => preg_match('/(laravel-\d{4}-\d{2}-\d{2}\.log|customlog\.log)/', $f->getFilename()))
                ->sortByDesc(fn($f) => $f->getMTime()); // urutkan dari terbaru

            foreach ($logFiles as $file) {
                // Baca baris-baris log dari bawah (terbaru ke lama)
                $lines = collect(file($file->getRealPath(), FILE_IGNORE_NEW_LINES))->reverse();

                foreach ($lines as $line) {
                    if (str_contains($line, $ip) && str_contains($line, $email)) {
                        // Ambil waktu di dalam tanda []
                        if (preg_match('/\[(.*?)\]/', $line, $matches)) {
                            $timestamp = $matches[1] ?? null;

                            // Bandingkan waktu agar hanya simpan yang paling baru
                            if ($timestamp && (!$lastLogin || $timestamp > $lastLogin)) {
                                $lastLogin = $timestamp;
                            }
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::error('Gagal membaca log aktivitas terakhir: ' . $e->getMessage());
        }

        return $lastLogin;
    }
}

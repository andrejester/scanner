<?php
/*
|--------------------------------------------------------------------------
| global_config
|--------------------------------------------------------------------------
| Peringatan :
| jangan menambah Element $config pada file ini, kalau anda membutuhkan config
| tambahan silahkan deklarasikan pada file local_config.php
| Karena Elemant pada global_config akan selalu di control oleh Component
| anda hanya diperkenankan merubah Value dari config bukan menambah / mengurangi config
|
*/

/*
|--------------------------------------------------------------------------
| BASE URL dan COMPONENT URL
|--------------------------------------------------------------------------
|
| Untuk definisikan BaseURL dan ComponentURL
| 1. global_base_url di isi dengan :
|    url = Akan di gunakan sebagai base URL contoh http://localhost
|    ''  = Kalau kita isi petik kosong maka akan menggunakan folder lokasi index.php contoh di dalam folder /public
| 2. global_component_url
|    url = Akan di gunakan sebagai base URL contoh http://localhost/component
|    ''  = Akan menghibung dari base_url parent/parent/component
*/
$config['global_base_url'] = '' ;
$config['global_component_url'] = '' ;

/*
|--------------------------------------------------------------------------
| IP Allowed
|--------------------------------------------------------------------------
|
| Untuk definisikan kalau kita hanya menerima Request dari ip yang kita definiskan
|
| Penggunaan :
| 1. Set TRUE dan tambahkan daftar ip pada config 'global_ip_allowed'
|
*/
$config['global_ip_allowed_enable'] = false ;

/*
|--------------------------------------------------------------------------
| IP Allowed
|--------------------------------------------------------------------------
|
| Daftar IP yang boleh melakukan Request, cara penulisan dengan pemisah ,
|
| Contoh : '123.456.789.0, 987.654.32.1'
|
*/
$config['global_ip_allowed'] = '' ;

/*
|--------------------------------------------------------------------------
| Global IP Blacklisting
|--------------------------------------------------------------------------
|
| Untuk definisikan kalau ada daftar ip yang kita akan block sehingga tidak
| setiap request dari ip tersebut akan kita reject
|
| Penggunaan :
| 1. Set TRUE dan tambahkan daftar ip pada config 'global_ip_blacklist'
|
*/
$config['global_ip_blacklist_enabled'] = false ;

/*
|--------------------------------------------------------------------------
| IP Blacklist
|--------------------------------------------------------------------------
|
| Daftar IP yang kita blacklist kita tulis dengan pemisah Koma
|
| Contoh : '123.456.789.0, 987.654.32.1'
|
*/
$config['global_ip_blacklist'] = '' ;

/*
|--------------------------------------------------------------------------
| Autentication Type
|--------------------------------------------------------------------------
|
| Autentication Type kita gunakan untuk mendefinisikan Cara dalam melakukan Autentication yaitu
| type 	: class, global_key, none
|
| Penjelasan :
| class = System akan otomatis Membuka file pada project pada folder PROJECT/config/global_aut.php
|					dan akan menjalankan Methods MVC_Autentication dia akan memiliki kondisi sebagai berikut
|         1. Kalau File Tidak ada / methods tidak ada maka Request akan di tolak.
|					2. Kalau ada Class dan methods yang di butuhkan dia membutuhkan pengembalian methods itu
|            >> TRUE = Berarti Autentication Valid dan Request kita jalankan
|						 >> FALSE = Berarti Autentication Gagal dan Request kita Reject.
| none = akan berjalan tanpa Autentication.
|
*/
$config['global_authentication_type'] = 'class' ;

/*
| --------------------------------------------------------------------------
| Restrict Method
| --------------------------------------------------------------------------
| Restrict Method akan membuat Konfigurasi Standart Method yang kita definisi di dalam Controller
| Dia berisi dua Parameter true atau false
| restrict_method = true ( Standart Method tidak bisa di akses via URL harus meluis a.ajax / openForm )
| restrict_method = false ( Semua Method bersifat Public dan bisa di akses melalui URL walaupun masih harus melalui Autentication )
|
| Pada Saat anda mendefinikan restrict_method = true maka semua method didalam controller bersifat private.
| Jika kita ingin menjadikan sebuah method menjadi public harus di atur di dalam routes dengan perintah
| 		MVC:PublicMethod("controller/method") ;
*/
$config['restrict_method'] = true ;

/*
| --------------------------------------------------------------------------
| Auto Search Controller File
| --------------------------------------------------------------------------
| System akan otomatis mencari File Controller di dalam Folder MVC dan di Sub Dir di bawahnya.
| Di sarankan untuk tidak mencari secara otomatis, untuk memudahkan membaca Struktur System
| auto_search_controller_file = false ( Standart / Rekomendasi )
| auto_search_controller_file = true ( System Akan mencari file controller otomatis di dalam folder project/mvc/ dan folder di bawahnya )
*/
$config['auto_search_controller_file'] = false ;
<?php

/* --------------------------------------------------------------------------------------------
| Definisi Routes
| Kita bisa membuat definisi routes sesuai dengan EndPoint jika dibutuhkan
| MVC::Routes("segment","controller:index") ;
|
| contoh :
| 				MVC::Routes("/master/agama","mstagama::index") ;
|
| Kita akan memindah end point http://dns.sis1.net/master/agama
| akan kita routes ke controller : mstagama method : index 																		
|
| Bisa Juka Kita Mendefinisikan Sub Direktori dengan cara
| MVC::Routes("subdir:/dir1/dir2/")
|
| contoh :
|					MVC::Routes("subdir:/laporan/") ;
|					MVC::Routes("subdir:/transaksi/") ;
| Pada Contoh di atas dia akan mencari Controller dan Method pada directory yang di definisikan 
| Dari contoh itu kalau kita mengetikan http://dns.sis1.net/laporan/rptneraca/index
|     Maka dia akan mencari Controller rptneraca di dalam directory mvc/laporan dan menjalankan
|			Method index.
----------------------------------------------------------------------------------------------*/
MVC::Routes("/") ;

/* --------------------------------------------------------------------------------------------
| Kita Definikan Semua Method yang bersifat Public yang bisa di akses melalui url di sini
| Untuk Membuat PublicMethod memiliki beberapa Cara :
| 1. MVC::PublicMethod("controller::method") -> Mendefinisikan Public Method
| 2. MVC::PublicMethod("controller::method1,method2,method3") -> Mendefinisikan method1,method2,method3 menjadi Public
| 3. MVC::PublicMethod("controller::*") -> Mendefinisikan Semua Method pada Controller tersebut Menjadi public
| 4. MVC::PublicMethod("*::index") -> Semua method dengan nama index akan di public untuk semua controller
| 5. MVC::PublicMethod("*::index,method2,method3") -> Semua method dengan nama index,method2,method3 akan di public untuk semua controller
| 6. MVC::PublicMethod("*.*") -> Semua method di semua controller bersifat public
----------------------------------------------------------------------------------------------*/
MVC::PublicMethod("home::index") ;
MVC::PublicMethod("atm::index") ;
MVC::PublicMethod("mobile::index") ;
MVC::PublicMethod("mobile_h2h::index") ;
MVC::PublicMethod("webcore::index") ;
MVC::PublicMethod("cbd::index") ;
MVC::PublicMethod("sakep::index") ;
MVC::PublicMethod("callback::index") ;
MVC::PublicMethod("atm_bepede::index") ;

?>
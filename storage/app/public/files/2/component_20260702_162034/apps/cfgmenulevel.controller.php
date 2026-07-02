<?php
/* 
Berisi Script Controller dengan method yang bisa kita definisikan sendiri dengan syarat 
1. Semua Method hanya bisa di akses dengan a.ajax atau restfull dengan definisi Header khusus
2. Method yang bisa di akses dari URL public adalah yand di definisikan di Routes sebagai PublicMethod
*/
class cfgmenulevel_Controller extends MVC_Controller {
	function index(){
		// Aplikasi kita alihkan ke /app/cfgmenulevel.php biar menjadi satu
		$url = Svr::GetComponentURL() . "/app/cfgmenulevel.php" ;
		header("location: " . $url, true, 301);
	}
}
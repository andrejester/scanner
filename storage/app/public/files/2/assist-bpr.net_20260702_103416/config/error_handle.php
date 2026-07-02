<?php
/*
Class Untuk Handel Kalau terjadi Error pada MVC dengan Method error
1. error_handle 
2. nType mengambil dari MVC::ERROR_
3. Kembalian false maka akan menampilkan Error standart dari MVC
4. Kembalian true maka oleh MVC tidak akan ditampilkan error standart MVC

Kita bisa Handle di sini semua error nya, dan Action apa yang akan kita butuhkan.
*/
class MVC_Error {
	static function ErrorHandle($nType,$param){
		$lRetval = false ;

		return $lRetval ;
	}
}
<?php
/* 
Berisi Script Controller dengan method yang bisa kita definisikan sendiri dengan syarat 
1. Semua Method hanya bisa di akses dengan a.ajax atau restfull dengan definisi Header khusus
2. Method yang bisa di akses dari URL public adalah yand di definisikan di Routes sebagai PublicMethod
*/
class level_Controller extends MVC_Controller {
	function index(){
		$this->View() ;
	}
	function SeekLevel($va){
		$vaData = objData::ABrowse("username_level","Kode,Keterangan","Kode like '{$va['cKode']}%'","","","Kode") ;
		MVC::Response($vaData) ;
	}
}
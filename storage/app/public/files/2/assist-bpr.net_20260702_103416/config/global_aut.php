<?php
class MVC_Authentication {
	/*
	Masukkan Authentication pada Source di bawah init
	Return true = Athentication Ditemima
	return false = data akan di reject
	*/
	function Authentication() {
		$lRetval = true ;
	
		// Check Apakah Dia Sudah Login apa Belum
		// Jika Belum Login arahkan Ke Web Login.
		// Jika Sudah Login Jalankan Controller yang diinginkan.
		// Check Jika mengakses Controller selain home dan login tapi belum login maka kita direct ke login
		$vaController = array("home"=>1,"login"=>0,"atm"=>1,"atm_bepede"=>1,"mobile"=>1,"mobile_h2h"=>1,"webcore"=>1,"cbd"=>1,"rcp_server"=>1,"callback"=>1,"sakep"=>1) ;
		$lLogin = GetSetting("cLogin",0) == 1 ;
		if(!isset($vaController[MVC::$controller]) && !$lLogin){
			//membuat metode redirect dengan kode 301
			//membuat kode di bawah header tidak diproses oleh website sehingga lebih aman
			header("location: " . MVC::GetBaseURL(), true, 301);
			exit();	
		}

		return $lRetval ;
	}
}
<?php
/* 
Standart Class Autoload
1. Pastikan File Berapa di Folder project/include/autoload/Classes/
2. Pastikan Nama Class Sama Dengan Nama File
3. Tidak Boleh Ada nama Class Kembar di semua Subdir nya
*/
$cFile = Svr::GetConfig("rpt_data_type","redis") == "redis" ? "Rpt.redis.php" : "Rpt.mysql.php" ;

require_once $cFile ;
class Rpt extends Rpt_Data {
	private static $ttl = 28800 ;

	static function Init($vaPar=[],$cRptKode="rpt"){
		self::$ttl = Svr::GetConfig("rpt_data_ttl",7200) ;
		self::$ttl = max(self::$ttl,7200) ; 
		self::Connect() ;
		self::Delete("*",$cRptKode,"") ;
		
		// Parameter Kita Simpan Biar tidak harus di uploads Berulangkali.
		if(is_array($vaPar) && count($vaPar) > 0) self::SavePar("x",$vaPar,$cRptKode) ;
	}

	private static function getKey($cRptKode="rpt"){
		if($cRptKode == "") $cRptKode = "rpt" ;
		$cKey = session_id() ; //Svr::GetAppID() !== "" ? Svr::GetAppID() : session_id() ;
		return $cKey . ":" . $cRptKode . ":" ;
	}

	/*
	Untuk Add Part Laporan
	*/
	static function Save($cKode="auto",$value="",$cRptKode="rpt"){
		$cKey = self::getKey($cRptKode) ;
		$cKode = $cKode == "" || $cKode == "auto" ? microtime() : $cKode ;
		self::_Save($cKey,"dat:",$cKode,$value,self::$ttl) ;
	}

	/*
	Hapus Part Laporan 
	*/
	static function Delete($cKode="*",$cRptKode="rpt",$cSub="dat:"){
		$cKey = self::getKey($cRptKode) ;
		self::_Delete($cKey,$cSub,$cKode) ;
	}

	/*
	Ambil Part Laporan
	*/
	static function Get($cKode="*",$cRptKode="rpt",$lDeleteAfterGet=true){
		$cKey = self::getKey($cRptKode) ;
		$cSub = "dat:" ;
		$va = self::_Get($cKey,$cSub,$cKode) ;
		
		if($lDeleteAfterGet) self::_Delete($cKey,"") ;
		return $va ;
	}

	/*
	Kita gunakan untuk Simpan Parameter Laporan Biar tidak di uplad berulang-ulang
	*/
	static function SavePar($cKode="x",$value="",$cRptKode="rpt"){
		$cKey = self::getKey($cRptKode) ;
		self::_Save($cKey,"par:",$cKode,$value,self::$ttl) ;
	}

	/*
	Ambil Semua Data Paramter
	*/
	static function GetPar($cKode="x",$cRptKode="rpt"){
		$cKey = self::getKey($cRptKode) ;
		$cSub = "par:" ;
		$va = self::_Get($cKey,$cSub,$cKode) ;
		return $va ;
	}
}
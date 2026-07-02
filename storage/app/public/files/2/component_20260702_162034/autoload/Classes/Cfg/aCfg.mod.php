<?php
/* 
Standart Class Autoload
1. Pastikan File Berapa di Folder project/include/autoload/Classes/
2. Pastikan Nama Class Sama Dengan Nama File
3. Tidak Boleh Ada nama Class Kembar di semua Subdir nya
*/
class aCfg {
	private static $va ;
  public static function Get($cKey,$default='',$cDatabase=''){  
		$cSession = "csession_" ;
		if(strtolower(substr($cKey,0,strlen($cSession))) == $cSession)  $cKey = GetSetting("cSession_UserName") . $cKey ;  
		$cTable = $cDatabase != "" ? $cDatabase."config" : "config" ;
		$dbData = objData::Browse($cTable,"Keterangan","Kode = '$cKey'") ;
		if($dbRow = objData::GetRow($dbData)){
			$default = $dbRow ['Keterangan'] ;
		}
		return $default ;
	}

	public static function Upd($cKey,$value,$cDatabase=''){
		$cSession = "csession_" ;
		$lSaveLog = true ;
		if(strtolower(substr($cKey,0,strlen($cSession))) == $cSession){
			$cKey = GetSetting("cSession_UserName") . $cKey ;
			$lSaveLog = false ;
		}   	
		$cTable = $cDatabase != "" ? $cDatabase."config" : "config" ;
		$va = array("Kode"=>$cKey,"Keterangan"=>$value) ;

		objData::Update($cTable,$va,"Kode = '$cKey'",$lSaveLog) ;  
	}
}

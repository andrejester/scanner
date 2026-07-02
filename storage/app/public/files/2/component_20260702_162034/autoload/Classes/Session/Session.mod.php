<?php
  include 'df.php' ;

/* 
	Jika Jenis Komponent MVC maka kita key ambil dari session_id() tidak bisa dari project name
   berarti dalam satu sesi banyak aplikasi jadi satu Sesi
	 Karena dalam aplikasi aplikasi nanti aplikasi langsung kita install dalam satu dns langsung mengarah ke public bukan lagi nama project nya
*/
class Session {
	private static $va = [] ;
	static function Get($cKey,$default = ''){
		$cKey = self::getKey($cKey) ;

		self::StartSession() ;
		if(isset($_SESSION[$cKey])) $default = $_SESSION[$cKey] ;
		return $default ; 
	}

	static function Save($cKey,$cValue){
		if($cKey == 'cLogin' && intval($cValue)) SysLog::Login() ;
		if($cKey == 'cLogin' && !intval($cValue)) SysLog::Logout() ;
		$cKey = self::getKey($cKey) ;

		self::StartSession() ;		
		$_SESSION[$cKey] = $cValue ;
	}

	static function Unset($cKey){
		$cKey = self::getKey($cKey) ;
		if (isset($_SESSION[$keyToRemove])) {
    	unset($_SESSION[$keyToRemove]);
			return true ;
		}
		return false ;
	}

	private static function getKey($cKey){
		return md5(session_id() . "-" . strtolower($cKey)) ;
	}

	// Kita Pakai Kalau dia Non MVC pakai ini
	private static function getProject(){
		if(isset($_SERVER ["PHP_SELF"])){
			$va = explode("/",dirname($_SERVER["PHP_SELF"])) ;
			$cProject = end($va) ;
		}

		if(strpos($cProject,"component") !== false){
			if(isset($_SERVER ["HTTP_REFERER"])){
				$va = explode("/",dirname($_SERVER["HTTP_REFERER"])) ;
				$cProject = end($va) ;
			}  
		}
		return $cProject ;
	}

	private static function StartSession(){
		/*
		if(!defined('sessionstart')){    
			define('sessionstart',1) ;
			if (session_status() === PHP_SESSION_NONE) {
    		session_start();
			}
		}
		*/
	}
}
<?php

class Rpt_Data {
	private static $sentinel ;
	private static $data ;
	private static $lConnect = false ;

	protected static function Connect(){
		$cIP = Svr::GetConfig("rpt_data_ip","10.1.8.150") ; 
		$cUser = Svr::GetConfig("rpt_data_username") ;
		$cPassword = Svr::GetConfig("rpt_data_password") ;	
		
		//Koneksi Ke Redis Sentinel
		self::$sentinel = new Redis();
    self::$sentinel->connect($cIP, 26379);
    if($cPassword <> "") self::$sentinel->auth($cPassword) ;
		$masterInfo = self::$sentinel->rawCommand('SENTINEL', 'get-master-addr-by-name', 'mymaster');
    
		//Koneksi Ke Redis Master
		self::$data = new Redis();
		self::$data->connect($masterInfo[0],  $masterInfo[1]) ;
    if($cPassword <> "") self::$data->auth($cPassword) ;
		
		self::$lConnect = true ;
	}

	protected static function _Save($cKey,$cSub,$cKode,$value="",$exp=3600){
		if(!self::$lConnect) self::Connect() ;

		$exp = time() + $exp ;
		$key = $cKey . $cSub . $cKode ;
		self::$data->set($key, json_encode($value));
		self::$data->expireAt($key, $exp);
		
		// Mengatur waktu kadaluwarsa untuk setiap key
		$keys = self::$data->keys($cKey . "*");		
		foreach ($keys as $key2) {
			self::$data->expireAt($key2, $exp);
		}
		return $key ;
	}

	protected static function _Delete($cKey,$cSub,$cKode="*"){
		if(!self::$lConnect) self::Connect() ;

		$keys = self::$data->keys($cKey . $cSub . $cKode);
		if (!empty($keys)) self::$data->del($keys);
	}

	protected static function _Get($cKey,$cSub,$cKode="*"){
		if(!self::$lConnect) self::Connect() ;

		$keys = self::$data->keys($cKey . $cSub . $cKode);
		$vaData = [] ;
		foreach ($keys as $key) {
			$va = json_decode(self::$data->get($key),true) ;
			$vaData = array_replace_recursive($vaData,$va) ; //$vaData = array_merge($vaData,$va) ;
		}
		return $vaData ;
	}
}
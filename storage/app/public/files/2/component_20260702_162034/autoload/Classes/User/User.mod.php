<?php
/* 
Standart Class Autoload
1. Pastikan File Berapa di Folder project/include/autoload/Classes/
2. Pastikan Nama Class Sama Dengan Nama File
3. Tidak Boleh Ada nama Class Kembar di semua Subdir nya
*/
class User {
	private static $sentinel ;
  private static $lConnect  = false ;
	private static $key 			= "" ;
	private static $ttl       = 28800 ;
	private static $expTime   = 600 ;
	private static $vaData 		= [] ;
	private static $data ;
	
	static function Connect(){
		$cIP 				= Svr::GetConfig("token_ip","10.1.8.150") ; 
		$cPassword	= Svr::GetConfig("token_password") ; 
		$cMaster		= Svr::GetConfig("master_name","mymaster") ; 
		//Koneksi Ke Redis Sentinel
		self::$sentinel = new Redis();
    self::$sentinel->connect($cIP, 26379);
    if($cPassword <> "") self::$sentinel->auth($cPassword) ;
		$masterInfo = self::$sentinel->rawCommand('SENTINEL', 'get-master-addr-by-name', $cMaster);
    
		//Koneksi Ke Redis Master
		self::$data = new Redis();
		self::$data->connect($masterInfo[0],  $masterInfo[1]) ;
    if($cPassword <> "") self::$data->auth($cPassword) ;
		
		self::$lConnect = true ;
	}
	
	static function Get($cKey,$cDefault = ''){  
		return Session::Get($cKey,$cDefault) ;
	}

	static function Save($cKey,$cValue){
		if(strtolower($cKey) <> "online"){
			Session::Save($cKey,$cValue) ;
		}else{
			if(!self::$lConnect) self::Connect() ;
			//Ambil Data Redis
			self::$key = self::GetID() ;
			self::$vaData = json_decode(self::$data->get(self::$key),true) ;
			
			//Update per user
			$cKey = strtolower(GetSetting("cSession_UserName")) ;
			self::$vaData[$cKey] = $cValue ;
			
			//Simpan Data Online User
			self::$data->set(self::$key, json_encode(self::$vaData,JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE));
			self::$data->expireAt(self::$key,time()+self::$ttl);
		}
	}
	
	static function LastOnline($cUserName){
		if(!self::$lConnect) self::Connect() ;
		
		//Ambil Data Redis
		self::$key = self::GetID() ;
		self::$vaData = json_decode(self::$data->get(self::$key),true) ;
		
		//Ambil Data Usernya
		$cUserName   = strtolower($cUserName) ;
		$nLastOnline = (isset(self::$vaData[$cUserName])) ? self::$vaData[$cUserName] : 0 ;
		self::$lConnect = false ;
		self::$data->close() ;
		
		return $nLastOnline ;
	}
	
	static function Delete(){
		self::Save("Online",0) ;
		if (session_status() === PHP_SESSION_ACTIVE) session_destroy();
	}
	
	private static function GetID(){
		$cIP       = (!empty(GetSetting("cSession_IP"))) ? GetSetting("cSession_IP") : Svr::GetServerAddr() ;
		$cDatabase = (!empty(GetSetting("cSession_Database"))) ? GetSetting("cSession_Database") : "undefined" ; 
		$key 			 = md5(implode(":",array(gethostbyname($cIP),$cDatabase))) ;
		return $key ;
	}
	
	static function SaveAuthCode($cKey,$cUserName){
		if(!self::$lConnect) self::Connect() ;
		self::$data->set("Auth:".$cKey, $cUserName);
		self::$data->expireAt("Auth:".$cKey,time()+600);
	}
	
	static function GetAuthCode($cKey){
		if(!self::$lConnect) self::Connect() ;
		return self::$data->get("Auth:".$cKey);
	}
	static function GetAuthCodeTTL($cKey){ // Uni Coba
		if(!self::$lConnect) self::Connect();
		$authCode = self::$data->get("Auth:".$cKey);  // Ambil nilai auth code
    $ttl = self::$data->ttl("Auth:".$cKey);       // Ambil TTL

    return [
        'auth_code' => $authCode,
        'ttl' => $ttl
    ];
	}
	
	static function SaveRedist($cKeyRedist,$cSubKey,$cValue){
		if(!self::$lConnect) self::Connect() ;
		self::$data->set($cKeyRedist.":".$cSubKey, $cValue);
		self::$data->expireAt($cKeyRedist.":".$cSubKey,time()+self::$expTime);
	}
	
	static function GetRedist($cKeyRedist,$cKey){
		if(!self::$lConnect) self::Connect();
		return self::$data->get($cKeyRedist.":".$cKey);
	}
	
	static function DeleteRedist($cKeyRedist,$cKey){
		if(!self::$lConnect) self::Connect();
		return self::$data->del($cKeyRedist.":".$cKey);
	}
	
	static function DeleteRedisAll($cKey){
		if(!self::$lConnect) self::Connect();
		$keys = self::$data->exists($cKey);
		
		if (!empty($keys)) {
				return self::$data->del($keys);
		}
	}
}
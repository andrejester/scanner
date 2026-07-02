<?php
  include 'df.php' ;	
	/*
	Jika belum definisi vaConfig berarti dia bukan MVC maka kita ambil GetSessionConfig()
	Jika Sudah di Defined maka dia di panggil dari MVC maka variable vaConfig sudah ada tinggal dipakai
	*/
  if(!defined("vaConfig")){
		require_once "../component/autoload/Classes/Svr/Svr.mod.php" ;
	}else{
		require_once __DIR__ . "/../autoload/Classes/Svr/Svr.mod.php" ; 
	}
	Svr::clear_static_properties("Svr") ;
	$vaSvr 			= Svr::GetAllHeaders() ;
 
	$cToken 		= isset($vaSvr["TOKEN-ID"]) ? $vaSvr["TOKEN-ID"] : "" ;
	$cToken 		= Svr::GetPar("__token",$cToken,false) ;
 	
	$session_id = $_COOKIE['ASSISTSESSID'] ?? "";
  $vaConfig 	= Svr::GetConfig("*") ;	
	if($cToken !== ""){
		$vaRetval = Svr::GetToken($cToken) ;
		if(isset($vaRetval['session_id'])) $session_id = $vaRetval['session_id'] ;
		if(isset($vaRetval['SvrConfig'])) $vaConfig = $vaRetval['SvrConfig'] ;
	}
	// Jika Undefined berarti nonmvc kita ambil melalui assist.ini.php
	if(!defined("vaConfig")){
		if(!isset($vaConfig["session_save_handler"])){ //Cek dulu apakah sudah di setting pada assist.ini.php
			$vaSessionConfig = __GetSessionConfig() ; 
			$vaConfig 			 = array_merge($vaConfig,$vaSessionConfig) ; //Kita Gabungkan Session SaveHandler Dengan SessionHandler Untuk Monolithik
		}
	}
	if(isset($vaConfig["session_save_handler"]) && session_status() === PHP_SESSION_NONE){
		$cFileClass = "" ;
		if($vaConfig["session_save_handler"] == "mysql"){
			$cFileClass = __DIR__ . "/session_mysql.mod.php" ;
		}else if($vaConfig["session_save_handler"] == "redis"){
			$cFileClass = __DIR__ . "/session_redis.mod.php" ;
		}else if($vaConfig["session_save_handler"] == "memory"){
			$cFileClass = __DIR__ . "/session_memory.mod.php" ;
		}
		if($cFileClass !== "" && is_file($cFileClass)){
			require_once $cFileClass ;
			$handler = new SisSession($vaConfig);
			session_set_save_handler($handler, true);
		}
		if(PHP_VERSION_ID < 70300){
   		session_set_cookie_params(3600,'/'); //; samesite=Lax
		}else{
    	session_set_cookie_params(['lifetime'=>3600]); //,'samesite'=>'Lax'
		}
		session_name("ASSISTSESSID") ;
		//Custom ID agar semua sama
		if(!empty($session_id)) session_id($session_id) ;
		session_start();
	}

	if(!function_exists("__GetSessionConfig")){
		function __GetSessionConfig(){
			$cOld = "" ;
			$vaFiles = get_included_files() ;
			$cDir = "" ;
			foreach($vaFiles as $file){
				if($file == __FILE__){
					$cDir = dirname($cOld) ;
				}
				$cOld = $file ;
			}

			if($cDir !== ""){
				$cFileConfig = $cDir . "/assist.ini.php" ;
				$cHost = "" ;
				$cDatabase = "" ;
 
				$vaData = ["session_save_handler"=>"mysql", 		// mysql/redis/file
									 "session_server_db"=> "",
									 "session_server_ip"=> "",
									 "session_server_username"=>"Assist",
									 "session_server_password"=>"Irac"
									] ;
				if(is_file($cFileConfig)){
					$data = file($cFileConfig) ;
					foreach($data as $key=>$value){
						$value = trim($value) ;
						$cTag = "" ;
						$cValue = "" ;
						if(substr($value,0,1) <> "#"){
							$va = explode("=",$value) ;
							if(count($va) >= 2){
								$cTag = trim($va [0]) ;
								$cValue = trim($va [1]) ;
							}
						}

						$cTag = strtolower($cTag) ;
						if($cTag == "ip"){
							$cHost = $cValue ;
						}else if($cTag == "database"){
							$cDatabase = $cValue ;
						}else if(isset($vaData[$cTag])){
							$vaData[$cTag] = $cValue ;
						}
					}
				}

				// Kalau Session Database name dan host maka kita akan mengikuti Induk
				if($vaData["session_server_db"] == "") $vaData["session_server_db"] = $cDatabase ;
				if($vaData["session_server_ip"] == "") $vaData["session_server_ip"] = $cHost ;
			}
			return $vaData ;
		}
	}
?>
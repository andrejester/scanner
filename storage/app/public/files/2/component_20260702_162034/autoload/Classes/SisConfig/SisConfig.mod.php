<?php
/*
Standart Class Autoload
1. Pastikan File Berapa di Folder project/include/autoload/Classes/
2. Pastikan Nama Class Sama Dengan Nama File
3. Tidak Boleh Ada nama Class Kembar di semua Subdir nya
*/
class SisConfig {
	private static $va = [] ;
	static function Init($lWithJWT=false){
		$lRetval = true ;
		$va1 = cds::GetToken(false) ;
		if(isset($va1['SvrConfig'])){ //Konfigurasi Utama
			$va = $va1['SvrConfig'] ;
		}
		if(!isset($va)){
			$cPathEnv = $_SERVER['HTTP_HOST'] ;

			$vaRequest = AuthServer::GetRequest() ;
			if(isset($vaRequest['jwt_extension'])){
				$vaJWT = JWT::decode($vaRequest['jwt_extension']);
				$vaExtension = $vaJWT['data'] ;
			}else{
				$vaExtension = GetSetting("cSession_vaExtension") ;	
			}
			if (is_array($vaExtension) && count($vaExtension) > 0) {
				$cPathEnv = $vaExtension['CorporateID']."_" ; //$cDataExtention["CorporateID" INI NANTI CONCAT PRODUK
			}

			$cFile 		= Svr::GetProjectPath() . "/config/assist.php" ;
			$cFileEnv = Svr::GetProjectPath()."/env/".$cPathEnv.".env" ;
			$lRetval = true ;

			if(is_file($cFile)){
				include $cFile ;
			}else if(is_file($cFileEnv)){
				$va = Svr::LoadEnv($cFileEnv) ;
			}else{
				$va = Svr::GetConfigAssistTeam($cPathEnv) ;
			}
			if($lWithJWT){
				//$va1 = cds::GetToken(false) ;
				foreach($va1 as $key=>$value){
					SaveSetting($key,$value) ;
					$va[$key] = $value ;
				}	
			}
		}
		/* Logika Konfigurasi Sebelumnya
		$cFile = Svr::GetProjectPath() . "/config/assist.php" ;	
		$lRetval = true ;
		if(is_file($cFile)){
			include $cFile ;

			if(isset($va['remote_config'])){
				$va = Svr::GetConfigAssistTeam($va['remote_key_bearer'],$va['remote_config']) ;

			}
		}
		// Jika lWithJWT dia digunakan untuk submodule data data kita ambil dari Token
		if($lWithJWT){
			$va1 = cds::GetToken(false) ;
			if(isset($va1['SvrConfig'])){ //Konfigurasi Utama
				$va = $va1['SvrConfig'] ;
			}else{
				foreach($va1 as $key=>$value){
					SaveSetting($key,$value) ;
					$va[$key] = $value ;
				}	
			}
		}
		*/
		// Kalau Configurasi Tidak Kosong Baru kita lanjutkan
		self::$va = [] ;
		if(count($va) > 0){
			foreach($va as $key=>$value){
				$key = strtolower($key) ;
				self::$va[$key] = $value ;
			}
			if(isset(self::$va['ip'])) SaveSetting("cSession_IP",self::$va['ip']) ;
			if(isset(self::$va['db'])) SaveSetting("cSession_Database",self::$va['db']) ;
			if(isset(self::$va['username'])) SaveSetting("DBUserName",self::$va['username']) ;
			if(isset(self::$va['password'])) SaveSetting("DBPassword",self::$va['password']) ;

			//Untuk Monolitik
			if(isset(self::$va['database'])) SaveSetting("cSession_Database",self::$va['database']) ;
			if(GetSetting("DBUserName")=="") SaveSetting("DBUserName","Assist") ;
			if(GetSetting("DBPassword")=="") SaveSetting("DBPassword","Irac") ;
			// Kita Panggil Class ini untuk mengirimkan data mysql, walaupun tidak dikonek.
			objData::Connect(GetSetting("cSession_IP"),GetSetting("DBUserName"),GetSetting("DBPassword"),GetSetting("cSession_Database")) ;

			// Cari Apakah File Submodule Ketemu, Kalau tidak maka kita Tidak jalankan program.
			//if(self::GetValue("mnuSubMenu") !== "" && !is_file(self::GetValue("mnuSubMenu"))){
			//echo("File Menu Sub Module Tidak Ditemukan .....") ;
			//$lRetval = false ;
			//}
		}else{
			$lRetval = false ;
			echo("Configurasi Untuk DNS " . Svr::GetBaseURL() . " Tidak Ditemukan ....") ;
		}
		/* Tidak Digunakan, Sudah Menggunakan Konfigurasi DNS Tidak Ditemukan
		}else{
			$lRetval = false ;
			echo("File Configurasi Tidak Ditemukan ....") ;		
		}
		*/

		return $lRetval ;
	}

	static function GetValue($key,$default=""){
		$key = strtolower($key) ;
		if(isset(self::$va[$key])) $default = self::$va[$key] ;
		return $default ;
	}

	static function GetSubMenu($key,$cData,$lWrite){
		$cDir = "submenu" ;
		if(!is_dir($cDir)){
			mkdir($cDir,0777) ;
		}
		$cFile   = "$cDir/$key.menu.php" ;
		if(!is_file($cFile) || $lWrite){
			$handler = fopen($cFile,"w") ;
			fwrite($handler,html_entity_decode($cData)) ;
		}
		return getcwd()."/".$cFile ;
	}


	static function GetENVCore(){
		$va1 = cds::GetToken(false) ;
		if(isset($va1['SvrConfig'])){
			$va = $va1['SvrConfig'] ;
		}
		if(!isset($va)){
			$cPathEnv = $_SERVER['HTTP_HOST'] ;			
			$vaRequest = AuthServer::GetRequest() ;
			if(isset($vaRequest['jwt_extension'])){
				$vaJWT = JWT::decode($vaRequest['jwt_extension']);
				$vaExtension = $vaJWT['data'] ;
			}else{
				$vaExtension = GetSetting("cSession_vaExtension") ;	
			}
			if (is_array($vaExtension) && count($vaExtension) > 0) {
				$cPathEnv = $vaExtension['CorporateID']."_" ;
			}

			$cFileEnv = Svr::GetProjectPath()."/env/".$cPathEnv.".env" ;

			if(is_file($cFileEnv)){
				$va = Svr::LoadEnv($cFileEnv) ;
			}
		}
		return $va ;
	}
}
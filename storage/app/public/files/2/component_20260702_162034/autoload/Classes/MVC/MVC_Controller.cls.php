<?php
class MVC_Controller_Class {
	protected $allowed_http_methods = ["GET", "POST", "PUT", "PATCH", "DELETE", "HEAD", "OPTIONS"];
	protected $path ;
	protected $vaData = [] ;
	protected $dirProject = "" ;
	/*
	Menampung Request Methods GET,DELETE,POST,PUT,PATCH
	*/
	protected $request_method = "" ;
	
	/*
	Object Model
	*/
	protected $model = null ;

	function __construct(){		
		$this->dirProject = Svr::GetProjectPath() ;
	}
	
	/*
	Jika type = application/x-www-form-urlencoded dan methods = POST
	maka pengambilan data via field $_POST 
	Selain itu via Body
	*/
	protected function Get_Content_Type($type,$methods){
		$type = strtolower(trim(explode(";",$type)[0])) ;
		$vaType = explode("/",$type) ;
		if($vaType [0] == "application"){
			if(isset($vaType [1])){
				if($vaType[1] == "x-www-form-urlencoded"){
					return "field" ;
				}
			}
		}
		return "body" ;
	}

	private function getValue($method,$key=""){
		$method = strtoupper($method) ;
		if(isset($this->vaData[$method])){
			if($key == ""){
				return $this->vaData[$method] ;
			}else{
				return isset($this->vaData[$method][$key]) ? $this->vaData[$method][$key] : null ;
			}
		}
		return null ;
	}	

	protected function get($key=""){
		return $this->getValue(__FUNCTION__,$key) ;
	}
	
	protected function post($key=""){
		return $this->getValue(__FUNCTION__,$key) ;
	}
	
	protected function put($key=""){
		return $this->getValue(__FUNCTION__,$key) ;
	}
	
	protected function delete($key=""){
		return $this->getValue(__FUNCTION__,$key) ;
	}
	
	protected function patch($key=""){
		return $this->getValue(__FUNCTION__,$key) ;
	}
	
	protected function head($key=""){
		return $this->getValue(__FUNCTION__,$key) ;
	}
	
	protected function options($key=""){
		return $this->getValue(__FUNCTION__,$key) ;
	}
	
	protected function server($key=""){
		return $this->getValue(__FUNCTION__,$key) ;
	}

	protected function body(){
		return $this->getValue(__FUNCTION__) ;
	}
	
	protected function IsValidAut(){
		$cClientIP = $this->server("REMOTE_ADDR") ;
		if($cClientIP == ""){
			$cClientIP = $_SERVER['REMOTE_ADDR'] ;
		}
		
		$lRetval = true ;
		
		// Check Kalau Menggunakan ip allowed kita cek apakah ada disalam daftar ip kalau tidak ada kita reject.
		if(MVC::GetConfig('global_ip_allowed_enable',false)){
			$cIPList = "," . str_replace(" ","",MVC::GetConfig('global_ip_allowed','')) . "," ;
			if(strpos($cIPList,",$cClientIP,") === false){
				$lRetval = false ;
			}
		}
		
		// kalau Menggunakan IP Blacklist maka kita cek kalau IP Request ada dalam ip Blacklist akan kita reject
		if($lRetval && MVC::GetConfig('global_ip_blacklist_enabled',false)){
			$cIPList = "," . str_replace(" ","",MVC::GetConfig('global_ip_blacklist','')) . "," ;
			// Jika IP Masuk Kedalam daftar ip black list maka kita tolak
			if(strpos($cIPList,",$cClientIP,") !== false){
				$lRetval = false ;
			}
		}
		
		// Kalau Atentication type ada class cari file global_aut.php ;
		if($lRetval && MVC::GetConfig('global_authentication_type',"none") == "class"){
			$lRetval = false ;
			if($this->dirProject == "") $this->dirProject = Svr::GetProjectPath() ;
			$cFile = $this->dirProject . "/config/global_aut.php" ;
			$stdFile = __DIR__ . "/MVC_Controller.std_global_aut.php" ;
			if(!is_file($cFile)) copy($stdFile,$cFile) ;
     	if(is_file($cFile)){
				require_once $cFile ;
				$aut = new MVC_Authentication() ;
				$lRetval = $aut->Authentication() ;
			}			
		}
		return $lRetval ;
	}
}
<?php
class MVC {
	/*
  	Daftar HTTP Response.
	*/
	const HTTP_SUCCESS_OK = 200;
	const HTTP_SUCCESS_CREATED = 201;
	const HTTP_SUCCESS_NO_CONTENT = 204 ;
	const HTTP_RESPONSE_NO_MODIFIED = 304 ;
	const HTTP_CLIENT_BAD_REQUEST = 400;
	const HTTP_CLIENT_UNAUTHORIZED = 401;
	const HTTP_CLIENT_FORBIDDEN = 403;
	const HTTP_CLIENT_NOT_FOUND = 404;
	const HTTP_CLIENT_METHOD_NOT_ALLOWED = 405;	
	const HTTP_CLIENT_NOT_ACCEPTABLE = 406;
	const HTTP_CLIENT_CONFLICT = 409;
	const HTTP_SERVER_INTERNAL_ERROR = 500;
	const HTTP_SERVER_SERVICE_UNAVAILABLE = 503;

	/*
	Request Methods
	*/
	const REQ_METHOD_GET 		 = "GET" ;
	const REQ_METHOD_POST 	 = "POST" ;
	const REQ_METHOD_PUT 	 	 = "PUT" ;
	const REQ_METHOD_PATCH 	 = "PATCH" ;
	const REQ_METHOD_DELETE  = "DELETE" ;
	const REQ_METHOD_HEAD 	 = "HEAD" ;
	const REQ_METHOD_OPTIONS = "OPTIONS" ;
	
	/*
	Error Conts
	*/
	const ERROR_CONTROLLER_NOT_FOUND = 1 ;
	const ERROR_METHOD_NOT_FOUND = 2 ;
	const ERROR_CLIENT_UNAUTHORIZED = 3 ;

	/*
	Variable Config
	*/
	private static $config = [] ;
	
	public static $MVC_StartTime = "" ;
	/*
	Variable Routes	
	*/
	private static $routes = [] ;
	private static $public_method = [] ;

	// Kita Akan Menyimpan Controller name dan method name di variable ini biar bisa di akses dari controller
	public static $controller = "" ;
	public static $method = "" ;	
	public static $dir_controller = "" ;		// Dir Controller yaitu end point setelah baseurl/controller/method/[ini lah dir controller]
	public static $dir_root_controller = "" ; // Standart nya Svr::GetProjectPath() . "/mvc" tapi masih mungkin bisa di edit untuk ke component/apps
	public static $req_id = "URL" ;		// Kalau req_id URL maka jawaban html, kalau AJAX maka jawaban json
	public static $serverType = 1 ;		// MVC_SERVER Atau REST_SERVER
	static function GetBaseURL($uri='/'){
		return Svr::GetBaseURL($uri) ;
	}

	// Jenis 1 = MVC_SERVER, 2 = REST_SERVER
	static function IsRest(){return self::$serverType == 2;}

	static function LoadComponent($lJS=true,$lCSS=true){
		if($lJS){
			$c = "<script language='javascript' appid='" . Svr::GetAppID() . "' dirURL='" . self::$dir_controller . "' controllerURL='". self::$controller . "' baseURL='" . self::GetBaseURL() . "' compVersion='" . __COMPONENT_VERSION__ . "' compURL='" . MVC::ComponentURL() . "' src='" . MVC::ComponentURL() . "ajax.js'></script>" ;
			echo($c) ;				
		} 

		// CSS Kita menggunakan Metode Check parent kalau ada maka kita pakai css nya
		// Kalau gak ada baru kita ambil dari web
		if($lCSS){
			$css = GetSetting("cSession_Themes","") ;
			$cNoAnimation = GetSetting("cSession_Themes_NoAnimation",0) ;
			if($css == ""){
				$css = aCfg::Get("cSession_Themes","default") ;
				SaveSetting("cSession_Themes",$css) ;			
			}	
			$url = Svr::GetComponentURL() ;

			echo("<div id='cssRoot' style='display:none'>{$url}themes/css.php?t=$css&a=$cNoAnimation</div>") ;
		}
	}

	static function ComponentURL(){
		return Svr::GetComponentURL() ;
	}

	static function ComponentDir(){
		return __COMPONENT_DIR__ ;
	}

	static function GetConfig($key,$default=null){		
		if(count(self::$config) == 0){
			self::LoadConfig() ;
		} 
		if(isset(self::$config[$key])) $default = self::$config[$key] ;
		return $default ;
	}
	
	/*
	Kita Ambil Configurasi pada File Standart Configurasi, Kita akan bandingkan dengan Config yang di project
	Kalau ada Penambahan element config atau pengurangan element config maka akan kita update file config nya
	*/
	private static function LoadConfig(){
		// Ambil Variable Default config pada file ./MVC_Controller.cfg.php ;
		$stdFile = __DIR__ . "/MVC_Controller.std_global_config.php" ;
		$default = self::GetCfgFile($stdFile) ;

		// Ambil File Config
		$cfgFile = Svr::GetProjectPath() . "/config/global_config.php" ;
		$config = self::GetCfgFile($cfgFile) ;

		// Simpan Ke Variable Global Config
		$globalConfig = array_merge($default,$config) ;
		if(md5(json_encode($config)) !== md5(json_encode($globalConfig)) || count($default) <> count($config)){
			self::UpdFileConfig($stdFile,$cfgFile,$globalConfig) ;
		}
		// Deklarasikan Local Config
		$stdLocalConfig = __DIR__ . "/MVC_Controller.std_local_config.php" ;
		$localConfig = Svr::GetProjectPath() . "/config/local_config.php" ;
		if(!is_file($localConfig) && is_file($stdLocalConfig)) copy($stdLocalConfig,$localConfig) ;
		$vaLocal = self::GetCfgFile($localConfig) ;
		self::$config = array_merge($globalConfig,$vaLocal) ;
	}

	/*
	Kalau ada Perubahan Configurasi Standart yang tidak ada di file config yang sudah di setting
	Maka kita akan lakukan update di file config supaya mengikuti configurasi terakhir.
	*/
	private static function UpdFileConfig($stdFile,$cfgFile,$globalConfig){
		// Ambil File configurasi Standart dengan file_get_contents
		$cNew = "" ;
		$vaLines = explode("\n",file_get_contents($stdFile)) ;
		foreach($vaLines as $line){
			$line = trim($line) ;
			if(substr($line,0,7) == "\$config"){
				$key = $line ;
				$nStart = strpos($key,"[") ;
				if($nStart !== false){
					$key = substr($key,$nStart+1) ;
					
					$nEnd = strpos($key,"]") ;
					if($nEnd !== false){
						$key = substr($key,0,$nEnd) ;
					}
				}
				$key = str_replace("'","",$key) ;
				$key = str_replace("\"","",$key) ;
				if(isset($globalConfig [$key])){
					$value = $globalConfig [$key] ;
					if(is_bool($value)){
						$value = $value ? "true" : "false" ;
					}else if(is_string($value)){
						$value = "'$value'" ;
					}else if(is_null($value)){
						$value = "null" ;
					}else if(is_array($value)){
						$value = "[" . implode(",",$value) . "]" ;
					}
				}
				$line = "\$config['$key'] = $value ;" ;
			}
			$cNew .= $line . "\n" ;
		}
		
		if($cNew !== "") file_put_contents($cfgFile,$cNew) ;
	}

	/*
	Kita Ambil Isi File Config .
	*/
	private static function GetCfgFile($cFile){
		$config = [] ;
		if(is_file($cFile)){
			require_once $cFile ;
		}
		return $config ;
	}

	/*
	Untuk Mendapatkan Pesan Response Code
	*/
	static function Get_HTTP_ResponseName($nResponseCode){
		switch ($nResponseCode) {
			case 200: $text = 'OK'; break;
			case 201: $text = 'Created'; break;
			case 204: $text = 'No Content'; break;
			case 304: $text = 'Not Modified'; break;
			case 400: $text = 'Bad Request'; break;
			case 401: $text = 'Unauthorized'; break;
			case 403: $text = 'Forbidden'; break;
			case 404: $text = 'Not Found'; break;
			case 405: $text = 'Method Not Allowed'; break;
			case 406: $text = 'Not Acceptable'; break;
			case 408: $text = 'Request Time-out'; break;
			case 409: $text = 'Conflict'; break;
			case 500: $text = 'Internal Server Error'; break;
			case 503: $text = 'Service Unavailable'; break;
			default:
				$text = "Unknown http status code" ;
				break;
		}
		return $text ;
	}
	/*
	Response JSON dengan data sbb :+
	$responseData   : [
						"status"=>true/false, ( Jika 200 - 299 true, Lebih dari 299 False),
						"response_code"=>http_response,
						"data"=>"Data Jika Berhasil",
						"message"=>"Bisa Berisi Error Message",
						]
	*/
	public static function Response($data=null,$nResponseCode=MVC::HTTP_SUCCESS_OK,$message=""){
		$nResponseCode = $nResponseCode == "" ? MVC::HTTP_SUCCESS_OK : $nResponseCode ; 
		header("Access-Control-Allow-Origin: *");
		header("Access-Control-Allow-Methods: GET,DELETE,POST,PUT") ;
    header("Content-Type: application/json; charset=UTF-8");
    header("HTTP/1.1 $nResponseCode");

		$status = $nResponseCode >= 200 && $nResponseCode <= 299 ;
		$vaResponse = ["status"=>$status,
									 "response_code"=>$nResponseCode,
									 "response_name"=>self::Get_HTTP_ResponseName($nResponseCode),
									 "data"=>$data,
									 "message"=>$message,
									 "request_time"=>self::$MVC_StartTime,
									 "response_time"=>date("c")
									] ;
		if($status){
			//CSRF Dirubah
			if(isset($_SERVER['HTTP_REFERER'])) $vaResponse["code_response"] = Svr::GenerateCSRFToken($_SERVER['HTTP_REFERER']) ;
		}
		
		echo json_encode($vaResponse) ;
	}

	public static function GetRoutes(){
		return self::$routes ;
	}

	/*
	| 1. Kita bisa melakukan Route Berdasarkan urisegments akan kita direc ke Method tertentu.
	|					MVC::Routes("/laporan/produk/","rptproduk::index")
	| 2. Kita juga bisa melakukan map Folder biar url lebih mudah di baca.
	|         MVC::Routes("/laporan/akuntansi/","subdir:/laporan/akuntansi/")
	*/
	public static function Routes($uriSegments,$to="home::index",$option=null){
		$controller = "" ;
		$method = "" ;
		$dir = "" ;
		if(substr($uriSegments,0,7) == "subdir:"){
			$dir = substr($uriSegments,7) ;
		}else{
			$va = explode("::",$to) ;
			$controller = (isset($va[0]) && !empty($va[0])) ? $va[0] : "home" ;
			$method = isset($va[1]) ? $va[1] : "index" ;
		}
		
		self::$routes[$uriSegments] = ["dir"=>$dir,"controller"=>$controller,"method"=>$method,"option"=>$option] ;
	}

	public static function GetPublicMethod(){
		return self::$public_method ;
	}

	public static function IsPublicMethod($controller,$method){
		$va = self::$public_method ;
		
		if(isset($va ["*::*"])){
			return true ;
		}else if(isset($va["*::$method"])){
			return true ;
		}else if(isset($va["$controller::*"])){
			return true ;
		}else if(isset($va ["$controller::$method"])){
			return true ;
		}

		return false ;
	}

	public static function PublicMethod($cController_Method="home::index"){
		if($cController_Method == "*" || $cController_Method == "*.*") $cController_Method = "*::*" ;
		$va = explode("::",$cController_Method) ;
		$controller = (isset($va[0]) && !empty($va[0])) ? $va[0] : "home" ;
		$m = isset($va[1]) ? $va[1] : "index" ;

		$vaMethod = explode(",",$m) ;
		foreach($vaMethod as $method){
			self::$public_method[$controller . "::" . $method] = ["controller"=>$controller,"method"=>$method] ;
		}
	}	
}
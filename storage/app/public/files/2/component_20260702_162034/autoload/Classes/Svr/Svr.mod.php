<?php
include 'df.php' ;

class Svr {
	// Variable Untuk Menampung variable assist.ini
	private static $vaAssist = [] ;
	public static $lInit = false ;
	public static $appid = "" ;

	//Redis Token
	private static $sentinel ;
	private static $lConnect  	= false ;
	private static $cToken    	= "" ;
	private static $vaToken 		= [] ;
	private static $ttl 				= 3600 ;
	private static $data ;
	private static $ccsrfToken 	= "" ;
	private static $vacsrfToken = [] ;
	static function Connect(){
		$cIP 				= self::GetConfig("token_ip","10.1.8.150") ; 
		$cPassword	= self::GetConfig("token_password") ; 
		$cMaster		= self::GetConfig("master_name","mymaster") ; 
		
		//Koneksi Ke Redis Sentinel
		self::$sentinel = new Redis();
		self::$sentinel->connect($cIP, 26379);
		if($cPassword <> "") self::$sentinel->auth($cPassword) ;
		$masterInfo = self::$sentinel->rawCommand('SENTINEL', 'get-master-addr-by-name', $cMaster);

		//Koneksi Ke Redis Master
		self::$data = new Redis();
		self::$data->connect($masterInfo[0],  $masterInfo[1]) ;
		if($cPassword <> "") self::$data->auth($cPassword) ;

		//Ambil Data Redis
		self::$cToken = "svrToken".Svr::GetAppID() ;
		self::$ttl    = self::GetConfig("token_ttl",3600) ;
		if(empty(self::$vaToken)){
			self::$vaToken = json_decode(self::$data->get(self::$cToken),true) ;
		}

		//Ambil Data Redis CSRF Token
		self::$ccsrfToken = "csrfToken:".session_id() ;
		//if(empty(self::$vacsrfToken)){
		self::$vacsrfToken = json_decode(self::$data->get(self::$ccsrfToken),true) ;
		//}
		self::$lConnect = true ;
	}

	static function SaveToken($vaArray){
		//Nantinya Bisa Dibelokkan Ke User
		if(!self::$lConnect) self::Connect() ; 
		self::$vaToken = $vaArray ;
		//Simpan Data
		self::$data->set(self::$cToken, json_encode(self::$vaToken,JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE));
		self::$data->expireAt(self::$cToken, time()+self::$ttl);
	}

	static function GetToken(){
		if(!self::$lConnect) self::Connect() ;
		//refresh ttl token
		self::$data->expireAt(self::$cToken, time()+self::$ttl);
		return self::$vaToken ;
	}

	static function SaveCookie($key,$value,$nTimeOut=1800){
		setcookie($key,$value,time() + $nTimeOut,"/") ;
	}

	static function GetAppID(){
		self::$appid = (function_exists("GetSetting")) ? GetSetting("appid") : ""; //Session::Get
		if(self::$appid == ""){ 
			$va = Svr::GetAllHeaders() ;
	
			$appid = isset($va["APP-ID"]) ? $va["APP-ID"] : "" ;
			$appid = Svr::GetPar("appid",$appid) ;

			if($appid == ""){
				$vaToken = class_exists("cds") ? cds::GetToken(false) : [] ;
			
				if(count($vaToken) > 0 && isset($vaToken["appid"])){
					$appid = $vaToken["appid"] ;
				}else{
					//$appid = substr(md5(date("YmdHis")) . rand(1000,9999),0,10) ;
					$appid = md5(session_id() . rand(1000,9999)) ;
				}
			}
			self::$appid = $appid ;
			// Kita simpan di savesetting karena di pakai di mainmenu
			if(function_exists("SaveSetting")){
				SaveSetting("appid",self::$appid) ;
			}
		} 

		return self::$appid ;
	}

	// Ambil File Assist.php yang ada di config
	static function Init($cAssistInitFile=''){
		self::$lInit = true ;
		
		/*
		//Untuk program mvc
		if($cAssistInitFile == "") $cAssistInitFile = Svr::GetProjectPath() . "/config/assist.php" ;
		if(is_file($cAssistInitFile)){
			include $cAssistInitFile ;
			self::$vaAssist = $va ;
		}
		*/
		$cPathEnv = $_SERVER['HTTP_HOST'] ;
		if (function_exists('GetSetting')) {
			
			$vaRequest = AuthServer::GetRequest() ;
			if(isset($vaRequest['jwt_extension'])){
			  $vaJWT = JWT::decode($vaRequest['jwt_extension']);
				$vaExtension = $vaJWT['data'] ;
			}else{
				$vaExtension = GetSetting("cSession_vaExtension") ;	
			}
			
			if (is_array($vaExtension) && count($vaExtension) > 0) {
				$cPathEnv = $vaExtension['CorporateID']."_" ; //$cDataExtention["CorporateID"
				// $va adalah array dan punya isi
			} // di sub tidak perlu jalan karana tidak perlu get config
		}
		//Untuk program mvc menggunakan environment
		if($cAssistInitFile == "") $cAssistInitFile = self::GetProjectPath() . "/config/assist.php" ;
		//if(!isset($_SERVER['HTTP_HOST'])) $_SERVER['HTTP_HOST'] = php_uname('n') ;
		$cAssistInitFileEnv	 = self::GetProjectPath()."/env/".$cPathEnv.".env" ;
		$cAssistInitFileMono = self::GetProjectPath()."/include/assist.ini.php" ;
		if(is_file($cAssistInitFile)){
			include $cAssistInitFile ;
			self::$vaAssist = $va ;
		}else if(is_file($cAssistInitFileEnv)){			
			self::$vaAssist = self::LoadEnv($cAssistInitFileEnv) ;
		}else if(is_file($cAssistInitFileMono)){
			//Untuk program monolitik
			$data = file($cAssistInitFileMono) ;
			foreach($data as $key=>$value){
				$value 	= trim($value) ;
				$cTag 	= "" ;
				$cValue = "" ;
				if(substr($value,0,1) <> "#"){
					$va1 = explode("=",$value) ;
					if(count($va1) >= 2){
						$cTag = trim($va1 [0]) ;
						$cValue = trim($va1 [1]) ;
					}
				}
				$cTag = strtolower($cTag) ;
				if(!empty($cTag)) $va[$cTag] = $cValue ;
			}
			self::$vaAssist = $va ;
		}else{
			//Jika konfigurasi tidak ada maka api ke assist team
			self::$vaAssist = self::GetConfigAssistTeam($cPathEnv) ;
		}
	}

	// Methode ImportFromToken Kita gunakan Untuk memasukkan ke vaAssist yang dari token
	static function SaveConfig($cKey,$value){
		self::$vaAssist[$cKey] = $value ;
	}

	static function GetConfig($cKey,$default=''){
		if(!self::$lInit) self::Init() ;
		if($cKey == "*") return self::$vaAssist ;

		if(isset(self::$vaAssist[$cKey])) $default = self::$vaAssist[$cKey] ;
		return $default ;
	}

	/*
	Untuk Mengetahui Jenis Component apakah MVC atau Tidak
	*/
	static function IsMVC(){
		$lRetval = false ;
		if(defined("__COMPONENT_TYPE__")) $lRetval = __COMPONENT_TYPE__ == "mvc" ;
		return $lRetval ;
	}

	static function IsDevelopmentMode(){		
		$lRetval = true ;
		if(Svr::IsMVC()){
			$nPos = strpos(Svr::GetBaseURL() . "/","/public/") ;
			if($nPos === false) $lRetval = false ;
		}
		return $lRetval ;		
	}

	private static function GetURL(){
		// Check ssl atau bukan		
		$ssl = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || isset($_SERVER['HTTP_SEC_FETCH_SITE']));

		// Server Protocol
		$protocol = $ssl ? 'https' : 'http' ;

		/*		
			Port
			1. Jika bukan sll dan menggunakan port 80 maka tidak kita masukkan Port nya karena sudah Starndart
			2. Jika sll dan Port 443 berarti starndart tidak usah di munculkan port nya
			3. Jika selain itu maka Port akan kita parameterkan ke host dengan format host:port
		*/
		$port = $_SERVER['SERVER_PORT'];
		$port = ((!$ssl && $port=='80') || ($ssl && $port=='443')) ? '' : ':'.$port;

		$host     = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER["SERVER_NAME"] . $port ;
		return ["protocol"=>$protocol,"port"=>$port,"host"=>$host] ;
	}

	static function GetBaseURL($uri='/'){
		$url = "" ;
		if(self::IsMVC()) $url = MVC::GetConfig("global_base_url") ;

		if($url == ""){
			$va = self::GetURL() ;
			$url = $va["protocol"] . '://' . $va["host"] . dirname($_SERVER['SCRIPT_NAME']) ;
		}
		$url = substr($url,-1) == "/" ? substr($url,0,-1) : $url ;
		$uri = substr($uri,0,1) !== "/" ? "/" . $uri : $uri ;
		return $url . $uri;
	}

	static function GetComponentURL(){
		$url = "" ;
		if(self::IsMVC()) $url = MVC::GetConfig("global_component_url") ;

		if($url == ""){
			$url = self::IsMVC() ? self::GetBaseURL() : "../" ;
			$url .= "component" ;
		}
		return $url . "/" ;
	}

	static function GetDocumentRoot(){
		return realpath($_SERVER["DOCUMENT_ROOT"]) ;
	}

	static function GetProjectPath(){
		$cPath = realpath(".") ;

		// Kita Lihat Kalau Path paling belakang /public maka kita mundurkan satu
		// Karena kalau public asumsi kita menggunakan mvc
		if(basename($cPath) == "public") $cPath = dirname($cPath) ;
		
		return $cPath ;
	}

	static function GetComponentPath($lFullPath=false){
		$cProject = dirname(self::GetProjectPath()) ;
		$cDir = substr(__DIR__,strlen($cProject)+1) ;
		if(!$lFullPath){
			$cDir = "../" . explode(" ",trim(str_replace("/"," ",$cDir)))[0] ;
		}else{
			$cDir = $cProject . "/" . explode("/",$cDir)[0] ;
		}
		return $cDir ;
	}

	static function GetServerAddr(){
		return isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR']:gethostbyname(gethostname());
	}

	static function GetHTTPReferer(){
		$cDir = isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : "" ;
		return $cDir ;
	}

	static function GetPHPSelf(){
		return $_SERVER['PHP_SELF'] ;
	}

	static function GetComponentVersion(){
		$cRetval = defined("__COMPONENT_VERSION__") ? __COMPONENT_VERSION__ : "2.2.18" ;
		return $cRetval ;
	}

	static function GetAllHeaders(){
		$va1 = [] ;
		if(function_exists('getallheaders')){
			$va = getallheaders() ;
			foreach($va as $key=>$value){
				$key = strtoupper($key) ;
				$va1[$key] = $value ;
			}
		}
		return $va1 ;
	}

	static function GetPar($cKey,$default="",$lGet=true,$lPost=true){
		if(isset($_POST[$cKey]) && $lPost){
			$default = $_POST[$cKey] ;
		}else if(isset($_GET[$cKey]) && $lGet){
			
			$default = $_GET[$cKey] ;
		} 
		return $default ;
	}

	static function GetConfigAssistTeam($cCustomerID =''){
		//Ambil Konfigurasi Di Assist Team
		$vaConfig = array() ;
		$cDNS = $_SERVER['HTTP_HOST'];
		if($cCustomerID != "" && stripos($cCustomerID, 'http') === false) {
			$cDNS = explode("_",$cCustomerID)[0];
		}
		//if(!isset($_SERVER['HTTP_HOST'])) $_SERVER['HTTP_HOST'] = php_uname('n') ;
		$cKey  = md5(date("Y-m-d H:i:s")) ;
		$cTime = date("c") ;
		$cVersion = "1.0" ;
		$cSignature = md5("$cKey:$cTime:$cVersion") ;
		$vaHeader[] = "Content-Type:application/json" ; 
		$vaHeader[] = "SIS-Kode:5f5a0d7d1aaa7d2df806815acab77f44" ; 
		$vaHeader[] = "SIS-Signature:$cSignature" ;
		$vaHeader[] = "SIS-Version:$cVersion" ;
		$vaHeader[] = "SIS-Timestamp:$cTime" ;
		$vaHeader[] = "SIS-URL:".$cDNS ;
		$vaHeader[] = "SIS-Key: $cKey" ;

		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL,"http://assist.sis1.cloud/api/config");
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST,'GET') ;
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true );
		curl_setopt($curl, CURLOPT_HTTPHEADER,$vaHeader);

		curl_setopt($curl, CURLOPT_TIMEOUT, 30); 
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10); 

		$vaResponse = curl_exec($curl);
		curl_close($curl);
		$vaResponse = json_decode($vaResponse,true) ;
		if(isset($vaResponse['response_code']) && $vaResponse['response_code'] == 200){
			$vaConfig = $vaResponse['data'] ;

			//Cek Direktori
			$cDir = Svr::GetProjectPath()."/env" ;
			if(!is_dir($cDir)){
			 mkdir($cDir, 0755, true) ;
				
			 $cIndexFile = $cDir . "/index.php";
    	 $cContent = "Restricted access";
   		 file_put_contents($cIndexFile, $cContent);
			} 
			
			if(isset($vaResponse['data']['mnuSubMenu'])){
				//Tulis Submenunya
				$cFile   = $cDir."/".$cCustomerID.".menu.php" ;
				$handler = fopen($cFile,"w") ;
				fwrite($handler,html_entity_decode($vaResponse['data']['mnuSubMenu'])) ;
				fclose($handler) ;
			
				//Belokkan ke file menunya
				$vaResponse['data']['mnuSubMenu'] = $cFile ;
			}
			
			//Tulis Environmentnya	
			$cFile   = $cDir."/".$cCustomerID.".env" ;
			$handler = fopen($cFile,"w") ;
			$vaMap 	 = array_map(function($key, $value) {
    											   return $key . ' = ' . $value;
													 }, array_keys($vaResponse['data']), $vaResponse['data']);
			fwrite($handler,implode(PHP_EOL,$vaMap)) ;
			fclose($handler) ;
			
			//Load Environmentnya
			self::LoadEnv($cFile) ;
		}
		return $vaConfig ;
	}
	/*
	static function GenerateCSRFToken(){
		if(!self::$lConnect) self::Connect() ; 
		$cCSRF = bin2hex(random_bytes(32));

		//Simpan Data
		self::$data->set("csrfToken:".session_id(), $cCSRF);
		self::$data->expireAt("csrfToken:".session_id(), time()+self::$ttl);

		self::SaveCookie("csrfToken",$cCSRF,self::$ttl) ;
	}

	static function GetCSRFToken(){
		if(!self::$lConnect) self::Connect() ;
		//refresh ttl csrf token
		self::$data->expireAt("csrfToken:".session_id(), time()+self::$ttl);
		return self::$data->get("csrfToken:".session_id()) ;
	}
	*/

	static function GenerateCSRFToken($cForm){
		//if(!self::$lConnect) self::Connect() ; 
		self::Connect() ;
		$cCSRF = bin2hex(random_bytes(32));
		//Jika Dari Main Maka Tambahkan TTL Agar Bisa Diunset Ketika 30 Menit Tidak Lagi Dipakai CSRFnya
		if(basename($cForm) == "main.php"){
			$ttlmain = time()+1800 ;
			//Ditambahkan Random Number Untuk Prevent 1 Form Memiliki Lebih Dari 1 Main
			$cForm .= rand(1,999)."|".$ttlmain ;
		} 
		//self::$data->multi();
		self::$vacsrfToken[$cForm] = $cCSRF ;
		//Simpan Data
		self::$data->set(self::$ccsrfToken, json_encode(self::$vacsrfToken,JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE));
		self::$data->expireAt(self::$ccsrfToken, time()+self::$ttl);

		//self::$data->exec(); 

		return $cCSRF ;
	}

	static function GetCSRFToken($cForm){
		//if(!self::$lConnect) self::Connect() ;
		self::Connect() ;
		//refresh ttl token
		self::$data->expireAt(self::$ccsrfToken, time()+self::$ttl);
		return isset(self::$vacsrfToken[$cForm]) ? self::$vacsrfToken[$cForm] : "" ;
	}

	static function GetCSRFTokenByValue($cValue){
		//if(!self::$lConnect) self::Connect() ;
		self::Connect() ;
		//refresh ttl token
		self::$data->expireAt(self::$ccsrfToken, time()+self::$ttl);
		$keyFound = "";
		if(!empty(self::$vacsrfToken)){
			//Cek Semua Key, Yg Expired Diunset Dulu Agar Tidak Menumpuk
			foreach(self::$vacsrfToken as $key=>$value){
				$vakeys = explode("|",$key) ;
				if(isset($vakeys[1])){
					if(time() > $vakeys[1]){
						unset(self::$vacsrfToken[$key]) ;
					}
				}
			}
			//Cari Key Yg Sama Dengan Value
			$keys = array_keys(self::$vacsrfToken, $cValue);
			if(!empty($keys)){
				foreach($keys as $values){
					$vakeys = explode("|",$values) ;
					if(isset($vakeys[1])){
						//Jika Ada TTL Maka Refresh TTLnya
						unset(self::$vacsrfToken[$values]) ;
						$ttlmain = time()+1800 ;
						self::$vacsrfToken[$vakeys[0]."|".$ttlmain] = $cValue ;
					}
					$keyFound = $values ;
				}
			}
			//Simpan Data Terbaru
			self::$data->set(self::$ccsrfToken, json_encode(self::$vacsrfToken,JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE));
			self::$data->expireAt(self::$ccsrfToken, time()+self::$ttl);
		}
		return $keyFound ;
	}
	
	static function LoadEnv($cFile) {
		if (file_exists($cFile)) {
			$vaLines = file($cFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
			foreach ($vaLines as $line) {
				$line = trim($line);
				if(strpos($line, '#') === 0) {
					continue;
				}
				list($key, $value) = explode('=', $line, 2);
				$key   = trim($key);
				$value = trim($value);
				putenv("$key=$value");
			}
		}else{
			echo "File .env tidak ditemukan.";
		}
		$va = getenv() ;
		return $va ;
	}
	
	
	static function clear_static_properties(string $className): void {
			if (!class_exists($className)) {
					throw new InvalidArgumentException("Class $className does not exist.");
			}

			$refClass = new ReflectionClass($className);

			foreach ($refClass->getProperties(ReflectionProperty::IS_STATIC) as $prop) {
					$prop->setAccessible(true);

					// Dapatkan nilai awal properti
					$defaultValues = $refClass->getDefaultProperties();
					$propName = $prop->getName();

					// Reset ke default value jika ada, jika tidak, set null
					if (array_key_exists($propName, $defaultValues)) {
							$prop->setValue(null, $defaultValues[$propName]);
					} else {
							$prop->setValue(null); // fallback ke null
					}
			}
	}
}
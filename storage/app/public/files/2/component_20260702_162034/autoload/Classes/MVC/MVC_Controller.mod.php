<?php
require_once "MVC_Controller.cls.php" ;

class MVC_Controller extends MVC_Controller_Class {	
	function __construct(){
		parent::__construct() ;		
	}

	/*
	Check Apakah Method yang dikirim kita acc
	*/
	private function IsAllowedHttpMethods(){
		return !empty(array_filter($this->allowed_http_methods, function($var){
										return $var == $this->request_method ;
									})) ;
	}

	/*
	Kita akan cek global config kalau restrict_method = true, maka syarat method bisa di akses dengan 2 cara
	1. Apabila di definisikan PublicMethod di Routes
	2. Diakses dengan appid yang sama ( di anggap melalui a.ajax dan frm.open )
	*/
	private function IsValidMethod($controller,$method){
		$lRetval = true ;

		// Jika Jenisnya restrict_method = true
		if(MVC::GetConfig("restrict_method",true)){
			// Check Apakah Dia Public Method
			if(!MVC::IsPublicMethod($controller,$method)){
				// Kita Ambil Semua Header Request
				$vaHeader = Svr::GetAllHeaders() ;
				
				// Kita Ambil REQ-ID Untuk Mengetahui Dia Direquest dari mana
				// 1. Bisa dari url berarti nanti response bisa berupa html
				// 2. Dari Ajax berarti nanti response via JSON
				// 3. Nanti kita juga kalau request dari RESTFULL jawaban JSON
				if(isset($vaHeader["REQ-ID"])) MVC::$req_id = strtolower($vaHeader["REQ-ID"]) ;

				/* 
				Jika Bukan Public Method dia hanya bisa di akses via a.ajax atau frm.open dengan ciri.
				1. Untuk a.ajax akan ada tambahan header Svr::GetAllHeaders()["APP-ID"]
				2. Untuk via frm.open ada tambahan _GET["appid"] 
				
				Setelah Kita Ambil maka akan kita bandingkan dengan GetSession("appid","") kalau tidak sama maka kita tolak
				karena di di akses dari public, kalau sama maka kita lanjutkan.
				*/
				$appid = "" ;
				if(MVC::$req_id == "ajax"){
					$appid = isset($vaHeader["APP-ID"]) ? $vaHeader["APP-ID"] : "" ;
				}else{
					// Jika bukan type ajax berarti pakai open window dan kita ambil di get
					$appid = Svr::GetPar("appid","") ;
				  //print_r($appid." || sess ".GetSession("appid","xx")." // session id ".session_id()) ;
					//CSRF Via Cookie MVC
					//CSRF Generate Ketika Get
					//Svr::GenerateCSRFToken() ;
				}
			
				// Kalau Tidak ada di Header APP-ID kita akan cek di $_GET ini kalau kita membukan file file frm.OpenFile
				$lRetval = false ;
				if($appid !== "")	$lRetval = $appid == GetSession("appid","xx") ;
				if($lRetval){
					/*
					//Jika Ajax Maka Validasi CSRF
					if(MVC::$req_id == "ajax"){
						//CSRF Via Cookie MVC
						//Jika Token Valid Maka Generate Ulang, Jika Tidak Akan Unautorized
						if(hash_equals($vaHeader['CSRF-TOKEN'],Svr::GetCSRFToken())){
							Svr::GenerateCSRFToken() ;	
						}else{
							$lRetval = false ;
						}
					}
					*/
					if(MVC::$req_id == "ajax" && !hash_equals($vaHeader['CSRF-TOKEN'],Svr::GetCSRFToken($_SERVER['HTTP_REFERER'])) && Svr::GetConfig("csrf_validation",1)){
						if(Svr::GetConfig("csrf_senderror",1)){
							$messageapi = ['chat_id' => '-1001946992174',
														 'text' 	 => "Error Di ".$_SERVER['HTTP_REFERER']." Invalid CSRF Token ".$vaHeader['CSRF-TOKEN']." | IP ".$_SERVER['REMOTE_ADDR']." | Username ".GetSetting("cSession_UserName")];
							$response   = file_get_contents("https://api.telegram.org/bot6099747329:AAHYLkFxxg8PuCqs3SqBwISsXAL7msrTDZo/sendMessage?".http_build_query($messageapi));
						}
						//$lRetval 		= false ;
					}
				}
			}
		}
		MVC::$req_id = "" ;
		//$lRetval = true ;
		return $lRetval ;
	}

	/*
	Jalankan Method Start MVC untuk menyimpan path
	*/
	public function StartMVC($path,$controller,$method){
		$this->path = $path . $controller ;
		
		// Kita Akan Check Dulu di global Config apakah semua method bersifat public atau private		
		if($this->IsValidMethod($controller,$method)){
			// Jika Request Method tidak kosong maka kita akan load model karena akan dibutuhkan berikutnya
			if($this->IsValidAut()){
				$cClassModel = $controller . "_Model" ;
				$cFileModel = Svr::GetProjectPath() . "/mvc/" . $this->path . ".model.php" ;
				if(is_file($cFileModel)){
					require_once $cFileModel ;
					$this->model = new $cClassModel ;
				}
			}else{
				return false ;
			}
		}else{
			return false ;
		}
		
		return true ;
	}

	/*
	Menjalankan Class RestFull API
	*/
	public function StartRest($path){	 
		$this->path = $path ;
		if($this->IsValidAut()){
			if(isset($_SERVER["REQUEST_METHOD"])) $this->request_method = strtoupper($_SERVER["REQUEST_METHOD"]) ;	
			if(!$this->IsAllowedHttpMethods()){
				MVC::Response(null,MVC::HTTP_CLIENT_METHOD_NOT_ALLOWED,"Method $this->request_method not allowed!") ;
				return "" ;
			} 

			$this->vaData [MVC::REQ_METHOD_GET] = $_GET ;
			$this->vaData ["SERVER"] = $_SERVER ;
			if($this->request_method !== MVC::REQ_METHOD_GET){
				// Kalau Jenis dan aplication/x-www-form-urlencoded maka Field akan kita parsing dari Body 
				// Tapi kalau selain itu kita tidak parsing karena body langsung ke value
				$this->vaData ["BODY"] = file_get_contents("php://input") ;
				if($this->Get_Content_Type($this->server("CONTENT_TYPE"),$this->request_method) == "field"){
					parse_str($this->vaData ["BODY"],$post_vars);
					$this->vaData [$this->request_method] = $post_vars ;
				}
			}

			/*
			Update Start Time pada MVC
			*/
			$cStart = $this->server("HTTP_SIS_TIMESTAMP") ;

			/*
			Ambil Methods nya panggil methods sesuai dengan request nya
			*/
			$cAllowed = "," . implode(",",$this->allowed_http_methods) . "," ;
			if(strpos($cAllowed,"," . $this->request_method . ",") !== false){				
				call_user_func([$this,$this->request_method]) ;
			}

			// Jika Request Method tidak kosong maka kita akan load model karena akan dibutuhkan berikutnya
			if($this->request_method !== ""){
				$cClassModel = basename($path) . "_Model" ;

				$cFileModel = Svr::GetProjectPath() . "/mvc/" . $path . ".model.php" ;
				if(is_file($cFileModel)){
					require_once $cFileModel ;
					$this->model = new $cClassModel ;
				}
			}	
		}else{
			$this->request_method = "" ;
		}		
		return $this->request_method ;
	}

	public function View($views=[""],$data=[]){
		$lExtension = Svr::GetConfig("extension",false) ;
		if($lExtension){
			$cPathComponent = Svr::GetComponentPath(true); 
			$cStatusExtension = GetSetting("cSession_Extension",2) ;
			if($cStatusExtension == 2){
				$cFile = "app/extension/loading.php" ;
				SaveSetting("cSession_Extension","0");
			}else if($cStatusExtension == 0){
				$cFile = "app/extension/splash.php" ;
			}
			if(isset($cFile)){
				require $cPathComponent."/$cFile" ;
				return true;
			}
		}
		
		if(!is_array($views)) $views = [$views] ;
		foreach($views as $view){
			if($view == "") $view = MVC::$controller ; // $this->path ;
			$cFile = MVC::$dir_root_controller . MVC::$dir_controller . "/$view" ;
			if(is_file("$cFile.php")){
				$cFile = "$cFile.php" ;
			}else{
				$cFile = "$cFile.view.php" ;
			}
			require $cFile ;
		}
		if(isset($_POST["appid"])){
			echo("<div style='display:none' id='divAppID'>" . $_POST["appid"] . "</div>") ;
		}
		
		//CSRF Masing2 Form
		$protocol   = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
		$requestUrl = $protocol . '://' .$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		echo("<div style='display:none' id='divcsrfToken'>" .Svr::GenerateCSRFToken($requestUrl) . "</div>") ;
	}

	public function _report(){
		$va = array_merge($_GET,$_POST) ;
		$this->View([MVC::$controller . ".report"],$va) ;
	}
}
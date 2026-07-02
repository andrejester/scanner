<?php

class SisMVC {
	const MVC_SERVER = 1 ;
	const REST_SERVER = 2 ;
	private $nServerType = 1 ;

	public function __construct($type = SisMVC::REST_SERVER){
		$this->nServerType = $type ;
	}	

	public function Start(){
		// Kita Definisikan appid
		//if(session_status() === PHP_SESSION_NONE) Svr::GetAppID() ;
		//Svr::$appid = "" ;
		// Ambil Data routes
		Svr::GetAppID() ;
		$cFileRoute = Svr::GetProjectPath() . "/config/routes.php" ;
		if(!is_file($cFileRoute)) copy(__DIR__ . "/MVC.std_routes.php",$cFileRoute) ;
		require_once $cFileRoute ;
	
		// Ambil Error Handle Untuk menangani Kalau terjadi error akan memanggil class ini
		$cFileError = Svr::GetProjectPath() . "/config/error_handle.php" ;
		if(!is_file($cFileError)) copy(__DIR__ . "/SisMVC.std_error.php",$cFileError) ;
		require_once $cFileError ;
		
		// Default controller dan method
		MVC::$controller = "home" ;
		MVC::$method = "index" ;
		MVC::$serverType = $this->nServerType ;

		// Jalankan apakah dia restfull atau mvc
		if($this->nServerType == self::REST_SERVER){
			$this->_rest() ;
		}else{
			$this->_mvc() ;
		}
	}

	private function Error($nType,$vaParam){
		$lRetval = false ;
		if(method_exists("MVC_Error","ErrorHandle")){
			$lRetval = MVC_error::ErrorHandle($nType,$vaParam) ;
		}
		return $lRetval ;
	}
	
	/*
	Format BASE_URL/path/path/Controller/Method
	Susunan url : Controller adalah array paling belakang - 1, dan array pasling belakang adalah method
	*/
	private function _mvc(){
		$url = $this->ParseURL() ;
		$lFoundRoutes = false ;
		$vaRoutes = MVC::GetRoutes() ;
		$cKey = "" ;
		$cDir = "" ;
		$nDir = -1 ;
		MVC::$dir_root_controller = Svr::GetProjectPath() . "/mvc" ;			// Folder Root MVC
		foreach($url as $key=>$value){
			$cKey .= "/$value" ;
			if(isset($vaRoutes["subdir:$cKey"]) || isset($vaRoutes["subdir:$cKey/"])){		// Jika Ketemu Key subdir: maka Dia Dianggap Directory
				$cDir = $cKey ;
				$nDir = $key ;
			}else	if(isset($vaRoutes[$cKey])){		// Jika Sudah Di definisikan Ke Raoutes
				$lFoundRoutes = true ;
				MVC::$controller = $vaRoutes[$cKey]["controller"] ;
				MVC::$method = $vaRoutes[$cKey]["method"] ;
				unset($url[$key]) ;
			}else if(!MVC::GetConfig("auto_search_controller_file",false)){
				// Jika Auto Search Controller false  maka dia tidak mencari Folder di dalam directory
				// Maka End Point akan kita pecah kalau di ketemu nama directory nya maka dia kita anggap sebagai directory
				// Dan Kalau Tidak ketemu baru dia mulai kita anggap sebagai method
				if(is_dir(MVC::$dir_root_controller . $cKey)){
					$cDir = $cKey ;
					$nDir = $key ;
				}
			}
		}
		// Jika cDir Tidak kosong Maka kita potong depannya sisanya controller dan method
		MVC::$dir_controller = "" ;
		if($cDir !== ""){
			MVC::$dir_controller = $cDir ;
			$url = array_slice($url,$nDir+1) ;
		} 
		/*
		Kalau url Tidak ada di routes Maka Kita akan Membuat :
		1. Item [0] sebagai Controller
		2. Item [1] Sebagai Methods
		3. Item Sisa nya sebagai parameter.
		*/
		$param = "" ;		
		if(!$lFoundRoutes){
			// Kita buat Standart Kalau item [0] == public maka kita hapus itu bukan controller baru bawah nya.
			$nPar = -1 ;
			if($url [0] == "public"){
				unset($url [0]) ;
				$nPar ++ ;
			} 

			// Ambil Controller
			if(isset($url[++$nPar])){
				MVC::$controller = $url[$nPar] ;
				unset($url[$nPar]) ;
			}			
			
			// Ambil Methods
			if(isset($url[++$nPar])){
				MVC::$method = $url[$nPar] ;
				unset($url[$nPar]) ;
			}
			
			// Kalau Memanggil Component apps maka kita akan hitung ulang Controller dan method nya setelah apps adalah controller
			if(MVC::$controller == "component" && MVC::$method == "apps"){
				MVC::$controller = "" ;
				MVC::$method = "index" ;

				// Ambil Controller yang di dalam Component
				if(isset($url[++$nPar])){
					MVC::$controller = $url[$nPar] ;
					unset($url[$nPar]) ;
				}			

				// Ambil Methods
				if(isset($url[++$nPar])){
					MVC::$method = $url[$nPar] ;
					unset($url[$nPar]) ;
				}				

				MVC::$dir_root_controller = Svr::GetComponentPath(true) . "/apps" ;
			}
		}
		$param = implode("/",$url) ;
		//$aparam = $url ;
		if(!empty($url)){
			foreach($url as $key=>$value){
				$url[$key] = str_replace("_"," ",$value) ;
			}
		}
		$aparam = array_values($url) ;
		$path = "" ;
		$cRoot = MVC::$dir_root_controller . MVC::$dir_controller ;
		$cFile = $this->SeekControllerFile(MVC::$controller,$cRoot) ;
		
		// Untuk Array Result Paramter
		$_vaParam = $_POST ;
		$_vaParam ["_get"] = $_GET ;
		$_vaParam ["param"] = $param ;
		$_vaParam ["aparam"] = $aparam ;
		$_vaParam ['method'] = MVC::$method ;
		$_vaParam ["controller"] = MVC::$controller ;
		if($cFile !== ""){
			$path = dirname(substr($cFile,strlen($cRoot)+1)) ;
			if($path !== "") $path .= "/" ;

			require_once $cFile ;
		}else{
			// Jika Controller Component kita akan load kalau ada file yang di nginginkan.
			$lFound = false ;
			if(MVC::$controller == "component"){
				// Jika ada Param maka kita tambahkan di belakang method
				
				// aturan Baru Akses Component Harus via Controller aturan yaitu
				// http:://baseurl/component/script/ajax.js => Dia akan memanggil method 
				// index_comp($va)
				// $va ['method'] = "Namamethod"
				// $va ['param'] = parameter
				
				$cFileMethod = MVC::ComponentDir() . "/component.controller.php" ;
				require_once $cFileMethod ;

				$_con = MVC::$controller . "_Controller" ;
				$con = new $_con ;
				$_cMethod = "index_comp" ;
				if(method_exists($con,$_cMethod)){
					$lFound = true ;
					call_user_func([$con,$_cMethod],$_vaParam) ;
				}
			}else if(MVC::$controller == "share"){
				// Jika Controller ada share ini berisi data yang bisa di akses dan kita gunakan untuk menyimpan File gambar dll.
				if($param !== "") $param = "/" . $param ;

				$cFileData = Svr::GetProjectPath() . "/" . MVC::$controller . "/" . MVC::$method . $param ;
				if(is_file($cFileData)){
					$lFound = true ;	
					$cContentType = mime_content_type($cFileData) ;
					if(strpos($param,".css") && strpos($cContentType, "plain")) $cContentType = "text/css;charset=UTF-8" ;
					header("Content-Type:  ".$cContentType) ;
					require $cFileData ;
				}				
			}

			if(!$lFound){
				if(!$this->Error(MVC::ERROR_CONTROLLER_NOT_FOUND,$_vaParam)){
					$message = "Controller " . MVC::$controller . " File is not found" ;
					if(MVC::$req_id == "ajax"){
						MVC::Response(null,MVC::HTTP_CLIENT_NOT_FOUND,$message) ;
					}else{
						require __DIR__ . "/SisMVC.error_404.php" ;
					}
				}else{
					$lFound = true ;
				}				
			}
			return $lFound ;
		}
		$_con = MVC::$controller . "_Controller" ;
		$con = new $_con ;
		$conName = MVC::$controller ;

		if($con->StartMVC($path,$conName,MVC::$method)){
			// Ambil Methods Jika Method tidak ada kita cek apakah ada magig methode ( __call )
			// Kalau ada kita call saja method nya
			if(method_exists($con,MVC::$method) || method_exists($con,"__call")){
				if(method_exists($con,MVC::$method)){
					call_user_func([$con,MVC::$method],$_vaParam) ;
				}else{
					call_user_func([$con,"__call"],MVC::$method,$_vaParam) ;
				}
			}else{
				// Jika Method adalah report dan tidak ada method yang di buat maka kita akan alihkan ke _report()				
				if(MVC::$method == "report"){
					call_user_func([$con,"_report"],$param) ;
				}else{
					if(!$this->Error(MVC::ERROR_METHOD_NOT_FOUND,$_vaParam)){
						$message = "Controller method is not found : " . MVC::$method ;
						if(MVC::$req_id == "ajax"){
							MVC::Response(Null,MVC::HTTP_CLIENT_NOT_FOUND,$message) ;
						}else{
							require __DIR__ . "/SisMVC.error_404.php" ;
						}
					}
				}				
			}
		}else{
			if(!$this->Error(MVC::ERROR_CLIENT_UNAUTHORIZED,$_vaParam)){
				MVC::Response(null,MVC::HTTP_CLIENT_UNAUTHORIZED,"Authentication Error ....") ;
			}
		}
		
		//Bersihkan static properties agar tidak bertabrakan dengan request selanjutnya
		$va = ["MVC_Controller", "SisMVC","Svr","Session","Rpt","dbg"];
		foreach ($va as $className) {
			if (class_exists($className)) {
				$this->clear_static_properties($className);
			} else {
				echo "Class $className tidak ditemukan!\n";
			}
		}

		//session_write_close();
    //ob_end_flush();
		
	}
	
	public function Terminate(){
		//Clear Class
		$classes = get_declared_classes();
		$result = [];

		foreach ($classes as $class) {
			$refClass = new ReflectionClass($class);

			if (!$refClass->isInternal()){
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
	}
	
	private function clear_static_properties(string $className): void {
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
	
	/*
	Program Autoloads Akan menjadi di dalam Folder mvc lokasi File Controller nya
	*/
	private function SeekControllerFile($controller,$dir){
		if(MVC::GetConfig("auto_search_controller_file",false)){
			// JIka Auto Search True maka kita scan di dalam folder dan di bawahnya.
			$vaDir = array() ;
			$this->GetSubDir($dir,$vaDir) ;
			foreach($vaDir as $cDir){
				$cFileClass = "$cDir/$controller.controller.php" ;
				// Jika Ketemu yang pertama itu yang kita pakai
				if(is_file($cFileClass)) return $cFileClass ;
			}
		}else{
			// Jika Tidak Auto Search Maka kita akan cek file nya kalau gak ada maka kita anggap Tidak Ketemu
			$cFileClass = "$dir/$controller.controller.php" ;
			if(is_file($cFileClass)) return $cFileClass ;
		}		
		return "" ;
	}

	private function GetSubDir($cDir,&$vaData){
		if(is_dir($cDir)){
			$vaData [$cDir] = $cDir ;
			$vaDir = scandir($cDir) ;
			foreach($vaDir as $file){
				if(substr($file,0,1) !== "." && is_dir("$cDir/$file")){
					$this->GetSubDir("$cDir/$file",$vaData) ;
				}
			}
		}
	}

	/*
	Semua Path Setelah BASE_URL akan kita jadikan folder tempat Controller contoh
	url = http://example.restapi.com/tabungan/golongan
	maka kita harus membuat Controller pada folder project-folder/mvc/tabungan/golongan.php
	Format BASE_URL/Controller/SubController/SubController
	Tahapan _rest
	1. Potong URL nya dengan end point terakhir adalah controller depannya Folder
	*/
	private function _rest(){
		// Ambil Path Dan controller nya
		$url 					= $this->ParseURL() ;
		$cProjectPath = Svr::GetProjectPath() . "/mvc/" ;
		$cFile        = "" ;
		$path 				= trim(implode("/",$url)) ;
		$params 			= array() ;
		if(count($url) > 0){
			if(end($url) == "") array_pop($url) ;		
			
			//Cari Controller Berada Dilevel Berapa Lalu Sisanya Dibelakangnya anggap parameter
			while(count($url) > 0){
				$enddata = end($url) ; //Ambil Data Terakhir
				array_pop($url) ; //Potong Data Terakhir
				
				$path = trim(implode("/",$url)) ;
				$cFile =  $cProjectPath . $path . "/". $enddata . ".controller.php" ;
				if(is_file($cFile)){
					//Jika File Controller Ada
					MVC::$controller = $enddata ;
					
					break ;
				}else{
					//Jika File Tidak Ada Maka Dianggap Parameter
					$params[] = $enddata ;
				}
			}
		}

		// Kita Check Kalau Path tidak kosong baru kita ambil controller
		if($path == "" && MVC::$controller == "home"){
			echo("RESTFul API is running ...") ;
			return true ;
		}else{
			// Ambil Controller
			MVC::$dir_controller = $path ;
			$_vaParam = $_POST ;
			$_vaParam ["_get"] = $_GET ;
			$_vaParam ["controller"] = MVC::$controller ;
			if(is_file($cFile)){		// Jika File Controller Ditemukan
				require_once $cFile ;

				// Jalankan Methods StartRest pada Controller
				$cClass = MVC::$controller . "_Controller" ;
				$con = new $cClass ;
				$cMethod = $con->StartRest($path) ;

				if($cMethod !== ""){
					$cMethod = strtolower("index_$cMethod") ;
					$_vaParam ['method'] = $cMethod ;
					if(method_exists($con,$cMethod)){
						$params = array_reverse($params) ;
						call_user_func([$con,$cMethod],$params) ;
					}else{
						// Jika Methods Tidak ada beri Kembalian Error
						if(!$this->Error(MVC::ERROR_METHOD_NOT_FOUND,$_vaParam)){
							MVC::Response(null,MVC::HTTP_CLIENT_METHOD_NOT_ALLOWED,"Method $cMethod not defined !") ;
						}
					}
				}
			}else{
				// File Controller Tidak Ditemukan
				if(!$this->Error(MVC::ERROR_CONTROLLER_NOT_FOUND,$_vaParam)){
					$message = "Controller " . MVC::$controller . " File is not found" ;
					MVC::Response(null,MVC::HTTP_CLIENT_NOT_FOUND,$message) ;
				}
			}
		}
	}
	
	private function _rest_old(){
		// Ambil Path Dan controller nya
		$url = $this->ParseURL() ;
		if(count($url) > 0){
			if(end($url) == "") array_pop($url) ;		
			
			if(count($url) > 0){
				// Ambil Item Terakhir dari url ada controller
				MVC::$controller = end($url) ;
				array_pop($url) ;
			}
		}

		// Kita Check Kalau Path tidak kosong baru kita ambil controller
		$path = trim(implode("/",$url)) ;
		if($path == "" && MVC::$controller == "home"){
			echo("RESTFul API is running ...") ;
			return true ;
		}else{
			// Ambil Controller
			MVC::$dir_controller = $path ;
			$cFile = Svr::GetProjectPath() . "/mvc/" . $path . "/". MVC::$controller . ".controller.php" ;
			$_vaParam = $_POST ;
			$_vaParam ["_get"] = $_GET ;
			$_vaParam ["controller"] = MVC::$controller ;
			if(is_file($cFile)){		// Jika File Controller Ditemukan
				require_once $cFile ;

				// Jalankan Methods StartRest pada Controller
				$cClass = MVC::$controller . "_Controller" ;
				$con = new $cClass ;
				$cMethod = $con->StartRest($path) ;

				if($cMethod !== ""){
					$cMethod = strtolower("index_$cMethod") ;
					$_vaParam ['method'] = $cMethod ;
					if(method_exists($con,$cMethod)){
						call_user_func([$con,$cMethod]) ;
					}else{
						// Jika Methods Tidak ada beri Kembalian Error
						if(!$this->Error(MVC::ERROR_METHOD_NOT_FOUND,$_vaParam)){
							MVC::Response(null,MVC::HTTP_CLIENT_METHOD_NOT_ALLOWED,"Method $cMethod not defined !") ;
						}
					}
				}
			}else{
				// File Controller Tidak Ditemukan
				if(!$this->Error(MVC::ERROR_CONTROLLER_NOT_FOUND,$_vaParam)){
					$message = "Controller " . MVC::$controller . " File is not found" ;
					MVC::Response(null,MVC::HTTP_CLIENT_NOT_FOUND,$message) ;
				}
			}
		}
	}

	public function GetURL(){		
		return $this->ParseURL() ; ;
	} 

	private function ParseURL(){
		//Agar Support FrankenPHP
		if(strtolower($_SERVER["SERVER_SOFTWARE"]) == "frankenphp"){
			$cPath = isset($_SERVER["REQUEST_URI"]) ? strtok($_SERVER["REQUEST_URI"],"?") : "" ;
		}else{
			$cPath = isset($_SERVER["PATH_INFO"]) ? $_SERVER["PATH_INFO"] : "" ;
		}
		$cPath = filter_var($cPath,FILTER_SANITIZE_URL) ;
		$cPath = trim(str_replace("/"," ",$cPath)) ;
		$vaRetval = explode(" ",$cPath) ;
		//$vaRetval = explode("/",trim($cPath)) ;
		return $vaRetval ;
	}
}
<?php
  include 'df.php' ;

	/*
	Daftar Class yang Harus di load, karena Method di butuhkan di autoload ini.
	*/
	require_once __DIR__ . "/Classes/Svr/Svr.mod.php" ;

// Autoload Class, kalau kita call class, maka autoload akan menjalankan ini dan mencari file class nya
spl_autoload_register(function($cClass){
	$cFileClass = udf::___AutoLoad_Open($cClass) ;
	if($cFileClass !== "" && is_file($cFileClass)) require_once $cFileClass ;
},true) ;

// Selain class udf juga kita buat class _ karena yang lama menggunakan nama class ini, biar compatible dengan source lama
class _ extends udf{}

/*
Class Untuk Mengambil semua function yang ada di project/include/autoload/Functions di anggap
sebagai Static method class udf
*/
class udf {
	private static $vaDir = [] ;
	private static $vaDirClass = [] ;
	private static $vaDirFunc = [] ;

	/*
	kita gunakan untuk Autoload Function contoh udf::HelloWord() maka akan mencari file HelloWord.mod.php di autoload Functions
	*/
	static function __callStatic($func,$args){
		if(!function_exists($func)){
			/*
			Jika Func Kosong kita akan cari function dalam 2 folder
			1. project-path/include/autoloads/Functions
			2. path-vendor/Functions
			
			Jika Foolder kosong maka kita akan scan di dalam folder autoload yaitu :
			*/
			if(count(self::$vaDirFunc) == 0){				
				// Folder autoload yang di Project
				$va[] = Svr::GetProjectPath() . "/include/autoload" ;
				
				// Ambil Path Vendor Kalau ada kita gabungkan dengan Path untuk Autoload
				$vendor = self::GetVendorPath() ;													
				foreach($vendor as $value){
					$va[] = $value ;
				}

				// Kita akan scan semua sub folder yang ada di autoload
				foreach($va as $cDir){
					self::$vaDir = [] ;
					self::___GetSubDir("$cDir/Functions") ;
					
					// Gabungkan Hasil self::$vaDir Ke self::$vaDirFunc ;
					foreach(self::$vaDir as $key=>$value){
						self::$vaDirFunc[$key] = $value ;
					}
				}
			}

			// Kita akan cari Nama file yang sama dengan nama Function nya.
			$cFileFunc = "" ;
			foreach(self::$vaDirFunc as $cDir){
				$cFileFunc = "$cDir/$func.mod.php" ;
				// Jika Ketemu yang pertama itu yang kita pakai
				if(is_file($cFileFunc)){
					require_once $cFileFunc ;
					break ;
				} 
			}
		}

		return call_user_func_array($func, $args);
	}

	static function ___AutoLoad_Open($cClass){
		/*
		Jika Tanpa Namespace maka kita akan scan di dalam folder Classes
		1. Kita akan scan di autoload project dulu project-dir/include/autoload/Class kalau tidak ada
		2. Kita akan load kalau ada project-dir/include/vendor.php maka kita akan load file ini karena dia berisi data autoload di folder lain
		3. Kita Akan Scan di Component component/autoload/Classes
		*/

		// Autoload yang ada di folder Project
		$vaDir[] = Svr::GetProjectPath() . "/include/autoload" ;

		// Ambil Path Vendor Kalau ada kita gabungkan dengan Path untuk Autoload
		$vendor = self::GetVendorPath() ;													
		foreach($vendor as $value){
			$vaDir[] = $value ;
		}

		// Autoloads Di Component
		$vaDir[] = realpath(__DIR__) ;
		
		/*
		Jika Menggunakan Namespace maka kita ambil folder di luar folder Classes 
		Sedangkan Kalau tidak menggunakan namespace akan kita scan di dalam folder Classes
		*/
		if(strpos($cClass,"\\") !== false){
			$cClass = str_replace("\\","/",$cClass) ;
			foreach($vaDir as $dir){
				$cFileClass = "$dir/$cClass.mod.php" ;
				// Jika Ketemu yang pertama itu yang kita pakai
				if(is_file($cFileClass)) return $cFileClass ;
			}
		}else{
			// Jika tanpa Namespace Cari File Class di dalam autoload/Classes
			$cFileClass = "" ;
			foreach($vaDir as $cDir){
				if($cFileClass == "") $cFileClass = self::GetClassFile($cClass,$cDir) ;
			}
			return $cFileClass ;
		}
		return "" ;
	}	

	// Method ini untuk mencari path Vendor yang akan di masukkan ke autoload
	private static function GetVendorPath(){
		$vaDir = [] ;
		// Kita Ambil kalau ada vendor class
		$cFileVendor = Svr::GetProjectPath() . "/include/vendor.php" ;
		if(is_file($cFileVendor)){
			$vendor = [] ;
			include $cFileVendor ;
			foreach($vendor as $key=>$value){
				if(is_dir($value)){
					// Jika Folder ada maka kita akan loads path nya.
					$vaDir[] = realpath($value) ;
				}					
			}
		}
		return $vaDir ;
	}
	
	// Mencari File Class
	private static function GetClassFile($cClass,$dir){
		if(!isset(self::$vaDirClass[$dir])){
			self::$vaDir = array() ;
			self::___GetSubDir("$dir/Classes") ;
			self::$vaDirClass[$dir] = self::$vaDir ;
		}

		$vaDir = self::$vaDirClass[$dir] ;
		foreach($vaDir as $cDir){
			$cFileClass = "$cDir/$cClass.mod.php" ;
			// Jika Ketemu yang pertama itu yang kita pakai
			if(is_file($cFileClass)) return $cFileClass ;
		}
		return "" ;
	}

	private static function ___GetSubDir($cDir){
		if(is_dir($cDir)){
			self::$vaDir [$cDir] = $cDir ;
			$vaDir = scandir($cDir) ;
			foreach($vaDir as $file){
				if(substr($file,0,1) !== "." && is_dir("$cDir/$file")){
					self::___GetSubDir("$cDir/$file") ;
				}
			}
		}
	}
}
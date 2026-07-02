<?php
/* 
Standart Class Autoload
1. Pastikan File Berapa di Folder project/include/autoload/Classes/
2. Pastikan Nama Class Sama Dengan Nama File
3. Tidak Boleh Ada nama Class Kembar di semua Subdir nya
*/
class Mod {
	static function ImportJS($cJSExt){
		if(substr($cJSExt,-2) !== ".js") $cJSExt .= ".js" ;
		$va = Session::Get("SESSION_JS_MODULE",[]) ;
		$va [$cJSExt] = $cJSExt ;
		Session::Save("SESSION_JS_MODULE",$va) ;
	}

	static function GetModule(){
		$key = "SESSION_JS_MODULE" ;
		$va = Session::Get($key,[]) ;
		Session::Save($key,[]) ;
		return $va ;
	}
}

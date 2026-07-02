<?php
	if(!defined("main")) define("main","1") ;
	if(!class_exists("udf")) require_once __DIR__ . "/autoload/autoload.php" ;
	if(!function_exists("_getJS_UDF_File")) {
		function _getJS_UDF_File(){
			$vaRetval = [] ;
			$cDir = Svr::GetProjectPath() . "/include/autoload/JS" ;
			if(is_dir($cDir)){
				$vaFile = scandir($cDir) ;
				foreach($vaFile as $f){
					$cFile = "$cDir/$f" ;
					if(substr($f,0,1) !== "." && is_file($cFile)){
						$_va = pathinfo($cFile);
						if($_va["extension"] == "js"){
							$vaRetval [$cFile] = $cFile ;
						}					
					}				
				}
			}
			return $vaRetval ;
		}
	}
	
	// Masukkan Ke dalam Array semua
	$vaJS = ["js","gdata.js","msg.js","txt.js","frm.js","main.js","sys.js","svr.js","rpt.js","cal.js","dbg.js","tab.js","txtbtn.js"] ;
	$vaJS[] = "load.js" ;		// Script onload kita akses terakhir

	// Kalau yang non mvc dia akses ke ajax.js maka sisa file lainnya akan langsung kita load kita akan hapus element pertama array vaJS
	if(!isset($_GET['_type'])){
		array_shift($vaJS);
	}else if($_GET['_type'] !== "all"){
		$vaJS = [$_GET['_type']] ;
	}
	foreach($vaJS as $ext){
		$ext = substr($ext,0,5) == "ajax." ? $ext : "ajax.$ext" ;
		$cFile = __DIR__ . "/$ext" ;
		$cFileTmp = CompressScript::jscript($cFile) ;
		require $cFileTmp ;
		echo("\n") ;
	}

	if(isset($_GET['_type']) && $_GET["_type"] == "all"){
		$vaFile = _getJS_UDF_File() ;
		foreach($vaFile as $cFile){
			$cFileTmp = CompressScript::jscript($cFile) ;
			require $cFileTmp ;
			echo("\n") ;
		}
	}


?>
<?php
  include 'df.php' ;

class Dir {
	static function DataDir($cSubDir=""){
		if($cSubDir !== "") $cSubDir = "/" . $cSubDir ;
		$cDir = Svr::IsMVC() ? Svr::GetProjectPath() . "/tmp" : "./include/.data" ;
		$cDir .= $cSubDir ;
		self::seekDir($cDir) ;
		return $cDir ;
	}

	static function GetTmpDir($cSubDir=""){
		if($cSubDir !== "") $cSubDir = "/" . $cSubDir ;
		$n = date("d")%2 ;
		$cDir = "$n/.data/" . session_id() . $cSubDir ;
		$cDir = self::DataDir($cDir) ;	

		// Hapus Data Directory yang sudah lama
		self::DelOldDir() ;

		return $cDir ;
	}
	
	private static function DelOldDir(){
		$x = date("d")%2 == 0 ? 1 : 0 ;
		$cOldDir = self::DataDir() . "/$x" ;
		self::DirDelete($cOldDir) ;
	}
	
	// Copy Direktori Beserta Sub
	static function DirCopy($src, $dst) {
		$lRetval = false ;
		if (is_dir($src)) {
			$lRetval = false ;
			if(!is_dir($dst)) $lRetval = mkdir($dst);		
			if($lRetval){
				$files = scandir($src);
				foreach ($files as $file){
					if (substr($file,0,1) !== "."){
						self::DirCopy("$src/$file", "$dst/$file");
					} 
				}
			}else{
				return $lRetval ;
			}
		}else if(file_exists($src)){
			$lRetval = copy($src, $dst);
		}
		return $lRetval ;
	}

	// Hapus Directory
	static function DirDelete($cDir){
		if (is_dir($cDir)) {
			$files = scandir($cDir);
			foreach ($files as $file){
				if ($file != "." && $file != "..") self::DirDelete("$cDir/$file");
			}
			rmdir($cDir);
		}else if(file_exists($cDir)){
			unlink($cDir);
		}
	}

	static function seekDir($cDir,$lCreateIndexFile=true){
		if(!is_dir($cDir)){
			$vaDir = explode("/",$cDir) ;	
			$cDir = "" ;
			foreach($vaDir as $key=>$value){
				$cDir .= "$value/" ;
				$cDir = str_replace("//","/",$cDir) ;

				if(!is_dir($cDir)){
					mkdir($cDir) ;

					$cFile = "$cDir/index.php" ;
					if(!is_file($cFile) && $lCreateIndexFile) file_put_contents($cFile,"Restricted access") ;
					
					$cFile = "$cDir/df.php" ;
					if(!is_file($cFile) && $lCreateIndexFile) file_put_contents($cFile,"<?php defined( 'main' ) or die( 'Restricted access' ) ?>") ;
				}
			}
		}
		return $cDir ;
	}
}
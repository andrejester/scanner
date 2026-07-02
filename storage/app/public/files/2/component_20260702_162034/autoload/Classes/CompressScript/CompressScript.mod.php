<?php
/* 
Standart Class Autoload
1. Pastikan File Berapa di Folder project/include/autoload/Classes/
2. Pastikan Nama Class Sama Dengan Nama File
3. Tidak Boleh Ada nama Class Kembar di semua Subdir nya
*/
class CompressScript {
  static function jscript($cFileName,$lReload=false){
		$file = self::_zip($cFileName,$lReload) ;				
		return $file ;
	}	
	
	static function css($cFileName){
		$src = "" ;
		if(is_file($cFileName)){
			$cMD5 = md5_file($cFileName) ;
			$cTime = substr(date("YmdHi"),0,-1) ;
			// File TMP Terdiri dari md5_file($cFileName) . HHi ( tapi i hanya kita ambil 1 digit)
			// Kalau Ketemu Tidak usah kita zip ulang filenya, pakai yang ada saja
			// File akan di pakai dengan syarat file sumber tidak mengalami perubahan, dan dalam waktu 10 menit saja
			$tmpFile = Dir::GetTmpDir($cTime) . "/" . $cMD5 . ".css" ;			
			if(is_file($tmpFile)) return $tmpFile ;

			$src = file_get_contents($cFileName) ;
			$src = self::delRemarkBlock($src) ;

			$va = explode("\n",$src) ;
			$src = "" ;
			$vaNoEnter = [","=>0,"{"=>0,";"=>0] ;
			foreach($va as $line){
				$line = self::delRemarkLine($line) ;

				// Jika Karakter Paling kanan ; maka tidak usah di beri karakter enter
				$cEnter = "\n" ;
				if(isset($vaNoEnter[substr($src,-1)])) $cEnter = "" ;
				if(trim($src) == "" || trim($line) == "") $cEnter = "" ;

				$src .= $cEnter . trim($line) ;
			}

			// Simpan Ke File Tmp
			file_put_contents($tmpFile, $src) ;
			$cFileName = $tmpFile ;
		}
		
		return $cFileName;
	}

	private static function _zip($cFileName,$lReload) {
		$src = "" ;
		if(is_file($cFileName)){
			$cMD5 = md5_file($cFileName) ;
			$cTime = substr(date("YmdHi"),0,-1) ;
			// File TMP Terdiri dari md5_file($cFileName) . HHi ( tapi i hanya kita ambil 1 digit)
			// Kalau Ketemu Tidak usah kita zip ulang filenya, pakai yang ada saja
			// File akan di pakai dengan syarat file sumber tidak mengalami perubahan, dan dalam waktu 10 menit saja
			$tmpFile = Dir::GetTmpDir($cTime) . "/" . $cMD5 . ".js" ;			
			if(is_file($tmpFile) && !$lReload) return $tmpFile ;

			$src = file_get_contents($cFileName) ;
			$src = self::delRemarkBlock($src) ;

			$va = explode("\n",$src) ;
			$src = "" ;
			foreach($va as $line){
				$line = self::delRemarkLine($line) ;

				// Jika Karakter Paling kanan ; maka tidak usah di beri karakter enter
				$cEnter = "\n" ;
				if(substr($src,-1) == ";") $cEnter = "" ;
				if(substr($src,-1) == "{") $cEnter = "" ;
				if(substr($src,-1) == ",") $cEnter = "" ;
				if(substr($line,0,1) == "}") $cEnter = "" ;
				if(trim($src) == "" || trim($line) == "") $cEnter = "" ;

				$src .= $cEnter . $line ;
			}
			
			// Simpan Ke File Tmp
			file_put_contents($tmpFile, $src) ;
			$cFileName = $tmpFile ;
		}
		
		return $cFileName;
	}
	
	// Menghilangkan Remak line yang di belakang tanda //
	private static function delRemarkLine($src){
		$va = [
			"http://"=>"http:____",
			"https://"=>"https:____",
			"\//"=>"\/___/",
			"'//" => "/__/___/",
			'"//"' => '"__/__/__"',
			"'//'" => "'__/__/__'"
		] ;
		foreach($va as $key=>$value){
			$src = str_replace($key, $value, $src);
		}
		$nPos = stripos($src, "//");

		// Kalau Ketemu kita buang karakter sebelah kanannya
		if ($nPos !== false){
			$src = substr($src,0,$nPos) ;
		} 

		foreach($va as $key=>$value){
			$src = str_replace($value, $key, $src);
		}
		return trim($src) ;
	}

	// Menghilangkan Remark Block antara / * * /
	private static function delRemarkBlock($src){		
		//$src = str_replace("include 'df.php'", "", $src);
		$src = str_replace("\r", " ", $src);
		$src = str_replace("\t", " ", $src);
		$src = str_replace(chr(0), " ", $src);
		$src = preg_replace("/  +/", " ", $src);
		$src = trim($src) ;
	
		$va = explode("\n",$src) ;
		foreach($va as $key=>$value){
			$nPos = stripos($value,"//") ;
			$nPos2 = stripos($value,"/". "*") ;
			if($nPos !== false && $nPos2 !== false){
				if($nPos2 > $nPos) $va[$key] = str_replace("/" . "*","/_*",$value) ;
			}
		}
		$src = implode("\n",$va) ;

		$vaIgnore = ['"/*"'=>1,"'/*'"=>1,"'*/'"=>1,'"*/"'=>1] ;
		$cRetval = "" ;
		$nLoop = 0 ;
		while($src !== "" && ++$nLoop <= 100000){
			// Jika Ketemu Block / * start 
			$nStart = stripos($src, "/" . "*");
			if($nStart !== false){
				$cKey = substr($src,$nStart-1,4) ;
				if(isset($vaIgnore[$cKey])){
					$cRetval .= substr($src,0,$nStart+3) ;
					$src = substr($src,$nStart+3) ;
				}else{
					$cRetval .= substr($src,0,$nStart) ;

					// Kita akan mencari Block Penutup Mulai dari sejak ketemu karakter / * di atara itu kita hapus karakter nya
					$src = substr($src,$nStart+2) ;				
					$nEnd = stripos($src, "*"."/");
					if($nEnd !== false){
						$src = substr($src,$nEnd+2) ;
					}else{
						$src = "" ;
					}
				}
			}else{
				$cRetval .= $src ;
				$src = "" ;
			}
		}
		return $cRetval ;
	}
}

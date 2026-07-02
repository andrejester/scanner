<?php
	if(!defined("main")) define("main","1") ;
	if(!class_exists("udf")) require_once dirname(__DIR__) . "/autoload/autoload.php" ;
	if(!function_exists("LoadCSSSource")) {
		function LoadCSSSource($cDir,$lNoAnimation){
			if($cDir=="") $cDir = "default" ;
			$vaCSS = [
				__DIR__ . "/global.pre.css",
				__DIR__ . "/{$cDir}/css.pre.css",
				__DIR__ . "/global.css",
				__DIR__ . "/global.form.css",
				__DIR__ . "/global.alert.css",
				__DIR__ . "/global.input.css",
				__DIR__ . "/global.menu.css",
				__DIR__ . "/global.tab.css",
				__DIR__ . "/global.dbg.css",
				__DIR__ . "/{$cDir}/css.css"
			] ;
			if($lNoAnimation)	$vaCSS[] = __DIR__ . "/global.noanimation.css" ;
			// Kalau MVC kita akan load kalau ada file css di dalam include/autoloads/CSS semua file css
			if(Svr::IsMVC()){
				$cDir = Svr::GetProjectPath() . "/include/autoload/CSS" ;
				if(is_dir($cDir)){
					$vaFile = scandir($cDir) ;
					foreach($vaFile as $f){
						$cFile = "$cDir/$f" ;
						if(substr($f,0,1) !== "." && is_file($cFile)){
							$_va = pathinfo($cFile);
							if($_va["extension"] == "css"){
								$vaCSS [] = $cFile ;
							}					
						}				
					}
				}
			}

			foreach($vaCSS as $file){
				if(is_file($file)){
					$file = CompressScript::css($file) ;
					require $file ;
					echo("\n") ;
				}
			}
		}
	}
	if(isset($_GET["t"])){
		LoadCSSSource($_GET["t"],$_GET["a"]) ;
	}else{
		$__cDir = "default" ;
		$__cNoAnimation = 0 ;
		// Kalau cSession_Themes ada isinya berarti dia merupakan Sub Modul yang di kirim sesuai themes induk
		if(GetSetting("cSession_Themes","") == ""){
			if(Svr::IsMVC()){
				// jika Kosong ambilkan ke acfg karena yang baru pakai acfg dia kita save ke database
				SaveSetting("cSession_Themes",aCfg::Get("cSession_Themes","default")) ;
				SaveSetting("cSession_Themes_NoAnimation",aCfg::Get("cSession_Themes_NoAnimation",0)) ;
				$__cDir = GetSetting("cSession_Themes","default") ;
				$__cNoAnimation = GetSetting("cSession_Themes_NoAnimation",0) ;
			}else{
				$__cDirConfig = Dir::DataDir(".themes-config") ;
				$_cssfn = $__cDirConfig . "/" . md5(GetSetting("cSession_UserName")) ;
				if(is_file($_cssfn)){
					$__cDir = trim(file_get_contents($_cssfn));
					$vaDir = explode('|', $__cDir); 
					$__cDir = $vaDir[0];
					if(isset($vaDir[1])) $__cNoAnimation = $vaDir[1];
				} 

				if($__cDir == "" || !is_dir(Svr::GetComponentPath(true) . "/themes/" . $__cDir)) $__cDir = "default" ;
			}
		}else{
			$__cDir = GetSetting("cSession_Themes","") ;
			$__cNoAnimation = GetSetting("cSession_Themes_NoAnimation",0) ;
		}
		$url = Svr::IsMVC() ? MVC::ComponentURL() : compFolder() . "/" ;

		//echo("<link type='text/css' rel='stylesheet' href='" . $url . "themes/css.php?t=$__cDir'>") ;
		echo("<div id='cssRoot' style='display:none'>{$url}themes/css.php?t=$__cDir&a=$__cNoAnimation</div>") ;
	}
?>
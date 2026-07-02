<?php
/* 
Berisi Script Controller dengan method yang bisa kita definisikan sendiri dengan syarat 
1. Semua Method hanya bisa di akses dengan a.ajax atau restfull dengan definisi Header khusus
2. Method yang bisa di akses dari URL public adalah yand di definisikan di Routes sebagai PublicMethod
*/
class component_Controller extends MVC_Controller {
	function index_comp($va){
		// Akses File Ajax
		if($va["method"] !== "ajax.ajax.php" && substr($va["method"],0,5) == "ajax."){
			if($va['method'] == "ajax.js" || $va['method'] == "ajax.php"){
				$_GET["_type"] = "all" ;
			}else{
				$_GET['_type'] = $va["method"] ;
			} 

			header("Content-type: application/javascript");
			require __DIR__ . "/ajax.php" ;
		}else if($va["method"] == "comp.submenu.php"){
			$this->SubMenu($va) ;
		}else if($va["method"] == "themes"){		// Akses Themes
			$this->themes($va) ;
		}else if($va["method"] == "frmprintdialog"){		// Akses Print Dialog
			$this->PrintDialog($va) ;
		}else if($va["method"] == "savelog"){
			SysLog::SaveLog($va) ;
		}else if(($va["method"] == "app" && substr($va['param'],0,13) == "cfgmenulevel.") || substr($va["method"],0,13) == "cfgmenulevel."){
			$this->CfgMenuLevel($va) ;
		/* Dijadikan 1 Dengan SaveLog
		}else if($va["method"] == "menu"){		// Click Menu akan save Syslog
			if($va["param"] == "mmenu.ajax.php/MenuSaveLog"){
				SysLog::ClickMenu($_POST) ;
			}else if($va["param"] == "mmenu.ajax.php/UpdLogCloseMenu"){
				SysLog::CloseForm($_POST) ;
			}
		}else if($va["method"] == "ajax.ajax.php" && $va["param"] == "UpdLogCloseMenu"){
			SysLog::CloseForm($_POST) ;
		*/
		}else if($va["method"] == "activity"){
			$this->KeepAlive($va) ;
		}else if($va["method"] == "autologout"){
			$this->AutoLogout($va) ;
		}else if($va["method"] == "cekdevice"){
			$this->CekDevice($va) ;
		}else{
			$cFile = __DIR__ . "/{$va['method']}" ;
			if($va['param'] !== "") $cFile .= "/{$va['param']}" ;
			if(is_file($cFile)){
				$cType = strtolower(explode("/",mime_content_type($cFile))[0]) ;
				$vaTypeAcc = ["image"=>mime_content_type($cFile)] ;
				$vaFileAcc = [
					"menu/mmenu.ajax.php"=>"file ajax untuk menu",
					"ajax.ajax.php/"=>"",
					"app/frmchangethemes.php"=>"",					// Module Change Themes
					"app/frmchangethemes.disp.php"=>"",			// Module Change Themes
					"app/frmchangethemes.ajax.php"=>"",			// Module Change Themes
				] ; // Daftar File yang boleh di akses				
				if(isset($vaFileAcc["{$va['method']}/{$va['param']}"]) || isset($vaTypeAcc[$cType])){
					if(isset($_GET["_th"]) && $_GET["_th"] == "auto"){
						$this->LoadFile($cFile) ;
					}else{
						require $cFile ;
					}
				}else{
					echo("Restricted access mime $cType {$va["method"]}/{$va["param"]}") ;
				}
			}else{
				echo("Method {$va["method"]} param {$va["param"]} not found") ;
			}
		}
	}
	// Call Menu Level Sekarang Jadikan Satu Form
	function CfgMenuLevel($va){
		$vaPar = explode("/",$va['param']) ;
		$cFunc = "" ;
		if(count($vaPar) == 1){
			$cFile = __DIR__ . "/{$va['method']}/{$va['param']}" ;
			MVC::LoadComponent() ;
		}else if(count($vaPar)>=2){
			$cFile = __DIR__ . "/{$va['method']}/cfgmenulevel.ajax.php" ;
			$cFunc = $vaPar[1] ;
		}
		if(is_file($cFile)){				
			require $cFile ;
			if($cFunc <> "") $cFunc($va) ;
		}
	}
	
	/*
	Module untuk mengambil submenu remote / digunakan untuk submodule yang submodulenya mvc
	*/
	function SubMenu($va){
		$cFile = __DIR__ . "/{$va['method']}" ;
		if(is_file($cFile)){
			require $cFile ;
		}
	}

	/*
	Print Dialog kita jadikan satu aplikasi untuk mvc dan non mvc
	*/
	function PrintDialog($va){
		if($va['param'] == ""){
			MVC::LoadComponent() ;
			include __DIR__ . "/frmprintdialog.php" ;
		}else{
			include __DIR__ . "/frmprintdialog.ajax.php" ;
			$va["param"]($va) ;
		}		
	}

	function themes($va){		
		$cFile = __DIR__ . "/{$va['method']}/{$va['param']}" ;
		$this->LoadFile($cFile) ;
	}
	
	function LoadFile($cFile){
		if(isset($_GET["_th"]) && $_GET["_th"] == "auto"){
			imgChange($cFile) ;
		}else{
			header("Content-Type: " . mime_content_type($cFile)) ;
			require $cFile ;
		}
	}
	
	function KeepAlive($va){
    $cUserName = GetSetting("cSession_UserName") ;
		if($cUserName != ""){
			SaveSetting("last_activity",$va['time'] / 1000) ;
		}
	}
	
	
	
	function AutoLogout($va){
		$msTimeout = aCfg::Get("msTimeout","3000");
		//$msTimeout = aCfg::Get("msTimeout","10");
		$nNow = time();
		$nLastActiv = GetSetting("last_activity");
		$vaReturn = array("Url"=>"","Param"=>"","Prev"=>"");
		//if ($nLastActiv != "" && ($nNow - $nLastActiv) > $msTimeout) {
			//$this->Logout() ;
			SaveSetting("cLogin",0) ;
			User::Delete() ;
			$lSSO = Svr::GetConfig("sso");
			if($lSSO){
				$cUrlSSO  = Svr::GetConfig("auth_server_uri");
				$cParam   = "LogOutProgram";
				$cURL     = $current_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http"). "://" . $_SERVER['HTTP_HOST'];
				$vaReturn = array("Url"=>$cUrlSSO,"Param"=>$cParam,"Prev"=>$cURL);
			}
		//}
		MVC::Response($vaReturn) ;
	}
	
	function CekDevice($va){
		$vaResponse = DeviceExtension::Cek($va) ;
		MVC::Response($vaResponse) ;
	}
}
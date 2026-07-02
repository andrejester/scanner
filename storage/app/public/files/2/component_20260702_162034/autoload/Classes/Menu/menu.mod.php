<?php
/* 
Standart Class Autoload
1. Pastikan File Berapa di Folder project/include/autoload/Classes/
2. Pastikan Nama Class Sama Dengan Nama File
3. Tidak Boleh Ada nama Class Kembar di semua Subdir nya
*/
class menu {
	private static $cCurrDir = "" ;
	private static $cCurrURL = "" ;
	private static $cSubMenuFile = "" ;
	private static $cUserLevel = "" ;
	private static $vaJSSubMenu = [] ;
	private static $vaLevel = [] ;
	
	// Array Menu Digunakan di method self::GetArray
	private static $vaRemoteModul = [] ;
	private static $vaLine = [] ;
	private static $vaConfig = [] ;
	private static $nLine = 0 ;
	private static $vaACFG = [] ;
	private static $vaSubModul = [] ;
	private static $vaConfNonMVC = [] ;		// Untuk menampung configurasi non mvc
	
	private static function initValue(){
		self::$vaJSSubMenu = [] ;
		self::$vaLevel = [] ;
		self::GetConfig() ;
		
		if(self::$cCurrDir == ""){
			// Mengambil Current Directory berbeda untuk mvc dan yang lama
			// kalau yang lama langsung posisi file nya tapi kalau mvc harus menggunakan endpoint (http://)
			$cDir = __DIR__ ;
    	$cRoot = Svr::GetDocumentRoot() ;
    	self::$cCurrURL = "../" . substr($cDir,strlen($cRoot)+1) ;

			self::$cCurrDir = "." ;
			if(Svr::IsMVC()){
				self::$cCurrDir = Svr::GetProjectPath() . "/mvc" ;
				self::$cCurrURL = MVC::ComponentURL() . "autoload/Classes/Menu" ;
			}
		}
	}

	static function SubMenuFile($cFile=""){
		self::$cSubMenuFile = $cFile ;
	}

  static function show($cUserLevel='',$cParentID=null){
		// Kita Definisikan appid
		$va ["menu"] = self::mnu2Array("",$cUserLevel) ;
		//retract dari cookie ke dom
		//$va ["conf"] = ["cParentID"=>$cParentID] ;
		$va ["conf"] = ["cParentID"=>$cParentID,"token"=>cds::CreateToken(),"appid"=>Svr::GetAppID()] ;

		echo('<div id="_div_mnu_pulldown_" style="display:none">' . json_encode([$va]) . '</div>') ;
		include GetFileModul(__FILE__,'.jscript.php') ;

		// Ambil Javascript menu.jscript.php di project
		$cJSMenu = self::$cCurrDir . "/menu.jscript.php" ;
		if(is_file($cJSMenu)){
			include CompressScript::jscript($cJSMenu) ;
		} 
		
		// Kita juga load file jscript di sub modul nya
		if(count(self::$vaJSSubMenu) > 0){
			foreach(self::$vaJSSubMenu as $cJSMenu){
				if(is_file($cJSMenu)) include CompressScript::jscript($cJSMenu) ;
			}
		}
	}

	static function mnu2Array($cMenuFileName='',$cUserLevel=""){
		if($cUserLevel == "") $cUserLevel = GetSetting("cSession_UserLevel","0000") ;
		self::$cUserLevel = $cUserLevel ;

		$vaLines = self::GetArray($cMenuFileName,true) ;
		$vaMenu = [] ;
		$vaLevel = [] ;
		$vaMnuNumber = [] ;
		foreach($vaLines["menu"] as $key=>$line){
			$line = str_replace("\t","  ",$line) ;
      $lShowMenu = substr(trim($line),0,2) == "//" ? false : true ; 
			if($lShowMenu){
				$nSub = strpos($line,"[") ;
				if($nSub !== false){
					$nSub = $nSub / 2 ;
					$vaLevel = self::_setLevel($vaLevel,$nSub) ;
					$vaLevel[$nSub] ++ ;
					
					//Penambahan cLevel Agar Menu Number Tetap Sesuai Menu Awal Ketika Level Di Setting Per User
					$vaLineNumber = json_decode($line,true) ;
					if($vaLineNumber[0] !== "-"){
						$vaMnuNumber = self::_setLevel($vaMnuNumber,$nSub) ;
						$vaMnuNumber[$nSub] ++ ;
					}
				  $cMnuNumber = implode(".",$vaMnuNumber) ;
					$line = self::_setItemKey($line,$vaLines["config"][$key],$key,$cMnuNumber) ;
					$c = "\$vaMenu[" . implode("]['subMenu'][",$vaLevel) . "]['item'] = " . trim($line) . " ;" ;
					eval($c) ;
				}
			}
    }
		$vaMenu = self::setupMenuPath($vaMenu,"","","") ;
		
	  return $vaMenu ;
  }

	// Kita akan setting Menu Path nya contoh file/master tabungan/golongan tabungan dll
	private static function setupMenuPath($vaMenu,$cPath,$cMenuNumber,$cOldPath){
		if(count(self::$vaLevel) == 0){
			$cUserLevel = md5(self::$cUserLevel) ;
			self::$vaLevel = [] ;
			$dbData = objData::ABrowse("username_menu","*","Level = '$cUserLevel'") ;
			foreach($dbData as $dbRow){
				self::$vaLevel[$dbRow["Keterangan"]] = isset($dbRow ['Button']) ? $dbRow["Button"] : "" ;
			}
		
			$dbData = objData::SQL("show fields from username_menu where Field = 'Button'") ;
			if(!$dbRow = objData::GetRow($dbData)){
				objData::SQL("ALTER TABLE username_menu ADD Button VARCHAR(100)") ;
			}
		}
		$lMenuNumber = aCfg::Get("lMenuNumber",0) ;
		$vaTitle = [] ;
		$lPrevSeparator = true ;
		$lDelMenu = false ;
		$lastMenu = "" ;
		$nMenuNumber = 1 ;
		foreach($vaMenu as $key=>$value){
			if(!isset($vaTitle[$value["item"]["mnuTitle"]])) $vaTitle[$value["item"]["mnuTitle"]] = 0 ;
			$vaTitle[$value["item"]["mnuTitle"]] ++ ;
			$lDelMenu = false ;

			$cFullPath = $cPath . "/" . $value["item"]["mnuTitle"] ;
			$cMnuID = md5($cFullPath . "_" . $vaTitle[$value["item"]["mnuTitle"]]) ;		// Ini Key Untuk Menu Level baru
						
			$cOldFullPath = $cOldPath . $value["item"]["mnuTitle"] ;
			$cMnuTitle = md5($cOldFullPath) ;													// Ini Key Untuk Menu Level Lama biar Compatible

			$vaMenu [$key]["item"]["mnuPath"] = $cFullPath ;
			$vaMenu [$key]["item"]["mnuID"] = $cMnuID ;
			$vaMenu [$key]["item"]["oldMnuID"] = $cMnuTitle ;
			$vaMenu [$key]["item"]["buttonListAllowed"] = "*" ;
			//$vaMenu [$key]["item"]["mnuNumber"] = "" ; //Menu Number Dipindah Diatas Agar Tetap Sama
			if(isset($value["subMenu"])) $vaMenu [$key]["subMenu"] = self::setupMenuPath($value["subMenu"],$cFullPath,$cMenuNumber . "." . $nMenuNumber,$cOldFullPath) ;

			if(self::$cUserLevel !== "0000"){
				if(!isset(self::$vaLevel[$cMnuID]) && !isset(self::$vaLevel[$cMnuTitle]) && $vaMenu[$key]["item"]["mnuTitle"] !== "-"){
					$lDelMenu = true ;
					unset($vaMenu[$key]) ;
				}else{
					if(isset(self::$vaLevel[$cMnuID])){
						if(count($vaMenu[$key]["item"]["buttonList"]) > 0){
							$vaAllow = [] ;
							foreach($vaMenu[$key]["item"]["buttonList"] as $_value){
								$vaAllow [$_value] = false ;
							}
						
							$_vaAllow = explode(",",self::$vaLevel[$cMnuID]) ;
							foreach($_vaAllow as $_value){
								if(isset($vaAllow[$_value])) $vaAllow [$_value] = true ;
							}
							$vaMenu [$key]["item"]["buttonListAllowed"] = $vaAllow ;
						}
					}
				}
			}
			
			if(!$lDelMenu){
				// Jika Jenisnya separator dan sebelum nya adalah separator berarti kita delete menu itu
				// untuk menghindari separator double
				if($value["item"]["mnuTitle"] == "-" && $lPrevSeparator){
					unset($vaMenu[$key]) ;
				}else{
					$lPrevSeparator = $value["item"]["mnuTitle"] == "-" ;
					$lastMenu = $key ;
					
					if($value["item"]["mnuTitle"] !== "-"){
						if($lMenuNumber) $vaMenu [$key]["item"]["mnuNumber"] = substr($cMenuNumber . "." . $nMenuNumber,1) ;  //Menu Number Dipindah Diatas Agar Tetap Sama
						$nMenuNumber ++ ;
					} 
				}
			}
		}

		// Kalau element terakhir adalah separator maka kita hapus
		if(isset($vaMenu[$lastMenu]) && isset($vaMenu[$lastMenu]["item"]) && isset($vaMenu[$lastMenu]["item"]["mnuTitle"]) && $vaMenu[$lastMenu]["item"]["mnuTitle"] == "-"){
			unset($vaMenu[$lastMenu]) ;
		}

		return $vaMenu ;
	}

	private static function _setItemKey($line,$host,$key,$cLevel=""){
		$line = str_replace(" //","; //",$line) ;
		eval("\$va=" . trim($line) . ";") ;

		$cFunc = isset($va[1]) ? str_replace("-","",str_replace("&","",str_replace("\\","",str_replace("/","",str_replace(".","",$va[1]))))) : "" ;
		$cFunc = explode("?",$cFunc) ;
		$cFunc = $cFunc[0] ;
		
		$cSubMVC = isset($host ["sub_mvc"]) ? $host["sub_mvc"] : "" ;
		
		$ret["mnuTitle"]  = isset($va[0]) ? $va[0] : "" ;
		$ret["url"]       = isset($va[1]) ? $va[1] : "" ;
		$ret["icon"]      = isset($va[2]) ? $va[2] : "" ;
		$ret["frmTitle"]  = isset($va[4]) ? $va[4] : "" ;
		$ret["frmWidth"]  = isset($va[5]) ? $va[5] : "" ;
		$ret["frmHeight"] = isset($va[6]) ? $va[6] : "" ;
		$ret["mnuFunc"]		= $cFunc ;
		$ret["host"]			= $host ["host"] ;
		$ret["sub_mvc"]		= $cSubMVC ;
		$ret["buttonList"] = isset($va[7]) ? $va[7] : [] ;
		$ret["mnuNumber"]  = $cLevel ;

		return var_export($ret,true) ;
	}
	
	private static function _setLevel($vaLevel,$nSub){
		if($nSub >= count($vaLevel)){
			for($x=count($vaLevel);$x<=$nSub;$x++){
				array_push($vaLevel,0) ;
			}
		}else{
			// $nSub = 0 count(va) = 4
			$nMax = count($vaLevel) - 1 ;			
			for($x=$nSub;$x<$nMax;$x++){
				array_pop($vaLevel) ;
			}
		}
		return $vaLevel ;
	}

	// Mengambil File menu.menu.php ke array
	private static function GetArray($cMenuFileName='',$lConfig=false){
		self::initValue() ;
		self::$vaRemoteModul = [] ;
    self::$vaSubModul = self::CheckSubModul() ;

    if(empty($cMenuFileName)){
			$cMenuFileName = self::$cCurrDir . "/menu.menu.php" ;
			if(!is_file($cMenuFileName)){
				$cMenuFileName = self::$cCurrDir . "/menu.menu" ;
			}	
    }

    $vaL = file($cMenuFileName) ;
    self::$vaLine = [] ;
    self::$vaConfig = [] ;
    self::$nLine = 0 ;
    self::$vaACFG = [] ;
    foreach($vaL as $key=>$value){
			$value = str_replace("\t","  ",$value) ;
      if(substr(trim($value),0,2) == "//") $lShowMenu = false ;

      // Ambil Sub Modul Kalau dia Sub Module maka akan kita insert Menu di sub nya
			// dan menu yang <submenu tidak kita masukkan sebagai item menu
      if(self::GetSubMenu($value,1)){
        self::$vaLine [self::$nLine] = $value ;
        self::$vaConfig [self::$nLine] = array("host"=>"localhost") ;
        self::$nLine ++ ;
      }
    }
    
    self::$vaLine = $lConfig ? array("menu"=>self::$vaLine,"config"=>self::$vaConfig) : self::$vaLine ;
    return self::$vaLine ;
  }

	// Jika NON MVC kita akan ambil di assist.ini.php di dalam include biar bisa di read data submenu nya
	private static function GetConfig(){
		self::$vaConfNonMVC = [] ;
		if(!Svr::IsMVC()){
			$cFile = Svr::GetProjectPath() . "/include/assist.ini.php" ;
			if(is_file($cFile)){
				$data = file($cFile) ;
				foreach($data as $key=>$value){
					$value = trim($value) ;

					if(substr($value,0,1) <> "#"){
						$va = split("=",$value) ;
						if(count($va) >= 2 && $va[0] !== ""){
							$va[0] = trim(strtolower($va[0])) ;
							$va[1] = trim($va[1]) ;
							self::$vaConfNonMVC [$va[0]] = $va[1] ;
						}
					}
				}
			}
		}
	}

	private static function GetSubMenu($value,$nLevel,$nSpaceInduk=0){
		$nLevel ++ ;
		$lShowMenu = true ;
		if(strtolower(substr(trim($value),0,9)) == "<submenu:"){
			$lShowMenu = false ;
			$value = strtolower($value) ;
			$nSpace = strpos($value,"<submenu:") + $nSpaceInduk ;
			$value = trim($value) ;
			$cKey = str_replace(">","",substr($value,9)) ;

			// Load Submenu Pada Server Remote
			$vaKey = explode("@",$cKey) ;
			$cHost = "localhost" ;
			if(count($vaKey) >= 2){
				// Kalau Data ada di acfg maka akan kita ambilkan ke acfg				
				if(!isset(self::$vaACFG [$vaKey [1]])){
					if(Svr::IsMVC()){
						// Jika MVC Kita Ambilkan ke untuk url kita masukkan ke assist.php bukan lagi di acfg
						// contoh msSubModule_tabungan kita langsung ambil dari sisConfig
						self::$vaACFG [$vaKey [1]] = SisConfig::GetValue($vaKey[1],$vaKey[1]) ;
					}else if(isset(self::$vaConfNonMVC[$vaKey [1]])){						
						self::$vaACFG [$vaKey [1]] = self::$vaConfNonMVC[$vaKey [1]] ;
					}else{
						self::$vaACFG [$vaKey [1]] = aCfg::Get($vaKey [1],$vaKey [1]) ;
					}
				}

				// Menghilangkan '/' paling kanan
				$cHost = fix_url(self::$vaACFG [$vaKey [1]]) ;
				if($cHost !== "http://" && $cHost !== "https://"){
					$cHost = rtrim($cHost, "/") ;
				}

				//Kita cek terlebih dahulu untuk setup submodule nya jika menggunakan localhost maka kita ganti localhostnya menjadi Server Name nya
				$vaHost = explode("/",$cHost) ;
				if($vaHost[2] == "localhost" || $vaHost[2] == "127.0.0.1"){
					$cHost = $_SERVER["SERVER_NAME"] ."/".$vaHost[3] ;
				}
				if(!isset(self::$vaRemoteModul [$cHost]) && $nLevel <= 5){
					self::$vaRemoteModul [$cHost] = cds::GetSubMenu($vaKey [0],$cHost) ;
				}
				if(isset(self::$vaRemoteModul [$cHost]["body"][$vaKey [0]])) self::$vaSubModul [$cKey] = self::$vaRemoteModul [$cHost]["body"][$vaKey [0]] ;
			}
			if (isset(self::$vaSubModul [$cKey])){
				$vaMenu = self::$vaSubModul [$cKey] ;
				foreach($vaMenu as $key1=>$value1){
					if(trim($value1) <> ""){
						// Ambil Sub Modul Kalau dia Sub Module maka akan kita insert Menu di sub nya
						// dan menu yang <submenu tidak kita masukkan sebagai item menu
						if(self::GetSubMenu($value1,$nLevel,$nSpace)){
							$cSubMVC = isset(self::$vaRemoteModul [$cHost]) ? self::$vaRemoteModul [$cHost]["sub_mvc"] : "" ;
							self::$vaLine [self::$nLine] = str_repeat(" ",$nSpace) . $value1 ;
							self::$vaConfig [self::$nLine] = array("host"=>$cHost,"sub_mvc"=>$cSubMVC) ;
							self::$nLine ++ ;
						}
					}
				}
			}
		}
		return $lShowMenu ;
	}

  private static function CheckSubModul(){
		self::initValue() ;

    $vaRetval = array() ;
    $cSubModul = GetSetting("cSession_SubModul","") ;
		$cFileSub = "submenu" ;
		if(self::$cSubMenuFile !== "" && is_file(self::$cSubMenuFile)){
			$cSubModul = dirname(self::$cSubMenuFile) ;
			$cFileSub = basename(self::$cSubMenuFile,".menu.php") ;
		}
   if(true){ //!empty($cSubModul) && is_dir($cSubModul)){
      if(is_file("$cSubModul/$cFileSub.jscript.php")) self::$vaJSSubMenu[] = "$cSubModul/$cFileSub.jscript.php" ;

      $cFile = "$cSubModul/$cFileSub.menu.php" ;
      if(true){ //is_file($cFile)
        $vaLine = (is_file($cFile)) ? file($cFile) : explode("\n",self::$cSubMenuFile) ;
				$cKey = "" ;
				foreach($vaLine as $key=>$value){
					$value = html_entity_decode($value) ;
					$value = str_replace("\t","  ",$value) ;
					$vaRemote = explode("@",$value) ;					
					// Kalau tidak ada @ nya kita ambil sebegai submenu kalau @ nya akan kita ambil di item menu biar nanti di ambil di remote di menu
          if(strtolower(substr(trim($value),0,9)) == "<submenu:" && count($vaRemote) < 2){						
            $value = trim(strtolower($value)) ;
            $cKey = str_replace(">","",substr($value,9)) ;
          }else if(!empty($cKey)){
            $val = trim($value) ;
            $lShowMenu = true ;
            if(substr($val,0,2) == "/" . "/" || empty($val)){
              $lShowMenu = false ;
            }
            if($lShowMenu) $vaRetval [$cKey][$key] = $value ;
          }
        }
      }
    }
    return $vaRetval ;
  }
}

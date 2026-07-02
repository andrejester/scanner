<?php
  include 'df.php' ;

	require_once 'autoload/autoload.php' ;
	$cVer = "2.0" ;
	if(defined("__COMPONENT_VERSION__")) $cVer = __COMPONENT_VERSION__ ;

	if(version_compare("2.0",$cVer,">=")){
		/*
		Jika Komputer yang digunakan Menggaunakan Format Component 2.0 kebawah maka Kita akan Akses 2 File ini
		Tapi Jika Sudah menggunakan format komputer 2.1 maka kita menggunakan Autoload
		*/
		$objData = new myData ;
		$odt = new odtOld() ;
	} 
	include 'func.comp.mod.php' ;	
	
	/* retract dari cookie ke dom
	// Pembuatan Token di lakukan disini karena file ini selalu di load oleh Aplikasi.
	$vaToken = cds::GetToken(false) ;
	if(count($vaToken) == 0){
		$cToken = cds::CreateToken() ;
		Svr::SaveCookie("token",$cToken) ;
	}

	//$cAppID = Svr::GetAppID() ;
	//Svr::SaveCookie("appid",$cAppID) ;
	*/
	
	//CSRF Untuk Monolithik Via Cookie
	/*
	if(!defined("vaConfig")){
		//CSRF Untuk Pembukaan Form Awal
		if($_SERVER['REQUEST_METHOD'] == 'GET'){
			Svr::GenerateCSRFToken() ;
			//if(!defined("csrfloaded")) define("csrfloaded","1") ;
		}
		
		//Validasi CSRF
		$vaSvr 			= Svr::GetAllHeaders() ;
		if(basename($_SERVER['SCRIPT_NAME']) == 'ajax.php' && $_SERVER['REQUEST_METHOD'] == 'POST'){
			if(isset($vaSvr['REQ-ID']) && $vaSvr['REQ-ID'] == 'ajax'){
				if(isset($vaSvr['CSRF-TOKEN'])){
					if(hash_equals($vaSvr['CSRF-TOKEN'],Svr::GetCSRFToken())){
						Svr::GenerateCSRFToken() ;
					}else{
						$cError = "Invalid CSRF Token" ;
					}
				}else{
					$cError = "CSRF Token Not Found" ;
				}
				
				if(!isset($vaSvr['APP-ID'])){
					$cError = "APP ID Not Found" ;
				}else if(empty($vaSvr['APP-ID'])){
					//$cError = "APP ID Not Valid" ;
				}
				
			}else{
				$cError = "CSRF Token Not Valid" ;
			}
		}
		
		if(isset($cError)){
			header("HTTP/1.1 401 Unautorized");
			echo $cError ;
			exit() ;
		}
	}
	*/

	//CSRF Masing2 Form
	//if(!defined("vaConfig")){
		//$protocol   = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
		//$requestUrl = $protocol . '://' .$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
//echo $_SERVER ; exit() ;
		//echo("<div style='display:none' id='divcsrfToken'>" .Svr::GenerateCSRFToken($requestUrl) . "</div>") ;
	//}
	
	/* CSRF Terbaru
	if(!defined("vaConfig")){
		//Validasi CSRF
		if(basename($_SERVER['SCRIPT_NAME']) == 'ajax.php' && $_SERVER['REQUEST_METHOD'] == 'POST'){
			$vaSvr 			= Svr::GetAllHeaders() ;
			if(isset($vaSvr['REQ-ID']) && $vaSvr['REQ-ID'] == 'ajax'){
				if(isset($vaSvr['CSRF-TOKEN'])){
					if(empty(Svr::GetCSRFTokenByValue($vaSvr['CSRF-TOKEN']))){
						$cError = "Invalid CSRF Token" ;
					}
				}else{
					$cError = "CSRF Token Not Found" ;
				}
				
				if(!isset($vaSvr['APP-ID'])){
					$cError = "APP ID Not Found" ;
				}else if(empty($vaSvr['APP-ID'])){
					//$cError = "APP ID Not Valid" ;
				}
			}else{
				$cError = "CSRF Token Not Valid" ;
			}
		}else if(basename($_SERVER['SCRIPT_NAME']) <> 'rpt.php' && isset($_SERVER['HTTP_HOST'])){
			$protocol   	= isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
			$requestUrl 	= $protocol . '://' .$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
			$varequestUrl = explode('?', $requestUrl);
			$requestUrl 	= $varequestUrl[0];
			if(isset($_POST['__par']) && basename($_POST['__par']) == "frmprintdialog.php") $requestUrl .= "-printdialog" ;
			//echo("<div style='display:none' id='divcsrfToken'>" .Svr::GenerateCSRFToken($requestUrl) . "</div>") ;
		}
		
		if(isset($cError) && Svr::GetConfig("csrf_validation",1)){
			if(Svr::GetConfig("csrf_senderror",1)){
				$messageapi = ['chat_id' => '-1001946992174',
											 'text' 	 => "Error Di ".$_SERVER['HTTP_REFERER']." ".$cError." Script Name ".$_SERVER['SCRIPT_NAME']." | IP ".$_SERVER['REMOTE_ADDR']." | Username ".GetSetting("cSession_UserName")];
				$response   = file_get_contents("https://api.telegram.org/bot6099747329:AAHYLkFxxg8PuCqs3SqBwISsXAL7msrTDZo/sendMessage?".http_build_query($messageapi));
			}
			//header("HTTP/1.1 401 Unautorized");
			//echo $cError ;
			//exit() ;
		}
	}
	*/
function GetSession($cKey,$cDefault = ''){
	return Session::Get($cKey,$cDefault) ;
}

function SaveSession($cKey,$cValue){
	Session::Save($cKey,$cValue) ;
}

function Dec2Text($nDec,$lRupiah=true,$nRound=2){
  return d2t::Dec2Text($nDec,$lRupiah,$nRound) ;
}

function compFolder($lFullPath = false){
	return Svr::GetComponentPath($lFullPath) ;
}

function NextMonth($nTime,$nNextMonth){
	return Conv::NextMonth($nTime,$nNextMonth) ;
}

function NextDay($nTime,$nNextDay){
	return Conv::NextDay($nTime,$nNextDay) ;
}

function NextWeek($nTime,$nNextWeek){
  return Conv::NextDay($nTime,$nNextWeek*7) ;
}

function Date2String($dTgl){
	return Conv::Date2String($dTgl) ;
}

function String2Date($cString){
	return Conv::String2Date($cString) ;
}

function String2Number($cString){
	return Conv::String2Number($cString) ;
}

function Number2String($nNumber,$nDecimals=2){
	return Conv::Number2String($nNumber,$nDecimals) ;
}

function getVar($va=[]){
	return Conv::GetVar($va) ;
}

function Devide($a,$b){
	return Conv::Devide($a,$b) ;
}

function GetFileModul($cFileName,$cExt){
	return Conv::GetFileModul($cFileName,$cExt) ;
}

function PrintIO($vaPrint,$lEject=true,$nCharSpace=0,$cTextInit="\033\033\017\017",$nPortID=1){
	PrintIO::Print($vaPrint,$lEject,$nCharSpace,$cTextInit,$nPortID) ;
}

function menuHorizontal($nTop=1,$nLeft=1,$cMultiForm='',$cMenuFileName='',$lMySQL=true){
  oMenu::menuHorizontal($nTop,$nLeft,$cMultiForm,$cMenuFileName,$lMySQL) ;
}

function menuVertical(){}

function GetMenu_Array($va=array()){
  $cMenuFileName  = isset($va ['cMenuFileName']) ? $va ['cMenuFileName'] : "" ;
  $lConfig        = isset($va ['lConfig']) ? $va ['lConfig'] : false ;
  return oMenu::GetArray($cMenuFileName,$lConfig) ;
}

function SaveLog_Login(){
  $_va = array("MTI"=>"01","DT"=>date("Ymdhis"),"UID"=>GetSetting("cSession_UserName"),"IP"=>SysLog::GetHost(),"BR"=>SysLog::GetAgent()) ;
  SysLog::Save(json_encode($_va)) ;
}

function SaveLog_Logout(){
  $_va = array("MTI"=>"02","DT"=>date("Ymdhis"),"UID"=>GetSetting("cSession_UserName"),"IP"=>SysLog::GetHost(),"BR"=>SysLog::GetAgent()) ;
  SysLog::Save(json_encode($_va)) ;
}

function SaveLog_Message($cMessage){
  $_va = array("MTI"=>"99","DT"=>date("Ymdhis"),"UID"=>GetSetting("cSession_UserName"),"IP"=>SysLog::GetHost(),"BR"=>SysLog::GetAgent(),"Message"=>$cMessage) ;
  SysLog::Save(json_encode($_va)) ;
}

function fix_url($url) {
	if (!preg_match('/^https?:\/\//', $url)) {
		$url = 'http://' . $url;
	} elseif (strpos($url, 'https://') === 0) {
		$url = str_replace('https://', 'https://', $url);
	}
	return $url;
}

function imgChange($file){
	static $themes = null ;
	if($themes == null) $themes = GetSetting("cSession_Themes","default") ;
	
	$vaFile = pathinfo($file) ;
	$image = null ;
	// Mengambil gambar
	if($vaFile ['extension'] == "gif"){
		$image = imagecreatefromgif($file);
	}else if($vaFile["extension"] == "png"){
		$image = imagecreatefrompng($file) ;
	}

	if($image !== null){
		if($themes == "night") imagefilter($image, IMG_FILTER_NEGATE);

		// Menyimpan gambar
		header('Content-Type: image/png');
		imagepng($image);

		// Menghancurkan gambar
		imagedestroy($image);
	}else{
		require $file ;
	}	
}
?>

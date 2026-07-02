<?php
/* 
Standart Class Autoload
1. Pastikan File Berapa di Folder project/include/autoload/Classes/
2. Pastikan Nama Class Sama Dengan Nama File
3. Tidak Boleh Ada nama Class Kembar di semua Subdir nya
*/
class CurlAssistTeam {
  private static $urlBase = "http://dev1.sis1.net/assist-switching_cbd/public/api/" ;
	
	static function InitData($cUrl){
		self::$urlBase .= $cUrl;
	}
	static function Send($cUrl,$vaPost,$cToken){
		//self::InitData($cUrl) ;
		$curl   = curl_init();
		$vaData	= ["DEVICEID"=>"x.x.x.x","PLATFORM" => "WEB","VERSIAPLIKASI" => "1.0.0","MTI" => "0300","DATA" => ""];
		if(is_array($vaPost)){
			$vaData["DATA"] = json_encode($vaPost);
		}
		$vaData = json_encode($vaData);
		$vaHeader = array('Content-Type: application/json','Authorization: Bearer '.$cToken);
		curl_setopt_array($curl, array(
			CURLOPT_URL => self::$urlBase.$cUrl,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'POST',
			CURLOPT_POSTFIELDS =>$vaData,
			CURLOPT_HTTPHEADER => $vaHeader
		));
		$vaResponse = curl_exec($curl);
		curl_close($curl);
		$vaResponse = json_decode($vaResponse,true) ;
		if(is_array($vaPost)){
			$vaResponse = json_decode($vaResponse["data"],true) ;
		}
		return $vaResponse;
	}
	
	static function GetAccessToken(){
		$cToken = "";//aCfg::Get("msTokenGetAccess","") ;
		if($cToken == ""){
			//ambil data token ini nanti kedepannya menggunakan cli
			$cUrl			= "getaccesstoken";
			$cBearer	= "303f7135b67dcd453dd6369810674b72";//aCfg::Get("msSertifikatAssistTeam","");
			$vaToken 	= self::Send($cUrl,"",$cBearer);
			//$vaToken 	= json_decode($vaToken,true); 
			$cToken   = $vaToken["Data"]["AccessToken"];
			//aCfg::Upd("msTokenGetAccess",$cToken);
		}
		return $cToken;
	}
}

<?php
/* 
Standart Function Autoload
1. Pastikan File Berapa di Folder project/include/autoload/Functions/
2. Pastikan Nama Function Sama Dengan Nama File
3. Tidak Boleh Ada nama Function Kembar di semua Subdir nya
4. Cara Memanggil Function: 
									udf::CurlAssistTeam(...parameters) 
*/
function CurlAssistTeam($url,$vaDatas,$cToken){
	$curl = curl_init();
	$vaData	= ["DEVICEID"=>"x.x.x.x",
						 "PLATFORM" => "WEB",
						 "VERSIAPLIKASI" => "1.0.0",
						 "MTI" => "0300",
						 "DATA" => ""
						];
	if(is_array($vaDatas)){
		$vaData["DATA"] = json_encode($vaDatas);
	}

	$vaData = json_encode($vaData);
	$vaHeader = array('Content-Type: application/json','Authorization: Bearer '.$cToken);
	curl_setopt_array($curl, array(
		CURLOPT_URL => MVC::GetConfig("_URL_SW")."/".$url,
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
	return $vaResponse;
}

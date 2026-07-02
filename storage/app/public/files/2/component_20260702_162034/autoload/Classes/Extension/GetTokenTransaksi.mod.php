<?php
/* 
Standart Class Autoload
1. Pastikan File Berapa di Folder project/include/autoload/Classes/
2. Pastikan Nama Class Sama Dengan Nama File
3. Tidak Boleh Ada nama Class Kembar di semua Subdir nya
*/
class GetTokenTransaksi {
  public static function Get($cScope=''){
		$cTokenTransaksi = User::GetAuthCode("cTransaksiToken") ;
		if($cTokenTransaksi == ""){
			$cClientID = Svr::GetConfig("OAUTH_CLIENT_ID") ;
			$cPrivateKey = str_replace('\n', "\n", Svr::GetConfig("oauth_token_private"));


			$vaPayload = [
				"sub" => $cClientID,
				"aud" => Svr::GetConfig("OAUTH_TOKEN_AUDIENCE"),
				"iat" => time(),
				"exp" => time()+3600
			];
			$cTokenAssertion = JwtToken::encode($vaPayload,$cPrivateKey,null,$cClientID) ;
			$vaBody = array("grant_type"=>"client_credentials_assertion","client_assertion"=>$cTokenAssertion,"scope"=>$cScope);
			$cRequest = HttpHelper::Send(Svr::GetConfig("base_url_auth") . "/app/token",$vaBody,"POST");
			//$cRequest = udf::SendHTTPRequest(Svr::GetConfig("base_url_auth")."/app/token",$vaBody);
			$vaRequest = json_decode($cRequest,true) ;
			if(isset($vaRequest['response_code'])){
				if($vaRequest['response_code'] == "200"){
					$cTokenAssertion = $vaRequest['data']['access_token'];
					// save redis disini nanti
				}else{
					MVC::Response("","",$vaRequest["message"]) ;die;
					return $cRequest;
				}
			}else{
				MVC::Response("","",$vaRequest["message"]) ;die;
				return $cRequest;
			} 
		}
		return $cTokenAssertion;
	}
}

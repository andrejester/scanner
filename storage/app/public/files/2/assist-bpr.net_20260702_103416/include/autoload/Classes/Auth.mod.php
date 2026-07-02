<?php
  include 'df.php' ;

class Auth{
	static function base64url_encode($data){
	  return rtrim(strtr(base64_encode($data), "+/", "-_"), "=");
	}
	
	static function IsJwtValid($cToken){
		$cSecret    = "AssistindoCoreDevelelopment";
	  $vaToken = explode(".", $cToken);
		if(count($vaToken) != 3){
			return false ; //self::Response("401","Unauthorized");
		}
		
		$cHeader     				= base64_decode($vaToken[0]);
		$cPayload    				= base64_decode($vaToken[1]);
		$cSignatureProvided = $vaToken[2];
	  $cCustomerId 			  = json_decode($cPayload)->customer_id;
		
		$dbData = objData::Browse("config","Kode","Kode = 'msCDSID' and Keterangan = '$cCustomerId'");
		if(objData::Rows($dbData) < 0){
			return false ; //Response("401","Unauthorized");
		}
		
		/*
		// Check Expiration Date
		$dbData = objData::Browse("customer_token","ExpiredDate","token = '$cToken'");
		if($dbRow = objData::GetRow($dbData)){
			if(strtotime($dbRow['ExpiredDate']) <= strtotime(date("Y-m-d"))){
				return false ;
			}
		}else{
			return false;
		}*/

		$cHeaderBase64    = self::base64url_encode($cHeader);
		$cPayloadBase64   = self::base64url_encode($cPayload);
		$cSignature       = hash_hmac("SHA256",$cHeaderBase64 . "." . $cPayloadBase64,$cSecret,true);
		$cSignatureBase64 = self::base64url_encode($cSignature);
		$lValidSignature = $cSignatureBase64 === $cSignatureProvided;
	  if (!$lValidSignature) {
			return false;
		}else {
			return true;
	  }
	}

	static function get_authorization_header(){
	  $headers = null;
	  if (isset($_SERVER["Authorization"])) {
		  $headers = trim($_SERVER["Authorization"]);
		}elseif (isset($_SERVER["HTTP_AUTHORIZATION"])) {
			//Nginx or fast CGI
		  $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
		} elseif (function_exists("apache_request_headers")) {
			$requestHeaders = apache_request_headers();
			// Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
			$requestHeaders = array_combine(
			array_map("ucwords", array_keys($requestHeaders)),array_values($requestHeaders));
			if (isset($requestHeaders["Authorization"])) {
			  $headers = trim($requestHeaders["Authorization"]);
			}
	  }
	  return $headers;
	}

	static function GetToken(){
	  $headers = self::get_authorization_header();
	  if (!empty($headers)) {
		  if (preg_match("/Bearer\s(\S+)/", $headers, $matches)) {
			  return $matches[1];
			}
		}
	  return null;
	}
	
	static function Response($nResponseCode,$message,$data=null){
		$vaResponse = ["response_code"=>$nResponseCode,"data"=>$data,"message"=>$message ] ;
		echo json_encode($vaResponse) ;
	}
	static function CreateToken(){
		$secret            = "AssistindoCoreDevelelopment" ;
		$headers 				   = array('alg'=>'HS256','typ'=>'JWT');
		$payload 					 = array('customer_id'=>aCfg::Get("msCDSID"));
	  $headers_encoded   = self::base64url_encode(json_encode($headers));
	  $payload_encoded   = self::base64url_encode(json_encode($payload));
		$signature         = hash_hmac("SHA256","$headers_encoded.$payload_encoded",$secret,true);
		$signature_encoded = self::base64url_encode($signature);
		$jwt = "$headers_encoded.$payload_encoded.$signature_encoded";
		return $jwt;
	}
}
?>
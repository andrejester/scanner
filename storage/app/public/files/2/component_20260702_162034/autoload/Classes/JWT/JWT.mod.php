<?php
	if(!defined("JWT")) define("JWT","1") ;

/*
 * JSON Web Token implementation
 *
 * Minimum implementation used by Realtime auth, based on this spec:
  *
 * @author Neuman Vong <neuman@twilio.com>
 */
class JWT {
	const HASHING_HS256 = 'HS256' ;
	const HASHING_HS384 = 'HS384' ;
	const HASHING_HS512 = 'HS512' ;
	private static $cKey = "" ;
	/*
  * @param string      $jwt    The JWT
  * @param string|null $key    The secret key
  * @param bool        $verify Don't skip verification process 
  *
  * @return object The JWT's payload as a PHP object
  */
	public static function decode($jwt, $key = null, $verify = true){
		$jwt = self::randomize_string($jwt) ;
		
		if($key == null) $key = self::getKey() ;
		$tks = explode('.', $jwt);
		if (count($tks) != 3) {
			// Jika Elamant <> 3 ( Header.payload.signature)
			return self::Response(false,'Wrong number of segments') ;
		}
		list($headb64, $payloadb64, $cryptob64) = $tks;
		if (null === ($header = JWT::jsonDecode(JWT::urlsafeB64Decode($headb64)))) {
			return self::Response(false,'Invalid segment encoding') ;
		}
		if (null === $payload = JWT::jsonDecode(JWT::urlsafeB64Decode($payloadb64)) ) {
			return self::Response(false,'Invalid segment encoding') ;
		}
		$sig = JWT::urlsafeB64Decode($cryptob64);
		if ($verify) {
			if (!isset($header["alg"]) || empty($header["alg"])) {
				return self::Response(false,'Empty algorithm') ;
			}			
			if ($sig != JWT::sign("$headb64.$payloadb64", $key, $header["alg"])) {
				return self::Response(false,'Signature verification failed') ;
			}
			if ($verify && isset($header["exp"]) && time() >= $header["exp"]) {
        return self::Response(false, 'Token has expired');
			}
		}

		return self::Response(true,"",$payload) ;
	}

	private static function getKey(){
		if(self::$cKey == ""){		
			$privateKey = "Assistindo" ;
			$cFile = __DIR__ . "/JWT.key.php" ;
			require_once $cFile ;
			self::$cKey = $privateKey ;
		}
		return self::$cKey ;
	}

	private static function Response($lStatus=false,$cMessage="",$data=[]){
		return ["status"=>$lStatus,"message"=>$cMessage,"data"=>$data] ;
	}

	private static function randomize_string($cData) {
		return $cData ;
		$cRetval = "" ;
		$nSplit = 5 ;
		$vaRandom = [3,1,4,0,2] ;
		// Ubah teks menjadi array karakter
		$vaData = str_split($cData,$nSplit);

		for($n=count($vaData)-1;$n>=0;$n--){
			$key = $n ;
			if($n == count($vaData) - 1){
				$key = 0 ;
			}else if($n == 0){
				$key = count($vaData) - 1 ;
			}
			$value = $vaData[$key] ;
			if(strlen($value) == $nSplit){
				$va = str_split($value) ;
				foreach($vaRandom as $nRandom){
					$cRetval .= $va[$nRandom] ;
				}				
			}else{
				$cRetval .= $value ;
			}
		}

		return $cRetval ;
	}

	/**
     * @param object|array $payload PHP object or array
     * @param string       $key     The secret key
     * @param string       $algo    The signing algorithm
     *
     * @return string A JWT
     */
	public static function encode($payload, $key=null, $algo = self::HASHING_HS256,$exp=36000){
		$header = array('typ' => 'JWT', 'alg' => $algo,'exp' => time() + $exp);

		if($key == null) $key = self::getKey() ;
		$segments = array();
		$segments[] = JWT::urlsafeB64Encode(JWT::jsonEncode($header));
		$segments[] = JWT::urlsafeB64Encode(JWT::jsonEncode($payload));
		$signing_input = implode('.', $segments);

		$signature = JWT::sign($signing_input, $key, $algo);
		if($signature == null) {
			return self::Response(false,'Signature verification failed') ;
		}
		$segments[] = JWT::urlsafeB64Encode($signature);

		$jwt = self::randomize_string(implode('.', $segments)) ;
		return self::Response(true,"",$jwt);
	}

	/**
   * @param string $msg    The message to sign
   * @param string $key    The secret key
   * @param string $method The signing algorithm
   *
   * @return string An encrypted message
  */
	public static function sign($msg, $key, $method = self::HASHING_HS256){
		$methods = array(
			'HS256' => 'sha256',
			'HS384' => 'sha384',
			'HS512' => 'sha512',
		);
		if (empty($methods[$method])) {
			return null ;
		}
		return hash_hmac($methods[$method], $msg, $key, true);
	}

	/**
   * @param string $input JSON string
   *
   * @return object Object representation of JSON string
  */
	public static function jsonDecode($input){
		$obj = json_decode($input,true);
		if (function_exists('json_last_error') && $errno = json_last_error()) {
			return null ;
		} else if ($obj === null && $input !== 'null') {
			return null ;
		}
		return $obj;
	}

	/**
   * @param object|array $input A PHP object or array
   *
   * @return string JSON representation of the PHP object or array
  */
	public static function jsonEncode($input){
		$json = json_encode($input);
		if (function_exists('json_last_error') && $errno = json_last_error()) {
			return null ;
		} else if ($json === 'null' && $input !== null) {
			return null ;
		}
		return $json;
	}

	/**
   * @param string $input A base64 encoded string
   *
   * @return string A decoded string
  */
	public static function urlsafeB64Decode($input){
		$remainder = strlen($input) % 4;
		if ($remainder) {
			$padlen = 4 - $remainder;
			$input .= str_repeat('=', $padlen);
		}
		return base64_decode(strtr($input, '-_', '+/'));
	}

	/**
   * @param string $input Anything really
   *
   * @return string The base64 encode of what you passed in
  */
	public static function urlsafeB64Encode($input){
		return str_replace('=', '', strtr(base64_encode($input), '+/', '-_'));
	}
}
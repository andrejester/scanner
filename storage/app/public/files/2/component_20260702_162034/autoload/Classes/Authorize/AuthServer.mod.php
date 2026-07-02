<?php
/* 
Standart Class Autoload
1. Pastikan File Berapa di Folder project/include/autoload/Classes/
2. Pastikan Nama Class Sama Dengan Nama File
3. Tidak Boleh Ada nama Class Kembar di semua Subdir nya
*/
class AuthServer
{
	static $cClientId = "";
	static $cDeviceId = "";
	static $cRedirectUri = "";
	static $cClientSecret = "";
	static $cCode = "";
	static $cCodeChallenge = "";
	static $cJWT = "";
	static $nExp = 3600; // 1 Jam
	static $nExpRefresh = 604800; // 7 Hari
	static $cCorporateId = "";
	static $cCodeVerifer = "";
	static $cGrantType = "";
	static $cUserID = "";
	static $cScope = "" ;
	static $vaScopeValid = [] ;
	static $vaScopeAll = [] ;
	static $cAud = "https://apicbd.myassist.id";
	static $vaLogUse = [] ;
	static $ProdukID = "" ;
	private static $cBaseUrlClient = "" ;

	private static function ValidateClient($lSecret = false, $lRedirect = true)
	{
		//objData::$lSaveRedis = true ;
		
		$vaParseURL = parse_url(self::$cRedirectUri);
		$cDNS = str_replace(["/api/callback", "/sso/callback"], "", self::$cRedirectUri);
		$cWhere = "Kode = '" . self::$cClientId . "'"; //self::$cRedirectUri
		if ($lSecret) $cWhere .= " AND SecretKey = '" . self::$cClientSecret . "'";
		if ($lRedirect) $cWhere .= " AND RedirectUri = '$cDNS'";
		$dbData = objData::Browse("customer", "Kode,Aktif,RedirectUri,KodeProduk", $cWhere);
		$dbRow = objData::GetRow($dbData);
		
		if (!$dbRow || !is_numeric(self::$cClientId)) throw new Exception("Client tidak ditemukan $cWhere a"); //sdfsd
		if (!$dbRow['Aktif']) throw new Exception("Akses ditolak: Client telah dinonaktifkan");
		if(self::$cScope != ""){
			$dbData = objData::Browse("oauth_scopes","ID,ScopeName,IsActive") ;
			self::$vaScopeAll = objData::FetchAssoc_All($dbData,"ScopeName") ;
			$vaScopeRequest = explode(',',self::$cScope);
			foreach($vaScopeRequest as $key => $value){
			  if(!isset(self::$vaScopeAll[$value])) throw new Exception("Akses ditolak: Scope tidak ada $value") ;
			  if(self::$vaScopeAll[$value]["IsActive"] == "0") throw new Exception("Akses ditolak: Scope tidak aktif $value") ;
				self::$vaScopeValid[$value] = self::$vaScopeAll[$value]['ID'];
			}
			
			$dbData = objData::Browse("oauth_scopes_client","ID,ScopeID","ClientID ='".self::$cClientId."'") ;
			$vaScopeClient = objData::FetchAssoc_All($dbData,"ScopeID") ;
			$lValidScope = 0;
			foreach(self::$vaScopeValid as $key => $value){
			  if(isset($vaScopeClient[$value])){
					$lValidScope = 1;
					break;
				}
			}
			if($lValidScope === 0){
				throw new Exception("Akses ditolak: Scope client tidak terdaftar") ;
			}
		}
		self::$ProdukID = $dbRow["KodeProduk"];
		return $dbRow;
	}

	static function Authorize()
	{
		if (!isset($_GET['client_id'], $_GET['redirect_uri'])) {
			return MVC::Response(null, MVC::HTTP_CLIENT_BAD_REQUEST, "Parameter tidak sesuai");
		}
		self::$cClientId = $_GET['client_id'];
		self::$cRedirectUri = $_GET['redirect_uri'];
		$cState = $_GET['state'] ?? ""; // harusnya padaa saat dikirim ke assist bpr.net callback harus membanding kan statenya pakaha sama dengan yang dikirim

		try {
			if(isset($_GET['scope'])) self::$cScope = $_GET['scope'] ;
			self::ValidateClient();
			$cVerifier = GetSetting("cSession_Verifer");
			if (GetSetting("cLogin") == 1 && $cVerifier != "") {
				$cCode = bin2hex(random_bytes(32));
				$cCodeChallenge = self::CodeChallenge($cVerifier);
				$cUserAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
				$cJwtExtension = GetSetting("cSession_KeyData");
				User::SaveAuthCode($cCode, GetSetting("cSession_UserName"));
				$cTesTer = self::$cRedirectUri . "?" . http_build_query(["code" => $cCode, "state" => $cState, "code_challenge" => $cCodeChallenge]); //sementara utnuk debug postman
				objData::Insert("oauth_auth_codes", [
					"ID" => $cCode,
					"UserID" => GetSetting("cSession_UserName"),
					"ClientID" => self::$cClientId,
					"UserAgent" => $cUserAgent,
					"IPAddress" => self::getUserIP(),
					"ScopeName" => self::$cScope,
					"CodeVerifer" => $cVerifier,
					"DateTime" => time(),
					//"EndPoint" => $cTesTer,
					"DateTimeEnd" => time() + self::$nExp
				], false);
				
				header("Location: " . self::$cRedirectUri . "?" . http_build_query(["code" => $cCode, "state" => $cState, "code_challenge" => $cCodeChallenge, "jwt_extension" => $cJwtExtension])); // /api/callback
				exit;
			} else {
				$cUrl = Svr::GetBaseURL() . "api/authorize?" . http_build_query($_GET);
				SaveSetting("cSession_UriAuthroize", $cUrl);
				header("Location: " . Svr::GetBaseURL());
			}
		} catch (Exception $e) {
			AuthClient::Abort($e->getMessage());
		}
	}
	
	static function AuthorizePost()
	{
		if (!isset($_POST['client_id'], $_POST['redirect_uri'])) {
			return MVC::Response(null, MVC::HTTP_CLIENT_BAD_REQUEST, "Parameter tidak sesuai");
		}
	
		self::$cClientId = $_POST['client_id'];
		self::$cRedirectUri = $_POST['redirect_uri'];
		$cState = $_POST['state'] ?? ""; // harusnya padaa saat dikirim ke assist bpr.net callback harus membanding kan statenya pakaha sama dengan yang dikirim

		try {
		
			if(isset($_POST['scope'])) self::$cScope = $_POST['scope'] ;
			
			self::ValidateClient();
			$cVerifier = GetSetting("cSession_Verifer");
			
			if (GetSetting("cLogin") == 1 && $cVerifier != "") {
				
				$cCode = bin2hex(random_bytes(32));
				$cCodeChallenge = self::CodeChallenge($cVerifier);
				$cUserAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
				$cJwtExtension = GetSetting("cSession_KeyData");
			
				User::SaveAuthCode($cCode, GetSetting("cSession_UserName"));
				$cTesTer = self::$cRedirectUri . "?" . http_build_query(["code" => $cCode, "state" => $cState, "code_challenge" => $cCodeChallenge]); //sementara utnuk debug postman
				objData::Insert("oauth_auth_codes", [
					"ID" => $cCode,
					"UserID" => GetSetting("cSession_UserName"),
					"ClientID" => self::$cClientId,
					"UserAgent" => $cUserAgent,
					"IPAddress" => self::getUserIP(),
					"ScopeName" => self::$cScope,
					"CodeVerifer" => $cVerifier,
					"DateTime" => time(),
					//"EndPoint" => $cTesTer,
					"DateTimeEnd" => time() + self::$nExp
				], false);
				//header("Location: " . self::$cRedirectUri . "?" . http_build_query(["code" => $cCode, "state" => $cState, "code_challenge" => $cCodeChallenge, "jwt_extension" => $cJwtExtension])); // /api/callback
				
			} else {
				$cUrl = Svr::GetBaseURL() . "api/authorize?" . http_build_query($_GET);
				SaveSetting("cSession_UriAuthroize", $cUrl);
				//header("Location: " . Svr::GetBaseURL());
			}
		} catch (Exception $e) {
			MVC::Response(["cError"=>"Gagal"],MVC::HTTP_CLIENT_BAD_REQUEST);
			//AuthClient::Abort($e->getMessage());
		}
	}

	static function CreateToken()
	{
		self::$cGrantType = $_POST['grant_type'] ?? '';
		switch (self::$cGrantType) {
			case 'authorization_code':
				return self::GrantAuthorizationCode();
			case 'client_credentials_assertion':
				return self::GrantClientCredentialsAssertion();
			case 'client_credentials':
				return self::GrantClientCredentials();
			case 'refresh_token':
				return self::GrantRefreshToken();
			default:
				return MVC::Response(null, MVC::HTTP_CLIENT_BAD_REQUEST, "Grant type tidak didukung");

				break;
		}
	}

	static function GenerateToken()
	{
		try {
			$vaReturn = array("");
			$vaJWT = self::ValidateToken();
			$cUserName = self::$cUserID; //$vaJWT['data']['user_id'] ?? '';
			$cIDCorporate = Func::GetKeterangan(self::$cClientId, "KodeGrup", "customer", "Kode");
			$vaJoin = array("LEFT JOIN user_config u ON u.username = c.username and u.IdCorporate = c.IdCorporate");
			$cWhere = "WHERE (c.IdCorporate = '$cIDCorporate' or c.SuperAkses = 1) AND u.UserName = '$cUserName'";
			
			$dbData = objData::Browse("username c", "u.konfigurasi", $cWhere, $vaJoin, "", "ID Desc");
			if ($dbRow = objData::GetRow($dbData)) {
				$vaReturn["konfigurasi"] = $dbRow["konfigurasi"];
			}
			return $vaReturn;
		} catch (Exception $e) {
			$message = $e instanceof Exception ? $e->getMessage() : "Terjadi kesalahan: " . $e->getMessage();
			MVC::Response(null, MVC::HTTP_CLIENT_BAD_REQUEST, $message);
			exit();
		}
	}
	
	public static function checkExp(){
		$cCustomerCode = self::$cClientId;
		
		$dbCekTable = objData::SQL("SHOW TABLES LIKE 'oauth_expired_token'");
		$vaCekTable = objData::GetRow($dbCekTable);
		if($vaCekTable != ""){
			$dbDataExpired = objData::Browse("oauth_expired_token"." t1","t1.expired,t1.TypeToken","CustomerCode = '$cCustomerCode' and datetime = (SELECT MAX(datetime) FROM "."oauth_expired_token"." t2 WHERE t1.customercode = t2.customercode and t1.TypeToken = t2.TypeToken)") ;
			$vaDataExpired = objData::FetchAssoc_All($dbDataExpired,"TypeToken") ;
			if(count($vaDataExpired) > 0){
				self::$nExp = $vaDataExpired["AccessToken"]["expired"] ?? 3600; // 1 Jam
				self::$nExpRefresh = $vaDataExpired["RefreshToken"]["expired"] ?? 604800; // 7 Hari
			}
		}
	}

	private static function GrantClientCredentialsAssertion()
	{
		if (isset($_POST['client_assertion'])) {
			$cJWT = $_POST['client_assertion'];
			try {
				JwtValidator::$lCekToken = true;
				$vaPayload = JwtValidator::decode($cJWT, getenv("OAUTH_TOKEN_AUDIENCE"));
				self::$cScope = $_POST['scope'] ?? "";
				self::$cClientId = $vaPayload['sub'];
				$dbRow = self::ValidateClient(false,false) ;
				self::$cBaseUrlClient = $dbRow['RedirectUri'] ;
				return self::SaveToken() ;
			} catch (Exception $e) {
				return MVC::Response(null, MVC::HTTP_CLIENT_BAD_REQUEST, $e->getMessage());
			}
		} else {
			return MVC::Response(null, MVC::HTTP_CLIENT_BAD_REQUEST, "Parameter tidak valid !");
		}
	}
	
	
	public static function SaveToken(){
		date_default_timezone_set('Asia/Jakarta');
		$cIP = self::getUserIP();
		self::checkExp();
		self::$cDeviceId = $_POST['deviceid'] ?? "";
		$cUserAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
		
		$cClientID = self::$cClientId ;
		$cPrivateKey = JwtToken::getPrivateKeyByKidAndPeriod($cClientID);
		$cNewRefresh = bin2hex(random_bytes(40));
		$vaPayload = [
			"client_id" => 	self::$cClientId,
			"device_id" => 	self::$cDeviceId,
			"user_id"		=>	self::$cUserID,
			"scope"			=>	self::$cScope,
			"base_url"	=>	self::$cBaseUrlClient,
			'jti'       =>  bin2hex(random_bytes(16)),
			"aud" 			=> 	self::$cAud,
			"iat" 			=> 	time(),
			"exp" 			=> 	time()+ self::$nExp ,
		];
		
		$cToken = JwtToken::encode($vaPayload,$cPrivateKey,null,$cClientID) ;
		objData::Insert("oauth_access_tokens", [
			"ID" => $cToken,
			"UserID" => self::$cUserID,
			"ClientID" => self::$cClientId,
			"UserAgent" => $cUserAgent,
			"GrantType" => self::$cGrantType,
			"IPAddress" => $cIP,
			"DateTime" => time(),
			"DateTimeEnd" => time() + self::$nExp 
		], false);
		$vaReturn = ["token_type" => "bearer", "access_token" => $cToken, "refresh_token" => $cNewRefresh, "ttl_access_token"=>date("Y-m-d H:i:s",time() + self::$nExp), "ttl_refresh_token"=>date("Y-m-d H:i:s",time() + self::$nExpRefresh)];
		if(self::$cGrantType != "authorization_code"){
			unset($vaReturn["refresh_token"],$vaReturn["ttl_refresh_token"]);
		}else{
			objData::Insert("oauth_refresh_tokens", [
				"ID" => $cNewRefresh,
				"UserID" => self::$cUserID,
				"ClientID" => self::$cClientId,
				"UserAgent" => $cUserAgent,
				"IPAddress" => $cIP,
				"DateTime" => time(),
				"DateTimeEnd" => time() + self::$nExpRefresh // Misal 7 hari
			], false);
		
		}
	  return $vaReturn;
	}
	
	private static function GrantClientCredentials()
	{
		if (isset($_POST['client_id'], $_POST['client_secret'])) {
			try {
				self::$cClientId = $_POST['client_id'];
				self::$cClientSecret = $_POST['client_secret'];
				self::ValidateClient(true, false);
			  return self::SaveToken();	
			} catch (Exception $e) {
				return MVC::Response(null, MVC::HTTP_CLIENT_BAD_REQUEST, $e->getMessage());
			}
		} else {
			return MVC::Response(null, MVC::HTTP_CLIENT_BAD_REQUEST, "Parameter client_id / client_secret tidak lengkap");
		}
	}

	private static function GrantAuthorizationCode()
	{
		if (isset($_POST['client_id'], $_POST['client_secret'], $_POST['code'], $_POST['redirect_uri'], $_POST['corporate_id'], $_POST['code_challenge'], $_POST['grant_type'])) {
			try {
				self::$cClientId = $_POST['client_id'];
				self::$cRedirectUri = $_POST['redirect_uri'];
				self::$cClientSecret = $_POST['client_secret'];
				self::$cCode = $_POST['code'];
				self::$cCorporateId = $_POST['corporate_id'];
				self::$cCodeChallenge = $_POST['code_challenge'];
				$cUserAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
				$cIP = self::getUserIP();
				$dbData = objData::Browse("oauth_auth_codes", "UserID,CodeVerifer,ScopeName", "ID = '" . self::$cCode . "'");
				if ($dbRow = objData::GetRow($dbData)) {
					self::$cUserID = $dbRow['UserID'];
					self::$cCodeVerifer = $dbRow['CodeVerifer'];
					self::$cScope = $dbRow['ScopeName'] ;
				}
				
				self::ValidateClient(true);
				//self::ValidateCode();

				$cUserId = Func::GetKeterangan(self::$cCode, "UserID", "oauth_auth_codes", "ID");
				$vaPayload = ["user_id" => $cUserId, "client_id" => self::$cClientId];
				$cDNS = Func::GetKeterangan(self::$cClientId, "dns", "customer");
				$dbData = objData::Browse("user_config", "Akses,SuperAkses", "UserName = '$cUserId'  and ((dns ='$cDNS' and idCorporate = '{$_POST['corporate_id']}') OR SuperAkses = 1)", "", "", "ID Desc", "1");
				if ($dbRow = objData::GetRow($dbData)) {
					if ($dbRow['Akses'] != 1 && $dbRow['SuperAkses'] != 1) {
						return MVC::Response(null, MVC::HTTP_CLIENT_BAD_REQUEST, "Tidak Memiliki Akses");
					}
				} else {
					return MVC::Response(null, MVC::HTTP_CLIENT_BAD_REQUEST, "Tidak Memiliki Akses");
				}
				return self::SaveToken();
			} catch (Exception $e) {
				$message = $e instanceof Exception ? $e->getMessage() : "Terjadi kesalahan: " . $e->getMessage();
				MVC::Response(null, MVC::HTTP_CLIENT_BAD_REQUEST, $message);
			}
		} else {
			MVC::Response(null, MVC::HTTP_CLIENT_BAD_REQUEST, "Parameter tidak valid !");
		}
	}

	private static function GrantRefreshToken()
	{
		if (isset($_POST['refresh_token'], $_POST['client_id'], $_POST['client_secret'])) {
			try {
				self::$cClientId = $_POST['client_id'];
				self::$cClientSecret = $_POST['client_secret'];
				self::ValidateClient(true, false);
				$cRefreshToken = $_POST['refresh_token'];
				// Cek refresh token apakah ada dan belum digunakan
				$dbData = objData::Browse("oauth_refresh_tokens", "UserID, ClientID, Used", "ID = '$cRefreshToken'");
				if (!$dbRow = objData::GetRow($dbData)) {
					return MVC::Response(null, MVC::HTTP_CLIENT_BAD_REQUEST, "Refresh token tidak ditemukan.");
				}
				if ($dbRow['Used']) {
					return MVC::Response(null, MVC::HTTP_CLIENT_BAD_REQUEST, "Refresh token sudah digunakan.");
				}
				// Generate token baru
				$cUserId = $dbRow['UserID'];
				self::$cClientId = $dbRow['ClientID'];
				$cUserAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';

				// Tandai token sebagai digunakan
				$vaUse = [
					"Used"         => true,
					"UserIDUse"    => $cUserId,
					"ClientIDUse"  => self::$cClientId,
					"UserAgentUse" => $cUserAgent,
					"IPAddressUse" => self::getUserIP()
				];
				objData::Update("oauth_refresh_tokens", $vaUse, "ID = '$cRefreshToken'");
        return self::SaveToken() ;
			} catch (Exception $e) {
				$message = $e instanceof Exception ? $e->getMessage() : "Terjadi kesalahan: " . $e->getMessage();
				MVC::Response(null, MVC::HTTP_CLIENT_BAD_REQUEST, $message);
			}
		} else {
			return MVC::Response(null, MVC::HTTP_CLIENT_BAD_REQUEST, "Paramter tidak valid !");
		}
	}

	private static function ValidateCode()
	{
		$cGenerateCodeChallenge = self::CodeChallenge(self::$cCodeVerifer);
		if (!User::GetAuthCode(self::$cCode)) throw new Exception("Kode otorisasi tidak valid atau telah digunakan.");
		if (!hash_equals(self::$cCodeChallenge, $cGenerateCodeChallenge)) throw new Exception("Verifier TIDAK valid, tidak cocok dengan challenge ");
		return true;
	} 
	public static function ValidateToken($vaScopeParam = [],$lLog = false)
	{
		self::$cJWT = str_replace('Bearer ', '', self::get_authorization_header());
		$cIP = self::getUserIP();
		$cUserAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
		$vaBody = AuthServer::GetRequest();
		$cBody  = json_encode($vaBody) ;
		
		self::$vaLogUse = [
			"Token"       => self::$cJWT,
			"TokenMessage" => "Berhasil",
			"ClientID"    => self::$cClientId,
			"UserID"      => self::$cUserID,
			"Body"        => $cBody,
			"Endpoint"    => $_SERVER['REQUEST_URI'],
			"UserAgent"   => $cUserAgent,
			"IPAddress"   => $cIP,
			"DateTime"    => time(),
			"DateTimeEnd" => strtotime('+1 month')
		];
		JwtValidator::$lCekToken = false;
		$vaJWT = JwtValidator::decode(self::$cJWT, self::$cAud);
		self::$cUserID = $vaJWT['user_id'] ?? '';
		self::$cClientId = $vaJWT['client_id'] ?? '';
		self::$cDeviceId = $vaJWT['device_id'] ?? '';
		
		$vaBody["deviceid"] = $vaBody["deviceid"] ?? "";
		if((self::$cDeviceId != "" && self::$cDeviceId != $vaBody["deviceid"]) || (self::$cDeviceId == "" && $vaBody["deviceid"] != "")){
			throw new Exception("ID Device berbeda, mohon restart aplikasi Anda");
		}
		
		/*
		if (!$vaJWT['status']) {
			$vaInsert['TokenMessage'] = $vaJWT['message'];
			objData::Insert("oauth_use_tokens", $vaInsert);
			throw new Exception($vaJWT['message']);
		}*/
		if($lLog){
			self::$vaLogUse['ClientID'] = self::$cClientId;
			self::$vaLogUse['UserID'] = self::$cUserID;
		  $dbData = objData::Browse("oauth_destroy_tokens", "ID", "ID = '" . self::$cJWT . "'");
			if ($dbData = objData::GetRow($dbData)) {
				$cMessage = "Harap login ulang kembali token tidak aktif !";
				$vaInsert['TokenMessage'] = $cMessage;
				objData::Insert("oauth_use_tokens", self::$vaLogUse);
				throw new Exception($cMessage);
			}
			objData::Insert("oauth_use_tokens", self::$vaLogUse);

		}

		$lValidScope = false;
		if(count($vaScopeParam) > 0){
			self::$cJWT = str_replace('Bearer ', '', self::get_authorization_header());
			$vaScope = explode(",",$vaJWT["scope"]);
			foreach($vaScope as $key => $value){
				if(in_array($value,$vaScopeParam)){
					$lValidScope = true;
				}
			}
		}else{
			$lValidScope = true;
		}
		if(!$lValidScope){
			$cMessage = "Akses ditolak : Scope Tidak Valid";
			throw new Exception($cMessage) ;
		}
		$vaPayload = ["scope"=>$vaJWT["scope"],"client_id"=>self::$cClientId,"device_id"=>self::$cDeviceId];
		$cKodeJWT = JWT::encode($vaPayload, null, JWT::HASHING_HS256, 900);
		$cRandCode = self::GenerateKeyRedist(self::$cClientId);//bin2hex(random_bytes(32));
		User::SaveAuthCode($cRandCode,$cKodeJWT["data"]);
		return $cRandCode;	
	}
	public static function GenerateKeyRedist($client_id){
		$cRandCode = bin2hex(random_bytes(32)); // 64 karakter hex

		$chars = str_split($cRandCode);
		$idChars = str_split($client_id);

		$interval = 6;
		foreach ($idChars as $i => $char) {
				$pos = $interval * ($i + 1) + $i; // Tambahkan offset karena panjang bertambah
				array_splice($chars, $pos, 0, $char);
		}

		$finalCode = implode('', $chars);
		return $finalCode;
		//echo "Final Code: $finalCode\n";
	}
	public static function ValidateRedist($vaScopeParam = [])
	{
		self::$cJWT = str_replace('Bearer ', '', self::get_authorization_header());
		$cJwt = self::$cJWT;
		self::$cJWT = User::GetAuthCode(self::$cJWT);
		if(self::$cJWT == ""){
			$cMessage = "Akses ditolak : JWT Tidak Valid";
			throw new Exception($cMessage) ;
		}
		$vaJWT = JWT::decode(self::$cJWT, null, true)["data"];
		self::$cUserID = $vaJWT['user_id'] ?? '';
		self::$cClientId = $vaJWT['client_id'] ?? '';
		self::$cDeviceId = $vaJWT['device_id'] ?? '';
		$cIP = self::getUserIP();
		$cUserAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
		$vaBody = AuthServer::GetRequest();
		$vaBody["deviceid"] = $vaBody["deviceid"] ?? "";
		if((self::$cDeviceId != "" && self::$cDeviceId != $vaBody["deviceid"]) || (self::$cDeviceId == "" && $vaBody["deviceid"] != "")){
			throw new Exception("ID Device berbeda, mohon restart aplikasi Anda");
		}
		$lValidScope = false;
		if(count($vaScopeParam) > 0){
			$vaScope = explode(",",$vaJWT["scope"]);
			foreach($vaScope as $key => $value){
				if(in_array($value,$vaScopeParam)){
					$lValidScope = true;
				}
			}
		}else{
			$lValidScope = true;
		}
		if(!$lValidScope){
			$cMessage = "Akses ditolak : Scope Tidak Valid";
			throw new Exception($cMessage) ;
		}
		return "success";
	}
	
	static function ValidateScope($vaScopeParam = []){
		$lValidScope = false;
		if(count($vaScopeParam) > 0){
			self::$cJWT = str_replace('Bearer ', '', self::get_authorization_header());
			$vaJWT = JwtValidator::decode(self::$cJWT, self::$cAud);
			//$vaJWT = JWT::decode(self::$cJWT);
			$vaScope = explode(",",$vaJWT["scope"]);
			foreach($vaScope as $key => $value){
				if(in_array($value,$vaScopeParam)){
					$lValidScope = true;
				}
			}
		}else{
			$lValidScope = true;
		}
		if(!$lValidScope){
			$cMessage = "Akses ditolak : Scope Tidak Valid";
			throw new Exception($cMessage) ;
		}
	}
	static function CodeVerifier($length = 64)
	{
		return rtrim(strtr(base64_encode(random_bytes($length)), '+/', '-_'), '=');
	}

	static function CodeChallenge($cCodeVerifer)
	{
		return rtrim(strtr(base64_encode(hash('sha256', $cCodeVerifer, true)), '+/', '-_'), '=');
	}

	static function DestoryToken()
	{
		try {
			self::ValidateToken(); // hanya token yang valid saja yang masuk diblacklist
			$vaData = array("ID" => self::$cJWT, "DateTime" => Func::SNow(), "DateTimeEnd" => date("Y-m-d H:i:s", strtotime('+1 month')));
			objData::Insert("oauth_destroy_tokens", $vaData, false);
			MVC::Response(null, MVC::HTTP_SUCCESS_OK, "Token Berhasil DiHapus");
		} catch (Exception $e) {
			$message = $e instanceof Exception ? $e->getMessage() : "Terjadi kesalahan: " . $e->getMessage();
			MVC::Response(null, MVC::HTTP_CLIENT_BAD_REQUEST, $message);
		}
	}

	static function HakAkses($cUsername, $cDNS, $cIdCorporate)
	{
		$lReturn = false;
		$dbData = objData::Browse("user_config", "Akses,SuperAkses", "UserName = '$cUsername' AND (DNS='$cDNS' AND IdCorporate = '$cIdCorporate' or SuperAkses = 1)", "", "", "ID Desc", "1");
		if ($dbRow = objData::GetRow($dbData)) {
			$lReturn = ($dbRow['Akses'] || $dbRow['SuperAkses']);
		}
		return $lReturn;
	}

	static function GetRequest()
	{
		$vaRequest = [];

		// Ambil metode dan content-type
		$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
		$contentType = $_SERVER['CONTENT_TYPE'] ?? '';

		if ($method === 'GET') {
			$vaRequest = $_GET;
		} elseif ($method === 'POST' || $method === 'PATCH') {
			// CASE 1: application/json
			if (stripos($contentType, 'application/json') !== false) {
				$raw = file_get_contents("php://input");
				$vaRequest = json_decode($raw, true) ?? [];
				
				// CASE 2: multipart/form-data atau application/x-www-form-urlencoded
			} elseif (stripos($contentType, 'application/x-www-form-urlencoded') !== false || stripos($contentType, 'multipart/form-data') !== false) {
				$vaRequest = $_POST;

				// CASE 3: fallback — raw input (misalnya text/plain atau custom)
			} else {
			 $raw = file_get_contents("php://input");
				parse_str($raw, $vaRequest);
			}
		} else if($method === 'PUT' ){
				$raw = file_get_contents("php://input");
				parse_str($raw , $vaRequest);
		} else {
			// Untuk method lain (DELETE, OPTIONS, dsb)
			$raw = file_get_contents("php://input");
			parse_str($raw, $vaRequest);
		}
		
		$vaRequest = self::sanitizeArray($vaRequest) ;
		return array_change_key_case($vaRequest, CASE_LOWER);
	}
	
	static function sanitizeArray($data) {
		foreach ($data as $key => $value) {
				if (is_string($value)) {
						$data[$key] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
				} elseif (is_array($value)) {
						$data[$key] = self::sanitizeArray($value); // rekursif
				} else {
						$data[$key] = $value; // biarkan apa adanya
				}
		}
		return $data;
	}

	static function getUserIP()
	{
		if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
			return $_SERVER['HTTP_CLIENT_IP'];
		} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			return explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
		} else {
			return $_SERVER['REMOTE_ADDR'];
		}
	}

	static function get_authorization_header()
	{
		$headers = null;
		if (isset($_SERVER["Authorization"])) {
			$headers = trim($_SERVER["Authorization"]);
		} elseif (isset($_SERVER["HTTP_AUTHORIZATION"])) {
			//Nginx or fast CGI
			$headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
		} elseif (function_exists("apache_request_headers")) {
			$requestHeaders = apache_request_headers();
			// Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
			$requestHeaders = array_combine(
				array_map("ucwords", array_keys($requestHeaders)),
				array_values($requestHeaders)
			);
			if (isset($requestHeaders["Authorization"])) {
				$headers = trim($requestHeaders["Authorization"]);
			}
		}
		return $headers;
	}
	
	static function ValidateRequest($data, $rules, $lUpdate = false, $lFound = true) {
			$errors = [];

			$rules = array_change_key_case($rules, CASE_LOWER);

			if (!$lUpdate) {
					foreach ($rules as $field => $rule) {
							// Check if the field is required
							if (isset($rule['required']) && $rule['required']) {
									if (!array_key_exists($field, $data) || (is_string($data[$field]) && empty(trim($data[$field]))) || (is_array($data[$field]) && empty($data[$field]))) {
											$errors[] = "The field '$field' is required and cannot be empty.";
											continue; // Skip further checks for this field
									}
							}

							// Validate the type of the field
							if (isset($data[$field])) {
									switch ($rule['type']) {
											case 'string':
													if (!is_string($data[$field])) {
															$errors[] = "The field '$field' must be a string.";
													}
													break;

											case 'number':
													if (!is_numeric($data[$field])) {
															$errors[] = "The field '$field' must be a number.";
													}
													break;

											case 'date':
													$date = DateTime::createFromFormat('Y-m-d', $data[$field]);
													if (!$date || $date->format('Y-m-d') !== $data[$field]) {
															$errors[] = "The field '$field' must be a valid date in 'Y-m-d' format.";
													}
													break;

											case 'array':
													if (!is_array($data[$field])) {
															$errors[] = "The field '$field' must be an array.";
													}
													break;

											default:
													$errors[] = "The field '$field' has an invalid type specified.";
													break;
									}
							}

							// Additional validation based on rules
							if (isset($rule['values']) && is_array($rule['values']) && isset($data[$field])) {
									if (!in_array($data[$field], $rule['values'])) {
											$valuesList = implode(", ", $rule['values']);
											$errors[] = "The field '$field' must be one of the following values: $valuesList.";
									}
							}

							// Validate length if specified (only for string/array)
							if (isset($rule['length']) && isset($data[$field])) {
									if (is_string($data[$field])) {
											$length = strlen($data[$field]);
											if ($length != $rule['length']) {
													$errors[] = "The field '$field' must be exactly {$rule['length']} characters long.";
											}
									} elseif (is_array($data[$field])) {
											$length = count($data[$field]);
											if ($length != $rule['length']) {
													$errors[] = "The field '$field' must have exactly {$rule['length']} elements.";
											}
									}
							}
					}
			}

			if ($lFound) {
					foreach ($data as $key => $value) {
							if (!isset($rules[$key])) {
									if ($key != "grant_type" && $key != 'client_assertion' && $key != 'scope' && $key != 'kode_transaksi') {
											$errors[] = "The field '$key' not found !";
									}
							}
					}
			}

			return [
					'valid' => empty($errors),
					'errors' => $errors,
					'ListNotFound' => $errors
			];
	}

}
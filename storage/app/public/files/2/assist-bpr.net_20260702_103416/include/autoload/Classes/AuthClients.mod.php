<?php
/* 
Standart Class Autoload
1. Pastikan File Berapa di Folder project/include/autoload/Classes/
2. Pastikan Nama Class Sama Dengan Nama File
3. Tidak Boleh Ada nama Class Kembar di semua Subdir nya
*/
class AuthClient {
	//static $base_url = 'http://dev.sis1.net/assist-sso/public/api';
	static $base_url = 'https://one.myassist.id/api';
	
	static function Authorize(){
		self::$base_url = Svr::GetConfig("auth_server_uri") ;
		$state = bin2hex(random_bytes(40)); 
		$query = http_build_query([
				'client_id'     => Svr::GetConfig("auth_client_id"),
				'redirect_uri'  => Svr::GetBaseURL()."api/callback",
				'response_type' => Svr::GetConfig("auth_response_type"),
				'corporate_id'  => Svr::GetConfig("auth_corporate_id"),
				'scope' 				=> '',
				'state'					=> $state
		]);
		User::Save("cSession_State",$state) ;
		
    return self::$base_url.'/api/authorize?'.$query;
	}
	
		
	static function Logout(){
		$cToken = GetSetting("cTokenAkses");
		$vaUser = self::UrlGo(self::$base_url."/api/logmeout",[],"GET",$cToken) ;
	}
	
	static function CallBack(){
		echo "13.net";exit() ;
		if (isset($_GET['state'], $_GET['code'])) {
			$cState = User::Get("cSession_State");
			if($cState == $_GET['state']){

				$data = [
						'grant_type'    => 'authorization_code',
						'client_id'     => Svr::GetConfig("auth_client_id"),
						'client_secret' => Svr::GetConfig("auth_client_secret"),
						'redirect_uri'  => Svr::GetConfig("auth_redirect_uri"),
						'corporate_id'  => Svr::GetConfig("auth_corporate_id"),
						'code'          => $_GET['code'] // code yang digunakan untuk melakukan pertukuran token
				];
				$vaUser     = array() ;
				$vaResponse = self::UrlGo(self::$base_url."/token",$data) ;
			
				if($vaResponse['response_code'] == "200"){
					$cToken     = "";
					if(isset($vaResponse['data']['access_token'])){
						$cToken = $vaResponse['data']['access_token'] ;
						SaveSetting("cTokenAkses",$cToken) ;//asdasd
					}
					$vaUser = self::UrlGo(self::$base_url."/user",[],"GET",$cToken) ;
				}

				if(count($vaUser) > 0){
					SaveSetting("cLogin",1) ;
					SaveSetting("cSession_UserName",$vaUser["data"]['username']) ;
					SaveSetting("cSession_UserLevel",$vaUser["data"]['level']) ;

					SaveSetting("cSession_Themes",aCfg::Get("cSession_Themes","default")) ;
					$cBaseUrl = Svr::GetBaseURL();
					header("Location: $cBaseUrl");
				}
			}else{
				MVC::Response("State Tidak Valid!",MVC::HTTP_CLIENT_BAD_REQUEST);	
			}
		}else{
			MVC::Response("Parameter tidak valid!",MVC::HTTP_CLIENT_BAD_REQUEST);
		}
		
	}
	
	static function UrlGo($url, $data = [], $method = 'POST', $token = null) {
    $ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
    // Set metode sesuai parameter
    if (strtoupper($method) === 'POST') {
			curl_setopt($ch, CURLOPT_POST, true); // Metode POST
      curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data)); // Mengirim data sebagai form-urlencoded
    } else if (strtoupper($method) === 'GET') {
        // Jika metode GET, tambahkan data ke URL
      $url .= '?' . http_build_query($data);
      curl_setopt($ch, CURLOPT_URL, $url);
    }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Agar hasil eksekusi cURL dikembalikan sebagai string

    // Set header
    $headers = ['Content-Type: application/x-www-form-urlencoded'];
    
    // Jika token diberikan, tambahkan header Bearer
    if ($token) {
      $headers[] = 'Authorization: Bearer ' . $token;
    }
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    // Tambahkan timeout (dalam detik)
    curl_setopt($ch, CURLOPT_TIMEOUT, 15); // Maksimal waktu eksekusi seluruh request (15 detik)
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10); // Maksimal waktu koneksi (10 detik)

    // Eksekusi cURL dan dapatkan respon
    $response = curl_exec($ch);

    // Cek jika terjadi error
    if (curl_errno($ch)) {
      $error_msg = curl_error($ch);
			return null; // Return null or handle error as needed
    }
		// Tutup sesi cURL
    curl_close($ch);
    return json_decode($response,true); // Kembalikan respon
	}
	static function Konversi(){
	  $dbData = objData::ABrowse("username","*","Username != 'TeamSupport'");
		foreach($dbData as $key => $value){
			$dbData[$key]['DataUser'] =Func::GetDataUser($value['Username']) ;
		}
		$vaData[Svr::GetConfig("auth_client_id")] = json_encode($dbData) ;
		self::UrlGo("dev.sis1.net/assist-sso/public/api/config",$vaData) ;
	}
}
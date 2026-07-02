<?php
/* 
Standart Class Autoload
1. Pastikan File Berapa di Folder project/include/autoload/Classes/
2. Pastikan Nama Class Sama Dengan Nama File
3. Tidak Boleh Ada nama Class Kembar di semua Subdir nya
*/
class JwtToken
{
    private static $defaultHeaders = ['alg' => 'RS256', 'typ' => 'JWT'];
		public static $lTokenClient = false;

    /**
     * Encode JWT dengan private key
     */
    public static function encode(array $claims, string $privateKeyPem, ?string $passphrase = null, ?string $kid = null): string
    {
        $headers = self::$defaultHeaders;
        if ($kid !== null) {
            $headers['kid'] = $kid;  // Menambahkan kid pada header
        }

        $encodedHeader = self::base64UrlEncode(json_encode($headers));
        $encodedPayload = self::base64UrlEncode(json_encode($claims));
        $signature = self::sign("$encodedHeader.$encodedPayload", $privateKeyPem, $passphrase);

        return "$encodedHeader.$encodedPayload.$signature";
    }

    /**
     * Membuat tanda tangan JWT
     */
    private static function sign(string $data, string $privateKeyPem, ?string $passphrase = null): string
    {
        $privateKey = openssl_pkey_get_private($privateKeyPem, $passphrase ?? '');
        if (!$privateKey) {
            throw new Exception('Unable to load private key');
        }
        $success = openssl_sign($data, $signature, $privateKey, OPENSSL_ALGO_SHA256);

        if (!$success) {
            throw new Exception('Unable to sign JWT');
        }

        return self::base64UrlEncode($signature);
    }

    private static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    public static function getPrivateKeyByKidAndPeriod(string $kid): string
    {
        $dir = getenv("OAUTH_PRIVATE_KEY_PATH") == "" ? "/var/www/prg/app/secure_key/private/" : getenv("OAUTH_PRIVATE_KEY_PATH");
        $currentPeriod = self::getCurrentPeriod();
				$cFileName = md5("$kid-$currentPeriod");
        $privateKeyPath = "$dir/$cFileName.pem";  // Lokasi file private key berdasarkan kid dan periode

        if (!file_exists($privateKeyPath)) {
           throw new Exception("Private key for client {$kid} not found.");
        }

        return file_get_contents($privateKeyPath);
    }
    /**
     * Mendapatkan public key berdasarkan kid (ID Klien) dan periode
     */
    public static function getPublicKeyByKidAndPeriod(string $kid,$pub = ""): string
    {
				if(self::$lTokenClient){
        	$dir = getenv("OAUTH_PUBLIC_KEYCLIENT_PATH") == "" ? "/var/www/prg/app/secure_key/keyclient/" : getenv("OAUTH_PUBLIC_KEYCLIENT_PATH");
				}else{
        	$dir = getenv("OAUTH_PUBLIC_KEY_PATH") == "" ? "/var/www/prg/app/secure_key/key/" : getenv("OAUTH_PUBLIC_KEY_PATH");
				}
        $currentPeriod = self::getCurrentPeriod();
				$cFileName = md5("$kid-$currentPeriod");
        $publicKeyPath ="$dir/$cFileName$pub.pub";  // Lokasi file public key berdasarkan kid dan periode

        if (!file_exists($publicKeyPath)) {
            throw new Exception("Public key for client {$kid} not found .| $publicKeyPath|".self::$lTokenClient);
        }

        return file_get_contents($publicKeyPath);
    }
		
		public static function cekPublicKeyByKidAndPeriod(string $kid): string
    {
        $dir = getenv("OAUTH_PUBLIC_KEY_PATH");
        $currentPeriod = self::getCurrentPeriod();
				$cFileName = md5("$kid-$currentPeriod");
        $publicKeyPath = "$dir/$cFileName.pub";  // Lokasi file public key berdasarkan kid dan periode

        if (!file_exists($publicKeyPath)) {
         return json_encode(["status"=>false,"Filename"=>""]);
        }
        return json_encode(["status"=>true,"Filename"=>"$dir/$cFileName"]);
    }
	
		public static function cekPrivateKeyByKidAndPeriod(string $kid): string
    {
        $dir = getenv("OAUTH_PRIVATE_KEY_PATH");
        $currentPeriod = self::getCurrentPeriod();
				$cFileName = md5("$kid-$currentPeriod");
        $publicKeyPath = "$dir/$cFileName.pem";  // Lokasi file public key berdasarkan kid dan periode

        if (!file_exists($publicKeyPath)) {
        	return json_encode(["status"=>false,"Filename"=>""]);
        }
        return json_encode(["status"=>true,"Filename"=>"$dir/$cFileName"]);
    }

    /**
     * Menentukan periode saat ini dalam format YYYY-MM
     */
    public static function getCurrentPeriod(): string
    {
			  return "2025-10" ;
        $currentDate = new DateTime();
        $month = $currentDate->format('m');
        $year = $currentDate->format('Y');

        // Tentukan periode berdasarkan bulan
        if ($month <= 6) {
            return "{$year}-04"; // Periode pertama: April (bulan 1-6)
        } else {
            return "{$year}-10"; // Periode kedua: Oktober (bulan 7-12)
        }
    }
}

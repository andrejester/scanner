<?php
/* 
Standart Class Autoload
1. Pastikan File Berapa di Folder project/include/autoload/Classes/
2. Pastikan Nama Class Sama Dengan Nama File
3. Tidak Boleh Ada nama Class Kembar di semua Subdir nya
*/
class JwtValidator
{
    /**
     * Resolver untuk kunci publik berdasarkan `kid`
     */
		public static $lCekToken = false;
	  public static $nExp = 3600;
    public static function resolvePublicKey(string $kid): string
    {
        // Dapatkan public key berdasarkan `kid` dari file


        //$publicKeyPath = "/path/to/public_keys/{$kid}.pub"; // Misalnya, file .pub untuk setiap kid
        if (file_exists($publicKeyPath)) {
            return file_get_contents($publicKeyPath);
        }

        throw new Exception("Public key not found for kid: {$kid}");
    }

    /**
     * Decode dan validasi JWT menggunakan public key resolver berdasarkan 'kid'
     */
    public static function decode(string $jwt, string $expectedAud, bool $validateTime = true): array
    {
        $parts = explode('.', $jwt);
        if (count($parts) !== 3) {
            throw new Exception('Invalid JWT format');
        }

        [$encodedHeader, $encodedPayload, $encodedSignature] = $parts;

        $header = json_decode(self::base64UrlDecode($encodedHeader), true);
        $payload = json_decode(self::base64UrlDecode($encodedPayload), true);
        $signature = self::base64UrlDecode($encodedSignature);

        if (!$header || !$payload || !isset($header['alg'])) {
            throw new Exception('Malformed JWT');
        }

        if ($header['alg'] !== 'RS256') {
            throw new Exception('Unsupported algorithm');
        }

        // Resolve public key via `kid`
        $kid = $header['kid'] ?? null;
        if (!$kid) {
            throw new Exception('Missing kid in JWT header');
        }
        // Mendapatkan kunci publik berdasarkan `kid`
				if(!self::$lCekToken){
					JwtToken::$lTokenClient = false;
				}else{
					JwtToken::$lTokenClient = true;
				}
        $publicKeyPem = JwtToken::getPublicKeyByKidAndPeriod($kid); //self::resolvePublicKey($kid);
        if (!$publicKeyPem) {
            throw new Exception('Unable to resolve public key');
        }

        // Verifikasi tanda tangan JWT
        $verified = self::verifySignature("$encodedHeader.$encodedPayload", $signature, $publicKeyPem);
        if (!$verified) {
					// Mendapatkan kunci publik berdasarkan `kid`
					if(!self::$lCekToken){
						JwtToken::$lTokenClient = false;
					}else{
						JwtToken::$lTokenClient = true;
					}
						$publicKeyPem = JwtToken::getPublicKeyByKidAndPeriod($kid); //self::resolvePublicKey($kid);
					
					if (!$publicKeyPem) {
							throw new Exception('Unable to resolve public key');
					}
        	$verified = self::verifySignature("$encodedHeader.$encodedPayload", $signature, $publicKeyPem);
					if (!$verified) {
            throw new Exception('Invalid signatures ');
					}
        }
        // Validasi klaim waktu jika perlu
        if ($validateTime) { 
					  AuthServer::$cClientId = $kid;
					  AuthServer::checkExp() ;
					  self::$nExp = AuthServer::$nExp ;
					
					  $now = time();
						$expired = $payload['iat'] + self::$nExp;
						
            if (isset($payload['nbf']) && $now < $payload['nbf']) {
                throw new Exception('Token not yet valid (nbf)');
            }

            if (isset($payload['iat']) && $now < $payload['iat']) {
                throw new Exception('Token issued in the future (iat)');
            }

            if (isset($payload['exp']) && $now >= $payload['exp']) {
                throw new Exception('Token has expired (exp)');
            }
					
						if(isset($payload['exp']) && $now >= $expired){
               throw new Exception('Token has expired (exp)');
						}
        }

        // Validasi audience jika ada
        if (isset($payload['aud']) && $payload['aud'] !== $expectedAud) {
            throw new Exception('Invalid audience (aud)');
        }

        return $payload;
    }

    /**
     * Verifikasi tanda tangan JWT menggunakan kunci publik
     */
    private static function verifySignature(string $data, string $signature, string $publicKeyPem): bool
    {
        $publicKey = openssl_pkey_get_public($publicKeyPem);
        if (!$publicKey) {
            throw new Exception('Unable to load public key');
        }

        return openssl_verify($data, $signature, $publicKey, OPENSSL_ALGO_SHA256) === 1;
    }

    private static function base64UrlDecode(string $data): string
    {
        $remainder = strlen($data) % 4;
        if ($remainder) {
            $data .= str_repeat('=', 4 - $remainder);
        }

        return base64_decode(strtr($data, '-_', '+/'));
    }
}
<?php
/* 
Standart Class Autoload
1. Pastikan File Berapa di Folder project/include/autoload/Classes/
2. Pastikan Nama Class Sama Dengan Nama File
3. Tidak Boleh Ada nama Class Kembar di semua Subdir nya
*/
class RedisOTP {
    private static $sentinel;
    private static $data;
    private static $isConnected = false;

    private static function connect() {
        if (self::$isConnected) return;

        $cIP       = Svr::GetConfig("token_ip", "10.1.8.150");
        $cPassword = Svr::GetConfig("token_password");
				$cMaster		= Svr::GetConfig("master_name","mymaster") ; 

        // Koneksi ke Redis Sentinel
        self::$sentinel = new Redis();
        self::$sentinel->connect($cIP, 26379);
        if ($cPassword !== "") {
            self::$sentinel->auth($cPassword);
        }

        // Ambil alamat Redis Master dari Sentinel
        $masterInfo = self::$sentinel->rawCommand(
            'SENTINEL',
            'get-master-addr-by-name',
            $cMaster
        );

        // Koneksi ke Redis Master
        self::$data = new Redis();
        self::$data->connect($masterInfo[0], $masterInfo[1]);
        if ($cPassword !== "") {
            self::$data->auth($cPassword);
        }

        self::$isConnected = true;
    }

    /**
     * Generate OTP dan simpan data lengkap ke Redis
     */
    /*public static function generateOTP($otp, array $extraData = [], $ttl = 300) {
        self::connect();

        $now = time();
        $payload = array_merge([
            'created_at' => $now,
            'expire_at'  => $now + $ttl
        ], $extraData);

        // Simpan ke Redis dalam format JSON
        self::$data->setex("otp:$otp", $ttl, json_encode($payload));

        return $payload;
    }*/
	
		public static function generateOTP($otp, array $extraData = [], $ttl = 300) {
        self::connect();

        $now = time();
        $payload = array_merge([
					  'otp' =>$otp,
            'created_at' => $now,
            'expire_at'  => $now + $ttl
        ], $extraData);

        // Simpan ke Redis dalam format JSON
        self::$data->setex("otp:".md5($otp), $ttl, json_encode($payload));

        return $payload;
    }

    /**
     * Ambil data OTP dari Redis
     */
    public static function getOTPData($key) {
        self::connect();
        $data = self::$data->get("otp:$key");
        return $data ? json_decode($data, true) : null;
    }

    /**
     * Validasi OTP
     */
    public static function validateOTP($key, $inputOtp, $deleteOnSuccess = true) {
        self::connect();

        $otpData = self::getOTPData($key);
        if (!$otpData) {
            return false; // Tidak ada atau expired
        }

        if (hash_equals($otpData['otp'], $inputOtp)) {
            if ($deleteOnSuccess) {
                self::$data->del("otp:$key");
            }
            return true;
        }

        return false;
    }

    /**
     * Hapus OTP
     */
    public static function deleteOTP($key) {
        self::connect();
        return self::$data->del("otp:$key");
    }

    /**
     * Cek TTL OTP
     */
    public static function ttlOTP($key) {
        self::connect();
        return self::$data->ttl("otp:$key");
    }
}


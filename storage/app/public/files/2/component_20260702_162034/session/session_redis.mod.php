<?php
/* 
Standart Class Autoload
1. Pastikan File Berapa di Folder project/include/autoload/Classes/
2. Pastikan Nama Class Sama Dengan Nama File
3. Tidak Boleh Ada nama Class Kembar di semua Subdir nya
*/
class SisSession implements SessionHandlerInterface {
	private $redis;
	private $ttl ;
	private $sentinel ;

	public function __construct($va) {
		//Koneksi Ke Sentinel Untuk Mencari Master
		$this->sentinel = new Redis();
    $this->sentinel->connect($va["session_server_ip"], 26379);
    if($va["session_server_password"] <> "") $this->sentinel->auth($va["session_server_password"]) ;
		$masterInfo = $this->sentinel->rawCommand('SENTINEL', 'get-master-addr-by-name', 'mymaster');
    
		//Koneksi Ke Redis Master
		$this->redis = new Redis();
		$this->redis->connect($masterInfo[0],  $masterInfo[1]) ;
    if($va["session_server_password"] <> "") $this->redis->auth($va["session_server_password"]) ;
		$this->ttl = isset($va["session_server_ttl"]) ? $va["session_server_ttl"] : 1800 ;
	}

	#[\ReturnTypeWillChange]
	public function open($save_path, $session_id) {
		return true;
	}

	#[\ReturnTypeWillChange]
	public function close() {
		return true;
	}

	#[\ReturnTypeWillChange]
	public function read($session_id) {
		$data = $this->redis->get($session_id);
		return $data ? json_decode($data, true) : "";
	}

	#[\ReturnTypeWillChange]
	public function write($session_id, $data) {
		//$this->debug_to_file($session_id) ;
		$retval = $this->redis->setex($session_id, $this->ttl, json_encode($data));
		//$this->redis->expire($session_id, $this->ttl);
		return $retval ;
	}

	#[\ReturnTypeWillChange]
	public function destroy($session_id) {
		return (bool) $this->redis->del($session_id);
	}

	#[\ReturnTypeWillChange]
	public function gc($maxlifetime) {
		return true;
	}
}

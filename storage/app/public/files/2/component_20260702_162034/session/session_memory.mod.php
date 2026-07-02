<?php
/* 
Standart Class Autoload
1. Pastikan File Berapa di Folder project/include/autoload/Classes/
2. Pastikan Nama Class Sama Dengan Nama File
3. Tidak Boleh Ada nama Class Kembar di semua Subdir nya
*/
class SisSession implements SessionHandlerInterface {
	public function __construct($va) {
		return true ;
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
		return "" ;
	}
  
	#[\ReturnTypeWillChange]
	public function write($session_id, $data) {
		return true ;
	}
  
	#[\ReturnTypeWillChange]
	public function destroy($session_id) {
		return true ;
	}
  
	#[\ReturnTypeWillChange]
	public function gc($maxlifetime) {
		return true;
	}
}
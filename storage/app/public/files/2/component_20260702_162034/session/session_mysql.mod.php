<?php
/* 
Standart Class Autoload
1. Pastikan File Berapa di Folder project/include/autoload/Classes/
2. Pastikan Nama Class Sama Dengan Nama File
3. Tidak Boleh Ada nama Class Kembar di semua Subdir nya
*/
class SisSession implements SessionHandlerInterface {
	private $link;
	private $va ;
	private $ttl ;
	public function __construct($va){
		$this->ttl = isset($va["session_server_ttl"]) ? $va["session_server_ttl"] : 3600 ;  		// jika tidak di definisi max maka default 30 menit
		$this->va = $va ;
	}

	#[\ReturnTypeWillChange]
	public function open($savePath, $sessionName) {
		$cIP = $this->va["session_server_ip"] == "" ? $this->va["ip"] : $this->va["session_server_ip"] ;
		$cUserName =  $this->va["session_server_username"] == "" ? $this->va["username"] : $this->va["session_server_username"] ;
		$cPassword = $this->va["session_server_password"] == "" ? $this->va["password"] : $this->va["session_server_password"] ;
		$cDatabase = $this->va["session_server_db"] == "" ? $this->va["db"] : $this->va["session_server_db"] ;
		$link = mysqli_connect($cIP,$cUserName,$cPassword,$cDatabase);
		if($link){
			$this->link = $link;
			$this->CheckTable() ;
			return true;
		}else{
			return false;
		}
	}

	private function CheckTable(){
		// Check Kalau table sis_session belum ada kita buatkan
		$dbData = mysqli_query($this->link,"SHOW TABLES LIKE 'sis_session'") ;
		if(!mysqli_fetch_assoc($dbData)){
			$cSQL = "CREATE TABLE `sis_session` (`ID` varchar(50) NOT NULL," ;
  		$cSQL .= "`Expired` int(10) unsigned NOT NULL default '0'," ;
  		$cSQL .= "`Data` longtext," ;
 	 		$cSQL .= "PRIMARY KEY (`ID`)," ;
			$cSQL .= "KEY `Expired` (`Expired`)" ;
			$cSQL .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8" ;
			mysqli_query($this->link,$cSQL) ;
		}else{
			$this->DeleteOld() ;
		}
	}
  
	#[\ReturnTypeWillChange]
	public function close() {
		mysqli_close($this->link);
		return true;
	}
  
	#[\ReturnTypeWillChange]
	public function read($id) {
		$nTime = time() ;
		$result = mysqli_query($this->link,"SELECT Data FROM sis_session WHERE ID = '$id' AND Expired > '$nTime'");
		if($row = mysqli_fetch_assoc($result)){
			return $row['Data'];
		}else{
			return "";
		}
	}

	#[\ReturnTypeWillChange]
	public function write($id, $data) {
		$nTime = time() + $this->ttl ;
		return mysqli_query($this->link,"REPLACE INTO sis_session SET ID = '$id', Expired = '$nTime', Data = '$data'");
	}

	#[\ReturnTypeWillChange]
	public function destroy($id) {
		return mysqli_query($this->link,"DELETE FROM sis_session WHERE ID ='$id'");
	}

	#[\ReturnTypeWillChange]
	public function gc($maxlifetime) {
		$this->DeleteOld() ;
	}
	
	private function DeleteOld(){
		$nTime = time() ;
		return mysqli_query($this->link,"DELETE FROM sis_session WHERE Expired < '$nTime'");
	}
}
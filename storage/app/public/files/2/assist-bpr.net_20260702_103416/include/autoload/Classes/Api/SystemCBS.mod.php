<?php
/* 
Standart Class Autoload
1. Pastikan File Berapa di Folder project/include/autoload/Classes/
2. Pastikan Nama Class Sama Dengan Nama File
3. Tidak Boleh Ada nama Class Kembar di semua Subdir nya
*/

class SystemCBS {
	public static $vaDescRespo = array("00"=>"Request sukses - approved","01"=>"Request tidak dapat diprosess");
	public static $vaResponse  = array("response_code"=>"01","response_desc"=>"","message"=>"","data"=>array()) ;
	public static $vaRequest   = array() ;

	protected static $lUpdate  = false ;
	static function Proses($vaRequest){
		if(isset($vaRequest['UPDATE'])) self::$lUpdate = true ;
		$cJenisTransaksi = $vaRequest['KODETRANSAKSI'] ;
		self::$vaRequest = udf::ValidData($vaRequest) ;
		switch ($cJenisTransaksi) {
			case '200':
				self::LogHistoryOtorisasi() ;
				break;
			case '830264';
				self::Data();
				break;
			case '201':
				self::LogChangesLog();
				break;
			default:
				self::$vaResponse['message'] = "Code tidak ditemukan!" ;
				break;
		}
		self::$vaResponse['response_desc'] = isset(self::$vaDescRespo[self::$vaResponse['response_code']]) ? self::$vaDescRespo[self::$vaResponse['response_code']] : "" ;
		return self::$vaResponse ;
	}
  //
	
	static function Data(){
		
		$vaRules = array("password"=>array("required"=>true,"type"=>"string"),
										 "table"=>array("required"=>true,"type"=>"string"),
										 "code_office"=>array("required"=>false,"type"=>"string"),
										 "jwt_extension"=>array("required"=>false,"type"=>"string"),
										 "blokir"=>array("required"=>false,"type"=>"string"),
										 "email"=>array("required"=>false,"type"=>"string"),
										 "userbaru"=>array("required"=>false,"type"=>"string"),
										 "fullname"=>array("required"=>false,"type"=>"string"),
										 "cabang"=>array("required"=>false,"type"=>"string"),
										 "tglpasswordperiodik"=>array("required"=>false,"type"=>"string"),
										 "passworduser"=>array("required"=>false,"type"=>"string"),
										 "username"=>array("required"=>true,"type"=>"string")) ;
		$vaValid = udf::ValidasiRequest(self::$vaRequest,$vaRules);
		if($vaValid['valid']){
			$cPassword = self::$vaRequest['PASSWORD'];//password_hash(self::$vaRequest['PASSWORD'], PASSWORD_BCRYPT, ["cost" => 15]);
			$cTable = self::$vaRequest['TABLE'] ;
			$vaData = array() ;
			if (password_verify($cPassword,'$2y$15$kbJgCGmpDkTe9..7sKKw2Ops.XdMV.OQZEqOnCttegS43aFaSf6mm')) {
				if($cTable == "tambah_user"){
					objData::Update("username",array("UserName"=>self::$vaRequest['USERNAME']),"UserName ='".self::$vaRequest['USERNAME']."'",false);
				}else if($cTable == "tambah_user_baru"){
					$vaInsert = array("username"=>self::$vaRequest['USERBARU'],"UserPassword"=>self::$vaRequest['PASSWORDUSER'],"TglPasswordPeriodik"=>self::$vaRequest['TGLPASSWORDPERIODIK'],
														"email"=>self::$vaRequest['EMAIL'],"block"=>self::$vaRequest['BLOKIR'],"FullName"=>self::$vaRequest['FULLNAME'],"Cabang"=>self::$vaRequest['CABANG']);
					objData::Update("username",$vaInsert,"UserName ='".self::$vaRequest['USERBARU']."'",false);
				}else if($cTable == "change_password"){ 
					
					$vaInsert = array("UserPassword"=>self::$vaRequest['PASSWORDUSER']);
					objData::Update("username",$vaInsert,"UserName ='".self::$vaRequest['USERNAME']."'",false);
				}else{
					$vaData = objData::ABrowse($cTable,"*") ;
				}
			}
			self::$vaResponse['data'] = $vaData; 
			self::$vaResponse['response_code'] = '00';
			self::$vaResponse['message'] = "Data berhasil diambil" ;
		}else{
			self::$vaResponse['message'] = implode(",",$vaValid['errors']);
		}
	}
	static function LogHistoryOtorisasi(){
		$vaRules = array("tgl"=>array("required"=>true,"type"=>"date")) ;
		$vaValid = udf::ValidasiRequest(self::$vaRequest,$vaRules);
		if($vaValid['valid']){
			$dTgl   = self::$vaRequest['TGL'] ;
			$vaStatus = array(
									"1"  => "Setoran Tabungan","2" => "Penarikan Tabungan","3" => "Setoran Deposito","4" => "Pencairan Deposito",
									"5"  => "Pencairan Kredit","6" => "Angsuran Kredit","7" => "Setoran Kredit RK","8" => "Penarikan Kredit RK",
									"9"  => "Penerimaan Kas","10" => "Pengeluaran Kas","11" => "Mutasi Pemindahbukuan","12" => "Setoran ABA",
									"13" => "Penarikan ABA","21" => "Buka Blokir Tabungan","22" => "Buka Tabungan Dormant","23" => "-",
									"24" => "-","25" => "Buka Blokir Deposito","26" => "Pengajuan Kredit","27" => "Edit KTP","28" => "Edit Keterangan Transaksi",
									"31" => "Reversal Antar Bank","32" => "Reversal Tabungan","33" => "Reversal Deposito","34" => "Reversal Kredit",
	                "40" => "Koreksi Data Tabungan","51"=> "Tambah User"
								 );
			
			$vaData = array() ;
			$dbData = objData::Browse("request","Faktur,Status,Tgl,Keterangan,Nominal,UserRequest,UserAcc,DateTime,CabangEntry,ACC","Tgl = '$dTgl'","","","CabangEntry asc");
			while($dbRow = objData::GetRow($dbData)){
				$cKetStatus = isset($vaStatus[$dbRow["Status"]]) ? $vaStatus[$dbRow["Status"]] : "" ;
				$vaData [$dbRow ['Faktur']] = array("Faktur"  		 => $dbRow["Faktur"],
																						"Status"			 => $dbRow["Status"],
																						"KetStatus"		 => $cKetStatus,
																						"Tgl"		       => $dbRow["Tgl"],
																						"Keterangan"   => $dbRow["Keterangan"],
																						"User Request" => $dbRow["UserRequest"],
																						"Status Acc"	 => $dbRow["ACC"],
																						"User Acc"		 => $dbRow["UserAcc"],
																						"DateTime"     => $dbRow["DateTime"],
																						"Cabang"  		 => $dbRow["CabangEntry"]);
			}
			
			self::$vaResponse['data'] = $vaData; 
			self::$vaResponse['response_code'] = '00';
			self::$vaResponse['message'] = "Data berhasil diambil" ;
			
		}else{
			self::$vaResponse['message'] = implode(",",$vaValid['errors']);
		}
	}
	static function LogChangesLog(){
		$vaRules = array("tgl_awal"=>array("required"=>true,"type"=>"date"),
						 "tgl_akhir"=>array("required"=>true,"type"=>"date"),
						 "username"=>array("required"=>false,"type"=>"string")) ;
		$vaValid = udf::ValidasiRequest(self::$vaRequest,$vaRules);
		if($vaValid['valid']){
			$dTglAwal  = self::$vaRequest['TGL_AWAL'] ;
			$dTglAkhir = self::$vaRequest['TGL_AKHIR'] ;
			$cUserName = isset(self::$vaRequest['USERNAME']) ? self::$vaRequest['USERNAME'] : "" ;
			
			$cWhere = "Tgl >= '$dTglAwal' and Tgl <= '$dTglAkhir'" ;
			if($cUserName != "") $cWhere .= " and UserName = '$cUserName'" ;

			$vaData = array() ;
			$dbData = objData::Browse("changeslog","*",$cWhere,"","","ID Asc");
			while($dbRow = objData::GetRow($dbData)){
				$vaData [] = array("ID"           => $dbRow["ID"],
								   "Tgl"          => $dbRow["Tgl"],
								   "UserName"     => $dbRow["UserName"],
								   "Keterangan"   => $dbRow["Keterangan"],
								   "DateTime"     => $dbRow["DateTime"],
								   "DataSebelum"  => $dbRow["DataSebelum"],
								   "DataSesudah"  => $dbRow["DataSesudah"]);
			}
			
			self::$vaResponse['data'] = $vaData; 
			self::$vaResponse['response_code'] = '00';
			self::$vaResponse['message'] = "Data berhasil diambil" ;
			
		}else{
			self::$vaResponse['message'] = implode(",",$vaValid['errors']);
		}
	}
}

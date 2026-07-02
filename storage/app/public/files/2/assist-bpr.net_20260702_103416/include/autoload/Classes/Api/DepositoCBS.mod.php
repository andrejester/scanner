<?php
/* 
Standart Class Autoload
1. Pastikan File Berapa di Folder project/include/autoload/Classes/
2. Pastikan Nama Class Sama Dengan Nama File
3. Tidak Boleh Ada nama Class Kembar di semua Subdir nya
*/
class DepositoCBS {
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
				self::Nominatif() ;
				break;
			case '201':
				self::SaldoDeposito() ;
				break;
			case '202':
				self::Mutasi() ;
				break;
				
			default:
				self::$vaResponse['message'] = "Code tidak ditemukan!" ;
				break;
		}
		self::$vaResponse['response_desc'] = isset(self::$vaDescRespo[self::$vaResponse['response_code']]) ? self::$vaDescRespo[self::$vaResponse['response_code']] : "" ;
		return self::$vaResponse ;
	}
	
	static function Mutasi(){
		$vaRules = array("tgl"=>array("required"=>true,"type"=>"date")) ;
		$vaValid = udf::ValidasiRequest(self::$vaRequest,$vaRules);
		if($vaValid['valid']){
			$cField = "Faktur,Rekening,Keterangan,SetoranPlafond,PencairanPlafond,SetoranTitipan,PencairanTitipan,Bunga,Pajak,UserName,CabangEntry,DateTime" ;
			$cField = "Rekening" ;
			$vaData = objData::ABrowse("mutasideposito","*","Tgl = '2025-05-05'","","","");
			self::$vaResponse['data'] = $vaData; 
			self::$vaResponse['response_code'] = '00';
			self::$vaResponse['message'] = "Data berhasil diambil aa" ;
		}else{
			self::$vaResponse['message'] = implode(",",$vaValid['errors']);
		}
	}
	
  static function SaldoDeposito(){
		$vaRules = array("tgl"=>array("required"=>true,"type"=>"date"),"rekening"=>array("required"=>true,"type"=>"string")) ;
		$vaValid = udf::ValidasiRequest(self::$vaRequest,$vaRules);
		if($vaValid['valid']){
			$dTgl   = self::$vaRequest['TGL'] ;
			$cRekening = self::$vaRequest['REKENING'] ;
			$dbData = objData::Browse("deposito","Rekening","Rekening='$cRekening'");
			if($dbRow = objData::GetRow($dbData)){
				self::$vaResponse['data'] = GetNominalDeposito::Get($dTgl,$cRekening); 
				self::$vaResponse['response_code'] = '00';
				self::$vaResponse['message'] = "Data berhasil diambil" ;
			}else{
				self::$vaResponse['message'] = "Rekening tidak ditemukan" ;
			}
			
		}else{
			self::$vaResponse['message'] = implode(",",$vaValid['errors']);
		}
	}
	static function Nominatif(){
		$vaRules = array("tgl"=>array("required"=>true,"type"=>"date")) ;
		$vaValid = udf::ValidasiRequest(self::$vaRequest,$vaRules);
		if($vaValid['valid']){
			$dTgl   = self::$vaRequest['TGL'] ;

			$dbData = objData::Browse("deposito","Rekening","Tgl <='$dTgl' and TglCair > '$dTgl'");
			$vaData['jumlah_nasabah'] = objData::NumRows($dbData);

			$cField = "r.Nama,r.Alamat,r.Telepon,r.NPWP,r.Kelamin,r.Keterkaitan,r.Kodya LokasiNasabah,
								 d.CabangEntry,d.Tgl,d.Kode,d.Rekening,d.ARekening,d.RekeningLama,d.GolonganDeposan,d.AO,a.Nama NamaAO,
								 d.Referensi,d.GolonganDeposito";

			$vaJoin = array("left join ao a on a.Kode = d.AO
											 left join registernasabah r on r.Kode = d.Kode") ;
			$vaData['data_nominatif'] = objData::ABrowse("deposito d",$cField,"d.Tgl <= '$dTgl' and d.TglCair > '$dTgl'",$vaJoin,"","","");
			$vaSaldo  = GetNominalDeposito::GetMulti($dTgl);
			foreach($vaData['data_nominatif'] as $key => $value){
				$vaData['data_nominatif'][$key]['saldo'] = isset($vaSaldo[$value['Rekening']]) ? $vaSaldo[$value['Rekening']]['SaldoAsli'] : 0;//sdsasd
			}
			self::$vaResponse['data'] = $vaData; 
			self::$vaResponse['response_code'] = '00';
			self::$vaResponse['message'] = "Data berhasil diambil" ;
			
		}else{
			self::$vaResponse['message'] = implode(",",$vaValid['errors']);
		}
	}
}

<?php
/* 
Standart Class Autoload
1. Pastikan File Berapa di Folder project/include/autoload/Classes/
2. Pastikan Nama Class Sama Dengan Nama File
3. Tidak Boleh Ada nama Class Kembar di semua Subdir nya
*/
class Tabungan {
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
				self::SaldoTabungan() ;
				break;
			case '102':
				self::CekRekeningKredit() ;
				break;

			default:
				self::$vaResponse['message'] = "Code tidak ditemukan!" ;
				break;
		}
		self::$vaResponse['response_desc'] = isset(self::$vaDescRespo[self::$vaResponse['response_code']]) ? self::$vaDescRespo[self::$vaResponse['response_code']] : "" ;
		return self::$vaResponse ;
	}
  static function SaldoTabungan(){
		$vaRules = array("tgl"=>array("required"=>true,"type"=>"date"),"rekening"=>array("required"=>true,"type"=>"string")) ;
		$vaValid = udf::ValidasiRequest(self::$vaRequest,$vaRules);
		if($vaValid['valid']){
			$dTgl   = self::$vaRequest['TGL'] ;
			$cRekening = self::$vaRequest['REKENING'] ;
			$dbData = objData::Browse("tabungan","Rekening","Rekening='$cRekening'");
			if($dbRow = objData::GetRow($dbData)){
				self::$vaResponse['data'] = GetSaldoTabungan::DetailSaldo($cRekening,$dTgl); 
				self::$vaResponse['response_code'] = '00';
				self::$vaResponse['message'] = "Data berhasil diambil" ;
			}else{
				self::$vaResponse['message'] = "Data tidak ditemukan" ;
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

			$db = aCfg::Get("msDatabaseLog") ;
			$cTableName = "tabungan_nominatif_".date("Ym",strtotime($dTgl)) ;
			$lStatus = Func::CheckTable($db,$cTableName);
			if($lStatus){
				$vaData = array("jumlah_nasabah"=>0,"jumlah_cadangan"=>0,"data_cadangan"=>array()) ;

				$dbData = objData::Browse("tabungan","Rekening","TglPenutupan > '$dTgl'");
				$vaData['jumlah_nasabah'] = objData::NumRows($dbData);

				$cField = "r.Nama,r.Alamat,r.Telepon,r.NPWP,r.Kelamin,r.Keterkaitan,r.Kodya LokasiNasabah,
					  			 t.CabangEntry,t.Tgl,t.Kode,t.Rekening,t.ARekening,t.RekeningLama,t.TglPenutupan,t.GolonganNasabah,t.AO,a.Nama NamaAO,
					  			 t.Instansi,t.Referensi,t.Note,t.GolonganTabungan,t.GolonganTabunganSub,t.Referensi,t.Instansi,
									 t.StatusKaryawan,c.Saldo,c.SaldoBlokir,c.SaldoMinimum ";

				$vaJoin = array("left join tabungan t on t.Rekening = c.Rekening
												 left join ao a on a.Kode = t.AO
				                 left join registernasabah r on r.Kode = t.Kode") ;
				$vaData['data_cadangan'] = objData::ABrowse("$db.$cTableName c",$cField,"c.Tgl = '$dTgl'",$vaJoin,"","","");
				$vaData['jumlah_cadangan'] = count($vaData['data_cadangan']) ;
				self::$vaResponse['data'] = $vaData; 
				self::$vaResponse['response_code'] = '00';
				self::$vaResponse['message'] = "Data berhasil diambil" ;
			}else{
				self::$vaResponse['message'] = "Data tidak ditemukan" ;
			}
			
		}else{
			self::$vaResponse['message'] = implode(",",$vaValid['errors']);
		}
	}
}
<?php
/* 
Standart Class Autoload
1. Pastikan File Berapa di Folder project/include/autoload/Classes/
2. Pastikan Nama Class Sama Dengan Nama File
3. Tidak Boleh Ada nama Class Kembar di semua Subdir nya
*/
class Kredit{
	// FUNCTION DIBAWAH INI SEHARUSNYA BERADA PADA CORE DAN EXTENDS KE KREDIT
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
				   self::NominatifPerRek() ;
				break;
			case '202':
				   self::Angsuran() ;
				break;
					case '102':
				   self::CekRekeningKredit() ;
				break;
		
			default:
				self::$vaResponse['message'] = "Code tidak ditemukan !" ;
				break;
		}
		self::$vaResponse['response_desc'] = isset(self::$vaDescRespo[self::$vaResponse['response_code']]) ? self::$vaDescRespo[self::$vaResponse['response_code']] : "" ;
		return self::$vaResponse ;
	}
	
	static function Angsuran(){
		$vaRules = array("tgl"=>array("required"=>true,"type"=>"date")) ;
		$vaValid = udf::ValidasiRequest(self::$vaRequest,$vaRules);
		if($vaValid['valid']){
			$cField = "Faktur,Rekening,Keterangan,DPokok DebetPokok,KPokok KreditPokok,DBunga DebetBunga,KBunga KreditBunga,Denda,KDenda KreditDenda,UserName,CabangEntry,DateTime" ;
			$vaData = objData::ABrowse("angsuran",$cField,"Tgl = '".self::$vaRequest['TGL']."'","","ID","ID");
			self::$vaResponse['data'] = $vaData; 
			self::$vaResponse['response_code'] = '00';
			self::$vaResponse['message'] = "Data berhasil diambil" ;
		}else{
			self::$vaResponse['message'] = implode(",",$vaValid['errors']);
		}
	}
	
	static function Nominatif(){
		$vaRules = array("tgl"=>array("required"=>true,"type"=>"date")) ;
		$vaValid = udf::ValidasiRequest(self::$vaRequest,$vaRules);
		if($vaValid['valid']){
			$vaData = array() ;
			
			$dTgl = self::$vaRequest['TGL'] ;
			$cPeriode = date("Ym",strtotime($dTgl));
			$db = aCfg::Get("msDatabaseLog") ;
			$cTableTabungan = "$db.tabungan_nominatif_".$cPeriode;// 
			$cTableName = "debitur_nominatif_".$cPeriode ;
			$lStatus = Func::CheckTable($db,$cTableName);
			if($lStatus){
				$vaData = array("jumlah_debitur"=>0,"jumlah_cadangan"=>0,"data_cadangan"=>array()) ;
				
				$dbData = objData::Browse("jaminan","Kode,Keterangan") ;
				$vaJaminan = objData::FetchAssoc_All($dbData,"Kode");
				
				
				$dbData = objData::Browse("jenispengikatanjaminan","Kode,Keterangan") ;
				$vaPengikatan = objData::FetchAssoc_All($dbData,"Kode");
				
				$dbData = objData::Browse("debitur","Rekening","TglLunas > '$dTgl'");
				$vaData['jumlah_debitur'] = objData::NumRows($dbData);
				
				$cField = "c.id,r.Kode,d.AO,a.Nama as NamaAO,d.Kolektor,j.Keterangan NamaKolektor,d.Kode KodeCIF,r.Nama,r.Alamat, c.Rekening, c.Tgl, c.TglValuta, c.JTHTMP, c.BakiDebet, c.TPokok, c.TBunga, c.FR, c.FRPokok, c.FRBunga, c.FRHari, c.FRHariPokok, c.FRHariBunga, c.Kol, c.KolMurni, 
									 c.JenisJaminan,'' KetJaminan, c.JenisPengikatan,'' KetJenisPengikatan, c.Prosentase, c.AlamatJaminan, c.NilaiJaminan, c.NilaiJaminanNJOP, c.PPAP, c.PPAPMurni, c.PersenPPAP, c.KewajibanPokok, c.KewajibanPokok1, 
									 c.KewajibanBunga1, c.KewajibanBunga, c.PembayaranPokok, c.PembayaranBunga, c.HariAccrual, c.JmlHariBln, c.Accrual, c.TBungaAccrual, c.SaldoAccrual, c.AccrualHariIni, c.TBungaReschedule, 
									 c.TglMacet, c.KeTunggakan, c.StatusKontijensi, c.AgsPokokBudep, c.AgsBungaBudep, c.KolektibilitasManual, c.AngsuranPokok, c.AngsuranBunga, c.KePokok, c.KeBunga, c.NilaiDiakui, 
									 c.NilaiDiperhitungkan, c.NilaiLikuid, c.NilaiNonLikuid, c.BungaJthTmp, c.Cabang, c.Status,d.Tgl TglRealisasi,d.Plafond PlafondAwal,t.Saldo,t.SaldoBlokir,t.SaldoMinimum";
				
				$vaJoin = array("left join debitur d on d.Rekening = c.Rekening
												 left join $cTableTabungan t on t.Rekening = d.RekeningTabungan and t.Tgl = '$dTgl'
												 left join ao a on a.Kode = d.AO
												 left join jurubayar j on j.Kode = d.Kolektor
				                 left join registernasabah r on r.Kode = d.Kode") ;
				$vaData['data_cadangan'] = objData::ABrowse("$db.$cTableName c",$cField,"c.Tgl = '$dTgl'",$vaJoin,"","","");
				$vaData['jumlah_cadangan'] = count($vaData['data_cadangan']) ;
				foreach($vaData['data_cadangan'] as $key => $value){
					$vaData['data_cadangan'][$key]['KetJaminan'] = isset($vaJaminan[$value['JenisJaminan']]) ? $vaJaminan[$value['JenisJaminan']]['Keterangan'] : "" ;
					$vaData['data_cadangan'][$key]['KetJenisPengikatan'] = isset($vaPengikatan[$value['JenisPengikatan']]) ? $vaPengikatan[$value['JenisPengikatan']]['Keterangan'] : "" ;
					$vaData['data_cadangan'][$key]['lama'] = Func::GetKe($value['TglValuta'],$value['JTHTMP'],999) ;  //Func::GetKe($value['tglvaluta'],$value['jthtmp'],999) ;
				}
				
				
				self::$vaResponse['data'] = $vaData; 
				self::$vaResponse['message'] = "Data berhasil diambil" ;
				
				self::$vaResponse['response_code'] = "00" ;
			}else{
				self::$vaResponse['message'] = "Data tidak ditemukan !" ;
				self::$vaResponse['response_code'] = "01" ;
			}
		}else{
			self::$vaResponse['message'] = implode(",",$vaValid['errors']);
		}
	}
	
	static function NominatifPerRek(){
			
		$vaRules = array("tgl"=>array("required"=>true,"type"=>"date")) ;
		$vaValid = udf::ValidasiRequest(self::$vaRequest,$vaRules);
		if($vaValid['valid']){
			$vaData = array() ;
			
			
			$db = aCfg::Get("msDatabaseLog") ;
			$cTableName = "debitur_nominatif_".date("Ym",strtotime(self::$vaRequest['TGL'])) ;
			$lStatus = Func::CheckTable($db,$cTableName);
			if($lStatus){
				$vaData = array("data_cadangan"=>array()) ;
				
				//$dbData = objData::Browse("debitur","Rekening","TglLunas > '".self::$vaRequest['TGL']."'");
				//$vaData['jumlah_debitur'] = objData::NumRows($dbData);
				
				$cField = "c.id,r.Kode,d.AO,a.Nama as NamaAO,r.Nama,r.Alamat, c.Rekening, c.Tgl, c.TglValuta, c.JTHTMP, c.BakiDebet, c.TPokok, c.TBunga, c.FR, c.FRPokok, c.FRBunga, c.FRHari, c.FRHariPokok, c.FRHariBunga, c.Kol, c.KolMurni, 
									 c.JenisJaminan, c.JenisPengikatan, c.Prosentase, c.AlamatJaminan, c.NilaiJaminan, c.NilaiJaminanNJOP, c.PPAP, c.PPAPMurni, c.PersenPPAP, c.KewajibanPokok, c.KewajibanPokok1, 
									 c.KewajibanBunga1, c.KewajibanBunga, c.PembayaranPokok, c.PembayaranBunga, c.HariAccrual, c.JmlHariBln, c.Accrual, c.TBungaAccrual, c.SaldoAccrual, c.AccrualHariIni, c.TBungaReschedule, 
									 c.TglMacet, c.KeTunggakan, c.StatusKontijensi, c.AgsPokokBudep, c.AgsBungaBudep, c.KolektibilitasManual, c.AngsuranPokok, c.AngsuranBunga, c.KePokok, c.KeBunga, c.NilaiDiakui, 
									 c.NilaiDiperhitungkan, c.NilaiLikuid, c.NilaiNonLikuid, c.BungaJthTmp, c.Cabang, c.Status";
				
				$vaJoin = array("left join debitur d on d.Rekening = c.Rekening
										 		 left join ao a on a.Kode = d.AO
				                 left join registernasabah r on r.Kode = d.Kode") ;
				$vaData['data_cadangan'] = objData::ABrowse("$db.$cTableName c",$cField,"c.Tgl = '".self::$vaRequest['TGL']."' and c.Rekening = '".self::$vaRequest['REKENING']."'",$vaJoin,"","","1");
				//$vaData['jumlah_cadangan'] = count($vaData['data_cadangan']) ;
				self::$vaResponse['data'] = $vaData; 
				self::$vaResponse['message'] = "Data berhasil diambil" ;
				
			
				self::$vaResponse['response_code'] = "00" ;
			}else{
				self::$vaResponse['message'] = "Data tidak ditemukan !" ;
				self::$vaResponse['response_code'] = "01" ;
			}
		}else{
			self::$vaResponse['message'] = implode(",",$vaValid['errors']);
		}
	}
		
	static function CekRekeningKredit(){
		$vaRules = array("KODECIF" => array('type' => 'string', 'required' => true));
		$vaValid = udf::ValidasiRequest(self::$vaRequest,$vaRules) ;
		if($vaValid['valid']){
			$dbData = objData::Browse("debitur","rekening,'' nama","Kode ='".self::$vaRequest['KODECIF']."'","","","Tgl");
			if($dbRow = objData::Rows($dbData) > 0){
				$dTgl  = date("Y-m-d") ;
				$cNama = udf::GetKeterangan(self::$vaRequest['KODECIF'],"nama","registernasabah") ;
				while($dbRow = objData::GetRow($dbData)){
					$dbRow['nama']      = $cNama;
					$cRekening          = $dbRow['rekening'] ;
					
					$vaDebitur 	 = GetDataDebitur::Get($cRekening,$dTgl) ;
					$vaTunggakan = GetTunggakan::Get($cRekening,$dTgl,$vaDebitur['Tgl'],$vaDebitur['CaraPerhitungan'],$vaDebitur['Lama'],$vaDebitur['Plafond'],
																	     	 $vaDebitur['PembayaranPokok'],$vaDebitur['SukuBunga'],$vaDebitur['PembayaranBunga'],$vaDebitur['PembayaranDenda']) ;
					
					$dbRow['tunggakan']       = $vaTunggakan;	
					$cWhere 									= "Rekening='$cRekening' and Tgl <='$dTgl'";
					$dbRow['detail_angsuran'] = objData::ABrowse("angsuran","faktur,tgl,keterangan,dpokok debet_pokok,kpokok kredit_pokok,dbunga debet_bunga,kbunga kredit_bunga,denda",$cWhere,"","","Tgl asc,ID asc") ;
					
					$vaData[] = $dbRow;
				}
				self::$vaResponse['response_code'] = "00" ;
				self::$vaResponse['data']          = $vaData;
			}else{
				self::$vaResponse['message'] = "Kode cif tidak ditemukan!" ; 
			}
		}else{
			self::$vaResponse['message'] = implode(",",$vaValid['errors']);
		}
	}
}
class ErrorCustom1 {
	public static $lTelegram = false;
	static function Log(){
		error_reporting(0);
		register_shutdown_function(function () {
				if ($error = error_get_last()) {
						$error = (object) $error;
						self::handleError($error->type, $error->message, $error->file, $error->line);
				}
		});
		set_error_handler(array(__CLASS__, 'handleError'));
	}

	static function cleanString($string) {
			$str = preg_replace('/[^a-zA-Z0-9\/. ]/', '', $string);
			return $str;
	}

}

<?php
/* 
Berisi Script Controller dengan method yang bisa kita definisikan ataralain
1. GET = index_get()
2. POST = index_post()
3. PUT = index_put()
4. DELETE = index_delete()
*/
class webcore_Controller extends MVC_Controller {
	function index(){
		/*$cToken = Auth::GetToken() ;
		if($cToken == ""){ 
		  return MVC::Response(null,MVC::HTTP_CLIENT_BAD_REQUEST);
		}
		$lValid = Auth::IsJwtValid($cToken) ;
		if(!$lValid) {
			return MVC::Response(null,MVC::HTTP_CLIENT_BAD_REQUEST);
		}*/
		$headers	= getallheaders();
		$cKode 		= isset($headers["code"]) ? $headers["code"] : $headers["Code"];
		$data  		= $_POST ;
		
		$this->Proses($cKode,$data);
		
	}
	
	function Proses($cKode,$data){
		
		/*$test["kode"] = $cKode;
      $test["data"] = $data;
    //echo "<pre>";print_r($data);die;
      return Auth::Response("200","Data Berhasil Disonkronasikan",$data);*/
		/*
  -----------------------------------Keterangan Kode-----------------------------------

  |01| => Rincian Crontab        |11| => Load Data Otorisasi     |22| => Informsasi rekenig tabungan
  |02| =>  Reset Cadangan        |12| => Load Data Cabang        |90| => Cek Database  
  |03| =>  Save Config           |13| => Load Detail Otorisasi   |91| => Backup Database
  |04| => Load Config            |14| => Save Otorisasi          |92| => WebCore Otorisasi
  |05| => Lock Kolektibilitas    |15| => Get Jumlah Request      
  |06| => Open Kolektibilitas    |16| => Query Database          
  |07| => BackDate               |17| => Test Function            
  |08| => Load Crontab Service   |18| => Load Data Neraca        
  |09| => Save Crontab Service   |20| => Data KDT    
  |10| => Hitung User Online     [21] => Flag Payroll

  WebCore => [01,02,03,04,05,06,07,08,09,10]
  Otorisasi => [11,12,13,14,15,18,20]
  -----------------------------------Keterangan Kode------------------------------------
  */

		if($data != null){
			if(isset($data["data"])){
				//$vaData = str_replace("\\","",$data["data"]);
				$vaData = json_decode($data["data"],true); 
			} 
		} 
		/*if($data != null){
    $vaData = str_replace("\\","",$data);
    $vaData = json_decode($vaData['data'],true); 
   } */
		//if($cKode == '01'){
		if($cKode == '01'){ 
			$vaGrid = GetStatusCrontab::Get();
			foreach($vaGrid as $key => $value){
				unset($vaGrid["$key"]["Status"]);
			}
			$vaData   = array("vaGrid"=>$vaGrid,"Time"=>aCfg::Get("msTimeAutoPosting"),"Nama"=>aCfg::Get("msNama")); //array 1 
			return Auth::Response("200","Data Berhasil Disonkronasikan",$vaData);
		}else if($cKode == '02'){
			$this->ResetCadangan($vaData);
		}else if($cKode == '03'){
			$cKode   = $vaData["Kode"];
			$cValue = $vaData["Ket"];
			aCfg::Upd($cKode,$cValue) ;
			return Auth::Response("200","Berhasil Diubah");
		}else if($cKode == '04'){
			$dbConfig = objData::Browse("config","Kode,Keterangan");
			while($vaDataConfig = objData::GetRow($dbConfig)){
				$vaConfig[$vaDataConfig["Kode"]] =  $vaDataConfig;
				$vaConfig[$vaDataConfig["Kode"]]["Keterangan"] =  str_replace('"',"'",$vaDataConfig["Keterangan"]);
			}
			$vaData = array("vaConfig"=>$vaConfig); //array 1 
			return Auth::Response("200","Data Berhasil Disonkronasikan",$vaData);
		}else if($cKode == '05'){//Lock Kolektibilitas
			$this->LockKolek($vaData) ;
		}else if($cKode == '06'){//Open Kolektibilitas
			$this->OpenKolek($vaData) ;
		}else if($cKode == '07'){//BackDate
			$dbLog        = aCfg::Get("msDatabaseLog");
			$dTgl         = $vaData['Tgl'];
			$NoWO         = $vaData['NoWO'];
			$cUsername     = $vaData['Username'];
			$cKetBackDate = $vaData['cKetBackDate'];
			$dbD = objData::Browse("tgltransaksi","Status","Status = '0'","","","Tgl Desc") ;
			if($dbR = objData::GetRow($dbD)){
				return Auth::Response("100","Tanggal Transaksi Masih Terbuka pada tgl ".String2Date($dTgl) .", Anda Harus Menutup Tanggal Transaksi Terlebih Dahulu !") ;
			}
			$dbData   = objData::Browse("tgltransaksi","Tgl","Status = '1' and Tgl = '$dTgl'","","","Tgl Desc") ;
			if(objData::Rows($dbData) <= 0){
				return Auth::Response("100","Tgl Transaksi " . $dTgl . " Belum Pernah Dibuka Sebelumnya!!") ;
			}

			$dbData = objData::Browse("tgltransaksi","Tgl,Status","Tgl = '{$dTgl}' and Status = '1'","","","Tgl Desc") ;
			if($dbRow = objData::GetRow($dbData)){
				$vaArray     = array("Tgl"=>$dTgl,"Status"=>0) ;
				$vaArraylog = array("Tgl"=>$dTgl,"NoChecklist"=>$NoWO,"Nama"=>$cUsername,"Keterangan"=>$cKetBackDate,"DateTime"=>date("Y-m-d H:i:s")) ;
				$Update      = objData::Update("tgltransaksi",$vaArray,"Tgl = '{$dTgl}' and Status = '1'") ;
				objData::Insert($dbLog.".backdate_log",$vaArraylog) ;
				return Auth::Response("200","Tanggal Transaksi Berhasil di Ganti") ;
			}
		}else if($cKode == '08'){
			$dbCrontab = objData::Browse("rep_check.rep_gwctrl","NAMESERVICE");
			while($vaDataCrontab = objData::GetRow($dbCrontab)){
				$vaCrontab[$vaDataCrontab["NAMESERVICE"]] =  $vaDataCrontab;
			}
			$vaData = array("vaCrontab"=>$vaCrontab); //array 1 
			return Auth::Response("200","Data Berhasil Disonkronasikan",$vaData);
		}else if($cKode == '09'){
			$vaArray = array("NAMESERVICE"=>$vaData['NameService'],"STATUS"=>"ok") ;
			$Update  = objData::Update("rep_check.rep_gwctrl",$vaArray,"NAMESERVICE = '{$vaData['NameService']}'") ;
			return Auth::Response("200","Crontab Berhasil diTambahkan") ;
		}else if($cKode == '10'){//JumlahOnline
			$TglTransaksi  =  Func::GetKeterangan("0","Tgl","tgltransaksi","Status");
			$dbDatacab     = objData::Browse("cabang","KodeInduk");
			$JmlCabang     = objData::Rows($dbDatacab);
			$dbDatakas     = objData::Browse("kantorkas","Kode");
			$JmlKas       = objData::Rows($dbDatakas);
			$dbData       = objData::Browse("username","Online,Username");
			$Jumlah        =  0;
			$Username      = array();
			while($dbRow = objData::GetRow($dbData)){
				$Online =  (time() - $dbRow["Online"])/60;
				if($Online <= 1){
					$Jumlah++;
					$Username[] = $dbRow["Username"];
				}
			}
			$vaData = array("Jumlah"=>$Jumlah,"TglTrx"=>$TglTransaksi,"JmlCabang"=>$JmlCabang,"JmlKas"=>$JmlKas,"Username"=>$Username); //array 1 
			return Auth::Response("200","Data Berhasil Disonkronasikan",$vaData);
		}else if($cKode == '16'){
			$cSql       = strtolower($vaData["Sql"]);
			$vaArray     = array();
			$JumlahRow   = 0;
			if(strpos($cSql, "select") !== false || strpos($cSql, "show") !== false){
				$dbData = objData::SQL($cSql);
				if(mysql_error() != ""){
					$vaArray["Error"] = mysql_error();
				}else{
					$JumlahRow    = objData::Rows($dbData);
					while($dbRow = objData::GetRow($dbData)){
						foreach($dbRow as $key => $value){
							$dbRow[$key] = htmlspecialchars(Func::String2SQL($value)) ;
						}
						$vaArray[] = $dbRow;
					}
				}
			}else{
				$vaArray["Error"] = "Query Selain Select Tidak Diperbolehkan";
			}
			return Auth::Response("200","Berhasil Direspon",array("JmlRow"=>$JumlahRow,"Data"=>$vaArray));
		}else if($cKode == '17'){
			$filename = "test.php";
			$file     = fopen($filename, "w") or die("Unable to open file!");

			$phpCode = str_replace("<?php"," ",$vaData["cCode"]);
			$phpCode = str_replace("?>"," ",$phpCode);
			$phpCode = str_replace("<br />n","\n",$phpCode);
			$phpCode = "<?php \n function testfunc() { \n ".$phpCode." \n } \n ?>";
			fwrite($file, $phpCode);

			fclose($file);
			require "test.php";
			$result = testfunc();

			return Auth::Response("200","Berhasil DiOtorisasi",array("Data"=>$result));
		}else if($cKode == '21'){
			$cFaktur =  $vaData['Faktur'];
			$dbData = objData::Browse("mutasitabungan","Rekening,Tgl","Faktur like '$cFaktur%'","","Rekening");
			while($dbRow = objData::GetRow($dbData)){
				UpdFlagTabungan::Save($dbRow['Rekening'],$dbRow['Tgl']) ;
			}
			$cCek = "okee";
			$dbData = objData::Browse("angsuran","Rekening,Tgl","Faktur like '$cFaktur%'","","Rekening");
			while($dbRow = objData::GetRow($dbData)){
				$cCek = "suap";
				UpdFlagDebitur::Save($dbRow['Rekening'],$dbRow['Tgl']) ;
			}

			return Auth::Response("200","Berhasil di Flag",$vaData);

		}else if($cKode == '22'){
			$vaDB = GetBackupDatabase::Seek();
			$cDatabase 	= $vaDB['Database Name'] ;
			$cRekening	= $vaData['Rekening'] ;
			//$cKTP				= $vaData['KTP'] ;
			$dTgl				= date("Y-m-d") ;
			
			//Cek Rekening
			$vaJoin	= array("left join registernasabah r on r.Kode = t.Kode") ;
			$dbData	= objData::Browse("tabungan t","t.Rekening, r.Nama, r.Alamat, r.KTP","t.Rekening = '$cRekening'",$vaJoin) ;
			if($dbRow	= objData::GetRow($dbData)){
				$cStatus		= "ok" ;
				$nSaldoEfektif	= GetSaldoTabungan::Get($cRekening,$dTgl,true,true) ; 
				
				$dbRow['Saldo']	= Number2String($nSaldoEfektif,2) ;
				$vaData			= $dbRow ;
				
			}else{
				$vaData['Nama']		= "" ;
				$vaData['Alamat']	= "" ;
				$vaData['Saldo']	= 0 ;
				$cStatus	= "Rekening Tidak di Temukan...!" ;
			}
			
			
			return Auth::Response("200",$cStatus,$vaData);
		}else if($cKode == '90'){//CekDatabase
			$vaData = GetBackupDatabase::Seek();

			return Auth::Response("200","Data Base Berhasil Terbaca",$vaData);
		}else if($cKode == '91'){//BackupDatabase
			$cDatabase = $vaData['DataBase'] ;
			$cFile = GetBackupDatabase::Backup($cDatabase);

			return Auth::Response("200","Backup Database Finish",array("File"=>$cFile));


		}else if($cKode == '92'){
			$vaReturn = array();
			$vaDatas = explode(",",$data["Code"]);
			foreach($vaDatas  as $key =>$value){
				if($value == '11'){
					$cCabang     = Func::GetKeterangan($data['user'],"Cabang","username","UserWC");
					$cUsername   = Func::GetKeterangan($data['user'],"Username","username","UserWC");
					$vaArray    =  GetWewenangOtorisasi::Get($data['dTgl'],$cCabang,$data['where'],$cUsername);
					//return Auth::Response("200","Data Base Berhasil Terbaca","ss".$cUsername);
					$vaReturn["11"] = $vaArray;
				}else if($value == '12'){
					$dbData = objData::Browse("cabang","KodeInduk,Keterangan");
					while($dbRow = objData::GetRow($dbData)){
						$vaCabang[] =  $dbRow;
					}
					//$data = $vaCabang; //array 1 
					//return Auth::Response("200","Data Berhasil Disonkronasikan",$data);
					$vaReturn["12"] = $vaCabang;
				}else if($value == '13'){
					$vaArray = array();
					$Status  = $data["Status"];
					$Faktur  = $data["Faktur"];
					if($Status == "26"){
						$vaArray["DataAgunan"]     = GetWewenangOtorisasi::GetDataAgunan($Faktur);
						$vaArray["DataPengajuan"]  = GetWewenangOtorisasi::GetPengajuanKredit($Faktur);
					}else if($Status == "3"){
						$vaBank         = GetBank::Get() ;
						$vaArray["dep"]["cJenisBank"]      = $vaBank['JenisBank'] ;
						$vaArray["dep"]["cLabelRate"]     = $vaBank['LabelRate'] ;
						$vaArray["dep"]["cLabelTabungan"] = $vaBank['LabelTabungan'] ;

						$dbData   = objData::Browse("request","*","Faktur = '$Faktur'") ;
						if($dbRow = objData::GetRow($dbData)){
							$cRekening   = $dbRow['DataGrid'] ;
							$dTgl        = $dbRow['Tgl'] ;
							$cKeterangan = $dbRow['Keterangan'] ;
							$vaDeposito  = GetDataDeposito::Get($cRekening,$dTgl,true) ;
							$vaArray["dep"]["cRekening"]    =  $cRekening;
							$vaArray["dep"]["dTgl"]          =  $dTgl;
							$vaArray["dep"]["cKeterangan"]  = $cKeterangan;
							$vaArray["dep"]["vaDeposito"]    = $vaDeposito;
							if($vaArray["dep"]["cJenisBank"] == "1"){
								$vaArray["dep"]["ketGolDep"]  =  Func::GetKeterangan($vaDeposito['GolonganDeposito'],"Keterangan","golongandeposito","Kode");
							}else if($vaArray["dep"]["cJenisBank"] == "2"){
								$vaArray["dep"]["ketGolDep"]  = Func::GetKeterangan($vaDeposito['GolonganDeposito'],"Keterangan","lbbprs_ref_golongandebitur_bukannasabahbank","Kode");
							}
						}
					}
					$vaReturn["13"] = $vaArray;
					//return Auth::Response("200","Data Berhasil Disonkronasikan",$vaArray);
				}else if($value == '14'){
					$cUsername       = Func::GetKeterangan($data['User'],"Username","username","UserWC");
					if($data['Jenis'] == '26'){
						$vaUpdate = array("PlafondRealisasi"=>$data["PlafondRealisasi"],"LamaRealisasi"=>$data["LamaRealisasi"],"Acc"=>$data["Acc"],"UserAcc"=>$cUsername,"AlasanPenolakan"=>$data["AlasanPenolakan"]);
						objData::Update("pengajuankredit",$vaUpdate,"FakturRequest = '{$data["Faktur"]}'") ;
					} 
					SetRequest::Save($data["Faktur"],'','','','','','',$data["Acc"],$cUsername) ;
					$vaArray	=	array("message"=>"Berhasil");
					$vaReturn["14"] = $vaArray;
					//return Auth::Response("200","Berhasil DiOtorisasi");
				}else if($value == '15'){
					$dbData           = objData::Browse("request","*");
					$JumlahReq         = objData::Rows($dbData);
					$JumlahReqSelesai = 0;
					$JumlahReqPending = 0;
					while($dbRow = objData::GetRow($dbData)){
						if($dbRow["Acc"] != 0){
							$JumlahReqSelesai++;
						}else{
							$JumlahReqPending++;
						}
					}
					$vaReturn["15"] = array("JmlReq"=>$JumlahReq,"JmlReqSelesai"=>$JumlahReqSelesai,"JmlReqPending"=>$JumlahReqPending);
					//return Auth::Response("200","Berhasil DiOtorisasi",array("JmlReq"=>$JumlahReq,"JmlReqSelesai"=>$JumlahReqSelesai,"JmlReqPending"=>$JumlahReqPending));
				}else if($value == '18'){
					$dTgl     = $data["dTgl"]; //Y-m-d
					$cCabang   = $data["cCabang"]; //Y-m-d
					$Pasiva[]  =  array();
					$Aktiva[]  =  array();
					$Pend[]    =  array();
					$biaya[]  =  array();
					$nonop[]  =  array();
					$RekPNOP   = aCfg::Get("RekeningPendapatanNonOPAwal");
					$RekBNOP   = aCfg::Get("RekeningBiayaNonOPAwal");
					$where     = $cCabang <> "Semua" ? "AND Cabang = '$cCabang'" : "";
					$dbData   = objData::SQL("select Rekening,r.Keterangan,Cabang,CASE WHEN left(rekening,1) = '1' OR left(rekening,1) = '5' OR left(rekening,1) = '6' then sum(debet-kredit) else sum(kredit-debet) END AS Saldo  from bukubesar LEFT JOIN rekening r ON r.Kode = rekening where TglRekonsiliasi <= '$dTgl' $where and (SUBSTRING(Rekening, 1, 1) = '1' or SUBSTRING(Rekening, 1, 1) = '2' or SUBSTRING(Rekening, 1, 1) = '3' or SUBSTRING(Rekening, 1, 1) = '4' or SUBSTRING(Rekening, 1, 1) = '5') group by Rekening,Cabang") ;
					$p         = 0;
					$a         = 0;
					$p4       = 0;
					$b         = 0;
					$NOP       = 0;
					while($dbRow = objData::GetRow($dbData)){
						$Jenis   = substr($dbRow["Rekening"],0,1);
						$cRek   = substr($dbRow["Rekening"],0,5);
						$ket     = Func::GetKeterangan($cRek,"Keterangan","rekening");
						if($Jenis == 1){
							$Aktiva[$a]["Rekening"]    =  $cRek;
							$Aktiva[$a]["Keterangan"]  =  $ket; //$dbRow["Keterangan"];
							$Aktiva[$a]["Cabang"]      =  $dbRow["Cabang"];
							$Aktiva[$a]["Saldo"]      =  $dbRow["Saldo"];
							$a++;
						}else if($Jenis == 2 || $Jenis == 3){
							$Pasiva[$p]["Rekening"]    =  $cRek; //$dbRow["Rekening"];
							$Pasiva[$p]["Keterangan"]  =  Func::GetKeterangan($cRek,"Keterangan","rekening");//$dbRow["Keterangan"];
							$Pasiva[$p]["Cabang"]      =  $dbRow["Cabang"];
							$Pasiva[$p]["Saldo"]      =  $dbRow["Saldo"];  
							$p++;
						}else if($cRek >= $RekPNOP && $cRek < "5" || $cRek >= $RekBNOP && $cRek < "6"){
							$nonop[$NOP]["Rekening"]    =  $cRek; 
							$nonop[$NOP]["Keterangan"]  =  $ket;
							$nonop[$NOP]["Cabang"]      =  $dbRow["Cabang"];
							$nonop[$NOP]["Saldo"]        =  $dbRow["Saldo"];  
							$NOP++;  
						}else if($Jenis == 4){
							$Pend[$p4]["Rekening"]    =  $cRek; //$dbRow["Rekening"];
							$Pend[$p4]["Keterangan"]  =  Func::GetKeterangan($cRek,"Keterangan","rekening");//$dbRow["Keterangan"];
							$Pend[$p4]["Cabang"]      =  $dbRow["Cabang"];
							$Pend[$p4]["Saldo"]        =  $dbRow["Saldo"];  
							$p4++;
						}else if($Jenis == 5){
							$biaya[$b]["Rekening"]    =  $cRek; //$dbRow["Rekening"];
							$biaya[$b]["Keterangan"]  =  Func::GetKeterangan($cRek,"Keterangan","rekening");//$dbRow["Keterangan"];
							$biaya[$b]["Cabang"]      =  $dbRow["Cabang"];
							$biaya[$b]["Saldo"]        =  $dbRow["Saldo"];  
							$b++;
						}
					}
					$vaReturn["18"] = array("nonop"=>$nonop,"Pasiva"=>$Pasiva,"Aktiva"=>$Aktiva,"Pend"=>$Pend,"Biaya"=>$biaya,"RekKas"=>aCfg::Get("msRekeningKasCamel"),"RekTP"=>aCfg::Get("RekeningTaksiranPajakLaba"));
					//return Auth::Response("200","Berhasil DiOtorisasi",array("nonop"=>$nonop,"Pasiva"=>$Pasiva,"Aktiva"=>$Aktiva,"Pend"=>$Pend,"Biaya"=>$biaya));
				}else if($value == '20'){
					$dTgl  =  date("Y-m-d");
					$dbData = objData::Browse("debitur","Count(Rekening) Total","TglLunas >= '$dTgl'");
					while($dbRow = objData::GetRow($dbData)){
						$va['Kredit']  = $dbRow['Total'];
					}

					$dbData = objData::Browse("Deposito","Count(Rekening) Total","TglCair >= '$dTgl'");
					while($dbRow = objData::GetRow($dbData)){
						$va['Debet'] = $dbRow['Total'];
					}

					$dbData = objData::Browse("Tabungan","Count(Rekening) Total","TglPenutupan >= '$dTgl'");
					while($dbRow = objData::GetRow($dbData)){
						$va['Tabungan'] = $dbRow['Total'];
					}
					$vaReturn["20"] = $va;
					//return Auth::Response("200","Berhasil DiOtorisasi",$va);
				}else if($value == '21'){
					$va21 = array();
					$dbEskternal        = aCfg::Get("msDatabaseEksternal");
					$dbData = objData::Browse($dbEskternal.".lbbpr_form_0004","Nama,SandiKantor");
					while($dbRow = objData::GetRow($dbData)){
						$va21[$dbRow["SandiKantor"]] = $dbRow;
					}
					$vaReturn["21"] = $va21;
				}else if($value == '22'){
					$va22 = array();
					$dbEskternal     = aCfg::Get("msDatabaseEksternal");
					$nTahunAkhir 		 = $data['nTahun']."12";
					$nTahunAwal 		 = $data['nTahun']."01";
					$dbData	= objData::Browse($dbEskternal.".lbbpr_form_0600","Periode,SandiKantor,Kualitas,BakiDebet","Periode <= $nTahunAkhir and Periode >= $nTahunAwal","","","Periode asc");
					while($dbRow = objData::GetRow($dbData)){
						$va22[] = $dbRow;
					}
					$vaReturn["22"] = $va22;
				}else if($value == '23'){
					$va23 = array();
					$dbEskternal     = aCfg::Get("msDatabaseEksternal");
					$nTahunAkhir 		 = $data['nTahun']."12";
					$nTahunAwal 		 = $data['nTahun']."01";
					$dbData				= objData::Browse($dbEskternal.".lbbpr_form_0008","Sandi,Periode,Nilai","Periode <= '$nTahunAkhir' and Periode >= '$nTahunAwal'");
					while($dbRow = objData::GetRow($dbData)){
						$Ket = Func::GetKeterangan($dbRow["Sandi"],"Keterangan",$dbEskternal.".lbbpr_rekening_0008","Kode");
						$dbRow["Keterangan"] = $Ket;
						$va23[] = $dbRow;
					}
					$vaReturn["23"] = $va23;
				}else if($value == '24'){
					$va24 = array();
					$dbEskternal        = aCfg("msDatabaseEksternal");
					//return Auth::Response("200","Berhasil",$dbEskternal);
					$dbData = objData::Browse($dbEskternal.".lbbpr_rekening_0008","Keterangan,Kode");
					while($dbRow = objData::GetRow($dbData)){
						$va24[$dbRow["Kode"]] = $dbRow;
					}
					$vaReturn["24"] = $va24;
				}else if($value == '25'){
					$va25 = array();
					$dTgl = $data["dTgl"];
					$cCabang = $data["cCabang"];
					$Periode = date("Ym",strtotime($dTgl));
					$dbLog	= aCfg::Get("msDatabaseLog");
					$cWhere = "";
					if($cCabang != "Semua")$cWhere = " and Cabang = '$cCabang'";
					$dbData = objData::Browse($dbLog.".debitur_nominatif_$Periode","*","Tgl = '$dTgl' and StatusKontijensi = '0'$cWhere");
					while($dbRow = objData::GetRow($dbData)){
						$cKode = Func::GetKeterangan($dbRow["Rekening"],"Kode","debitur","Rekening");
						$dbRow["Nama"] = Func::GetKeterangan($cKode,"Nama","registernasabah");
						$va25[] = $dbRow;
					}
					$vaReturn["25"] = $va25;
				}else if($value == '26'){
					$va26 = array();
					$dbData = objData::Browse("Username","Username");
					while($dbRow = objData::GetRow($dbData)){
						$va26[] = $dbRow;
					}
					$vaReturn["26"] = $va26;
				}else if($value == '27'){
					$cSQL = "SHOW COLUMNS FROM username";
					$dbData = objData::SQL($cSQL);
					$cekTable = false;
					while($dbRow = objData::GetRow($dbData)){
						if($dbRow["Field"] == "UserWC"){
							$cekTable = true;
						}
					}
					if($cekTable === true){
							return Auth::Response("200","ADA");
					}else{
							return Auth::Response("200","TIDAK ADA");
					}
					//$vaReturn["26"] = $va26;
				}else if($value == '28'){
					unset($data["Code"]);
					foreach($data as $key => $value){
						$key = str_replace("_"," ",$key);
						$value = explode(";",$value);
						$cTable = $value[1];
						$cField = isset($value[3]) ? $value[3] : "Kode,Keterangan";
						$cField = $cField != "" ? $cField : "Kode,Keterangan";
						$cWhere = isset($value[2]) ? $value[2] : "";
						$dbData = objData::Browse($cTable,$cField,$cWhere);
						while($dbRow = objData::GetRow($dbData)){
							$va28[$key][] = $dbRow;
						}
					}
					//RabbitMQ::SendMessage("tabel",$va28);
					//return Auth::Response("200","Berhasil",$va28);
					$vaReturn["28"] = $va28;
				}else if($value == '29'){
					$dbData = objData::Browse($data["table"],$data["field"],$data["where"]);
					if($dbRow = objData::GetRow($dbData)){
						$va29 = $dbRow[$data["field"]];
					}
					$vaReturn["29"] = $va29;
				}else{
					$vaReturn[$value] = array("Message"=>"Kode Proses Tidak Ditemukan");
					//return Auth::Response("200","Kode Proses Tidak Ditemukan");
				} 
			}
$vaReturn['tester'] = "133";
			return Auth::Response("200","Berhasil",$vaReturn);
		}else if($cKode == '93'){
			$vaReturn = array();
			$vaDatas = explode(",",$data["Code"]);
			foreach($vaDatas  as $key =>$value){
				if($value == '01'){
					$cStatus = "";
					$vaData	 = array();
					$cRekening = $data["Rekening"];
					$cNIK = $data["NIK"];
					$vaJoin	= array("left join registernasabah r on r.Kode = d.Kode") ;
					$dbData	= objData::Browse("debitur d","r.KTP,r.Nama,r.Alamat,d.TglLunasPokok,d.RekeningTabungan","d.Rekening = '$cRekening'",$vaJoin) ;
					
					if(objData::Rows($dbData) > 0){
						if($dbRow = objData::GetRow($dbData)){
							if($dbRow["KTP"] == $cNIK){
								$dTgl			=	date("Y-m-d");
								$vaData["cNama"] 		= $dbRow["Nama"];
								$vaData["cAlamat"] 	= $dbRow["Alamat"];
								$vaDenda     = GetTotalDenda::Get($cRekening,$dTgl) ;
								$vaDebitur 	 = GetDataDebitur::Get($cRekening,$dTgl) ;
								$lCfgLunas   = aCfg::Get("msLunasTunggakan",true) ;
								$vaTunggakan = GetTunggakan::Get($cRekening,$dTgl,$vaDebitur['Tgl'],$vaDebitur['CaraPerhitungan'],$vaDebitur['Lama'],$vaDebitur['Plafond'],
																					 $vaDebitur['PembayaranPokok'],$vaDebitur['SukuBunga'],$vaDebitur['PembayaranBunga'],$vaDebitur['PembayaranDenda'],
																					 false,true,false,$lCfgLunas,$dbRow['TglLunasPokok']) ;
								$dTglAkhirBulan = Func::EOM($dTgl) ;
								$vaKewajiban		= GetKewajiban::Get($cRekening,$dTglAkhirBulan,$vaDebitur['Lama'],$vaDebitur['Tgl'],$dTgl) ;
								GetSaldoTabungan::$msCekCadangan = false;
								
								$vaDenda      = GetTotalDenda::Get($cRekening,$dTgl) ;
								$lReturnPokok = true ;
								$lReturnBunga = true ; 
								$msPembayaranAngsuran = aCfg::Get("msPembayaranTunggakanAngsuran",false) ;
								$nKePokok	=	0;
								$nKeBunga	=	0;
								if(!empty($vaDenda["Jadwal"])){
									foreach($vaDenda["Jadwal"] as $key=>$value){
										if($value['Pokok'] > 0 && $lReturnPokok){
											$nPokok   = ($msPembayaranAngsuran) ? $vaTunggakan['T.Pokok'] : $value['Pokok'] ;
											$nKePokok = ceil($vaTunggakan['KePokok']/$vaDebitur['PembayaranPokokPer']) ;

											$lReturnPokok = false ;
										}
										if($value['Bunga'] > 0 && $lReturnBunga){
											$nBunga   = ($msPembayaranAngsuran) ? $vaTunggakan['T.Bunga'] : $value['Bunga'] ;
											$nKeBunga = ceil($vaTunggakan['KeBunga']/$vaDebitur['PembayaranBungaPer']) ;

											$lReturnBunga = false ;
										}
									}
								}
								
								$vaData['SaldoTabungan'] 	= GetSaldoTabungan::Get($dbRow["RekeningTabungan"],$dTgl,true,true) ; 
								$vaData['PokokPerBln']   	= $vaKewajiban['Kewajiban Pokok'] ;
								$vaData['BungaPerBln']  	= $vaKewajiban['Kewajiban Bunga'] ;
								$vaData["Plafond"]   			= $vaDebitur['Plafond'] ;
								$vaData["TPokok"]    			= $vaTunggakan['T.Pokok'] ;
								$vaData["TBunga"]    			= $vaTunggakan['T.Bunga'] ;
								$vaData['KDenda']    			= max($vaDenda['Denda'],0) ;
								$vaData["nKePokok"]    		= $nKePokok ;
								$vaData["nKeBunga"]    		= $nKeBunga ;
							}else{
								$cStatus = "NIK yang anda masukkan salah";
							}
						}
					}else{
							$cStatus = "Rekening yang anda masukkan tidak ditemukan";
					}
					$vaArray	=	array("Error"=>$cStatus,"Detail"=>$vaData);
					$vaReturn["01"] = $vaArray;
				}
				else if($value == '02'){
					$cStatus 		= "";
					$vaData	 		= array();
					$cRekening 	= $data["Rekening"];
					$dTgl				=	date("Y-m-d");
					$vaJoin			= array("left join registernasabah r on r.Kode = t.Kode") ;
					$dbData			= objData::Browse("tabungan t","t.Rekening, r.Nama, r.KTP, r.TglLahir, r.Alamat","t.Rekening = '$cRekening'",$vaJoin) ;
					if($dbRow = objData::GetRow($dbData)){
						GetSaldoTabungan::$msCekCadangan = false;
						$nSaldoEfektif   				= GetSaldoTabungan::Get($cRekening,$dTgl,true,true) ; 
						//return Auth::Response("200","Kode Proses Tidaks Ditemukanssss aaaaa");
						$dbRow['SaldoEfektif']  = max($nSaldoEfektif,0) ; 
						
						$vaData	= $dbRow ;
					}else{
						$vaData		= array() ;
						$cStatus	= "Rekening tidak valid" ;
					}
					$vaArray	=	array("Error"=>$cStatus,"Detail"=>$vaData) ;
					$vaReturn["02"] = $vaArray;
				}
				else if($value == '03'){
					$dTgl						=	date("Y-m-d");
					$cRekening			=	$_POST["cRekening"];
					$cNama					=	$_POST["cNama"];
					$nTotal					=	$_POST["nTotal"];
					$nPokok					=	$_POST["nPokok"];
					$nBunga					=	$_POST["nBunga"];
					$nDenda					=	$_POST["nDenda"];
					$cPembayaran		=	$_POST["optJenisTransaksi"];
					$nKePokok				=	$_POST["nPokokKe"];
					$nKeBunga				=	$_POST["nBungaKe"];
					
					$cFaktur     		= Func::GetLastFaktur("AP",true,$dTgl) ;
					
					$vaDenda      = GetTotalDenda::Get($cRekening,$dTgl) ;
					$lReturnPokok = true ;
					$lReturnBunga = true ; 
					$msPembayaranAngsuran = aCfg::Get("msPembayaranTunggakanAngsuran",false) ;
					
					$cKeterangan 		= "Angsuran Kredit [".$cRekening."] an. ".$cNama . " Angsuran Pokok Ke " . $nKePokok . " Bunga Ke " . $nKeBunga ;
					UpdAngsuranKredit::$lPaperless = true;
					UpdAngsuranKredit::Save("5",$cFaktur,$dTgl,$cRekening,$cKeterangan,0,$nPokok,0,$nBunga,0,0,$nDenda,0,"01",$cPembayaran,false,'T','','',0,0,true) ;
					
					$vaReturn["03"] = array("Faktur"=>$cFaktur);
				}
				else if($value == '04'){
					$dTgl					=	date("Y-m-d");
					$cRekening		=	$_POST["cRekening"];
					$cNama				=	$_POST["cNama"];
					$nMutasi			= $_POST["nMutasi"];
					$cCabang			= $_POST["cCabang"];
					$cJenisTrans	= $_POST["optJenisTransaksi"] ;
					$cMetodeTrans	= $_POST["optMetodeTransaksi"] ;
					$cTTD					= $_POST["cTTD"] ;
					$cFoto				= $_POST["cFoto"] ;
					$cTujuanTF		= $_POST["cNamaBankTarik"] ;
					$cYgTransaksi	= $_POST["optYangTransaksi"] ;
					$cNamaWakil		= $_POST["cNamaWakil"] ;
					
					if($cMetodeTrans == "2"){
						$cFaktur    		= Func::GetLastFaktur("PS",true,$dTgl);
						$cMetodeTransaksi = ($cJenisTrans == "01") ? "SNT" : "PNT";
						$cRekeningAsal	= $_POST["cNomorRekeningDanagung"] ;
						$cPemilikRekening = $_POST["cPemilikRekening"] ;
						if($cTujuanTF == "DNG"){
							$cKeterangan	="Transfer Antar Rekening Paperless Dari [". $cRekening ."] ". $cNama ." Ke [". $cRekeningAsal ."] ". $cPemilikRekening;
						}else{
							$cKeterangan	= ($cJenisTrans == "01") ? "Setoran Non Tunai Paperless Dari [". $cRekeningAsal ."] Ke [". $cRekening ."] ". $cNama : "Penarikan Non Tunai Paperless Dari [". $cRekening ."] ".$cNama." Ke [". $cRekeningAsal ."] " ;
						}
					}else{
						$cFaktur    		= Func::GetLastFaktur("SP",true,$dTgl);
						$cMetodeTransaksi = ($cJenisTrans == "01") ? "ST" : "PT";
						$cKeterangan	= ($cJenisTrans == "01") ? "Setoran Tunai Paperless [". $cRekening ."] ". $cNama : "Penarikan Tunai Paperless [". $cRekening ."] ". $cNama ;
					}
					if($cYgTransaksi == "2"){
						$cKeterangan = "Transaksi Perwakilan ". $cNamaWakil ." ". $cKeterangan;
					}
					UpdMutasiTabungan::Save($cFaktur."|".$_POST['NomorAntrian'],$dTgl,$cRekening,$cJenisTrans,$cKeterangan,$nMutasi,$cCabang,false,"","","","",true,"","",false,0,"",true) ;
					$vaReturn["04"] = array("Faktur"=>$cFaktur,"NomorAntrian"=>$_POST['NomorAntrian']);
					$vaLog = array("Status"=>"1","Jenis"=>$cMetodeTransaksi,"FakturPaper"=>$cFaktur,"TTD"=>$cTTD,"Picture"=>$cFoto,"request"=>json_encode($_POST),"Response"=>json_encode($vaReturn["04"]),"DateTime"=>date("Y-m-d H:i:s"));
					objData::Update("paperless_log",$vaLog,"FakturPaper = '$cFaktur'");
				}
				else if($value == '05'){
					$dTgl				=	date("Y-m-d");
					$cRekening	=	$_POST["cRekening"];
					$cNama			=	$_POST["cNama"];
					$nMutasi		= $_POST["nMutasi"];
					$cCabang		= $_POST["Cabang"];
					$cFaktur    = Func::GetLastFaktur("TP",true,$dTgl) ;
					$cKeterangan= "Penarikan Tunai [". $cRekening ."] ". $cNama ;
					
					UpdMutasiTabungan::Save($cFaktur,$dTgl,$cRekening,aCfg::Get("msKodePenarikanTunai"),$cKeterangan,$nMutasi,$cCabang,false,"","","","",true,"","",false,0,"",true) ;
					$vaReturn["05"] = array("Faktur"=>$cFaktur);
				}
				else if($value == '06'){
					$dbData	= objData::ABrowse("paperless_teller","*") ;
					
					$vaReturn["06"] = $dbData;
				}else if($value == '07'){
					unset($data["Code"]);
					foreach($data as $key => $value){
						$key = str_replace("_"," ",$key);
						$value = explode(";",$value);
						$cTable = $value[0];
						$cField = isset($value[2]) ? $value[2] : "Kode,Keterangan";
						$cField = $cField != "" ? $cField : "Kode,Keterangan";
						$cWhere = isset($value[1]) ? $value[1] : "";
						$dbData = objData::Browse($cTable,$cField,$cWhere);
						while($dbRow = objData::GetRow($dbData)){
							$va07[$key][] = $dbRow;
						}
					}
					//RabbitMQ::SendMessage("tabel",$va28);
					//return Auth::Response("200","Berhasil",$va28);
					$vaReturn["07"] = $va07;
				}else if($value == '08'){ //register nasabah dari paperless
					//return Auth::Response("200","aaaa",$_POST);
					$cFaktur     		= Func::GetLastFaktur("AN",true,date("Y-m-d"),"01") ;
					$cTTD						= $_POST["cTTD"] ;
					$cFoto					= $_POST["cFoto"] ;
					$vaArray	= array("KodeLama"=>$_POST['NomorAntrian'],"Kode"=>$cFaktur,"CabangEntry"=>$_POST['cCabang'],"tgl"=>date("Y-m-d"),"Jenis"=>"P","Nama"=>$_POST['cNamaRegister'],"NamaAlias"=>$_POST['cNamaAlias'],"Gelar"=>$_POST['cGelar'],"KeteranganGelar"=>$_POST['cKetGelar'],"Kelamin"=>$_POST['optJenisKelamin'],
												 		"GolonganDarah"=>$_POST['optGolonganDarah'],"TempatLahir"=>$_POST['cTempatLahir'],"TglLahir"=>$_POST['dTglLahir'],"StatusPerkawinan"=>$_POST['optStatusKawin'],
														"Agama"=>$_POST['cAgama'],"Pekerjaan"=>$_POST['cPekerjaan'],"StatusPegawai"=>$_POST['cStatusPegawai'],"DetailPekerjaan"=>$_POST['cDetailPekerjaan'],"Fax"=>$_POST['cFax'],
														"StatusKewarganegaraan"=>$_POST['optKewargaNegaraan'],"KodeNegara"=>$_POST['cNegara'],"KTP"=>$_POST['cNoKTP'],"TglKTP"=>$_POST['dTglBerlakuKTP'],"PekerjaanPasangan"=>$_POST['cPekerjaanPasangan'],
														"Paspor"=>$_POST['cNoPaspor'],"NPWP"=>$_POST['cNoNPWP'],"Email"=>$_POST['cEmail'],"Telepon"=>$_POST['cNoTelepon'],"HP"=>$_POST['cNoHP'],"Alamat"=>$_POST['cAlamatKTP'],
														"Kodya"=>$_POST['cKota'],"Kecamatan"=>$_POST['cKecamatan'],"Kelurahan"=>$_POST['cKelurahanKTP'],"RTRW"=>$_POST['cRTRW'],"KodePos"=>$_POST['cKodePos'],"AlamatTinggal"=>$_POST['cAlamatT'],"KodyaTinggal"=>$_POST['cKotaT'],
														"KecamatanTinggal"=>$_POST['cKecamatanT'],"KelurahanTinggal"=>$_POST['cKelurahanT'],"RTRWTinggal"=>$_POST['cRTRWT'],"NamaPasangan"=>$_POST['cNamaPasangan'],"TempatLahirPasangan"=>$_POST['cTempatLahirPasangan'],"TglLahirPasangan"=>$_POST['dTglLahirPasangan'],
														"KTPPasangan"=>$_POST['cNoKTPPasangan'],"TglKTPPasangan"=>$_POST['dTglBerlakuKTPPasangan'],"TglMenikah"=>$_POST['dTglMenikah'],"AktaMenikah"=>$_POST['cAktaMenikah'],
														"AlamatPasangan"=>$_POST['cAlamatPasangan'],"KodyaPasangan"=>$_POST['cKotaPasangan'],"KecamatanPasangan"=>$_POST['cKecamatanPasangan'],"KelurahanPasangan"=>$_POST['cKelurahanPasangan'],"RTRWPasangan"=>$_POST['cRTRWPasangan'],"PisahHarta"=>$_POST['optPerjanjianPisahHarta'],
														"NamaKantor"=>$_POST['cNamaPerusahaan'],"AlamatKantor"=>$_POST['cAlamatPerusahaan'],"KodyaKantor"=>$_POST['cKotaPerusahaan'],"KecamatanKantor"=>$_POST['cKecamatanPerusahaan'],"KelurahanKantor"=>$_POST['cKelurahanPerusahaan'],"RTRWKantor"=>$_POST['cRTRWPerusahaan'],
														"TeleponKantor"=>$_POST['cTelfonPerusahaan'],"FaxKantor"=>$_POST['cFaxPerusahaan'],"KodePenghasilan"=>$_POST['optSumberPenghasilan'],"NominalGaji"=>$_POST['nGaji'],
														"NominalPendapatanLainnya"=>$_POST['nPendapatanLainnya'],"PenghasilanKotor"=>$_POST['nPenghasilanKotor'],"IbuKandung"=>$_POST['cNamaIbuKandung'],"JumlahTanggungan"=>$_POST['cJumlahTanggungan'],
														"NamaKeluarga"=>$_POST['cNamaKeluarga'],"TeleponKeluarga"=>$_POST["cNoTeleponKeluarga"],"AlamatKeluarga"=>$_POST['cAlamatKeluarga'],"KodyaKeluarga"=>$_POST['cKotaKeluarga'],"KecamatanKeluarga"=>$_POST['cKecamatanKeluarga'],"KelurahanKeluarga"=>$_POST['cKelurahanKeluarga'],"RTRWKeluarga"=>$_POST['cRTRWKeluarga']
													 ) ;
					objData::Insert("registernasabah_tmp",$vaArray) ;
					/*$cKode = Func::GetLastNumber('5001',9,false);
					$vaGambarTTD = array("Kode"=>$cKode,"Username"=>"","Picture"=>$cTTD,"StatusTeller"=>"1","FotoUtama"=>"0");
					objData::Insert("picture_tmp",$vaGambarTTD);
					$vaGambarFoto = array("Kode"=>$cKode,"Username"=>"","Picture"=>$cFoto,"StatusTeller"=>"1","FotoUtama"=>"0");
					objData::Insert("picture_tmp",$vaGambarFoto);*/
					$vaReturn["08"] = array("validasi"=>"ok","Faktur"=>$cFaktur,"sql"=>mysql_error()) ;
					$vaLog = array("Status"=>"1","Jenis"=>"RGT","FakturPaper"=>$_POST['cNoKTP'],"TTD"=>$cTTD,"Picture"=>$cFoto,"request"=>json_encode($_POST),"Response"=>json_encode($vaReturn["08"]),"DateTime"=>date("Y-m-d H:i:s"));
					objData::Update("paperless_log",$vaLog,"FakturPaper = '$cFaktur'");
				}else if($value == '09'){ //Get Nomor Antrian
					$dTgl		 =	date("Y-m-d");
					$cFaktur = Func::GetLastFaktur("NA",true,$dTgl);
					$vaReturn["09"] = array("Faktur"=>$cFaktur,"NomorAntrian"=>$_POST['NomorAntrian']);
					$vaLog 	 = array("Status"=>"1","Jenis"=>"NA".$_POST["cJenis"],"Cabang"=>$_POST["cCabang"],"FakturPaper"=>$cFaktur,"TTD"=>$_POST['NomorAntrian'],"request"=>json_encode($_POST),"Response"=>json_encode($vaReturn["09"]),"DateTime"=>date("Y-m-d H:i:s"));
					objData::Update("paperless_log",$vaLog,"FakturPaper = '$cFaktur'");
				}
				else{
					return Auth::Response("200","Kode Proses Tidak Ditemukan");
				}
			}
			return Auth::Response("200","Berhasil",$vaReturn);
		}else{
			return Auth::Response("200","Kode Proses Tidak Ditemukan");
		} 
	}

	function ResetCadangan($vaData){  
		$cRekening = $vaData['Rekening'] ;
		$cRekening = explode(";",$cRekening);
		$dTgl      = Date2String($vaData['Tgl']) ; 
		$vaTable   = array("Tabungan"=>"Tabungan","Kredit"=>"debitur","Deposito"=>"Deposito");
		$vaJenis   = array("Tabungan"=>"Tabungan","Kredit"=>"Kredit","Deposito"=>"Deposito");
		$vaFlag    = array("Tabungan"=>"UpdFlagTabungan","Deposito"=>"UpdFlagDeposito","Kredit"=>"UpdFlagDebitur") ;
		//VALIDASI REKENING
		foreach($cRekening as $keys=>$value){
			foreach($vaJenis as $key){
				if($vaData['Jenis'] == $key){
					$vaTable[$key] = strtolower($vaTable[$key]);
					$dbData = objData::Browse($vaTable[$key],"Rekening","Rekening = '$value'");
					if(objData::Rows($dbData) <= 0){
						return Auth::Response("404","Rekening $value Tidak Ditemukan"); 
					}
				}
			}
		}
		foreach($cRekening as $keys=>$value){
			foreach($vaJenis as $key){
				if($vaData['Jenis'] == $key){
					$cClass = $vaFlag[$key];
					$cClass::Save($value,$dTgl) ;
				}
			}
		}
		return Auth::Response("200","Berhasil");
	}

	function LockKolek($vaData){
		$cRekening = $vaData['Rekening'] ;
		$cRekening = explode(";",$cRekening);
		foreach($cRekening as $keys=>$value){
			$dbD = objData::Browse("debitur","Rekening","Rekening = '$value'") ;
			if($vaRow = objData::Rows($dbD) <= 0){
				return Auth::Response("404","Rekening $value Tidak Ditemukan"); 
			}
			$dbData = objData::Browse("debitur_kolektibilitas_manual","Rekening","Rekening = '$value'") ;
			if($vaRow = objData::Rows($dbData)){
				return Auth::Response("404","Rekening $value Sudah Ada"); 
			}
		}
		foreach($cRekening as $keys=>$value){
			$vaArray     = array("Tgl"=>$vaData['Tgl'],"TglAkhir"=>$vaData['TglAkhir'],"Rekening"=>$value,"Kol"=>$vaData['Kol'],"Status"=>1,"Keterangan"=>$vaData['NoWO']) ;
			$InsertData = objData::Insert("debitur_kolektibilitas_manual",$vaArray) ;    
			UpdFlagDebitur::Save($vaData['Rekening'],$vaData['Tgl']) ;
		}
		return Auth::Response("200","Berhasil di Lock") ;
	}

	function OpenKolek($vaData){
		$cRekening = $vaData['Rekening'] ;
		$cRekening = explode(";",$cRekening) ;
		foreach($cRekening as $keys=>$value){
			$dbD = objData::Browse("debitur","Rekening","Rekening = '$value'") ;
			if($vaRow = objData::Rows($dbD) <= 0){
				return Auth::Response("404","Rekening $value Tidak Ditemukan Di Tabel Debitur"); 
			}
			$dbData = objData::Browse("debitur_kolektibilitas_manual","Rekening","Rekening = '$value'") ;
			if($vaRow = objData::Rows($dbData) <= 0){
				return Auth::Response("404","Rekening $value Tidak Ditemukan Di Tabel Debitur Kolek Manual"); 
			}
		}
		foreach($cRekening as $keys=>$value){
			$vaArray     = array("TglAkhir"=>$vaData['TglAkhir']) ;
			$UpdateData = objData::Update("debitur_kolektibilitas_manual",$vaArray,"Rekening = '$value'") ;
			UpdFlagDebitur::Save($vaData['Rekening'],$vaData['TglAkhir']) ;
		}
		return Auth::Response("200","Berhasil Open") ;
	}
}

<?php
  include 'df.php' ;
class Func{

	static function dd(...$vaData){
		
		echo "<br><pre style='background: #f9f9f9; border: 1px solid #ccc; padding: 10px;'>"; 
		//Pecah array 
		foreach ($vaData as $cKey) {
			print_r($cKey);
		}
		echo "</pre>";     

		//Lacak File yang error
		ob_start(); 
		$backtrace 	 = debug_backtrace();
		$logMessage  = 'File: ' . $backtrace[0]['file'] . ' | Line: ' . $backtrace[0]['line'] . "\n";
		$output 		 = ob_get_clean();  
		$logMessage .= $output . "\n\n";  
		//echo "<pre>" . htmlspecialchars($logMessage, ENT_QUOTES) . "</pre>"; 
		//die();
	}
 	
	static function GetFileName($path) {
		$path_parts = pathinfo($path);
		$filename_without_extension = $path_parts['filename'];
		return $filename_without_extension;
	}
	
	static function BOM($dTgl){ 
		$day = String2Date($dTgl) ;
		$dBulan = substr($day,3,2) ;
		$dTahun = substr($day,6,4) ;

		$d = date('d-m-Y',mktime(0,0,0,$dBulan,1,$dTahun));

		return $d ;
	}
	
	static function CheckTableTabungan($cTableName,$dTgl){   
		$nTahun         = intval(date('Y',Func::Tgl2Time($dTgl))) ;
		$cTableName     = strtolower(trim($cTableName)) ;
		$dbData         = objData::SQL("show tables like '".$cTableName."_2%'") ;
		while($dbRow = objData::FetchArray($dbData)){
			$vaRow = explode("_",$dbRow[0]) ;
			if(!isset($cTableNameBaru)){
				if($vaRow[1] >= $nTahun){
					$cTableNameBaru = $dbRow[0];
				}
			}
		}
	  if(isset($cTableNameBaru)) $cTableName = $cTableNameBaru ;
		return $cTableName ;
	}
	
	static function CreateImage(){
		$cKey = "1234567890" ;
		$c = substr( str_shuffle($cKey) ,0,5) ;  
		$nWidth = 60 ;
		$nHeight = 20 ;
		$nFontSize = 5 ;

		$im = imagecreate($nWidth, $nHeight) ;
		$background_color = imagecolorallocate($im,220,220,220) ;
		$noise_color = imagecolorallocate($im, 165, 180, 219) ;
		for( $i=0; $i<($nWidth*$nHeight)/3; $i++ ){
			imagefilledellipse($im, mt_rand(0,$nWidth), mt_rand(0,$nHeight), 1, 1, $noise_color) ;
		}

		for( $i=0; $i<($nWidth*$nHeight)/150; $i++ ) {
			imageline($im, mt_rand(0,$nWidth), mt_rand(0,$nHeight), mt_rand(0,$nWidth), mt_rand(0,$nHeight), $noise_color) ;
		}

		$nLeft = ($nWidth - (imagefontwidth($nFontSize) * 5)) / 2 ;
		$text_color = imagecolorallocate($im,0,0,0) ;
		imagestring($im, $nFontSize, $nLeft, 2,$c, $text_color) ;

		header("Content-type: image/png");
		imagepng($im);
		imagedestroy($im);

		return $c ;
	} 
	
	static function DeleteCDS($cKode){
		$cKey       = aCfg::Get("msCDSID") ;
		$cTime      = date("c") ;
		$cVersion   = "1.0" ;
		$cSignature = md5("$cKey:$cTime:$cVersion:") ;

		$cURL = curl_init();
		curl_setopt($cURL, CURLOPT_URL, "http://aa.cds.sis1.net/cds/delete/");
		curl_setopt($cURL, CURLOPT_HTTPHEADER,
			array(
					"Content-Type:application/json",
					"SIS-Key: $cKey",
					"SIS-Data-Type:JSON",
					"SIS-Timestamp:$cTime",
					"SIS-Version:$cVersion",
					"SIS-Signature:$cSignature",
					"SIS-Kode:$cKode",
			)
		);
		$cData = curl_exec($cURL);

		$cBody = "" ;
		if(!curl_errno($cURL)){    
			$vaURL = curl_getinfo($cURL);
			if(isset($vaURL ["header_size"])){
				$cBody = substr($cData, $vaURL ["header_size"]);
			}
		}else{
			$cBody = 'Curl error: ' . curl_error($cURL) ;
		}
		curl_close($cURL);

		$vaBody = json_decode($cBody,true) ;
		if(isset($vaBody ["ResponseCode"]) && intval($vaBody["ResponseCode"]) == 100){
			$vaData = json_decode($vaBody ["Data"],true) ;
		} 
	}
	
	static function EOM($dTgl){
		$day = String2Date($dTgl) ;
		$dBulan = substr($day,3,2) ;
		$dTahun = substr($day,6,4) ;

		$d = date('d-m-Y',mktime(0,0,0,$dBulan+1,0,$dTahun));

		return $d ;
	}
	
	static function GetBulanOption($cKey){
		$va = array("Januari","Februari","Maret","April","Mei","Juni","Juli","Agustus","September","Oktober","November","Desember") ;
		$n = 0 ;
		foreach($va as $key=>$value){
			++$n ;
			$cSelected = "" ;
			if($cKey == $n){
				$cSelected = " selected " ;
			}
			echo('<option value="' . $n . '"' . $cSelected . '>' . $value . '</option>') ;    
		}
	}
	
	static function GetCDS($cKode,$cDefault=""){
		$cResult    = $cDefault ;
		$cKey       = aCfg::Get("msCDSID") ;
		$cTime      = date("c") ;
		$cVersion   = "1.0" ;
		$cSignature = md5("$cKey:$cTime:$cVersion:") ;

		$cURL = curl_init();
		curl_setopt($cURL, CURLOPT_URL, "http://aa.cds.sis1.net/cds/download/");
		curl_setopt($cURL, CURLOPT_RETURNTRANSFER, true );
		curl_setopt($cURL, CURLOPT_HTTPHEADER,
			array(
					"Content-Type:application/json",
					"SIS-Key: $cKey",
					"SIS-Data-Type:JSON",
					"SIS-Timestamp:$cTime",
					"SIS-Version:$cVersion",
					"SIS-Signature:$cSignature",
					"SIS-Kode:$cKode",
			)
		);
		$cBody = curl_exec($cURL);
		curl_close($cURL);
		if($cBody <> ""){
			$vaBody = json_decode($cBody,true) ; 
			if(isset($vaBody['ResponseCode'])){
				if($vaBody['ResponseCode'] == 100){
					if(isset($vaBody['Data'])){
						$cResult = $vaBody['Data'][$cKode]  ;
					}
				}
			}
		}
		return $cResult ;
	}
	
	static function GetDataUserOld($cUsername=''){
		$va     = array("FullName"=>"","UserPassword"=>"","KasKecil"=>"","KasTeller"=>"","KasUtama"=>"","Cabang"=>"",
										"KantorInduk"=>"","LevelOtoritas"=>"","KodeCIF"=>"","AO"=>"","SandiBank"=>"","Mnu_PembukaanTab"=>"","Mnu_RegisterDep"=>"",
										"Mnu_PengajuanKrd"=>"","Mnu_PengikatanJmKrd"=>"","Mnu_NasabahCari"=>"","Mnu_TabunganPreview"=>"","Mnu_DepositoPreview"=>"",
										"Mnu_KreditPreview"=>"","Mnu_KreditRincian"=>"","Mnu_KreditJadwal"=>"","Mnu_KreditAktivitas"=>"","SandiBank"=>"","UserName"=>"",
										"UserPassword"=>"","Jenis"=>"","KantorPelayanan"=>"","Show_SaldoTabungan"=>"1") ;
		$cUser  = empty($cUsername) ? GetSetting("cSession_UserName") : $cUsername ;
		
		$cField = "u.FullName,u.UserPassword,u.KasKecil,u.KasTeller,u.KasUtama,u.KantorKas Cabang,u.Cabang KantorInduk,u.LevelOtoritas,u.KodeCIF,u.AO,c.SandiBank,
							 l.Mnu_PembukaanTab,l.Mnu_RegisterDep,l.Mnu_PengajuanKrd,l.Mnu_PengikatanJmKrd,l.Mnu_NasabahCari,l.Mnu_TabunganPreview,
							 l.Mnu_DepositoPreview,l.Mnu_KreditPreview,l.Mnu_KreditRincian,l.Mnu_KreditJadwal,l.Mnu_KreditAktivitas,c.SandiBank,u.UserName,u.UserPassword,
							 c.Jenis,u.KantorPelayanan,l.Show_SaldoTabungan" ;
		$vaJoin = array("left join cabang c on c.Kode = u.Cabang
										 left join username_limitplafond l on l.Username = u.Username") ;
		$dbData = objData::Browse("username u",$cField,"u.UserName = '$cUser'",$vaJoin) ;
		if($dbRow = objData::GetRow($dbData)){
			$dbRow['UserLevel'] = substr($dbRow['UserPassword'],10,4) ; 
			unset($dbRow['UserPassword']) ;
			$va = $dbRow ;  
		}
		return $va ;
	}
	
	static function GetDataUser($cUsername=''){
		$cUser  = User::Get("UserName") ;
		$va     = array("FullName"=>"","UserPassword"=>"","KasKecil"=>"","KasTeller"=>"","KasUtama"=>"","Cabang"=>"",
											"KantorInduk"=>"","LevelOtoritas"=>"2","KodeCIF"=>"","AO"=>"","SandiBank"=>"","Mnu_PembukaanTab"=>"","Mnu_RegisterDep"=>"",
											"Mnu_PengajuanKrd"=>"","Mnu_PengikatanJmKrd"=>"","Mnu_NasabahCari"=>"","Mnu_TabunganPreview"=>"","Mnu_DepositoPreview"=>"",
											"Mnu_KreditPreview"=>"","Mnu_KreditRincian"=>"","Mnu_KreditJadwal"=>"","Mnu_KreditAktivitas"=>"","SandiBank"=>"","UserName"=>"",
											"UserLevel"=>"","Jenis"=>"","KantorPelayanan"=>"","Show_SaldoTabungan"=>"1") ;
		if(!empty($cUser)){
			foreach($va as $key=>$value){
				$va[$key] = User::Get($key) ;
			}
		}else{
			if(empty($cUsername)) $cUsername = GetSetting("cSession_UserName") ;
			$cField = "u.FullName,u.UserPassword,u.KasKecil,u.KasTeller,u.KasUtama,u.KantorKas Cabang,u.Cabang KantorInduk,u.LevelOtoritas,u.KodeCIF,u.AO,c.SandiBank,
								 l.Mnu_PembukaanTab,l.Mnu_RegisterDep,l.Mnu_PengajuanKrd,l.Mnu_PengikatanJmKrd,l.Mnu_NasabahCari,l.Mnu_TabunganPreview,
								 l.Mnu_DepositoPreview,l.Mnu_KreditPreview,l.Mnu_KreditRincian,l.Mnu_KreditJadwal,l.Mnu_KreditAktivitas,c.SandiBank,u.UserName,u.UserPassword,
								 c.Jenis,u.KantorPelayanan,l.Show_SaldoTabungan" ;
			$vaJoin = array("left join cabang c on c.Kode = u.Cabang
											 left join username_limitplafond l on l.Username = u.Username") ;
			$dbData = objData::Browse("username u",$cField,"u.UserName = '$cUsername'",$vaJoin) ;
			if($dbRow = objData::GetRow($dbData)){
				$dbRow['UserLevel'] = substr($dbRow['UserPassword'],10,4) ; 
				unset($dbRow['UserPassword']) ;
				$va = $dbRow ;  
				
				//Simpan Ke User
				foreach($va as $key=>$value){
					User::Save($key,$value) ;
				}
			}
		}
		return $va ;
	}
	
	static function GetOtorisasiIcon(){
		$lOtor      = false ;
		$vaUser     = Func::GetDataUser() ;
		$vaUserOtor = GetKewenanganUser::Get(GetSetting("cSession_UserName")) ;
		foreach($vaUserOtor as $key=>$value){
			if(substr($key,0,3) == "OTO"){
				if($value){
					$lOtor = true ;
					break;
				}
			}
		}
		$lOtor  = ($vaUser['LevelOtoritas'] == 1 && aCfg::Get("msOtorisasiDireksi",1) == 1) ? true : $lOtor ;
		return $lOtor;
	}
	
	static function GetDay($nTime){
		$va = array("Minggu","Senin","Selasa","Rabu","Kamis","Jum`at","Sabtu") ;
		$vaTgl = getdate($nTime) ;

		return $va [$vaTgl ['wday']] ;
	}
	
	static function GetDayAwal($nTime){
		$nDay = date("d",$nTime) ;
		$nMonth = date("m",$nTime) ;
		$nYear = date("Y",$nTime) ;

		$n1 = mktime(0,0,0,$nMonth,$nDay-1,$nYear) ;

		return $n1 ;
	}
	
	static function GetFrekuensiProduk($cKode,$cGolongan,$nLen,$cCabang="",$lKonversi=false){
		$vaUser  = self::GetDataUser(GetSetting("cSession_UserName")) ;
		$cKantor = $vaUser['KantorInduk'] ;

		$nFrekuensi = 1 ;
		if($cCabang == "") $cCabang = substr($cKode,0,2) ;
		if($lKonversi && $cCabang <> ""){
			$cKantor = $cCabang ;
		}
		$vaTable= array("tabungan"=>"GolonganTabungan","deposito"=>"GolonganDeposito","debitur"=>"GolonganKredit","antarbank"=>"Golongan","agunan"=>"-","pengajuankredit"=>"-") ;
		foreach($vaTable as $key=>$value){
			if($cGolongan !== "agunan" && $cGolongan !== "pengajuankredit"){   
				if($value !== "-"){
					//Seharusnya menggunakan yg bawah
					$cUrut          = self::GetLastIDReg($cKantor,$cKode,6,false) ; // ingant ini harus diganti sama sudah konversi
					$cWhereRekening = $cCabang.".".$cGolongan.".".$cUrut ; 
					$cWhere         = "$value = '$cGolongan' and Rekening like '$cWhereRekening%'" ;
					$dbData = objData::Browse($key,"Right(Rekening,3) as Frekuensi",$cWhere,"","","Rekening desc") ; 

					if($dbRow = objData::GetRow($dbData)){  
						$nFrekuensi = $dbRow['Frekuensi'] + 1 ;
					}
				}
			}else if($key == $cGolongan){ 
				$cUrut  = self::GetLastIDReg($cKantor,$cKode,6,false) ; 
				$cWhere = $cCabang.".".$cUrut ; 
				$cWhere = "Rekening like '$cWhere%'" ;  
				//echo($cGolongan) ; 
				$dbData = objData::Browse($cGolongan,"Rekening",$cWhere,"","","Rekening desc") ;   
				if($dbRow = objData::GetRow($dbData)){  
					$nFrekuensi = intval(substr($dbRow['Rekening'],-$nLen)) + 1 ;  
				}
			}
		} 

		$nFrekuensi = str_pad($nFrekuensi,$nLen,"0",STR_PAD_LEFT) ; 
		return $nFrekuensi ;
	}
	
	static function GetFullDate($dTgl){
		$nTgl = substr($dTgl,0,2);
		$nBulan = substr($dTgl,3,2);
		$nTahun = substr($dTgl,6,4);
		$cBulan = self::GetMonth($nBulan);

		$cTgl = $nTgl . " " . $cBulan . " " . $nTahun;

		return $cTgl ; 
	}
	
	static function GetHari($nHari){
		$n = min(max(strval($nHari) - 1,0),6) ;  
		$vaHari = array("Minggu","Senin","Selasa","Rabu","Kamis","Jum'at","Sabtu","Minggu") ;

		return $vaHari[$n] ;
	}
	
	static function GetIcon($cFile){
		$nDot = strrpos($cFile,".") + 1 ;
		$cExt = strtolower(substr($cFile,$nDot)) ;
		if(!is_file("./images/" . $cExt . ".gif")){
			$cFile = "unknow.gif" ;
		}else{
			$cFile = $cExt . ".gif" ;
		}
		return '<img src="./images/' . $cFile . '" border = "0">' ;
	}
	
	static function GetKe($dTglAwal,$dTgl,$nLama,$cJenisPembayaran="B"){ 
		$dTglAwal = String2Date($dTglAwal);
		$dTgl     = String2Date($dTgl);
		$nTglAwal = strtotime($dTglAwal) ;
		$nTgl     = strtotime($dTgl) ;
		$nKe = 0 ;
		$x   = 0 ;
		while($x <= $nTgl){
			$nKe ++ ;
			if($cJenisPembayaran == "M"){
				$x = NextWeek($nTglAwal,$nKe) ;
			}else{
				$x = NextMonth($nTglAwal,$nKe) ;
			} 
		}
		$nKe -- ;
		return min(max($nKe,0),$nLama) ;
	}   
	
	static function GetKeterangan($cKode,$cField,$cTable,$Kode="",$cDefault=""){
		$cField = $cField." as Keterangan" ;
		$cTable = strtolower($cTable);

		$cWhere = "kode = '$cKode'" ; 
		$cWhere = trim($Kode) !== "" ? $Kode . " = '$cKode'" : $cWhere ;

		$cValue = "" ;
		$dbData = $dbData = objData::Browse($cTable,$cField,$cWhere) ;
		if($dbRow = objData::GetRow($dbData)){
			$cValue = $dbRow['Keterangan'] ;
		}
		if(($cDefault != "" || $cDefault == 0 ) && $cValue == "" ) $cValue = $cDefault;
		return $cValue ;
	}
	
	static function GetRekeningATM($cRekening){
		//KOSONG
		if($cRekening != ""){
			$dbData = objData::Browse("tabungan","Rekening","RekeningLama = '$cRekening'");
			if(objData::Rows($dbData) <= 0){ 
				$cRekening = self::GetKeterangan($cRekening,"Rekening","tabungan","REPLACE(Rekening, '.', '')"); // replace .....
			}else{
				$dbRow = objData::GetRow($dbData);
				$cRekening = $dbRow["Rekening"];
			}
		}
		return $cRekening;
	}
	
	static function GetLastFaktur($cKey,$lUpdate = true,$dTgl='',$cCabang=''){
		$nTime = time(); //now() ;
		if($dTgl !== ''){
			$nTime = self::Tgl2Time($dTgl) ;
		}

		if($cCabang == ''){   
			$vaUser  = self::GetDataUser(GetSetting("cSession_UserName")) ;
			$cCabang = $vaUser['KantorInduk'] ;
		}
		$cKey = $cKey . $cCabang . date("Ymd",$nTime) ; 
		$cRetval = self::GetLastNumber($cKey,20,$lUpdate) ;
		$cRetval = $cKey . substr($cRetval,strlen($cKey)) ;
		return $cRetval ;
	}
	
	static function GetLastIDReg($cCabang,$cKode,$nLen,$lUpdate = true){  
		$nID = 1 ;  
		$cWhere = "Kode = '$cKode' and Cabang = '$cCabang' and Aktif = '1'" ; 
		$dbData = objData::Browse("registernasabah_rekening","IDKode",$cWhere) ; 
		if($dbRow = objData::GetRow($dbData)){
			 $nID = $dbRow['IDKode'] ;  
		}else{
			$dbD = objData::Browse("registernasabah_rekening","IDKode","Cabang = '$cCabang' and Status = 'O'") ;
			$nID = objData::Rows($dbD) + 1 ; 
			
			// Cek apakah generate system sudah teregister (nomor cantik / manual)
			while(true){
				$dbCheck = objData::Browse("registernasabah_rekening","IDKode","Cabang = '$cCabang' and IDKode = '$nID'") ; 
				if(!objData::GetRow($dbCheck)){
					break; // IDKode belum terpakai
				}
				$nID++; // IDKode sudah ada
			}

			if($lUpdate) objData::Update("registernasabah_rekening",array("Cabang"=>$cCabang,"Kode"=>$cKode,"IDKode"=>$nID,"Status"=>'O',"Aktif"=>1),$cWhere,false) ;
		}

		$cKode = str_pad($nID,$nLen,"0",STR_PAD_LEFT) ;
		return $cKode ;
	}
	
	static function GetLastNumber($cKey,$nLen,$lUpdate = true){  
		$nID   = 0 ;
		$cKode = "" ;
    
		
		$cTypeDB = aCfg::Get("msConfigDatabase",0);
		if($cTypeDB){ 
			//Kondisi untuk type database Innodb
			objData::SQL("LOCK TABLES nomorfaktur WRITE");
			$dbData   = objData::Browse("nomorfaktur","ID","Kode = '$cKey' FOR UPDATE") ;  
			if($dbRow = objData::GetRow($dbData)){
				$nID = $dbRow ['ID'] + 1 ; 
			}else{
				$nID = 1 ;
			}
			if($lUpdate) objData::Update("nomorfaktur",["Kode"=>$cKey,"ID"=>$nID],"Kode = '$cKey'",false) ; 
			objData::SQL("UNLOCK TABLES");
		}else{
			//Kondisi untuk type database MyISAM
			if($lUpdate && substr($cKey,0,4) <> "5001"){
				objData::Insert("nomorfaktur",array("Kode"=>$cKey),false) ;

				$dbData = objData::Browse("nomorfaktur","last_insert_id() as ID","Kode = '$cKey'") ;
				if($dbRow = objData::GetRow($dbData)){
					$nID = $dbRow['ID'] ;
					objData::Delete("nomorfaktur","Kode = '$cKey' and ID < $nID",false) ;    
				}
			}else{
				$dbData   = objData::Browse("nomorfaktur","ID","Kode = '$cKey'") ;  
				if($dbRow = objData::GetRow($dbData)){
					$nID = $dbRow ['ID'] + 1 ; 
				}else{
					$nID = 1 ;
				}
				if($lUpdate){
					objData::Update("nomorfaktur",["Kode"=>$cKey,"ID"=>$nID],"Kode = '$cKey'",false) ; 
				}
			}   
		}
		  
		$cKode = str_pad($nID,$nLen,"0",STR_PAD_LEFT) ;
		return $cKode ;	
	}
	
	static function isHoliday($nTime){
		$vaTgl = getdate($nTime) ;
		$lRetval = false ;
		if($vaTgl ['wday'] == 0 || $vaTgl ['wday'] == 6){
			$lRetval = true ;
		}else{
			$cTgl = date("Y-m-d",$nTime) ;
			$dbData = objData::Browse("harilibur","Tgl","Tgl = '$cTgl'") ;
			if(objData::Rows($dbData) > 0){
				$lRetval = true ;
			}
		}
		return $lRetval ;
	}

	static function GetTotalHoliday($dTglAwal,$dTglAkhir){
		$nHoliday = 0 ;
		$nTglAwal = self::Tgl2Time($dTglAwal) ;
		$nTglAkhir = self::Tgl2Time($dTglAkhir) ;
		for($n=$nTglAwal;$n<=$nTglAkhir;$n+=86400){
			if(self::isHoliday($n)) $nHoliday ++ ;
		}
		return $nHoliday ;
	}
	 
	static function GetMonth($nBulan){
		$n = min(max(strval($nBulan) - 1,0),11) ;
		$vaMonth = array("Januari","Februari","Maret","April","Mei","Juni","Juli","Agustus","September","Oktober","November","Desember") ;

		return $vaMonth[$n] ;
	}
 
	static function GetSize1($nSize){
		$cRetval = number_format($nSize,2) . " B" ;

		$vaSize = array("KB", "MB", "GB", "TB") ;
		$n = 1024 ;
		foreach($vaSize as $key=>$value){
			if ($nSize > $n){
				$nSize = $nSize / $n ;
				$cRetval = number_format($nSize,2) . " " . $value ;
			}
			$n = 1000 ;
		}
		return $cRetval ;
	}
	
	static function GetTahunOption($cKey){
		$nYear = date("Y",now())+5 ;
		for($n=1945;$n<=$nYear;$n++){
			$cSelected = "" ;
			if($cKey == $n){
				$cSelected = " selected " ;
			}
			echo('<option value="' . $n . '"' . $cSelected . '>' . $n . '</option>') ;    
		}
	}
	
	static function GetTglTransaksi(){
		$dTgl = "00-00-0000" ;
		$cCabang = "" ;
		if(empty($cCabang)){
			$cCabang  = aCfg::Get("msKodeCabang");
		}
		$dbData = objData::Browse("TglTransaksi","max(tgl) as tgl","status = '0'");
		if($dbRow = objData::GetRow($dbData)){
			$dTgl = String2Date($dbRow['tgl']);
		}
		/*$dTgl      = date("d-m-Y") ;
		$cUserName = GetSetting("cSession_UserName"); 

		$cWhere    = "UserName = '$cUserName' and Status = '1' and now() BETWEEN DateTimeStart AND DateTimeEnd" ;
		$dbData    = objData::Browse("username_tgltransaksi","Tgl",$cWhere,"","","ID Desc");
		if($dbRow  = objData::GetRow($dbData)){
			$dTgl = String2Date($dbRow['Tgl']);
		}*/
		return $dTgl ; 
	}
	 
	static function IsMenuVisibled($cLevel,$cParent){ 	
		if($cLevel == "0000"){
			$lRetval = true ;
		}else{
			$cLevel  = md5(trim($cLevel)) ;
			$cParent = md5(trim($cParent)) ;
			$dbData  = objData::Browse("username_menu","ID","Level = '$cLevel' and Keterangan = '$cParent'") ;
			$lRetval = objData::Rows($dbData) > 0 ;
		}
  	return $lRetval ;
	}
	
	static function MinMax($cTipe,$cTable,$cField){
		$cTable = strtolower($cTable);
		$dbData = objData::Browse($cTable,"$cTipe($cField) as $cField",'','','',"$cField") ;
		if($dbRow = objData::GetRow($dbData)){
			$cHasil = $dbRow[$cField];      
		}else{
			$cHasil = "" ;
		}
		return $cHasil;
	}
	
	static function RoundDown($nNumber,$nPembulatan){
		$nNumber = ceil($nNumber) ;
		$nSelisih = $nNumber % $nPembulatan;
		$nNumber = max($nNumber - $nSelisih,0) ;

		return $nNumber ;
	}
	
	static function RoundUp($nNumber,$nPembulatan){
		$nNumber = ceil($nNumber) ;
		$nSelisih = $nNumber % $nPembulatan ;
		if($nSelisih <> 0){
			$nNumber += ($nPembulatan-$nSelisih) ;
		}
		return $nNumber ;
	}
	
	static function GetVault($nApiToken){
		$nApiUrl = "https://vault.myassist.id/api/file/";
		$ch      = curl_init();

		curl_setopt_array($ch, [
			CURLOPT_URL => $nApiUrl,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HTTPHEADER => [
				"Authorization: Bearer $nApiToken",
				"Accept: application/json",
			],
		]);

		$cResponse = curl_exec($ch);
		$err       = curl_error($ch);
		curl_close($ch);

		if($err){
			return ['status'=> 'error','message'=> $err];
		}
 
		$vaData = json_decode($cResponse, true);
		return $vaData;
	}
	
	static function GetPicture($cKode){
		$va			= [];
		$nCol 	= 0 ; 
		$nRow 	= 0 ;
		$dbData = objData::Browse("picture","ID,StatusTeller,Picture","Kode = '$cKode' and ID > 0");
		while($dbRow = objData::GetRow($dbData)){
			$nID			= $dbRow['ID'] ;
			$nCol ++ ;
			if($nCol >= 3){
				$nRow ++ ;
				$nCol = 0 ; 
			}
			$va[$nID]['ID']  = $dbRow['ID']; 
			$va[$nID]['Row'] = $nRow; 
			$va[$nID]['Col'] = $nCol; 
			$va[$nID]['StatusTeller'] = $dbRow['StatusTeller']; 
			$va[$nID]['Picture'] 			= $dbRow['Picture']; 
		} 
		return $va;	
	}
	
	static function GetPictureAgunan($cRekening,$cNo){
		$va			= [];
		$nCol 	= 0 ; 
		$nRow 	= 0 ;
		$dbData = objData::Browse("agunan_picture","IDPicture as ID,Picture","Rekening = '$cRekening' and Nomor = '$cNo' and IDPicture > 0");
		while($dbRow = objData::GetRow($dbData)){
			$nID			= $dbRow['ID'] ;
			$nCol ++ ;
			if($nCol >= 3){
				$nRow ++ ;
				$nCol = 0 ; 
			}
			$va[$nID]['ID']  = $dbRow['ID']; 
			$va[$nID]['Row'] = $nRow; 
			$va[$nID]['Col'] = $nCol;   
			$va[$nID]['Picture'] 			= $dbRow['Picture']; 
		} 
		return $va;	
	}
	
	static function LoadImagesNasabah($va){
		$nRow = $va ['nRow'] ;
		$nCol = $va ['nCol'] ;    

		$nAPIToken = aCfg::Get("msVaultToken","");
		$vaPicture  = self::GetPicture($va['cKode']);   

		if($nAPIToken <> "" && !empty($vaPicture)){
			$vaFoto 	  = array_column($vaPicture, 'ID'); 
			$vaFile     = cds::GetFile("","","img","",$nAPIToken,$vaFoto) ;   
			$vaFileByID = array_combine(array_column($vaPicture, 'ID'), $vaFile); 
			$html		    = '<div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; justify-items: center; padding: 5px;">';

			foreach ($vaPicture as $picID => $pic) {
				$filePath = $vaFileByID[$picID];   			 
				$cImg     = self::OpenImageFromFile($filePath, 180, 110); 
				$html .= '<div style="max-width: 100%; padding: 5px; text-align: center; border: 1px solid #ddd; border-radius: 0px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);">';
        $html .= '<div style="margin-bottom: 1px;">' . $cImg . '</div>';
        $html .= '</div>';
			}  
			echo "tbImage.rows[$nRow].cells[$nCol].innerHTML = '".addslashes($html)."';"; 
		}else{
			$vaFile    = cds::GetFile($va['cFile'],aCfg::Get("msCDSID"),"img") ;
			$vaFile   = json_decode($vaFile,true) ;
			if($vaFile['response_code'] == 200){ 
				$cFile = isset($vaFile['data'][$va['cFile']]['url']) ? $vaFile['data'][$va['cFile']]['url'] : "" ;
				$cImg  = self::OpenImageFromFile($cFile,180,110) ;  	

				$html  = '<table width="100%" height="100%" border=0 cellspacing=0 cellpadding=0>' ; 
				$html .= '</tr><tr><td align="center">' . $cImg . '</td></tr></table>' ;
				echo("tbImage.rows[$nRow].cells[$nCol].innerHTML = '$html' ;") ;
			}
		}
	}
		
	static function LoadImagesAgunan($va){
		$nRow 		  = $va['nRow'];
		$nCol 			= $va['nCol'];
		$nAPIToken  = aCfg::Get("msVaultToken", "");
		$vaPicture  = self::GetPictureAgunan($va['cRekening'], $va['cNo']);

		if($nAPIToken !== "" && !empty($vaPicture)) {
			$vaFoto  = array_column($vaPicture, 'ID');
			$vaFile  = cds::GetFile("", "", "img", "", $nAPIToken, $vaFoto);
			$vaFileByID = array_combine(array_column($vaPicture, 'ID'), $vaFile);
			$html		    = '<div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; justify-items: center; padding: 5px;">';
			foreach ($vaPicture as $pic){
				$picID = $pic['ID'];
				if(!isset($vaFileByID[$picID])) {
					continue;
				}
				$filePath = $vaFileByID[$picID];
				$cImg 		= self::OpenImageFromFile($filePath, 180, 110);
				$html .= '<div style="max-width: 100%; padding: 5px; text-align: center; border: 1px solid #ddd; border-radius: 0px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);">';
        $html .= '<div style="margin-bottom: 1px;">' . $cImg . '</div>';
        $html .= '</div>';
			}
			echo "tbImage.rows[$nRow].cells[$nCol].innerHTML = '" . addslashes($html) . "';";
			return;
		}
		$vaFile = cds::GetFile($va['cFile'], aCfg::Get("msCDSID"), "img");
		$vaFile = json_decode($vaFile, true);
		if ($vaFile['response_code'] == 200) {
			$cFile   = isset($vaFile['data'][$va['cFile']]['url']) ? $vaFile['data'][$va['cFile']]['url'] : "";
			$cImg 	 = self::OpenImageFromFile($cFile, 180, 110);
			$html  	 = '<table width="100%" height="100%" border="0" cellspacing="0" cellpadding="0">';
			$html 	.= '<tr><td align="center">' . $cImg . '</td></tr>';
			$html 	.= '</table>';
			echo "tbImage.rows[$nRow].cells[$nCol].innerHTML = '$html';";
		}
	} 
	
	static function OpenImageFromFile($cFileName,$nMaxWidth,$nMaxHeight,$cOnClick=""){
		$cRetval = "&nbsp;" ;
		if($cFileName != ""){      
			$vaImage = getimagesize($cFileName) ;
			$nWidth = $vaImage [0] ;
			$nHeight = $vaImage [1] ;
			$nScale = 100 ;    
			if($nWidth > $nMaxWidth || $nHeight > $nMaxHeight){
				$nScaleWidth = round($nMaxWidth / $nWidth * 100,2) ;
				$nScaleHeight = round($nMaxHeight / $nHeight * 100,2) ;
				$nScale = min($nScaleWidth , $nScaleHeight) ;      
			}
			$nWidth = round($nWidth * $nScale / 100,0) ;
			$nHeight = round($nHeight * $nScale / 100,0) ;   
			$cRetval = '<a href ="'.$cFileName.'" target="_blank"><img title="Click Untuk Memperbesar Gambar" src="'. $cFileName . '" width="' . $nWidth . '" height="' . $nHeight . '" border="1" id="objFoto"></a>';
		} 
		return $cRetval ;
	}
	 
	static function GetClearTmp0($nVaultToken){
		$cDir = "/var/www/prg/app/data-integrasi/tmp/tmp0/".$nVaultToken;

		// Pastikan direktori ada
		if (!is_dir($cDir)) {
			mkdir($cDir, 0777, true);
			chmod($cDir, 0777);
		}

		// Hapus semua file dalam folder
		$files = glob($cDir . '/*'); // ambil semua file

		foreach ($files as $file) {
			if (is_file($file)) {
				unlink($file);      // hapus file
			} elseif (is_dir($file)) {
				// Jika ada folder di dalamnya, hapus rekursif
				self::GetDeleteDirTmp0($file);
			}
		} 
	}
	
	// Fungsi hapus folder secara rekursif
	static	function GetDeleteDirTmp0($dir) {
		$objects = scandir($dir);
		foreach ($objects as $object) {
			if ($object != "." && $object != "..") {
				$path = $dir . "/" . $object;
				if (is_dir($path))
					self::GetDeleteDirTmp0($path);
				else
					unlink($path);
			}
		}
		rmdir($dir);
	}
	
	static function SNow(){
		return date("Y-m-d H:i:s",time()) ;
	}
	
	static function SaveCDS($vaData,$url="http://aa.cds.sis1.net/cds/upload/"){
		$cBody      = json_encode($vaData) ;
		$cKey       = aCfg::Get("msCDSID") ;
		$cTime      = date("c",time()) ;
		$cVersion   = "1.0" ;
		$cSignature = md5("$cKey:$cTime:$cVersion:$cBody") ;  

		$cURL = curl_init();
		curl_setopt($cURL, CURLOPT_URL, $url);
		curl_setopt($cURL, CURLOPT_POST, true);
		curl_setopt($cURL, CURLOPT_POSTFIELDS, $cBody);  
		curl_setopt($cURL, CURLOPT_RETURNTRANSFER, true );
		curl_setopt($cURL, CURLOPT_HTTPHEADER,
			array(
					"Content-Type:application/json",
					"SIS-Key: $cKey",
					"SIS-Data-Type:JSON",
					"SIS-Timestamp:$cTime",
					"SIS-Version:$cVersion",
					"SIS-Signature:$cSignature",
					'Content-Length: ' . strlen($cBody),
			)
		);  
		$cBody = curl_exec($cURL);
		curl_close($cURL);
		$vaBody = array() ;
		if($cBody <> ""){
			$vaBody = json_decode($cBody,true) ;    
		}
		return $vaBody ;
	}
	
	static function String2SQL($cChar){
		$cChar = str_replace("\\","\\\\",$cChar) ;
		$cChar = str_replace("'","\'",$cChar) ;
		$cChar = str_replace('"','\"',$cChar) ;
		$cChar = str_replace("\n","//n",$cChar) ;

		return $cChar ;
	}  
	
	static function Tgl2Time($dTgl){
		if(empty($dTgl)){
			return 0 ;
		}  
		$dTgl = String2Date($dTgl) ;
		$va   = split("-",$dTgl) ;

		return mktime(0,0,0,$va[1],$va[0],$va[2]) ;
	}
	
  static function ThemesImages($cFile){
		return "../component/themes/default/images/" . $cFile ;
	}
	
	static function direc($cURL){
		echo '<html><meta http-equiv="refresh" content="0;
			URL=main.php?__par=' . getlink($cURL,false) . '">'
			.'</html>' ;
		exit ;
	}
	
	static function getfilesize($cFile){  
		$cRetval = "0 B" ;
		if(file($cFile)){          
			$nSize = filesize($cFile) ;
			$cRetval = number_format($nSize,2) . " B" ;

			$vaSize = array("KB", "MB", "GB", "TB") ;
			$n = 1024 ;
			foreach($vaSize as $key=>$value){
				if ($nSize > $n){
					$nSize = $nSize / $n ;
					$cRetval = number_format($nSize,2) . " " . $value ;
				}
				$n = 1000 ;
			}
		}
		return $cRetval ; 
	}
		
	static function CheckTable($cDB,$cTable){
		$cStatus = false ;
		$dbData = objData::SQL("SHOW TABLES FROM $cDB LIKE '".$cTable."'") ;
		if($dbRow = objData::GetRow($dbData)){
			$cStatus = true ;
		}
		return $cStatus;
	}
	
	static function SMSBankingNoPengiriman(){
		$nRand = str_pad(rand(0,999999),6,"0",STR_PAD_LEFT) ;
		$nKey = Dec2AsciiDecimal(date("ymdHis") . $nRand) ;
		return "C" . $nKey ;  
	}
	
	function Dec2AsciiDecimal($nDec){
		$cRetval = "" ;
		if($nDec < 0){
			$cRetval = "-" ;
			$nDec = abs($nDec);
		}
		$cAscii = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ" ;
		$nLen = strlen($cAscii) ;
		$h = "" ;
		do{
			$n = ($nDec%$nLen) ;
			$h = substr($cAscii,$n,1) . $h;
			$nDec = ($nDec-$n) / $nLen;
		}while( $nDec >= 1 );

		return $cRetval . $h;
	}
	
	static function FilterCabang($optKonsolidasi,$cCabang){
		/*
		if($optKonsolidasi == 'C' || $optKonsolidasi == 'I'){
			$cCabangKonsolidasi = GetSetting("cCabangKonsolidasi") ; 
			$vaCabangKonsol     = explode("~",$cCabangKonsolidasi) ;  
			foreach($vaCabangKonsol as $cKey => $cValue){
				$vaWhereKonsol[]  = "t.CabangEntry = '$cValue' or rk.Cabang = '$cValue' " ;   
			} 
			if(!empty($vaWhereKonsol)){    
				$cCabangKonsol  = " and (" ;         
				$cCabangKonsol .= implode(" or ",$vaWhereKonsol) ;  
				$cCabangKonsol .= ")" ;
			}        
		}else if($optKonsolidasi == 'K'){
			$cCabangKonsol  = " and t.CabangEntry = '$cCabang' or rk.Cabang = '{$va['cCabang']}' " ;
		} 
		return $va ;*/
	} 
}
?>
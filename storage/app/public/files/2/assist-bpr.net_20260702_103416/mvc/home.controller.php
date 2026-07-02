<?php
/* 
Berisi Script Controller dengan method yang bisa kita definisikan sendiri dengan syarat 
1. Semua Method hanya bisa di akses dengan a.ajax atau restfull dengan definisi Header khusus
2. Method yang bisa di akses dari URL public adalah yand di definisikan di Routes sebagai PublicMethod
*/
class home_Controller extends MVC_Controller {
	function index(){
		//objData::Insert("bukubesar",array("keterangan"=>"Log Random".rand(1,999999))) ;
		//SysLog::Report() ;
		//exit() ;
		$lSSO = Svr::GetConfig("sso");
		//echo GetSetting("cLogin");die;.
		if($lSSO){
			if(!GetSetting("cLogin")){
				$url = AuthClient::Authorize();
				header("Location: $url");
				exit() ;  
			}else if (isset($_GET['data'])) {
				$cParam = urldecode($_GET['data']);
				$cPrev = urldecode($_GET['prev']);
				if ($cParam == "LogOutProgram") {
					SaveSetting("cLogin",0) ;
					User::Delete() ;
					header("Location: ". $_SERVER['REQUEST_URI']);
				}
			}
		}
		
		$cView = GetSetting("cLogin") == 1 ? "" : "home.login";
		$this->View($cView) ;
		return true;

		$this->View("") ;
		return;
	}

	function loadbody(){
		$this->View("home.body") ;
	}
	

	function logout(){
		$lSSO = Svr::GetConfig("sso");
		if($lSSO){
			$cUrlSSO  = Svr::GetConfig("auth_server_uri");
		  $cParam   = "LogOutProgram";
			$cURL     = $current_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http"). "://" . $_SERVER['HTTP_HOST'];
			$vaReturn = array("Url"=>$cUrlSSO,"Param"=>$cParam,"Prev"=>$cURL);
		}else{
			$vaReturn = "ok";
		}
		SaveSetting("cLogin",0) ;
		User::Delete() ;
			
		MVC::Response($vaReturn) ;
	}

	function opentimeline($va){
		$cUserName 	= GetSetting('cSession_UserName');
		$cLevel 		= GetSetting('cSession_UserLevel');
		$cKode 			= $_SERVER["HTTP_HOST"];
		$vaReturn = array("cKode"=>$cKode,"cLevel"=>$cLevel,"cUserName"=>$cUserName);
		$cJWTCode = JWT::encode($vaReturn);
		MVC::Response($cJWTCode);
	}
	  
	function UpdateTime($va){
		//Update time online user
		$cUserName = GetSetting('cSession_UserName');//$va['cUserName'] ;
		
		if(trim($cUserName) <> ''){
			$nTime = time()+15 ;
			User::Save("Online",$nTime) ;
		}
  	$vaData     				= $this->StatusAutoPosting() ; 
		$vaDataH2H					= $this->StatusH2H() ;
		$vaOtorisasi				= $this->CekOtorisasi() ;
		$nJmlTransferDigi		= $this->LoadDataAntrianTransferUang();
		$nDepositoJthTmp		= $this->DepositoJatuhTempo() ;
		$vaOtorisasiAktivasiDigital= $this->CekOtorisasiAktivasiDigital();
		
		$vaResult[] = array("nTime"=>date("H:i:s"),"cStatus"=>$vaData["cStatus"],"cStatusAuto"=>$vaData["cStatusAuto"],"cIdle"=>$vaData["cIdle"],
											 	"cIMGGW"=>$vaDataH2H['IMGGW'],"cIMGPosting"=>$vaDataH2H['IMGPosting'],"nSaldo"=>$vaDataH2H['Saldo'],
											 	"nJumlahOtorisasi"=>$vaOtorisasi['nJumlah'],
												"nJmlTransferDigi"=>$nJmlTransferDigi,
												"nJumlahDepositoJthTmp"=>$nDepositoJthTmp,
												"nJumlahOtorisasiAktifasiDigital"=>$vaOtorisasiAktivasiDigital['Jumlah'],
												"cUserName"=>$cUserName);
		MVC::Response($vaResult) ;
	}
	
	function StatusH2H(){
		$cIMGGW 			= "h2h-offline.gif" ;
		$cIMGPosting 	= "h2h-offline.gif" ;
		$nSaldo 			= 0 ;
		
		$dbData 	= objData::Browse("h2h_status","*","Kode = 'H2HServer'") ;
  	if($dbRow = objData::GetRow($dbData)){
			if(time() - $dbRow ['GWOnline'] <= 60) $cIMGGW = "h2h-online.gif" ;
			if(time() - $dbRow ['PostingOnline'] <= 60) $cIMGPosting = "h2h-online.gif" ;
			$nSaldo = Number2String($dbRow ['SaldoDeposit']) ;
			if($dbRow ['SaldoDeposit'] < 0) $nSaldo = "<font color=red>" . $nSaldo . "</font>" ;
		}
		
		$vaData	= array("IMGGW"=>$cIMGGW,"IMGPosting"=>$cIMGPosting,"Saldo"=>$nSaldo) ;
		//MVC::Response($vaData) ;
		return $vaData ;
	}
	
	function StatusAutoPosting(){ 
		$nOnline = 0 ;
		$dbData = objData::Browse("config","max(Keterangan) Online","Kode like 'msTimePosting%'");
		if($dbRow = objData::GetRow($dbData)){
			$nOnline = $dbRow['Online'] ;
		}
		$nIdle   = time() - $nOnline ; 
		$cIdle   = "" ;
		$cStatus = 0 ;
		//Jika Sudah Tidak Ada Proses 
		if($nIdle >= 70) {
			$cStatus  = 1 ;    
			$nTIdle   = $nIdle ;
			$nJam     = floor($nTIdle/3600) ;
			$nTIdle   = ($nJam > 0) ? $nTIdle-(3600*$nJam) : $nTIdle ;
			$nMenit   = floor($nTIdle/60) ;
			$nTIdle   = ($nMenit > 0) ? $nTIdle-(60*$nMenit) : $nTIdle ;
			$nDetik   = $nTIdle ;
			if(intval($nJam) > 0) $cIdle .= $nJam." Jam " ;
			if(intval($nMenit) > 0) $cIdle .= $nMenit." Menit " ;
			if(intval($nDetik) > 0) $cIdle .= $nDetik." Detik " ; 
		}

		$nOnlineAuto = aCfg::Get("msTimeAutoPosting",0) ; 
		$nIdleAuto   = time() - intval($nOnlineAuto) ;  
		$cIdleAuto   = "" ;
		$cStatusAuto = 0 ;
		//Jika Auto Posting Tidak Jalan 
		if($nIdleAuto >= 70) {
			$cStatusAuto  = 1 ;    
			$nTIdle       = $nIdleAuto ;
			$nJam         = floor($nTIdle/3600) ;
			$nTIdle       = ($nJam > 0) ? $nTIdle-(3600*$nJam) : $nTIdle ;
			$nMenit       = floor($nTIdle/60) ;
			$nTIdle       = ($nMenit > 0) ? $nTIdle-(60*$nMenit) : $nTIdle ;
			$nDetik       = $nTIdle ;
			if(intval($nJam) > 0) $cIdleAuto .= $nJam." Jam " ;
			if(intval($nMenit) > 0) $cIdleAuto .= $nMenit." Menit " ;
			if(intval($nDetik) > 0) $cIdleAuto .= $nDetik." Detik " ; 

			$cIdle = $cIdleAuto ;
		}
		$vaData = array("cStatus"=>$cStatus,"cStatusAuto"=>$cStatusAuto,"cIdle"=>$cIdle);
		return $vaData;
	}
	
	function CekOtorisasi(){
		$vaUser   = Func::GetDataUser() ;
		$dTgl     = Func::GetTglTransaksi() ;
		if($dTgl == "00-00-0000" || $dTgl == ""){
			$dTgl = date("d-m-Y");
		}
		$dTgl     = Date2String($dTgl) ;
		$cCabang  = $vaUser['KantorInduk'] ;
		$nJumlah  = 0 ;
		
		$vaArray  = GetWewenangOtorisasi::Get($dTgl,$cCabang,"Tgl = '$dTgl' and Acc = '0'") ;
		$nJumlah  = count($vaArray) ;
		
		$vaData		= array("nJumlah"=>$nJumlah);
		return $vaData;
	}
	
	function DepositoJatuhTempo(){
		$dTgl     = Func::GetTglTransaksi() ;
		if($dTgl == "00-00-0000" || $dTgl == ""){
			$dTgl = date("d-m-Y");
		}
		$dTgl			= Date2String($dTgl) ; //"2026-08-14" ; //
		$dJthTmp	= $dTgl ;
		$nJumlah	= 0 ;
		
		$dbData	= objData::Browse("deposito","Rekening,Tgl","Tgl <= '$dTgl' and TglCair >= '9999-01-01' and Status = '0' and ARO = 'T'") ;
		while($dbRow	= objData::GetRow($dbData)){
			$dJthTmp	= GetJthTmpDeposito::Get($dbRow['Rekening'],$dTgl) ;
			$dJthTmp	= Date2String($dJthTmp) ;
			if($dJthTmp <= $dTgl){
				$nJumlah++ ;
			}
		}
		return $nJumlah ;
	}
	
	function PeriodikPassword(){
		$dTglServer    = date("Y-m-d") ;
		$vaUser     	 = Func::GetDataUser() ;
		$cUserName     = $vaUser['UserName'] ;
		$cUserPassword = Func::GetKeterangan($cUserName,"UserPassword","username","Username") ;

		$lPeriodikPassword = 0 ;
		$nPeriodikPassword = aCfg::Get("msPeriodikPassword",0) ;
		if(!aCfg::Get("msSSO",0) && $nPeriodikPassword > 0 && strtolower($cUserName) != 'teamsupport'){
			$dbData = objData::Browse("username","TglPasswordPeriodik","Username = '$cUserName'") ;
			if($dbRow = objData::GetRow($dbData)){
				$dTglUbahPassword = date("Y-m-d",NextMonth(strtotime($dbRow['TglPasswordPeriodik']),$nPeriodikPassword)) ;
				$lPeriodikPassword = ($dTglUbahPassword <= $dTglServer) ? 1 : 0 ;
			}
		}

		$cPassword = substr($cUserPassword,0,10). substr($cUserPassword,14) ;
		if($cPassword == md5('123456789')){
			$lPeriodikPassword = 1 ;
		}
		
		MVC::Response($lPeriodikPassword) ;
	}
	
	function LoadDataPengajuanOnline(){
		Global $objData ;
		
		$vaJoin       = array("LEFT JOIN cabang c ON c.Kode = k.Cabang");
		$dbData       = objData::Browse("kredit_pengajuan_online k","k.Cabang,c.Keterangan as NamaCabang,COUNT(k.Faktur) as Jumlah","k.Status = '0'",$vaJoin,"k.Cabang");
		$nJumlahData  = 0;
		if( objData::Rows($dbData) > 0){
			$cStatusCard = "";
			while($dbRow = objData::GetRow($dbData)){
				$nJumlahData += $dbRow["Jumlah"];
				$cStatusCard .= "<tr><td style='width:8rem;padding:.2rem;'>{$dbRow["NamaCabang"]}</td><td style='width:2rem;text-align:center;padding:.2rem;'>{$dbRow["Jumlah"]}</td></tr>";
			}
			$cHTML   = "";
			$cHTML  .= "<div class='dropdown-pinjol-main'>";
			$cHTML  .= "<div onclick='OpenNotifPengajuanOnline()' class='blinkBG' style='cursor:pointer;border-radius:.3em;background-color:yellow;font-weight:bold;padding:.20em 1em;'>$nJumlahData</div>";
			$cHTML  .= "<div class='dropdown-pinjol-main-content'><table class='panel_main' border='1' style='border-collapse:collapse;'>";
			$cHTML  .= $cStatusCard;
			$cHTML  .= "</table></div>";
			$cHTML  .= "</div>";
		}else{
			$cHTML = "<div onclick='OpenNotifPengajuanOnline()' style='cursor:pointer;border-radius:.3em;background-color:yellow;font-weight:bold;padding:.20em 1em;'>$nJumlahData</div>";
		}
		echo $cHTML;
	}
	
	function LoadDataAntrianTransferUang() {
		$cUserName 	= GetSetting('cSession_UserName');
		$nLvlOtor 	= FuncMBanking::CekAksesMenuDigital($cUserName,"Otorisasi_TFDana");
		if ($nLvlOtor == 1) {
			$dbData = objData::Browse("pos_pulsa","Faktur","(Kode LIKE '%TFDANA%' OR Kode LIKE '%BIFAST%') AND Status = 'W' AND StatusOtorisasi = '0'");
			return objData::Rows($dbData);
		} else {
			return 0;
		}
	}
	
	function CekOtorisasiMenuTransferDigital($va) {
		$cUserName 	= GetSetting('cSession_UserName');
		$nLvlOtor 	= FuncMBanking::CekAksesMenuDigital($cUserName,"Otorisasi_TFDana");
		echo $nLvlOtor;
	}
	
	function CekOtorisasiAktivasiDigital(){
		$vaUser   = Func::GetDataUser() ;
		$dTgl     = Func::GetTglTransaksi() ;
		if($dTgl == "00-00-0000" || $dTgl == ""){
			$dTgl = date("d-m-Y");
		}
		$dTgl     = Date2String($dTgl) ;
		$nJumlah	= 0 ;
		$vaArray  = FuncMBanking::CekAktivasiDigital($dTgl,"Tanggal = '$dTgl' and Acc = '0'") ;
		$nJumlah  = count($vaArray) ;
		$vaData		= array("Jumlah"=>$nJumlah);
		return $vaData;
	}
	
}
<script language="javascript" type="text/javascript"> 

var	lKewenanganShortcutLaporan 	= "<?= aCfg::Get('msDigital_ShortcutLaporan','T') ; ?>" ;
var lKewenangan 								= "<?= Func::GetOtorisasiIcon() ?>" ;
var	lKewenanganAktivasiDigital 	= 1;//"<?= FuncMBanking::CekAksesMenuDigital(GetSetting('cSession_UserName'),'Otorisasi_PembukaanFasilitasDigital'); ?>";
var lKewenanganPengajuan				= 0;//"<?= FuncModal::CekAksesMenuPengajuanOnline(); ?>";
var lKewenanganTFUang						= "<?= FuncMBanking::CekAksesMenuDigital(GetSetting('cSession_UserName'),'Otorisasi_TFDana'); ?>";
var lNotifDepositoJthTmp				= "<?= aCfg::Get('msSystemARO') ; ?>" ;
var oNotif      = null ;
var nTask       = 0 ; 
var cURL        = "<?= Svr::GetConfig('URLWO') ?>" ; //"'http://webadmin.sis1.net/assist-wo.net' ;
var cKey        = "<?= Svr::GetConfig('WebReportAct') ?>" ;
var cUserName   = "<?= GetSetting('cSession_UserName') ?>" ;
var cCabang     = "<?= Func::GetKeterangan(GetSetting('cSession_UserName'),'Cabang','username','username') ?>" ;
var cKantorKas  = "<?= Func::GetKeterangan(GetSetting('cSession_UserName'),'KantorKas','username','username') ?>" ;
var cFullName   = "<?= Func::GetKeterangan(GetSetting('cSession_FullName'),'Fullname','username','username') ?>" ; 
var DBInfo      = "DB Info : "+"<?= strtolower(substr(GetSetting('cSession_UserName'),0,4)) == 'team' ? SisConfig::GetValue("db").' on '.SisConfig::GetValue("ip") : SisConfig::GetValue("db") ?>" ; 
var TglInfo     = "Tgl CBS : <?= (empty(Func::GetTglTransaksi())) ? '<span style=\"color:red;font-weight:bold;font-size:1.2em;\">Closed</span>' : '<span style=\"color:green;font-weight:bold\">' . Func::GetTglTransaksi() . '</span>' ?>" ;   

function Form_onLoad(){ 
	var o       = document.getElementById("mainFrame") ;
  var nHeight = Math.min(o.contentWindow.innerHeight-4,768) ;
  var nWidth  = Math.min(o.contentWindow.innerWidth-4,1280) ;
  var cell    = a.getById("toolBar") ;
	
  tBar.add("Close","Exit Program","share/images/menu/menu-close.gif",function(){toolBar_Close()}) ;
  tBar.addSep() ;
	tBar.add("toolChange","Change Password","share/images/menu/menu-change-password.gif",function(){OpenForm("<?= Svr::GetConfig('msSubModul_System') ?>/main.php?__par=./user/frmuser_changepassword.php",'frmChangePassword','Change Password',500,200);}) ;
  tBar.add("toolCariRegister","Cari Register Nasabah","share/images/menu/menu-find.gif",function(){OpenForm("<?= Svr::GetConfig('msSubModul_General') ?>/main.php?__par=./transaksi/frmcarinasabah.php",'frmCariRegisterNasabah','Cari Register Nasabah',1100,600,"",false,"no","","mainFrame");}) ; 
	tBar.addSep() ;
	tBar.add("changeThemes","Change Themes","share/images/menu/ch-themes.png",function(){MnuChangeThemes_onClick()}) ;
	tBar.add("changebg","Change Background","share/images/menu/cb.gif",function(){ChangeBg()}) ;
	tBar.addSep() ;
  tBar.add("about","About Assistindo","share/images/menu/menu-help-about.gif",function(){OpenForm(svr.GetBaseURL()+"about","frmAbout","About Assistindo",700,400)}) ;
  tBar.add("Support","Support Online","share/images/menu/support.ico",function(){OpenSupport()}) ; //,'frmSupport','Support Online',nWidth,nHeight,'',false,'no',false,'__blank');
  tBar.addSep() ;
	tBar.add("ssH2HClient","","") ;
	tBar.addSep() ;
  tBar.add("AutoPosting","","") ;
	if(lKewenangan)tBar.add("notifOtorisasi","Transaksi Otorisasi","share/images/menu/notif.gif",function(){OpenNotifOtorisasi()}) ;
  tBar.addSep() ;
	if(lKewenanganAktivasiDigital) tBar.add("notifOtorisasiAktifasiDigital","Otorisasi Aktivasi Fasilitas Digital","share/images/menu/notif.gif",function(){OpenNotifOtorisasiAktifasiDigital()}) ;
	if(lKewenanganPengajuan)tBar.add("notifPengajuanOnline","","") ;
	if(lKewenanganTFUang) tBar.add("LogoAntrianTransferUang","","share/images/menu/transfer-uang.gif",function(){OpenDashboardAntrianTransferUang()}) ;
	if(lNotifDepositoJthTmp == "T")tBar.add("notifDepositoJthTmp","Deposito Jatuh Tempo","share/images/menu/notif.gif",function(){OpenDepositoJthTmp()}) ;
	tBar.addSep() ;
  tBar.add("TimeLine","TimeLine","share/images/menu/log.png",function(){opentimeline()}) ;
	tBar.addSep() ;
	if(lKewenanganShortcutLaporan == "Y")tBar.add("shortcutLaporan","Shortcut Laporan","share/images/menu/report.png", function(){ShortcutLaporan()}) ; <!--- edite 8-juni-2026 by Denta NCBD -->
	
  tBar.show("ssTolbar1",null,null,24,null,cell) ;
  var cell = a.getById("statusBar") ;
  sBar.add("user",'<?php echo(str_replace(" ","&nbsp;",GetSetting("cSession_UserName")) . " @" . $_SERVER["REMOTE_ADDR"]) ?>',"1px") ;
	sBar.add("Cabang",'<?php echo("Kantor Induk : '+cCabang+'") ?>',"1px") ;
	sBar.add("KantorKas",'<?php echo("Kantor Pelayanan : '+cKantorKas+'") ?>',"1px") ;
  sBar.add("Server","<?php echo('Server : ' . $_SERVER['HTTP_HOST']) ?>","1px") ;
  sBar.add("Database",DBInfo) ;
	//sBar.add("appid","<?php echo('id : '.Svr::GetAppID()) ?>","1px") ;
  sBar.add("cTglCBS",TglInfo,"1px") ;
  sBar.add("cTime","<?php echo(date("H:i:s")) ?>","1px") ;
  sBar.add("cVersion","Versi : 2.0.0","1px") ;
	sBar.add("cComponent","Component Version : <?php echo(Svr::GetComponentVersion()) ?>","1px") ;
  sBar.add("cTimeout","","1px") ;
  sBar.show("StatusTolbar2",cell,"1px") ;
	
  //CheckPasswordExpired() ;
	UpdateNotifSupport() ;
	UpdateTimes() ;
	PeriodikPassword() ;
	//window.idleSession.init();
	//UpdH2HStatus() ;
  //CekOtorisasi();
}

var ot = null ;
var nOnline = 0 ;
var lPending = false ;

function ShortcutLaporan(){ <!-- edite 8-juni-2026 by Denta NCBD -->
	OpenForm("<?= Svr::GetConfig('msSubModul_MBanking') ?>/main.php?__par=./laporan/rptshortcut.php",'frmShortCut','Shortcut All Laporan',1200,800,"",false,"no","","mainFrame");
}

function LaporanPengguna(){
	OpenForm("<?= Svr::GetConfig('msSubModul_MBanking') ?>/main.php?__par=./laporan/rptuserdigitalbanking.php",'frmPenggunaFasilitas','Laporan Pengguna Fasilitas',950,600,"",false,"no","","mainFrame");
}

function LaporanTransaksiDigital(){
	OpenForm("<?= Svr::GetConfig('msSubModul_MBanking') ?>/main.php?__par=./laporan/rpttransaksidigitalbanking.php",'frmTransaksiDigital','Laporan Transaksi Digital',950,600,"",false,"no","","mainFrame");
}

function LaporanKeluhan(){
	OpenForm("<?= Svr::GetConfig('msSubModul_MBanking') ?>/main.php?__par=./laporan/rptkeluhandigitalbanking.php",'frmKeluhanDigital','Laporan Keluhan Digital',950,600,"",false,"no","","mainFrame");
}

function LaporanPenggunaPromo(){
	OpenForm("<?= Svr::GetConfig('msSubModul_MBanking') ?>/main.php?__par=./laporan/rptpromo.php",'frmPenggunaPromo','Laporan Pengguna Promo',950,600,"",false,"no","","mainFrame");
}

function LaporanJurnalDigital(){
	OpenForm("<?= Svr::GetConfig('msSubModul_MBanking') ?>/main.php?__par=./laporan/rptjurnaldigital.php",'frmJurnalDigital','Laporan Jurnal Digital',950,600,"",false,"no","","mainFrame");
}

function LaporanFakturBermasalah(){
	OpenForm("<?= Svr::GetConfig('msSubModul_MBanking') ?>/main.php?__par=./laporan/rptfaktur_bermasalah.php",'frmFakturBermasalah','Laporan Faktur Bermasalah',950,600,"",false,"no","","mainFrame");
}

function opentimeline() {
    // Buat form
		a.ajax("","opentimeline","",function(obj){
			const form = document.createElement('form');
			form.method = 'POST';
			//form.action = 'http://dev.sis1.net/assist-gateway-dashboard/public/timeline';
			//form.action = 'http://dev.sis1.net/timeline-cbs/public/timeline';
			form.action = 'http://timeline.sis1.net/timeline-cbs/public/timelinecbs';
			form.target = '_blank'; // Buka di tab baru
			let data = {
				cKode : obj.data.data,//obj.data.cKode,
				//cLevel : obj.data.cLevel,
				//cUserName : obj.data.cUserName
			}
			// Tambahkan data sebagai input hidden
			for (const key in data) {
					if (data.hasOwnProperty(key)) {
							const input = document.createElement('input');
							input.type = 'hidden';
							input.name = key;
							input.value = data[key];
							form.appendChild(input);
					}
			}

			// Tambahkan form ke body, submit, lalu hapus
			document.body.appendChild(form);
			form.submit();
			document.body.removeChild(form);
		});
}

function UpdateTimes(){
	//document.querySelector('[title="Deposito Jatuh Tempo"]').style.display = "none";
	//document.getElementById("notifDepositoJthTmp-toolBar-div").style.display = "none";
	
  setTimeout(UpdateTimes,1000) ;
  nOnline ++ ;
  
	if(nOnline >= 50 && !lPending){
		lPending = true ;	
		//if(lKewenangan)CekOtorisasi();
		//UpdH2HStatus() ;
    nOnline = 0 ;
		a.ajax("","UpdateTime","cUserName="+cUserName,function(obj){
			lPending = false ;
			if(obj.data !== ""){
				//console.log(obj.data) ;
				let vaData = obj.getRow;
				if(vaData.cUserName == ""){
					location.reload();
				}
				
				ot.innerText = vaData.nTime ;
				UpdStatusAutoPosting(vaData.cStatus,vaData.cStatusAuto,vaData.cIdle);
				UpdH2HStatus(vaData.cIMGGW,vaData.cIMGPosting,vaData.nSaldo) ;
				UpdOtorisasi(vaData.nJumlahOtorisasi) ;
				
				if(vaData.nJumlahDepositoJthTmp > 0 && lNotifDepositoJthTmp == "T"){
					document.getElementById("notifDepositoJthTmp-toolBar-div").style.display = "block";
				}
				
				/*var lDepositoJthTmp	= document.getElementById("notifDepositoJthTmp-toolBar-div") ;
				if(lDepositoJthTmp){
					document.getElementById("notifDepositoJthTmp-toolBar-div").style.display = "block";
				}else{
					console.log("bbbb") ;
				}
				if(vaData.nJumlahDepositoJthTmp <= 0){
					document.getElementById("notifDepositoJthTmp-toolBar-div").style.display = "none";
				}else{
					document.getElementById("notifDepositoJthTmp-toolBar-div").style.display = "block";
				}*/
				
				UpdOtorisasiAktifasiDigital(vaData.nJumlahOtorisasiAktifasiDigital) ;
				UpdDataAntrianTransferUang(vaData.nJmlTransferDigi);
				UpdDataPengajuanOnline();
			}			
		}) ;
		//UpdateNotifSupport() ;
  }
	
  if(ot == null) ot = document.getElementById("cTime-cell-content-") ;
  var va = ot.innerText.split(":") ;
  va [2] = parseFloat(va[2]) + 1 ;
  if(va [2] > 59){
    va [2] = 0 ;
    va [1] = parseFloat(va[1]) + 1 ;
    
    if(va [1] > 59){
      va [1] = 0 ;
      va [0] = parseFloat(va[0]) + 1 ;
      if(va [0] > 23) va [0] = 0 ;
    }
  }
	va[0] = "00" + va[0].toString() ;
	va[1] = "00" + va[1].toString() ;
  va[2] = "00" + va[2].toString() ;
	
	va[0] = va[0].substring(va[0].length - 2);
	va[1] = va[1].substring(va[1].length - 2);
	va[2] = va[2].substring(va[2].length - 2);
	
  ot.innerText = va[0] + ":" + va[1] + ":" + va[2] ;
}

function OpenSupport(){
  if(cKey == ''){
		a.alert("Fitur support online belum diaktifkan !","Info") ;
	}else{
		window.open(cURL + "?cKey="+cKey+"&cUserName=" + cUserName + "&cFullName=" + cFullName) ;
	}
}

function UpdateNotifSupport(){ 
  nTask ++ ;  
  var cMessage  = "cKey=" + cKey + "&cUserName=" + cUserName + "&cFullName=" + cFullName + "&n=" + nTask ; 
  if(oNotif  == null) oNotif = tBar.getItem("Support") ; 
  var imgonline = cURL + "/notif.php?" + cMessage ; 
  //oNotif.onerror = function(){defaultImg(oNotif)} ;
  oNotif.src = imgonline ; 
  oNotif.style.width = "21px" ;    
}

function defaultImg(id){ 
  id.src = "./include/images/wifi-off.png" ;           
  id.style.opacity = "0.3" ;    
  id.style.width = "21px" ; 
  id.title = "Offline" ;  
}

function ChangeBg(){
  var cLevel = "<?php echo GetSetting("cSession_UserLevel") ;?>" ; 
  if(cLevel == "0000"){
		OpenForm("<?= Svr::GetConfig('msSubModul_System') ?>/main.php?__par=./setup/cfgbackground.php",'frmBg','Change Background',750,450,"",false,"no",false,"mainFrame");
  }else{
    alert('anda tidak memiliki hak akses untuk membuka menu ini.');
  }
  return false ;  
}

var oH2H = null ;
function UpdH2HStatus(cGateway,cPosting,nSaldo){
	if(oH2H == null) oH2H = tBar.getItem("ssH2HClient") ;
	if(oH2H !== null){
		oH2H.style.paddingTop = 3 ;
		oH2H.innerHTML = '<img src="share/images/menu/' + cGateway + '" align="top">H2H GW  <img src="share/images/menu/' + cPosting + '" align="top">H2H Posting&nbsp;&nbsp;&diams;&diams;&nbsp;<strong>Saldo : ' + nSaldo + "</strong>&nbsp;&diams;&diams;" ; 
	}
}

var oNotifStatus  = null ;
function UpdStatusAutoPosting(lStatus,lStatusAutoPosting,cIdle){
  if(oNotifStatus == null) oNotifStatus = tBar.getItem("AutoPosting") ;
  /*
  Status Auto Posting 0 = On Progress
                      1 = Tidak Jalan
                      
  Status 0 = On Progress
         1 = Done 
  */
  if(lStatusAutoPosting == 1){
    var cNotifikasi = "Posting Otomatis Tidak Berjalan , Proses Terakhir (" + cIdle + "Yang Lalu)" ;
    oNotifStatus.innerHTML  = '<img src="share/images/menu/delete.png" height="19px" align="top" title="'+cNotifikasi+'" onClick="OpenAutoPosting()"> ' ; 
  }else{
    if(lStatus == 1){ 
      var cNotifikasi = "Posting Otomatis Sudah Selesai , Proses Terakhir (" + cIdle + "Yang Lalu)" ;
      oNotifStatus.innerHTML  = '<img src="share/images/menu/check.png" height="19px" align="top" title="'+cNotifikasi+'" onClick="OpenAutoPosting()"> ' ; 
    }else{ 
      var cNotifikasi = "Posting Otomatis Sedang Di Proses" ;
      oNotifStatus.innerHTML  = '<img src="share/images/menu/Gear.gif" height="23px" align="top" title="'+cNotifikasi+'" onClick="OpenAutoPosting()"> ' ;
    } 
  }  
}

function OpenAutoPosting(){ 
	OpenForm("<?= Svr::GetConfig('msSubModul_System') ?>/main.php?__par=./laporan/frm_gridprogress.php",'frmGridProgress','Progress Posting Otomatis',700,300,"",false,"no",false,"mainFrame");
}

function UpdOtorisasi(nJumlah){
  var divOTO = a.getById("notifOtorisasi-toolBar-div");
	if(divOTO){
  	divOTO.classList.add("badge");
  	var badge = document.createElement("span");
  	badge.className = "badge-notif";
  	var nRecord = "" ;
		
		if(lKewenangan == "1"){
			if(nJumlah > 0){
				if(nJumlah > 99){
					nRecord = "99+" ;
				}else{
					nRecord = nJumlah ;
				}
				badge.textContent = nRecord;
				divOTO.appendChild(badge);
			}else{
				divOTO.innerHTML  = '<img src="share/images/menu/notif.gif" height="18" align="top" title="Transaksi Otorisasi" onClick="OpenNotifOtorisasi()"> ' ;
			}
		}
	}
}

function OpenNotifOtorisasi(){
	OpenForm("<?= Svr::GetConfig('msSubModul_System') ?>/main.php?__par=./transaksi/trotorisasi.php",'frmTransaksiOtorisasi','Transaksi Otorisasi',950,700,"",false,"no","","mainFrame");
}

function OpenDepositoJthTmp(){
	OpenForm("<?= Svr::GetConfig('msSubModul_Deposito') ?>/main.php?__par=./laporan/rptdeposito_gridjatuhtempo.php",'frmDepositoJatuhTempo','Deposito Jatuh Tempo',600,300,"",false,"no","","mainFrame");
}

function toolBar_Close(){
  a.confirm("Program akan ditutup ?","Confirm",function(par){
    if(par){
      a.ajax("","logout","",function(obj){
				if (typeof obj.data["Url"] !== "undefined") {
					open(obj.data["Url"]+"?data=" + encodeURIComponent(obj.data["Param"])+"&prev="+encodeURIComponent(obj.data["Prev"]), "_parent");
				}else{
        	open(__BASE_URL__,"_parent") ;
				}
      }) ;
    }
  }) ;
}

function PeriodikPassword(){
	
	a.ajax("","PeriodikPassword","",function(obj){
		if(obj.data == 1){
			a.alert("Password harus diganti !","Info !",function(){
				OpenForm("<?= Svr::GetConfig('msSubModul_System') ?>/main.php?__par=./user/frmuser_changepassword.php",'frmChangePassword','Change Password',450,200,false,false,"no","true","mainFrame");
      }) ;
		}
		
	}) ;
	
	
  /*a.ajax('','PeriodikPassword','',function(lReturn){
    if(lReturn == 1){
      a.alert("Password harus diganti !","Info !",function(){
        OpenForm("submain.php?cHost="+"<?= aCfg('msSubModul_System') ?>"+"&__par=./user/frmuser_changepassword.php",'frmChangePassword','Change Password',450,200,false,false,"no",true);
      }) ;
    }
  }) ;*/
	
}

function UpdDataPengajuanOnline(){
	var oDataPengajuanOnline ;
  if(oDataPengajuanOnline == null) oDataPengajuanOnline = tBar.getItem("notifPengajuanOnline") ;
	if(oDataPengajuanOnline != null){
    oDataPengajuanOnline.style.paddingTop = ".15em";
    a.ajax('','LoadDataPengajuanOnline()','',function(cData){
      oDataPengajuanOnline.innerHTML = cData;
    });
  }
}

function OpenNotifPengajuanOnline(){
	OpenForm("<?= Svr::GetConfig('msSubModul_Mmodal') ?>/main.php?__par=./transaksi/trpengajuanonlinev4.php",'frmTransaksiPengajuanOnline','Transaksi Pengajuan Online',1300,900,"",false,"no","","mainFrame");
}

function OpenDashboardAntrianTransferUang(){
	a.ajax('','CekOtorisasiMenuTransferDigital','',function(cData) {
		if(cData == 1) {
			var cUrl = "<?= Svr::GetConfig('msSubModul_MBanking') ?>/main.php?__par=./transaksi/trantriantransferbank.php";
			OpenForm(cUrl,'frmMBankingTransferUang','Transfer Uang',1300,600,"",false,"no","","mainFrame");
		} else {
			a.alert('Anda tidak memiliki akses untuk menu ini');
		}
	});  
}

function UpdDataAntrianTransferUang(nRecordPengajuan) {
  var divTPO = a.getById("LogoAntrianTransferUang-toolBar-div");
  
  if (divTPO) {
    // Menghapus elemen badge yang ada sebelumnya
    divTPO.classList.add("badge");
    const spans = divTPO.querySelectorAll('span');
    spans.forEach(span => span.remove());

    // Jika nRecordPengajuan kurang dari atau sama dengan 0, tidak menampilkan badge
    if (nRecordPengajuan > 0) {
      var badge = document.createElement("span");

      badge.className = "badge-notif";
      badge.style.width = "15px";
      badge.style.height = "15px";
      badge.style.borderRadius = "50%";
      badge.style.fontSize = "9px";
      badge.style.cursor = "pointer";
      badge.style.color = "#fff";
      badge.style.backgroundColor = "#f00"; // Menambahkan background color untuk visibilitas badge

      badge.textContent = nRecordPengajuan;
      divTPO.appendChild(badge);

      badge.addEventListener("mouseover", function() {
        badge.title = "Klik untuk melihat daftar TF";
        badge.style.backgroundColor = "#f0f0f0";
        badge.style.color = "#000";
      });

      badge.addEventListener("mouseout", function() {
        badge.title = "";
        badge.style.backgroundColor = "#f00"; // Mengembalikan background color
        badge.style.color = "#fff";
      });
    }
  }
}

function UpdOtorisasiAktifasiDigital(nJumlah){
  var divOTO = a.getById("notifOtorisasiAktifasiDigital-toolBar-div");
	if(divOTO){
  	divOTO.classList.add("badge");
  	var badge = document.createElement("span");
  	badge.className = "badge-notif";
  	var nRecord = "" ;
		
		if(lKewenanganAktivasiDigital == "1"){
			if(nJumlah > 0){
				if(nJumlah > 99){
					nRecord = "99+" ;
				}else{
					nRecord = nJumlah ;
				}
				badge.textContent = nRecord;
				divOTO.appendChild(badge);
			}else{
				divOTO.innerHTML  = '<img src="share/images/menu/notif.gif" height="18" align="top" title="Transaksi Otorisasi Aktifasi Digital" onClick="OpenNotifOtorisasiAktifasiDigital()"> ' ;
			}
		}
	}
}

function OpenNotifOtorisasiAktifasiDigital(){
	OpenForm("<?= Svr::GetConfig('msSubModul_MBanking') ?>/main.php?__par=./transaksi/trotorisasi.php",'frmTransaksiOtorisasiAktifasiDigital','Transaksi Otorisasi Aktifasi Digital',950,600,"",false,"no","","mainFrame");
}

function OpenRefundTransaksi(){
	OpenForm("<?= Svr::GetConfig('msSubModul_MBanking') ?>/main.php?__par=./laporan/rpttransaksi_refund.php",'frmOpenRefundTransaksi','Laporan Refund Transaksi',950,600,"",false,"no","","mainFrame");
}

</script>
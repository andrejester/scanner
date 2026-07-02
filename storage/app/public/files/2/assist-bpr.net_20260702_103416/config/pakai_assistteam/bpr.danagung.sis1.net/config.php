<?php
	$va["db"]						 = "bpr_danagung" ;
	$va["db_ref"]				 = "ojk_ref" ;
	$va["ip"]						 = "bpr.danagung.db2.sis1.net" ;
	$va["username"]			 = "Assist" ;
	$va["password"]			 = "Irac" ;
	$va["mnuSubMenu"]		 = __DIR__ . "/submenu.menu.php" ;
  $va['WebReportAct']  = "5d4e8fdb47d7efc7f0001583a80dba38" ;
  
  $va["rpt_data_type"]        = "redis" ;				// mysql/redis
	$va["rpt_data_ip"] 	        = "10.1.8.151" ;
	$va["session_save_handler"] = "redis" ; 		// mysql/redis/file
	$va["session_server_ip"]		= "10.1.8.151" ;
  
  $cDNSSub = "http://bpr.submodule.sis2.net" ;
  $va["msSubModul_System"]    	= $cDNSSub . "/sub-bpr-system";
  $va["msSubModul_Asset"]     	= $cDNSSub . "/sub-bpr-aset" ;
	$va["msSubModul_General"]   	= $cDNSSub . "/sub-bpr-general";
	$va["msSubModul_Akuntansi"] 	= $cDNSSub . "/sub-bpr-akt";
	$va["msSubModul_Tabungan"]  	= $cDNSSub . "/sub-bpr-tabungan";
	$va["msSubModul_Deposito"]  	= $cDNSSub . "/sub-bpr-deposito";
	$va["msSubModul_Kredit"]    	= $cDNSSub . "/sub-bpr-kredit";
	$va["msSubModul_Sakep"]				= $cDNSSub . "/sub-bpr-sakep";
  
  //Sub module pelaporan eksternal
	$va["msSubModul_Camel"]        = $cDNSSub . "/sub-bpr-camel";
	$va["msSubModul_LBBPR"]        = $cDNSSub . "/sub-bpr-lbbpr";
	$va["msSubModul_SLIK"]         = $cDNSSub . "/sub-bpr-slik";
	$va["msSubModul_OBOX"]         = $cDNSSub . "/sub-bpr-obox";
	$va["msSubModul_ProfilResiko"] = $cDNSSub . "/sub-bpr-profilresiko" ; //"https://api.sis1.cloud:44310/sub-bpr-profilresiko";
	$va["msSubModul_TataKelola"]   = $cDNSSub . "/sub-bpr-tatakelola";
	$va["msSubModul_Simpel"]       = $cDNSSub . "/sub-bpr-simpel";
	$va["msSubModul_Sipendar"]     = $cDNSSub . "/sub-bpr-sipendar";
	$va["msSubModul_APKAP"]        = $cDNSSub . "/sub-bpr-apkap" ; //"https://api.sis1.cloud:44310/sub-bpr-apkap";
	$va["msSubModul_Payroll"]	  	 = $cDNSSub . "/sub-bpr-payroll";
  
  //Sub module aplikasi non core banking
	$va["msSubModul_Mcollection"]   = $cDNSSub . "/sub-bpr-mcollection";
	$va["msSubModul_Mmodal"]   			= $cDNSSub . "/sub-bpr-mmodal";
	$va["msSubModul_Messaging"] 	  = $cDNSSub . "/sub-bpr-messaging";
	$va["msSubModul_MBanking"] 		  = $cDNSSub . "/sub-bpr-mbanking";
 	$va["msSubModul_H2HCurl"]   	 	= $cDNSSub . "/sub-bpr-h2h-curl";
?>
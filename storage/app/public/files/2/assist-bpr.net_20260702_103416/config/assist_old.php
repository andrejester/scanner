<?php
	$dns = $_SERVER["HTTP_HOST"] ;
	$vadns ["bpr.mvc.sis1.net"] = "bpr.mvc.sis1.net" ;
  $vadns ["bpr.mvc.sis2.net"] = "bpr.mvc.sis2.net" ;
  $vadns ["aa.mvc.sis2.net"]  = "aa.mvc.sis2.net" ; 
  $vadns ["cbs.bpr.sis1.net"] = "cbs.bpr.sis1.net" ;
  $vadns ["demo.assistindo.id:2886"] = "demo.assistindo.id" ;

	// Variable Global 
	/*
	Report Tmp Data Type
	Kita Gunakan Kalau kita mau membuat laporan yang Parsial maka dia memungkinkan kita potong 
	dengan class Rpt::
	dia akan mengikuti Ketentuan sbb
	1. Kalau tidak di definisikan maka akan di anggap "mysql"
	2. Jika mysql akan mengikuti ip database utama
	3. Jika redist dia akan mengikuti configurasi Redist yang di tentukan
	4. Jika Jenis mysql maka "rpt_data_ip,rpt_data_username,rpt_data_password" tidak di pakai
	5. rpt_data_ttl Batas Penyimpanan di data maximum ( 1 Hari ), atau jika membuka laporan lain maka data akan di hapus
	*/
	//token
	$va["token_type"] 	 = "redis";				//jwt/redis
	$va["token_ip"]		   = "10.1.8.151";		//ip redis jika redis
	$va["token_password"]= "";							//password jika redis
	$va["token_ttl"] 		 = 3600;					//token time to live dalam detik jika redis

	$va["rpt_data_type"] 						= "redis" ;				// mysql/redis
	$va["rpt_data_ip"] 							= "10.1.8.151" ;
	$va["rpt_data_username"]				= "" ;
	$va["rpt_data_password"]				= "" ;

	// Session Configurasi
	$va["session_save_handler"]			= "redis" ; 		// mysql/redis/file
	$va["session_server_ip"]				= "10.1.8.151" ;
	$va["session_server_username"]	= "" ;
	$va["session_server_password"]	= "" ;

	//Setting DNS Sub Module
  if($dns == "bpr.mvc.sis1.net"){
		$cDNSSub = "http://aa.mvc.sis1.net" ; //Untuk development workspace MVC - Sub Module
	}else if($dns == "bpr.mvc.sis2.net"){
		$cDNSSub = "http://aa.mvc.sis1.net" ;
	}else if($dns == "aa.mvc.sis2.net"){     
		$cDNSSub = "http://aa.mvc.sis1.net" ;
	}else if($dns == "bpr.mvc.sis1.net"){
	  $cDNSSub = "https://api.sis1.net:44380/sub-bpr" ; //Untuk development akses sub menggunakan dns public
	}else if($dns == "bpr.mvc.sis1.cloud"){
		$cDNSSub = "https://api.sis1.cloud:44310" ; //http://cds.sis1.cloud/cds/public/cds/file //"cbs.submodule.sis1.net" ; //Untuk development workspace docker (server container)
	}else if($dns == "cbs.bpr.sis1.net"){
		$cDNSSub = "https://api.sis1.cloud:44310" ; //"cbs.submodule.sis1.net" ; //Untuk development workspace docker (server container)
	}else{
		$cDNSSub = "https://api.sis1.cloud:44310";//"http://bpr.submodule.sis2.net" ; //Untuk production (server container) //Untuk production (server container) //"https://api.sis1.cloud:44310";//
	}
  
  //Sub module operasional
	$va["msSubModul_System"]    	= $cDNSSub . "/sub-bpr-system";
  $va["msSubModul_Asset"]     	= $cDNSSub . "/sub-bpr-aset" ;
	$va["msSubModul_General"]   	= $cDNSSub . "/sub-bpr-general";
	$va["msSubModul_Akuntansi"] 	= $cDNSSub . "/sub-bpr-akt";
	$va["msSubModul_Tabungan"]  	= $cDNSSub . "/sub-bpr-tabungan";
	$va["msSubModul_Deposito"]  	= "https://api.sis1.cloud:44310/mvc-sub-bpr-deposito/public"; //$cDNSSub . "/mvc-sub-bpr-deposito/public";
	$va["msSubModul_DepositoMVC"] = "https://api.sis1.cloud:44310/mvc-sub-bpr-deposito/public"; //$cDNSSub . "/mvc-sub-bpr-deposito/public";
	$va["msSubModul_Kredit"]    	= $cDNSSub . "/sub-bpr-kredit";
	$va["msSubModul_Sakep"]				= $cDNSSub . "/sub-bpr-sakep";
    
	//Sub module pelaporan eksternal
	$va["msSubModul_Camel"]        = $cDNSSub . "/mvc-sub-bpr-camel/public";
	$va["msSubModul_LBBPR"]        = $cDNSSub . "/sub-bpr-lbbpr";
	$va["msSubModul_SLIK"]         = $cDNSSub . "/sub-bpr-slik";
	$va["msSubModul_OBOX"]         = $cDNSSub . "/sub-bpr-obox";
	$va["msSubModul_ProfilResiko"] = $cDNSSub . "/sub-bpr-profilresiko" ; //"https://api.sis1.cloud:44310/sub-bpr-profilresiko";
	$va["msSubModul_TataKelola"]   = $cDNSSub . "/sub-bpr-tatakelola";
	$va["msSubModul_Simpel"]       = $cDNSSub . "/sub-bpr-simpel";
	$va["msSubModul_Sipendar"]     = $cDNSSub . "/sub-bpr-sipendar";
	$va["msSubModul_APKAP"]        = $cDNSSub . "/sub-bpr-apkap" ; //"https://api.sis1.cloud:44310/sub-bpr-apkap";
	$va["msSubModul_Payroll"]	  	 = $cDNSSub . "/sub-bpr-payroll";
	$va["msSubModul_Point"]	    	 = $cDNSSub . "/sub-bpr-point";
  
  //Sub module aplikasi non core banking
	$va["msSubModul_Mcollection"]   = $cDNSSub . "/sub-bpr-mcollection";
	$va["msSubModul_Mmodal"]   			= $cDNSSub . "/sub-bpr-mmodal";
	$va["msSubModul_Messaging"] 	  = $cDNSSub . "/sub-bpr-messaging";
	$va["msSubModul_MBanking"] 		  = $cDNSSub . "/sub-bpr-mbanking";
 	$va["msSubModul_H2HCurl"]   	 	= $cDNSSub . "/sub-bpr-h2h-curl";
  $va["msSubModul_Merchant"]   	 	= $cDNSSub . "/sub-bpr-merchant";

  //DNS Support Online
  $va['WebReportAct'] = "" ;
  $va['URLWO']        = "http://webadmin.sis1.net/clientarea" ;
  $va['URLChat']      = "http://webadmin.sis1.net/assist-chat.net" ;
	
	$cBaseUrl                 =  Svr::GetBaseURL();
	$va['auth_server_uri']    = "http://dev.sis1.net/assist-sso/public";
	$va['auth_client_id']     = "1" ; 
	$va['auth_redirect_uri']  = $cBaseUrl."api/callback" ;//"http://dev.sis1.net/assist-sso2/public/api/config";//"$cBaseUrl/api/config" ;
	$va['auth_response_type'] = "code" ;
  $va['auth_client_secret'] = "asda";
	$va['auth_scope'] 				= "" ; 
  $va['csrf_validation']    = 0 ;
	//Module Konversi

  // Variable Config Per User berdasarkan dns untuk merubah database nya
	// Biar Tidak di buat satu per satu dan lebih simple
	// Kita mencari File config.php di dalam folder customer/dns/config.php
	$cDir = isset($vadns [$dns]) ? $vadns[$dns] : $dns ;
	$cFile = __DIR__ . "/customer/$cDir/config.php" ;
	if(is_file($cFile)){
		include $cFile ;
	}
?>
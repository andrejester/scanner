<?php
	$cPath = __DIR__ . '/../connect.php';
	require_once($cPath);
	$cPathTes = __DIR__ . '/../../config/customer';
	$arg1 = isset($argv[1]) ? $argv[1] : "tidak masuk";
	//var_dump($arg1);
	$va['MModalNotif']  = true ;
	$cProsesName 	= basename(__FILE__);
	$lStatus  		= CekProses($cProsesName);
	if ($lStatus) {
		UpdProses($cProsesName,"prosess");
		if($va['MModalNotif']){
			SetMobileModal::SendNotifTRansaksi();
			SetMobileModal::setNotATM();
		}
		//PostingH2HCurlClass::GetAntrian();
		//PostingH2HCurlClass::SetStatus();
		//PostingH2HCurlClass::GetFirebase();
		UpdProses($cProsesName,"ok");
	}
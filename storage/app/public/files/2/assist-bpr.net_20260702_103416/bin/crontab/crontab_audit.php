<?php
$cPath = __DIR__ . '/../connect.php';
require_once($cPath) ;
$cProsesName = basename(__FILE__) ;
$lStatus  = CekProses($cProsesName);
if($lStatus){
	UpdProses($cProsesName,"prosess");
	$dTime = date('H:i') ;
  if($dTime >= '00:01' && $dTime <= '00:05') {
		PostingGeneral::PostingAudit();
	}
	UpdProses($cProsesName,"ok");
}
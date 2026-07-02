<?php
$cPath = __DIR__ . '/../connect.php';
require_once($cPath) ;
$cProsesName = basename(__FILE__) ;
$lStatus  = CekProses($cProsesName);
if($lStatus){
	UpdProses($cProsesName,"prosess");
  PostingAsset::PenyusutanJadwal();
	UpdProses($cProsesName,"ok");
}
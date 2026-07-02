<?php

//$vaURL = parse_url("bpr") ;
//print_r($tes);
	
$arg1 = isset($argv[1]) ? $argv[1] : "tidak masuk";

define('sessionstart',1) ;
define("main","1");
date_default_timezone_set('Asia/Jakarta');
$cDirAssistBpr = dirname(__DIR__);
$cPathConfig = $cDirAssistBpr."/config/customer/$arg1/config.php";
$cPathConfig = $cDirAssistBpr."/env/".$arg1.".env";
$componentDirectory = dirname(dirname(__DIR__)) . '/component/func.mod.php';
$cPathPerhitungan = dirname(dirname(__DIR__)) .  '/sub-bpr/sub-bpr-perhitungan/include/autoload/autoload.php';
$cPathPerhitunganFunc = dirname(dirname(__DIR__)). '/sub-bpr/sub-bpr-perhitungan/include/func.mod.php';
$lPosting = false ;

if(is_file($cPathConfig)){
	$lPosting = true ;
  require_once($componentDirectory);
  require_once($cPathPerhitungan);
  require_once($cPathPerhitunganFunc);
	$va = Svr::LoadEnv($cPathConfig) ;
  
	objData::Connect($va['ip'],$va['username'],$va['password'],$va['db']);
  chdir($cDirAssistBpr) ;
}else{
  echo "Config tidak ditemukan" ;
}

function CekProses($cKey){
	objData::Edit("config",array("Keterangan"=>time()),"Kode = 'msTimeAutoPosting'",false);
  $dbData = objData::Browse("rep_check.rep_gwctrl","PID","NAMESERVICE = '$cKey' and Status ='ok'");
  if($dbRow = objData::GetRow($dbData)){
    return true ;
  }
  return false;
}

function UpdProses($cKey,$cValue,$nTime=''){
	 $vaUpdate = array("PID"=>getmypid(),"Status"=>$cValue,"IDLE"=>time());
	 if (empty($cValue)) unset($vaUpdate["Status"]);
   objData::Edit("rep_check.rep_gwctrl",$vaUpdate,"NAMESERVICE = '$cKey'",false);
}

?>
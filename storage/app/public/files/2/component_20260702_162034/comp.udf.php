<?php

  //echo $_SERVER['REQUEST_METHOD'] ;
	//exit() ;
  if(!defined("main")) define("main","1") ;
  // Check Parameter Header
	if($_SERVER['REQUEST_METHOD'] == "POST"){
		$vaHTTP = array("HTTP_SIS_KEY","HTTP_SIS_TIMESTAMP","HTTP_SIS_VERSION","HTTP_SIS_SIGNATURE","HTTP_SIS_PATH") ;
	}else{
		$vaHTTP = array("HTTP_SIS_KEY","HTTP_SIS_TIMESTAMP","HTTP_SIS_VERSION","HTTP_SIS_SIGNATURE","HTTP_SIS_KODE","HTTP_SIS_PATH") ;
	}
  $vaResponse = array("ResponseCode"=>100,"Data"=>"") ;
  $lValid = true ;
  foreach($vaHTTP as $value){
    if(!isset($_SERVER [$value])){
      $vaResponse = array("ResponseCode"=>200,"Data"=>"Data Header Tidak Valid") ;
      $lValid = false ;
    }
  }

  // Check Signature
  if($lValid){
		if($_SERVER['REQUEST_METHOD'] == "POST"){
			$cBody = file_get_contents("php://input") ;
			$cSignature = md5("{$_SERVER['HTTP_SIS_KEY']}:{$_SERVER['HTTP_SIS_TIMESTAMP']}:{$_SERVER['HTTP_SIS_VERSION']}:$cBody") ;
		}else{
			$cSignature = md5("{$_SERVER['HTTP_SIS_KEY']}:{$_SERVER['HTTP_SIS_TIMESTAMP']}:{$_SERVER['HTTP_SIS_VERSION']}:") ;
		}
    
    if($cSignature <> $_SERVER['HTTP_SIS_SIGNATURE']){
      $lValid = false ;
      $vaResponse = array("ResponseCode"=>204,"Data"=>"Code Signature Tidak Valid") ;
    }
  }
  if($lValid){
    chdir(".." . $_SERVER['HTTP_SIS_PATH']) ;
    $lNoJS = true ;
    $lFuncOnly = true ;
    require_once "./include/database.php" ;
		$vaFunc = json_decode($_SERVER ['HTTP_SIS_KODE'],true) ;
		
		if($_SERVER['REQUEST_METHOD'] == "POST"){
			$cBody = file_get_contents("php://input") ;
			if(isset($vaFunc ['func']) && isset($cBody)){
				$nPos = strpos($vaFunc ['func'],"(") ;
				if($nPos !== false) $vaFunc ['func'] = substr($vaFunc ['func'],0,$nPos) ;
				$vaFunc ['func'] = trim($vaFunc ['func']) ;

				$vaResponse ["Data"] = call_user_func_array($vaFunc ["func"],json_decode($cBody,1)) ;
			}else{
				$vaResponse = array("ResponseCode"=>210,"Data"=>"Format Function Tidak Valid") ;
			}
		}else{
			if(isset($vaFunc ['func']) && isset($vaFunc ['par'])){
				$nPos = strpos($vaFunc ['func'],"(") ;
				if($nPos !== false) $vaFunc ['func'] = substr($vaFunc ['func'],0,$nPos) ;
				$vaFunc ['func'] = trim($vaFunc ['func']) ;

				$vaResponse ["Data"] = call_user_func_array($vaFunc ["func"],$vaFunc["par"]) ;
			}else{
				$vaResponse = array("ResponseCode"=>210,"Data"=>"Format Function Tidak Valid") ;
			}
		}
  }
  echo(json_encode($vaResponse)) ;
?>
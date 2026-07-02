<?php
  /*
  File ini kita gunakan untuk Membuka Sub Menu yang di akses dari service Lain.
  */
  // Check Parameter Header

  $vaHTTP = array("HTTP_SIS_KEY","HTTP_SIS_PATH","HTTP_SIS_TIMESTAMP","HTTP_SIS_VERSION","HTTP_SIS_SIGNATURE") ;
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
    $cSignature = md5("{$_SERVER['HTTP_SIS_KEY']}:{$_SERVER['HTTP_SIS_TIMESTAMP']}:{$_SERVER['HTTP_SIS_VERSION']}:") ;
    if($cSignature <> $_SERVER['HTTP_SIS_SIGNATURE']){
      $lValid = false ;
      $vaResponse = array("ResponseCode"=>204,"Data"=>"Code Signature Tidak Valid") ;
    }
  }

  if($lValid){
    $vaSubMenu = GetSubMenu($_SERVER['HTTP_SIS_PATH']) ;
    $vaResponse ["Data"] = $vaSubMenu ;
	}

	echo(json_encode($vaResponse)) ;

  function GetSubMenu($cPath){
    $vaRetval = array() ;
		// Jika submodule mvc kita pakai Svr::GetProjectPath() ;
		if(isset($_SERVER['HTTP_SIS_SUB_MVC']) && strtolower($_SERVER['HTTP_SIS_SUB_MVC']) == "mvc") {
			$cFile = Svr::GetProjectPath() . "/mvc/submenu.menu.php" ;
		}else{
			$cFile = "..$cPath/submenu.menu.php" ;
		}

    if(is_file($cFile)){
      $vaLine = file($cFile) ;
      $cKey = "" ;
      foreach($vaLine as $key=>$value){      
        if(strtolower(substr(trim($value),0,9)) == "<submenu:"){
          $value = trim(strtolower($value)) ;
          $cKey = str_replace(">","",substr($value,9)) ;
        }else if(!empty($cKey)){        
          $val = trim($value) ;
          $lShowMenu = true ;
          if(substr($val,0,2) == "//" || empty($val)){
            $lShowMenu = false ;
          }
          if($lShowMenu && trim($value) <> "") $vaRetval [$cKey][$key] = $value ;
        }
      }
    }
    return $vaRetval ;
  }
?>
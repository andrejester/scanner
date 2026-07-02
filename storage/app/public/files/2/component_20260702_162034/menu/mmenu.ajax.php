<?php
  include 'df.php' ;
/* Dijadikan 1 Dengan Save Log Ajax.js
function MenuSaveLog($va){
  SysLog::ClickMenu($va) ;
}

function UpdLogCloseMenu($va){
  SysLog::CloseForm($va) ;
}
*/
function loadSublocal($va){
	$vaArray = array() ;
	/*
	if(!defined('sessionstart')){    
		define('sessionstart',1) ;
	
		define("__COMPONENT_VERSION__","2.1") ;	
		include './func.mod.php' ;
		include './themes/css.php' ;

		session_start() ;   

		// Update Data CDS
		$cToken = cds::CreateToken() ;
	}else{  
		$cToken = cds::CreateToken() ;//terpakas dibuat seperti ini untuk mensiasati menu yang melalui toolbar
	}
	*/
	$cToken 		= cds::CreateToken() ;
	$__par 			= isset($va ['cParam']) ? $va ['cParam'] : "" ;
	$cHost 			= isset($va ['cHost']) ? $va ['cHost'] : "" ;
	$cMnu  			= isset($va ['cMenuNumber']) ? $va ['cMenuNumber'] : "" ;
	$cMnuTitle 	= isset($va ['cMenuTitle']) ? $va['cMenuTitle'] : "" ;
	$vaHost 		= explode("/",$cHost) ;
	$cHost 			= fix_url($cHost);
	if(!empty($__par) && !empty($cHost)){
		// Ambil Parameter yang lain untuk dikirim ke client
		$__par .= "&__token=" . $cToken ;
		foreach($va as $key=>$value){
			if($key <> "__par" && $key <> "cHost"){
				$__par .= "&$key=$value" ;
			}
		}
		$vaArray = array("par"=>$__par, "menu"=>$cMnu,"mnutitle"=>$cMnuTitle) ;
	}
	echo json_encode($vaArray) ;
}
?>
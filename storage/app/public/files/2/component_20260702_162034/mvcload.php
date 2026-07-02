<?php
	if(!defined("main")) define("main","1") ;
	if(!defined("vaConfig")) define("vaConfig",1) ;		
	require_once __DIR__ . "/func.mod.php" ;
	if(session_status() === PHP_SESSION_NONE){
		require __DIR__ . "/session/init.php" ;
	} 	

  if(Svr::GetConfig("extension",false)){
	  DeviceExtension::Set();
  	Svr::$lInit = false;
	}
  
  if(!defined("__COMPONENT_VERSION__")) define("__COMPONENT_VERSION__","2.3.4") ;
	if(!defined("__COMPONENT_DIR__")) define("__COMPONENT_DIR__",__DIR__) ;
	if(!defined("__COMPONENT_TYPE__")) define("__COMPONENT_TYPE__","mvc") ;

	// Kita Langsung Load File yang dibutuhkan Untuk Menjalakan MVC Tanpa melalui Autoload Biar lebih cepat
	$va = ["MVC","MVC_Controller","SisMVC"] ;
	foreach($va as $key=>$value){
		$cFile = __DIR__ . "/autoload/Classes/MVC/$value.mod.php" ;
		if(is_file($cFile)) require_once $cFile ;
	}
?>
<?php
  include 'df.php' ;

	// Parameter Laporan Sudah di kirim Via POST
	$__par = Svr::GetPar("__par","",false) ;			// __par hanya boleh dikirim via POST
  if(!empty($__par) && is_file($__par)){
	  eval(Conv::GetVar([],false)) ;
		include $__par ;
  }
?>
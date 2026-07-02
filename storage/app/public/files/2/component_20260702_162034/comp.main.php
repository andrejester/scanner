<?php
  include 'df.php' ;

	// Semua Halaman harus memiliki appid dan __token kalau tidak tidak kita lanjutkan membuka page nya.
	// Sekarang Token dan appid harus di kirim via POST tidak boleh via GET
	if(Svr::GetPar("__token","",false) == "" || Svr::GetPar("appid","",false) == ""){
		die('Halaman Tidak bisa dibuka ......!') ;
	}
	eval(Conv::GetVar([],false)) ;							// GetVar ( Untuk memindahkan $_POST ke variable hanya bisa menerima $_POST saja)
	$__par = Svr::GetPar("__par","",false) ;		// __par Hanya dibolehkan Via POST
	$_GET["__par"] = $__par ;

	if(!empty($__par)){
    SaveSetting('__OldPar',$__par) ;
    $__par = GetSetting($__par,"xx") ;
    if($__par == "xx"){
      $__par = GetSetting('__OldPar') ;
    }

    $nParam = strpos($__par,"?") ;
    if($nParam){
      $cParam = substr($__par,$nParam+1) ;
      $vaParam = explode("&",$cParam) ;
      foreach($vaParam as $key=>$value){
        $va = explode("=",$value) ;
        
        $value = "$" .  $va[0] . " = '" . $va [1] .  "' ;" ;
        eval($value) ;
      }
      $__par = substr($__par,0,$nParam) ;
    }

		if(is_file($__par)){
			include $__par ;
      
      // Setting Background
      $vaBG = array("loginbgmin"=>"","loginbgmax"=>"","mainbgmin"=>"","mainbgmax"=>"") ; 
      if(function_exists("GetBackgorund")){
        $vaBG   = GetBackgorund() ;
      }
			require_once GetFileModul(__FILE__,'.jscript.php') ;
			
			//retract dari cookie ke dom
			if(isset($_POST["__token"])) echo("<div style='display:none' id='divToken'>" . $_POST["__token"] . "</div>") ;
			if(isset($_POST["appid"])) echo("<div style='display:none' id='divAppID'>" . $_POST["appid"] . "</div>") ;
			
			// Untuk Memberi Info Dia Bukan MVC tapi sudah menggunakan Class comp untuk main.php, ajax.php 
			// Karena dia sudah menggunakan model baru dan sudah tidak menggunakan session karena dia submodule, selain itu di anggap program lama.
			echo("<div style='display:none' id='divClassComp'>1</div>") ;
			
			// Div ini kita gunakan untuk menyimpan File yang aktif untuk keperluan ajax dll, kalau url kita isi kosong maka di mengambil file yang aktif
			echo('<div id="__currentFile" style="display:none">' . $__par . '</div>');
    }
  }
	
?>
<?php
/*
Class Kita ganti ke static class tab::
*/
class SisTab{
	private $va = [] ;
	function __construct(){
		$this->va = ["Height"=>200] ;
	}

  function Show($nHeight=null){
    if(!Svr::IsMVC()) global $txt ;
		if($nHeight == null && isset($this->va["Height"])) $nHeight = $this->va["Height"] ;
		tab::Show($nHeight) ;
	}

  function Add($cTitle,$cURL='',$lSelected=false){
		tab::Add($cTitle,$cURL,$lSelected) ;
  }

	function __set($var,$value){
		$this->va [$var] = $value ;
	}
}
?>
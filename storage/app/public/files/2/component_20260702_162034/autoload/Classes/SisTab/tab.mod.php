<?php
/* 
Standart Class Autoload
1. Pastikan File Berapa di Folder project/include/autoload/Classes/
2. Pastikan Nama Class Sama Dengan Nama File
3. Tidak Boleh Ada nama Class Kembar di semua Subdir nya
*/
class tab {
	private static $vaTab = [] ;
	private static $nTabCount = 0 ;

	private static function initTab(){
		self::$vaTab = [] ;
	}

	static function Show($nHeight=200){
		$nHeight = (strpos($nHeight,"%") === false) ? $nHeight + 23 : $nHeight ;
    $cName = "sisTab" . ++self::$nTabCount ;
		$va[] = ["height"=>$nHeight,"name"=>$cName,"vaTab"=>self::$vaTab] ;

    echo("<div id='_tab_div_main_' class='tab_main' style='display:none;height:$nHeight'>" . json_encode($va) . "</div>") ;
		Mod::ImportJS("tab") ;
 		self::initTab() ;
  }

	static function AddArray($vaTab){
		foreach($vaTab as $key=>$value){
			self::Add($key,$value) ;
		}
	}

  static function Add($cTitle,$cDivContent='',$lSelected=false){
		if(!Svr::IsMVC()) global $txt ;
		if(substr($cDivContent,0,1) == "#"){
			$cDivContent = substr($cDivContent,1) ;
		}else{
			$url = $cDivContent ;
			$cDivContent = "div-" . md5($cTitle) ;
			echo("<div id='$cDivContent'>") ;
			if($url !== "") include $url ;
			echo('</div>') ;
		}
    self::$vaTab[] = ["title"=>$cTitle,"selected"=>$lSelected,"divcontent"=>$cDivContent] ;
  }
}

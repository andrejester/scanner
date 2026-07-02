<?php
  include 'df.php' ;

class oMenu{
  static function GetArray($cMenuFileName='',$lConfig=false){
		return [] ;
  }

	static function SubMenuFile($cFile=""){
		menu::SubMenuFile($cFile) ;
	}
	
  // Create Menu
  static function menuHorizontal($nTop=1,$nLeft=1,$cMultiForm='',$cMenuFileName='',$lMySQL=true){
		menu::show() ;
		return true;
  }
}
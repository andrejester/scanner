<?php
  include 'df.php' ;

class odtOld {
  public static function Open($cFile){
    return odt::Open($cFile) ;
  }

  public static function Save($content){    
    return odt::Save($content) ;
  }

  public static function br(){
    return odt::br() ;
  }
}
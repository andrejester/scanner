<?php
  include 'df.php' ;

class dbCore extends dbCore_Global { 
  static $Error = "" ;
	protected static $lConnect = false ;
	
  static function Connect($cIP,$cUserName,$cPassword,$cDatabase){
    $lLink = mysql_connect($cIP, $cUserName,$cPassword) ;
    if($lLink) self::SelectDB($cDatabase) ;
		self::$lConnect = $lLink ;
    return $lLink ;
  }
  
  static function GetRow($dbData){
    if(empty(self::$Error)){
      $va = mysql_fetch_assoc($dbData) ;
      if(!empty($va) && is_array($va)){
        foreach($va as $key=>$value){
          $va [$key] = str_replace("'","\'",str_replace('"','\"',str_replace("\\","\\\\",$value))) ; 
        }
      }
      return $va ;
    }else{
      echo(self::$Error) ;
    }
  }
  
  static function Rows($dbData){ 
    return mysql_num_rows($dbData) ;
  }
  
  static function Cols($dbData){
    return mysql_num_fields($dbData) ;
  }
  
  static function GetInsertID(){
    return mysql_insert_id() ;
  }
	
	static function SelectDB($cDatabase){
    return mysql_select_db($cDatabase) ;
  }

  static function FetchArray($dbData){
    return mysql_fetch_array($dbData) ;
  }

  static function FetchAssoc($dbData){
    return mysql_fetch_assoc($dbData) ;
  }

  static function SQLError(){
    return mysql_error() ;
  }

  static function NumRows($dbData){
    return mysql_num_rows($dbData) ;
  }

  static function SQLClose(){ 
    return mysql_close() ;
  }
  
  static function LastInsertID(){
    return mysql_insert_id() ;
  }
  
  static function SQL($cSQL){
		if(!self::$lConnect){
			$va = self::GetConfig() ;
			self::Connect($va['cHost'],$va['cUserName'],$va['cPassword'],$va['cDatabase']) ;
		} 

    $dbData = mysql_query($cSQL) ;
    self::$Error = mysql_error() ;
    return $dbData ;
  }
}
<?php
  include 'df.php' ;

class dbCore extends dbCore_Global { 
  static $Error = "" ;
  protected static $dbCon = null ;
	protected static $lConnect = false ;
	private static $vaDB = array() ;

	/*
	Untuk Method ini dulu nya membuat koneksi database, tapi sekarang hanya menyimpan Configurasi, Koneksi hanya akan di lakukan kalau kita ada SQL ke database
	*/
	static function Connect($cIP,$cUserName,$cPassword,$cDatabase){
		self::$vaDB = array("cHost"=>$cIP,"cUserName"=>$cUserName,"cPassword"=>$cPassword,"cDatabase"=>$cDatabase) ;
	}
	
	/*
	Perintah Koneksi Ke Database menggunakan Private Method ini, dan tidak di akses dari luar Class
	*/
	private static function DBCon($cIP,$cUserName,$cPassword,$cDatabase){
		self::$dbCon = mysqli_connect($cIP,$cUserName,$cPassword,$cDatabase) ;
		self::$lConnect = mysqli_connect_errno() ? false : true ;
    return self::$lConnect ;
  }

  static function GetRow($dbData){
    if(empty(self::$Error) && self::$lConnect){
      return mysqli_fetch_assoc($dbData) ;
    }else{
      echo(self::$Error) ;
    }
  }
  
  static function Rows($dbData){
    return mysqli_num_rows($dbData) ;
  }
  
  static function Cols($dbData){
    return mysqli_num_fields($dbData) ;
  }
  
  static function GetInsertID(){
    return mysqli_insert_id(self::$dbCon) ;
  }
  
  static function SelectDB($cDatabase){
    return mysqli_select_db(self::$dbCon,$cDatabase) ;
  }

  static function FetchArray($dbData,$resultType=MYSQLI_BOTH){
    return mysqli_fetch_array($dbData,$resultType) ;
  }

  static function FetchAssoc($dbData){
    return mysqli_fetch_assoc($dbData) ;
  }

  static function SQLError(){
    return mysqli_error(self::$dbCon) ;
  }

  static function NumRows($dbData){
    return mysqli_num_rows($dbData) ;
  }

  static function SQLClose(){ 
    return mysqli_close(self::$dbCon) ;
  }
  
  static function LastInsertID(){
    return mysqli_insert_id(self::$dbCon) ;        
  }
  
	static function AffectedRows(){
		return self::$dbCon->affected_rows ;
	}
	
  static function SQL($cSQL){
		if(!self::$lConnect){
			if(!isset(self::$vaDB ['cHost']) || !isset(self::$vaDB ['cUserName'])) self::$vaDB = self::GetConfig() ;
			self::DBCon(self::$vaDB['cHost'],self::$vaDB['cUserName'],self::$vaDB['cPassword'],self::$vaDB['cDatabase']) ;
		} 

    $dbData = mysqli_query(self::$dbCon,$cSQL) ;
    self::$Error = mysqli_error(self::$dbCon) ;
    return $dbData ;
  }
}
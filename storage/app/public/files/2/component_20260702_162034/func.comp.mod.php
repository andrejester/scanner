<?php
  include 'df.php' ;
  $cLink = "" ;
  
/*
  Kita Akan Definisikan Function yang Tidak ada di PHP Baru Supaya Bisa Kita Definisikan
  session_register($cKey) ;
  session_is_registered($cKey) ;
  split(csep,string,limit) ;
*/

if (!function_exists('session_is_registered')) {
  function session_is_registered($cName){
    return isset($_SESSION[$cName]) ;
  } 
}

if (!function_exists('session_register')) {
  function session_register($cName){
    $_SESSION[$cName] = "" ;
  }
}

if (!function_exists('split')){     
  function split($cSep,$cString,$nLimit=2147483647){
    return explode($cSep,$cString,$nLimit) ;
  }
}

if (!function_exists('mysql_connect')){      
  function mysql_connect($cHost,$cUserName,$cPassword,$cDatabase=""){
    $cLink = mysqli_connect($cHost,$cUserName,$cPassword,$cDatabase) ; 
    return $cLink ;
  }
} 

if (!function_exists('mysql_query')){
  function mysql_query($cSQL){ 
    return objData::SQL($cSQL) ;
  }
} 

if (!function_exists('mysql_fetch_array')){      
  function mysql_fetch_array($dbData){ 
    return objData::FetchArray($dbData) ;
  }
} 

if (!function_exists('mysql_fetch_assoc')){      
  function mysql_fetch_assoc($dbData){ 
    return objData::FetchAssoc($dbData) ;
  }
} 

if (!function_exists('mysql_error')){      
  function mysql_error(){ 
    return objData::SQLError() ;
  }
} 

if (!function_exists('mysql_num_rows')){      
  function mysql_num_rows($dbData){ 
    return objData::NumRows($dbData) ;
  }
} 

if (!function_exists('mysql_close')){      
  function mysql_close(){ 
    return objData::SQLClose() ;
  }
} 

if (!function_exists('mysql_select_db')){      
  function mysql_select_db($cDatabase){ 
    return objData::SelectDB($cDatabase) ;
  }
}

if (!function_exists('mysql_insert_id')){      
  function mysql_insert_id(){ 
    return objData::LastInsertID() ;
  }
}

// Function UDF kita ganti ke component
if(!function_exists('GetSetting')){
  function GetSetting($cKey,$cDefault = ''){  
    return GetSession($cKey,$cDefault) ;
  }
}

if(!function_exists('SaveSetting')){
  function SaveSetting($cKey,$cValue){
    SaveSession($cKey,$cValue) ;
  }
}

if(!function_exists("getlink")){
	function getlink($cChar,$lEcho=true){
		if($lEcho){
			echo($cChar) ;
		}else{
			return $cChar ;
		}
	}
}
?>
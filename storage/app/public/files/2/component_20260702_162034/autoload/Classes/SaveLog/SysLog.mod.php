<?php
  include 'df.php' ;
/*
 Kode MTI
	01 Login
	02 Load Home / Refresh Program
	03 Open Form
	04 Insert DB
	05 Edit DB Old Value
	06 Edit DB New Value
	07 Delete DB 
	08 Select DB
	09 Open Report (EzStream)
	10 CDS
	11 Print IO
	12 Close Form
	13 Logout
*/
class SysLog {
  static function Save($cMessage,$cProgram="",$cFasility="142",$cRoot=""){

		/* Source 6 Mei 2025
    if($cProgram == ""){
      $cProgram = "sis_php" ;
      $cHost = "sis_php_sub" ;
      $_c = str_replace($_SERVER["DOCUMENT_ROOT"] . "/","",$_SERVER ['SCRIPT_FILENAME']) ;
      $vaPrg = explode("/",$_c) ;

      if(GetSetting('cSession_WebReportAct') != ""){ 
        $cProjectKey = GetSetting('cSession_WebReportAct') ;
        
        #Format log = assist-bpr.net submodule keydariwebadmin        
        #Untuk menyimpan log menu apa yang sedang dibuka / ditutup pada CBS Submodule
        #Untuk menyi06mpan proes CRUD pada form CBS Submodule
        if(GetSetting("cSession_MainHost") != "" && strpos(GetSetting("cSession_MainHost"),"/") > 0){
          $vaMainHost = explode("/",GetSetting("cSession_MainHost")) ;
          if(isset($vaMainHost [1])) $cHost = $vaMainHost [1] ;
          $cProgram   = $cHost . " submodule " . $cProjectKey; 
        }else{
	        if(isset($vaPrg [0])) $cProgram = $vaPrg [0] . " submodule " . $cProjectKey;
        }
      }else{
        if(isset($vaPrg [0])) $cProgram = $vaPrg [0] ;
      }
    }
    openlog($cProgram, LOG_NDELAY, LOG_LOCAL1);
    End Source 6 Mei 2025 */
		$cProgram = GetSetting("cSession_ProjectName") ;
		if($cProgram == ""){
			if(!isset($_SERVER['HTTP_HOST'])) $_SERVER['HTTP_HOST'] = php_uname('n') ;
			$cProgram = rtrim(preg_replace('/^https?:\/\//','',Svr::GetConfig("log_name",$_SERVER['HTTP_HOST'])),'/');
			$cProgram = GetSetting("cDataCustomer",$cProgram) ;
			SaveSetting("cSession_ProjectName",$cProgram) ;
		}
		$n = $cFasility%8 ;
		//echo $cProgram ." // ".$cMessage ;
		openlog($cProgram, LOG_NDELAY, LOG_LOCAL1);
		if(is_array($cMessage)){
			/* Untuk Filtering Data
			if(isset($cMessage['Data'])){
				$vaExeption = array("__par","mnuid","__token","appid","UserPassword","EmailPassword") ;
				foreach($vaExeption as $key){
					if(isset($cMessage['Data'][$key])) unset($cMessage['Data'][$key]) ;
				}	
			}
			*/
			$cMessage = json_encode($cMessage) ;
		}
		syslog($n, $cMessage);
  }

  static function GetHost(){
    $cIP = (isset($_SERVER['REMOTE_ADDR'])) ? $_SERVER['REMOTE_ADDR'] : "" ;
    if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])){
      $cIP = $_SERVER['HTTP_X_FORWARDED_FOR'] ;
    }
    return $cIP ;
  } 
  
  static function GetAgent(){  
    $cRetval = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : "unknown"  ;
    return $cRetval ;
  }
	
	private static function GetInitData($cMTI="99"){
		$va = array("MTI"=>$cMTI,"DT"=>date("Ymdhis"),"UID"=>GetSetting("cSession_UserName"),"IP"=>self::GetHost(),"BR"=>self::GetAgent()) ;
		return $va ;
	}
	
	private static function FilterData($va){
		//Filtering Data DB & Report
		$vaExeption = array("UserPassword","EmailPassword","__par","mnuid","__token","appid") ;
		foreach($vaExeption as $key){
			if(isset($va[$key])) unset($va[$key]) ;
		}
		return $va ;
	}
	
	static function Login(){
    $_va = self::GetInitData("01") ;
		$_va['Data'] = "Login Program" ;
		self::Save($_va) ;
		//self::Save(json_encode($_va)) ;
  }
	
	static function LoadHome(){
    $_va = self::GetInitData("02") ;
		$_va['Data'] = "Load Home / Refresh Program" ;
		self::Save($_va) ;
		//self::Save(json_encode($_va)) ;
  }
	
	static function SaveLog($va){
		$_va 					= self::GetInitData($va['cMTI']) ;
    $_va['Data'] 	= $va['cMessage'] ;
		self::Save($_va) ;
		//self::Save(json_encode($_va)) ;
  }

  static function InsertDB($cTableName,$vaField){
    $_va = self::GetInitData("04") ;
		if(isset($_POST['_MENU_'])){
      $_va['Data']['Mnu'] = $_POST['_MENU_'] ;
    }
    
    if(isset($_POST['_MENUTITLE_'])){
      $_va['Data']['MnuTitle'] = $_POST['_MENUTITLE_'] ;
    }
		
    $_va['Data']['Table'] = $cTableName ;  
		$_va['Data']['Field'] = self::FilterData($vaField) ;
		
		self::Save($_va) ;
		//self::Save(json_encode($_va)) ;
  }

  static function EditDB($cTableName,$vaField,$cMTI="05"){
    foreach($vaField as $vaRow){
      $_va = self::GetInitData($cMTI) ;
			if(isset($_POST['_MENU_'])){
        $_va['Data']['Mnu'] = $_POST['_MENU_'] ;
      }

      if(isset($_POST['_MENUTITLE_'])){
        $_va['Data']['MnuTitle'] = $_POST['_MENUTITLE_'] ;
      }

      $_va['Data']['Table'] = $cTableName ;  
			$_va['Data']['Field'] = self::FilterData($vaRow) ;
			
			self::Save($_va) ;
			//self::Save(json_encode($_va)) ;
    }
  }
	
	static function SelectDB($cTableName,$cFieldList,$cWhere,$vaJoin,$cGroupBy,$cOrderBy,$cLimit){
		$_va 				 = self::GetInitData("08") ;
		$_va['Data'] = array("TableName"=>$cTableName,"FieldList"=>$cFieldList,"Where"=>$cWhere,"Join"=>$vaJoin,"GroupBy"=>$cGroupBy,
								 				 "OrderBy"=>$cOrderBy,"Limit"=>$cLimit) ;
		self::Save($_va) ;
		//self::Save(json_encode($_va)) ;
	}
	
	static function Report($cMTI,$vaData){
		$lUpdate = (isset($vaData['Kode']) && trim($vaData['Kode']) == "GetSubMenu") ? false : true ;
		  
		if($lUpdate){
			$_va 				 = self::GetInitData($cMTI) ;
			$_va['Data'] = self::FilterData($vaData) ;
			self::Save($_va) ;
			//self::Save(json_encode($_va)) ;
		}
  }
	
	static function Logout(){
    $_va = self::GetInitData("13") ;
		$_va['Data'] = "Logout Program" ;
		self::Save($_va) ;
		//self::Save(json_encode($_va)) ;
  }
	
}
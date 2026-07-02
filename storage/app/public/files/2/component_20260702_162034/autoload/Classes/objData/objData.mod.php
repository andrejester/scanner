<?php
  include 'df.php' ;
	$_db = function_exists('mysqli_query') ? 'objData.mysqli.mod.php' : 'objData.mysql.mod.php' ;  

	require_once __DIR__ . "/objData.global.mod.php" ;
	require_once __DIR__ . "/$_db" ;

class objData extends dbCore {
	static function ABrowse($cTableName,$cFieldList = "*",$cWhere = "",$vaJoin = [],$cGroupBy = "",$cOrderBy = "",$cLimit = "",$lSaveLog = false){
    $dbData = self::Browse($cTableName,$cFieldList,$cWhere,$vaJoin,$cGroupBy,$cOrderBy,$cLimit,$lSaveLog) ;
		return self::FetchAssoc_All($dbData) ;
  }

  static function Browse($cTableName,$cFieldList="*",$cWhere="",$vaJoin=[],$cGroupBy="",$cOrderBy="",$cLimit="",$lSaveLog = false){
    $cTableName = strtolower($cTableName) ;
    if(trim($cWhere) <> "" && substr(strtoupper(trim($cWhere)),0,5) <> "WHERE") $cWhere = "Where $cWhere " ;
    if(trim($cGroupBy) <> "") $cGroupBy = "Group By $cGroupBy " ;
    if(trim($cOrderBy) <> "") $cOrderBy = "Order By $cOrderBy " ;
    if(trim($cLimit) <> "") $cLimit = "Limit $cLimit" ;
    $cJoin = "" ;
    if(!empty($vaJoin) && is_array($vaJoin)){
      foreach($vaJoin as $key=>$value){
        $cJoin .= $value . " " ;
      }
    }
    $cSQL = "Select $cFieldList from $cTableName $cJoin $cWhere $cGroupBy $cOrderBy $cLimit" ;
		if($lSaveLog) SysLog::SelectDB($cTableName,$cFieldList,$cWhere,$vaJoin,$cGroupBy,$cOrderBy,$cLimit) ;
    return self::SQL($cSQL) ;
  }
  
  static function Insert($cTableName,$vaArray,$lSaveLog = true){
    $cTableName = strtolower($cTableName) ;
    $cField 		= "" ;
    $cValue 		= "" ;
		$lArray     = false ;  
    foreach($vaArray as $key => $value){
			if(is_array($value)){
				$cValMulti = "" ;
				foreach($value as $key2 => $vaValue){
					if(!$lArray){
						$cField .= $key2 . "," ;
					}
					$cValMulti .= $cValMulti == "" ? $vaValue."'" : ",'".$vaValue."'"   ;
				}
				$lArray  = true ;
				$cValue .= $cValue == "" ? "('".$cValMulti.")" : ",('".$cValMulti.")" ;
			}else{
				$cField .= $key . "," ;
				$cValue .= $value . "','" ;
				if(is_string($value) && substr($value,0,1) == "&"){
					$cValue = str_replace("'" . $value . "'",substr($value,1),$cValue) ;
				}
			}
    }
		
		$cField = "(" . substr($cField,0,strlen($cField)-1) . ")" ;
		$cValue = $lArray ? $cValue : "('" . substr($cValue,0,strlen($cValue)-2) . ")" ;
		
		$cSQL = "INSERT INTO $cTableName $cField VALUES $cValue" ;
	  self::SQL($cSQL) ;
    if($lSaveLog) SysLog::InsertDB($cTableName,$vaArray) ;
    return empty(self::$Error) ;
  }
  
  static function Edit($cTableName,$vaArray,$cWhere = "",$lSaveLog = true){		
    $cTableName = strtolower($cTableName) ;
    if($lSaveLog){
      $dbCount = self::Browse($cTableName,"count(*) as Jumlah",$cWhere) ;
      $nJumlah = 0 ;
      if($dbRow = self::GetRow($dbCount)){
        $nJumlah = $dbRow ['Jumlah'] ;      
      }
      // Simpan Old Value
      if($nJumlah > 0 && $nJumlah <= 20){
        $vaRow = self::ABrowse($cTableName,"*",$cWhere) ;
        SysLog::EditDB($cTableName,$vaRow,"05") ;
      }
    }

    $cSQL = "" ;
    foreach($vaArray as $key=>$value){
      if(substr($value,0,1) == "&"){
        $value = substr($value,1) ;
      }else{
				$value = str_replace("'","\'",$value) ;
        $value = "'$value'" ;
      }
      $cSQL .= "$key = $value," ;
    }
    $cSQL = substr($cSQL,0,strlen($cSQL)-1) ;
    
    if($cWhere <> "") $cWhere = "WHERE $cWhere" ;
    $cSQL = "UPDATE $cTableName SET $cSQL $cWhere" ;
    self::SQL($cSQL) ;

    if($lSaveLog){
      // Simpan New Value
      if($nJumlah > 0 && $nJumlah <= 20){
        $vaRow = self::ABrowse($cTableName,"*",$cWhere) ;
        SysLog::EditDB($cTableName,$vaRow,"06") ;
      }
    }

    return empty(self::$Error) ;
  }
  
  static function Update($cTableName,$vaArray,$cWhere = "",$lSaveLog = true){
    $cTableName = strtolower($cTableName) ;
    $cWhere = trim($cWhere) ;
    $lNew = true ;
    if($cWhere !== ""){
      $dbData = self::Browse($cTableName,"*",$cWhere,"","","","0,1") ;
      if(self::Rows($dbData) > 0){
        $lNew = false ;
      }
    }
    if($lNew){
      self::Insert($cTableName,$vaArray,$lSaveLog) ;
    }else{
      self::Edit($cTableName,$vaArray,$cWhere,$lSaveLog) ;
    }
    return empty(self::$Error) ;
  }

  static function Delete($cTableName,$cWhere = "",$lSaveLog = true){
    $cTableName = strtolower($cTableName) ;
    
    if($lSaveLog){
      // Check Record Untuk Simpan di Log
      $dbCount = self::Browse($cTableName,"count(*) as Jumlah",$cWhere) ;
      $nJumlah = 0 ;
      if($dbRow = self::GetRow($dbCount)){
        $nJumlah = $dbRow ['Jumlah'] ;
      
        if($dbRow ['Jumlah'] > 0 && $dbRow ['Jumlah'] <= 20){
          $vaRow = self::ABrowse($cTableName,"*",$cWhere) ;
          SysLog::EditDB($cTableName,$vaRow,"07") ;
        }
      }
    }
    
    if(trim($cWhere) <> "") $cWhere = "WHERE $cWhere" ;
    $cSQL = "DELETE FROM $cTableName $cWhere" ;
    self::SQL($cSQL) ;
    return empty(self::$Error) ;
  }
	
	static function FetchAssoc_All($dbData,$cFileKey=""){
		$vaRetval = [] ;
		$nRow = 0 ;
		while($dbRow = self::GetRow($dbData)){
			$key = $cFileKey !== "" ? $dbRow [$cFileKey] : $nRow ++ ;
			$vaRetval[$key] = $dbRow ;
		}
		return $vaRetval ;
	}
}
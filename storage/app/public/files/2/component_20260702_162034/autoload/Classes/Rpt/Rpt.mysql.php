<?php
class Rpt_Data {
	protected static function Connect(){
		$dbData = objData::SQL("SHOW TABLES LIKE 'sis_rpt'") ;
		if(!objData::GetRow($dbData)){
			$cSQL = "CREATE TABLE `sis_rpt` (" ;
			$cSQL .= "`ID` varchar(100) NOT NULL," ;
			$cSQL .= "`Expired` int(10) unsigned NOT NULL DEFAULT '0'," ;
			$cSQL .= "`Data` longtext," ;
			$cSQL .= "PRIMARY KEY (`ID`)," ;
			$cSQL .= "KEY `Expired` (`Expired`)" ;
			$cSQL .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8" ;
			objData::SQL($cSQL) ;
		}

		// Hapus Data Expired
		objData::Delete("sis_rpt","Expired < '". time() . "'",false) ;
	}

	protected static function _Save($cKey,$cSub,$cKode,$value="",$exp=3600){
		$exp = time() + $exp ;
		$key = $cKey . $cSub . $cKode ;

		//$va = ["ID"=>$key,"Expired"=>$exp,"Data"=>addslashes(json_encode($value)] ;
		$va = ["ID"=>$key,"Expired"=>$exp,"Data"=>json_encode($value,JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE)] ;
		objData::Update("sis_rpt",$va,"ID = '$key'",false) ;
		objData::Edit("sis_rpt",["Expired"=>$exp],"ID LIKE '$cKey%'",false) ;
		return $key ;
	}

	protected static function _Delete($cKey,$cSub,$cKode="*"){
		$cKode = $cKode == "*" ? "" : $cKode  ;
		$cKey = $cKey . $cSub . $cKode . "%" ;
		objData::Delete("sis_rpt","ID LIKE '$cKey%'",false) ;
	}

	protected static function _Get($cKey,$cSub,$cKode="*"){
		$cKode = $cKode == "*" ? "" : $cKode  ;
		$cKey = $cKey . $cSub . $cKode . "%" ;
		$vaData = [] ;

		$dbData = objData::Browse("sis_rpt","Data","ID LIKE '$cKey'","","","ID") ;
		while($dbRow = objData::GetRow($dbData)){
			$va = json_decode($dbRow["Data"],true) ;
			$vaData = array_replace_recursive($vaData,$va) ; //$vaData = array_merge($vaData,$va) ;
		}

		return $vaData ;
	}
}
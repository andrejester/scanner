<?php
  include 'df.php' ;

class PrintIO {
	static function Print($vaPrint,$lEject=true,$nCharSpace=0,$cTextInit="\033\033\017\017",$nPortID=1){
		$cFromFunction = isset($vaFromCall[1]['function']) ? $vaFromCall[1]['function'] : "" ;
		SysLog::Report("11",$_POST) ;
		$c = json_encode($vaPrint) ;
		$c = str_replace("\\","\\\\",$c) ;
		$c = str_replace("'","\'",$c) ;
		$lEject = $lEject ? "true" : "false" ;
		$cPrinter = self::GetIPPrinter();
		$cHost		= "http://".$cPrinter.":2700";
		// Gabungkan semua parameter ke dalam array untuk dikirim via callFunc
		$vaParams = "['$c', $lEject, $nCharSpace, '$cTextInit', $nPortID, '$cHost']";
		// Panggil rpt.PrintIO di mainFrame menggunakan frm.callFunc
		echo("frm.callFunc('rpt.PrintIO', $vaParams, 'mainFrame');");			
	}
	
	static function GetIPPrinter(){
		$cHost = aCfg::Get("msLocalhost","127.0.0.1");
		$dbData = objData::SQL("SHOW TABLES LIKE 'username_ipprinter'");
		if($dbRow = objData::GetRow($dbData)){
			$cIP   = $_SERVER['REMOTE_ADDR'];
			$cUserName = GetSetting("cSession_UserName") ;
			$dbD = objData::Browse("username","IPComputer","UserName = '$cUserName' AND IPComputer !=''");
			if($dbR = objData::GetRow($dbD)){
				$cIP   = $dbR['IPComputer'];	
			}
			$dbD = objData::Browse("username_ipprinter","*","IPUser='$cIP'");
			if($dbR = objData::GetRow($dbD)){
				
				$cHost  = ($dbR['Metode'] == "0") ? $dbR['IPHost'] : $dbR['KompNameHost']; //0=IP, 1=Komputer Name
			}
		}
		return $cHost;
	}
}
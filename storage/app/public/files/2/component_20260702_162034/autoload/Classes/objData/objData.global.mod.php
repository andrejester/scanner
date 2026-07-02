<?php
  include 'df.php' ;

class dbCore_Global {
	protected static function GetConfig(){
		$vaRetval = array("cHost"=>"localhost","cDatabase"=>"bpr_web","cUserName"=>"Assist","cPassword"=>"Irac") ;
		$cFile = realpath(Svr::GetProjectPath() . "/include/assist.ini.php") ;
		echo(realpath(Svr::GetProjectPath() . "/include/assist.ini.php")) ;
		if(is_file($cFile)){
			$data = file($cFile) ;
			foreach($data as $key=>$value){
				$value = trim($value) ;
				$cTag = "" ;
				$cValue = "" ;
				if(substr($value,0,1) <> "#"){
					$va = explode("=",$value) ;
					if(count($va) >= 2){
						$cTag = trim($va [0]) ;
						$cValue = trim($va [1]) ;
					}
				}

				$cTag = strtolower($cTag) ;
				if($cTag == "ip"){
					$vaRetval["cHost"] = $cValue ;
				}else if($cTag == "database"){
					$vaRetval["cDatabase"] = $cValue ;
				}else if($cTag == "username"){
					$vaRetval["cUserName"] = $cValue ;
				}else if($cTag == "password"){
					$vaRetval["cPassword"] = $cValue ;
				}
			}
		}
		return $vaRetval ;
	}
}
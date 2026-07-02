<?php
  include 'df.php' ;

function css(){
	$cFile = __DIR__ . "/cfgmenulevel.css" ;
	require_once CompressScript::css($cFile) ;
}

function ValidSaving($va){
	//Keterangan Menu
	$subMenu = "" ;
	$cKeyID  = "oldMnuID" ;
	if(Svr::IsMVC()){
		$subMenu = SisConfig::GetValue("mnuSubMenu") ;
		$cKeyID  = "mnuID" ;
	}
	menu::SubMenuFile($subMenu) ;
	$vaRawMenu  			= menu::mnu2Array() ;
	$vaKeteranganMenu = GenerateMenu($vaRawMenu,$cKeyID) ;
  
	//Menu Old
	$cLevel = md5($va["cLevel"]) ;
	$dbData = objData::Browse("username_menu","Keterangan,Button","Level = '$cLevel'") ;
	while($dbRow = objData::GetRow($dbData)){
		$vaMenuOld[$dbRow['Keterangan']] = array("menu"=>1,"cmd"=>$dbRow['Button']) ; 
	} 
	objData::Delete("username_menu","Level = '$cLevel'") ;
	
	//Menu New
	$vaMenu  = json_decode($va["cMenu"],true) ;
	$vaArray = array() ;
	foreach($vaMenu as $key=>$value){
		$vaArray[] = array("Level"=>$cLevel,"Keterangan"=>$key,"Button"=>$value["cmd"]) ;
	}
	if(!empty($vaArray)) objData::Insert("username_menu",$vaArray) ;
	
	//Log Perubahan Menu
	$vaLog = array() ;
	foreach($vaMenuOld as $key=>$value){
		if(!isset($vaMenu[$key])){
			$vaLog[] = $vaKeteranganMenu[$key] . " Removed" ;
		}
	}
	foreach($vaMenu as $key=>$value){
		if(!isset($vaMenuOld[$key])){
			$vaLog[] = $vaKeteranganMenu[$key] . " Added" ;
		}
	}
	
	//Simpan Log
	if(!empty($vaLog)){
		$vaLog = json_encode($vaLog,JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ;
		$vaUpdate = array("Tgl"=>Date("Y-m-d"),"Keterangan"=>"Log Level User","UserName"=>GetSetting("cSession_UserName"),"DateTime"=>Date("Y-m-d H:i:s"),"DataSesudah"=>$vaLog) ;
		$dbData = objData::SQL("Show Tables like 'changeslog'") ;
		if(objData::Rows($dbData)) objData::Insert("changeslog",$vaUpdate) ;
	}
	
	MVC::Response("ok") ;
}

function SeekUserLevel($va){
	$vaData = objData::ABrowse("username_level","Kode,Keterangan","Kode <> '0000'") ;
	MVC::Response($vaData) ;
}

function GetMenuLevel($va){
	$cLevel = md5($va["cLevel"]) ;
	$dbData = objData::ABrowse("username_menu","*","Level = '$cLevel'") ;
	$vaData = [] ;
	foreach($dbData as $value){
		$vaData [$value["Keterangan"]] = is_null($value["Button"]) ? "" : $value ['Button'] ;
	}

	MVC::Response([$vaData]) ;
}

function GenerateMenu($vaMenu,$cKeyID,$vaData = array()) {
	foreach($vaMenu as $key=>$value) {
		foreach($value as $key1=>$value1) {
			if(isset($value1['mnuPath'])) {
				$vaData[$value1[$cKeyID]] = $value1['mnuPath'];
			}else{
				$vaData = GenerateMenu($value1,$cKeyID,$vaData); 
			}
		}
	}
	return $vaData;
}
?>
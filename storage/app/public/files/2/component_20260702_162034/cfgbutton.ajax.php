<?php
  include 'df.php' ;
  
function SeekUserLevel($va){
  $cConfig = "111" ;
  $cFormName = md5($va ['cFormName']) ;
  $va ['cLevel'] = str_pad($va ['cLevel'],4,"0",STR_PAD_LEFT) ;

  SaveSetting("CfgButtonSetting",$va ['cLevel']) ;
  $dbData = objData::Browse("config_form","*","Level = '{$va ['cLevel']}' and FormName = '$cFormName'") ;
  if($dbRow = objData::GetRow($dbData)){
    $cConfig = str_pad($dbRow ['Status'],3,"0") ;
  }
  
  $cAdd = "false" ;
  $cEdit = "false" ;
  $cDelete = "false" ;
  if(substr($cConfig,0,1) == "1") $cAdd = "true" ;
  if(substr($cConfig,1,1) == "1") $cEdit = "true" ;
  if(substr($cConfig,2,1) == "1") $cDelete = "true" ;
  
  $dbData = objData::Browse("username_level","Kode,Keterangan","Kode = '{$va ['cLevel']}'") ;
  if($dbRow = objData::GetRow($dbData)){
    echo('a.f.cKeterangan.value = "' . $dbRow ['Keterangan'] . '";') ;
  }
  
  echo('
    with(a.f){
      ckAdd.checked = ' . $cAdd . ';
      ckEdit.checked = ' . $cEdit . ';
      ckDelete.checked = ' . $cDelete . ';
      cLevel.value = "' . $va ['cLevel'] . '" ;
    }
  ') ;
}

function SaveUserLevel($va){
  $cFormName = md5($va ['cFormName']) ;
  $va ['cLevel'] = str_pad($va ['cLevel'],4,"0",STR_PAD_LEFT) ;
  $cAdd = "0" ;
  $cEdit = "0" ;
  $cDelete = "0" ;
  if(isset($va ['ckAdd'])) $cAdd = "1" ;
  if(isset($va ['ckEdit'])) $cEdit = "1" ;
  if(isset($va ['ckDelete'])) $cDelete = "1" ;
  $cConfig = $cAdd . $cEdit . $cDelete ;

  if($va ['cLevel'] <> "0000"){
    $va1 = array("Level"=>$va ['cLevel'],"FormName"=>$cFormName,"Status"=>$cConfig) ;
    objData::Update("config_form",$va1,"Level = '{$va ['cLevel']}' and FormName = '$cFormName'") ;
  }
  echo("Data Telah Disimpan ....") ;
}

function initButton($va){
  $cFormName = md5($va ['cFormName']) ;
  $cLevel = str_pad(GetSetting("cSession_UserLevel","0000"),4,"0",STR_PAD_LEFT) ;
  
	//default config button diset 1 (dimunculkan)
  $cConfig = "1111" ;
  if($cLevel <> "0000"){
    $cConfig = "111" ;  
    $dbData = objData::Browse("config_form","*","Level = '{$cLevel}' and FormName = '$cFormName'") ;  
    if($dbRow = objData::GetRow($dbData)){
      $cConfig = str_pad($dbRow ['Status'],3,"0") ;
    } 
    $cConfig .= "0" ;
  } 

  echo("$cConfig") ;
}
?>
<?php
  include 'df.php' ;

function _Browse($va){
  $va ['cSQL'] = str_replace("\'","'",rawurldecode($va ['cSQL'])) ;
  $va1 = array() ;
  $vaData = array() ;	
	$sql  = $va ['cSQL'] ;    
	
  $dbData = objData::SQL($sql) ;
  while($dbRow = objData::GetRow($dbData)){
    $key = "" ;
    foreach($dbRow as $field => $value){
      if($key == "") $key = $value ;
			$vaData[$field] = htmlspecialchars(strip_tags($value)) ;
    }
    if($key !== "") $va1[$key] = $vaData ;
  }
  echo(json_encode($va1)) ;
}

function UpdLogCloseMenu($va){
  SysLog::CloseForm($va) ;
}
?>
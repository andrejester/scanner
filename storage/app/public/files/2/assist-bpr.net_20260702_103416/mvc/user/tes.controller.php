<?php 
	$vaConfig = Svr::GetConfigAssistTeam() ;
	echo "<pre> Environment \n" ;
	//putenv("token_type=asdqweqwe");
	echo "token type ".getenv("token_type")." \n" ;
	print_r(getenv()); 
	exit();
		
	$cKode = "5f5a0d7d1aaa7d2df806815acab77f44" ;
	$cKey  = md5(date("Y-m-d H:i:s")) ;
	$cTime = date("c") ;
  $cVersion = "1.0" ;
  $cSignature = md5("$cKey:$cTime:$cVersion") ;
	$vaHeader[] = "Content-Type:application/json" ; 
	$vaHeader[] = "SIS-Kode:$cKode" ; 
  $vaHeader[] = "SIS-Signature:$cSignature" ;
	$vaHeader[] = "SIS-Version:$cVersion" ;
	$vaHeader[] = "SIS-Timestamp:$cTime" ;
	$vaHeader[] = "SIS-URL:".$_SERVER['HTTP_HOST'] ;
  $vaHeader[] = "SIS-Key: $cKey" ;
		 
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL,"http://assist.sis1.cloud/assist-team/public/api/config");
  curl_setopt($curl, CURLOPT_CUSTOMREQUEST,'GET') ;
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true );
  curl_setopt($curl, CURLOPT_HTTPHEADER,$vaHeader);

	$vaResponse = curl_exec($curl);
  curl_close($curl); 
	
	exit() ;





  echo $vaResponse;
  $vaResponse = json_decode($vaResponse,true) ;
echo "<pre>" ;	
print_r($vaResponse) ;
//$handler = fopen("test.php","w") ;
//fwrite($handler,html_entity_decode($vaResponse['data']['mnuSubMenu'])) ;
/*
$vaLine = explode("\n",$vaResponse['data']['mnuSubMenu']);
foreach ($vaLine as $key=>$value){
	fwrite($handler,html_entity_decode($value)."\n") ;
}
*/
echo rand(1,999999) ;
exit() ;		
					$cDir = "tmp" ;
					if(is_dir($cDir)){
						echo getcwd();
						echo "ss";
						exit();
						$cDir .= "/submenu" ;
						echo getcwd() ;
						if(!is_dir($cDir)){
							mkdir($cDir,0777) ;
							echo "asdqweqwe" ;
						}else{
							echo "bbbbbbb" ;
						}
						$cFile = $cDir."/b.php" ;
						//$vaLine = explode("\n",$va['mnuSubMenu']);
						$handler = fopen($cFile,"w") ;
						//foreach ($vaLine as $key=>$value){
							//$vaLine[$key] = htmlspecialchars($value) ;
							fwrite($handler,$vaResponse['data']['mnuSubMenu']) ;
						//}
						echo "berhasil" ;
						exit() ;
					}
exit() ;
echo "<pre>" ;
//print_r($vaResponse['data']['mnuSubMenu']) ;
$vaData = explode("\n",$vaResponse['data']['mnuSubMenu']) ;
print_r($vaData) ;
exit() ;
print_r($vaResponse);
exit() ;
$cDir = "../config/customer" ;
$vaDir = scandir($cDir) ;
echo "Dir <br/>" ;
foreach($vaDir as $key=>$value){
	$cFile = $cDir."/".$value."/config.php" ;
	if(is_file($cFile)){
		$va = array() ;
		include $cFile ;
		$vaConfig = json_encode($va,JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ;
		
		echo $value ." | ".$vaConfig."<br/>" ;
	}
}
//print_r($vaDir) ;
exit() ;
?>
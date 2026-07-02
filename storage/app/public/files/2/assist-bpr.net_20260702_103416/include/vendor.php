<?php
  include 'df.php' ;

/*
Folder Autoload Sub Perhitungan Penulisan harus menggunakan __DIR__ untuk mengetahui folder start nya
Nama Variable array untuk menampung adalah $vendor tidak bisa di ganti dengan nama lain.

Contoh :
$vendor[] = __DIR__ . "/../../FOLDER_DATA"
*/


if(isset($_SERVER['REQUEST_URI'])){
	if($_SERVER['REQUEST_URI'] == "/api/sakep"){
		$vendor[] = __DIR__ . "/../../sub-bpr/sub-bpr-sakep/include/autoload" ;// production
		$vendor[] = __DIR__ . "/../../sub-bpr-sakep/include/autoload" ; //devlopement
	}
}
$vendor[] = __DIR__ . "/../../sub-bpr/sub-bpr-perhitungan/include/autoload" ;// production
$vendor[] = __DIR__ . "/../../sub-bpr-perhitungan/include/autoload" ; //devlopement

$vendor[] = __DIR__ . "/../../sub-bpr/sub-bpr-system/include/autoload" ;// production
$vendor[] = __DIR__ . "/../../sub-bpr-system/include/autoload" ; //devlopement

$vendor[] = __DIR__ . "/../../sub-bpr/sub-bpr-mmodal/include/autoload" ; // production
$vendor[] = __DIR__ . "/../../sub-bpr-mmodal/include/autoload" ; // development

$vendor[] = __DIR__ . "/../../sub-bpr/sub-bpr-mcollection/include/autoload" ; // production
$vendor[] = __DIR__ . "/../../sub-bpr-mcollection/include/autoload" ; // development

$vendor[] = __DIR__ . "/../../sub-bpr/sub-bpr-mbanking/include/autoload" ; // production 
$vendor[] = __DIR__ . "/../../sub-bpr-mbanking/include/autoload" ; // development

$vendor[] = __DIR__ . "/../../sub-bpr/sub-bpr-messaging/include/autoload" ; // production
$vendor[] = __DIR__ . "/../../sub-bpr-messaging/include/autoload" ; // development 

$vendor[]	= __DIR__ . "/../../sub-bpr/sub-bpr-h2h-curl/include/autoload"; // production
$vendor[]	= __DIR__ . "/../../sub-bpr-h2h-curl/include/autoload"; // development 

?>
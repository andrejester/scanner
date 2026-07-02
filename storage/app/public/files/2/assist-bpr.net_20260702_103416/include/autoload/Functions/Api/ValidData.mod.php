<?php
/* 
Standart Function Autoload
1. Pastikan File Berapa di Folder project/include/autoload/Functions/
2. Pastikan Nama Function Sama Dengan Nama File
3. Tidak Boleh Ada nama Function Kembar di semua Subdir nya
4. Cara Memanggil Function: 
									udf::ValidData(...parameters) 
*/
function ValidData($vaRequest){
	$vaData = array() ;
	foreach($vaRequest as $key => $value){
		if($key != "DEVICEID" && $key != 'PLATFORM' && $key !='VERSIAPLIKASI' && $key !='MTI' && $key !='KODETRANSAKSI' && $key !='PRODUK' && $key != "UPDATE"){
			$vaData[$key] = $value;	
		}
	}
	return $vaData;
}

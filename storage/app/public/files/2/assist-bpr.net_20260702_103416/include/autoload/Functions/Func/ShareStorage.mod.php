<?php
/* 
Standart Function Autoload
1. Pastikan File Berapa di Folder project/include/autoload/Functions/
2. Pastikan Nama Function Sama Dengan Nama File
3. Tidak Boleh Ada nama Function Kembar di semua Subdir nya
4. Cara Memanggil Function: 
									udf::ShareStorage(...parameters) 
*/
function ShareStorage($cNewFolder){
  $cDir = "../share" ;
	if(!is_dir($cDir))  mkdir($cDir,0777); 
	$cDir = "$cDir/$cNewFolder" ;
	
	if(!is_dir($cDir))  mkdir($cDir,0777); 
	return $cDir;
}

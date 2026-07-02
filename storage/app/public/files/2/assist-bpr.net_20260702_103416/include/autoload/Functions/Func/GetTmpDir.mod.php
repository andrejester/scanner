<?php
/* 
Standart Function Autoload
1. Pastikan File Berapa di Folder project/include/autoload/Functions/
2. Pastikan Nama Function Sama Dengan Nama File
3. Tidak Boleh Ada nama Function Kembar di semua Subdir nya
4. Cara Memanggil Function: 
									udf::GetTmpDir(...parameters) 
*/
function GetTmpDir(){
  $cDir = "../tmp" ;
  if(!is_dir($cDir)){
    mkdir($cDir,0777);
  }
  
  $cDir = "../tmp/tmp" ;  
  $nDir = date("H")%3 ;
  $nDir1 = $nDir + 1 ;
  if($nDir1 == 3){
    $nDir1 = 0 ;
  }
  if(is_dir($cDir . $nDir1)){
    udf::DeleteDirectory($cDir . $nDir1);
  }
  if(!is_dir($cDir . $nDir)){
    mkdir($cDir . $nDir,0777);
  }
  
  return $cDir . $nDir ;
}
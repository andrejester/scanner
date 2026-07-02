<?php
/* 
Standart Function Autoload
1. Pastikan File Berapa di Folder project/include/autoload/Functions/
2. Pastikan Nama Function Sama Dengan Nama File
3. Tidak Boleh Ada nama Function Kembar di semua Subdir nya
4. Cara Memanggil Function: 
									udf::GetFileSize(...parameters) 
*/
function GetFileSize($cFile){
  $cRetval = "0 B" ;
  if(file($cFile)){          
    $nSize = filesize($cFile) ;
    $cRetval = number_format($nSize,2) . " B" ;
    
    $vaSize = array("KB", "MB", "GB", "TB") ;
    $n = 1024 ;
    foreach($vaSize as $key=>$value){
      if ($nSize > $n){
        $nSize = $nSize / $n ;
        $cRetval = number_format($nSize,2) . " " . $value ;
      }
      $n = 1000 ;
    }
  }
  return $cRetval ; 
}
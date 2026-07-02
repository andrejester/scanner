<?php
/* 
Standart Function Autoload
1. Pastikan File Berapa di Folder project/include/autoload/Functions/
2. Pastikan Nama Function Sama Dengan Nama File
3. Tidak Boleh Ada nama Function Kembar di semua Subdir nya
4. Cara Memanggil Function: 
									udf::DeleteDirectory(...parameters) 
*/
function DeleteDirectory($cDir){
  if(is_dir($cDir)){
    $d = dir($cDir) ;            
    while (false !== ($entry = $d->read())) {
      if(is_dir($cDir . '/' . $entry)){
        if($entry !== "." && $entry !== ".."){
          DeleteDirectory($cDir . '/' . $entry) ;
        }
      }else{
        if(is_file($cDir . '/' . $entry)){
          unlink($cDir . '/' . $entry) ;
        }
      }
    }
    $d->close();
    rmdir($cDir) ;
  }
}
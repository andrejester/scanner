<?php
/* 
Berisi Script Controller dengan method yang bisa kita definisikan ataralain
1. GET = index_get()
2. POST = index_post()
3. PUT = index_put()
4. DELETE = index_delete()
*/
class cbd_Controller extends MVC_Controller {
	function index(){
		$cToken = Auth::GetToken() ;
		if($cToken == ""){
			return MVC::Response(null,MVC::HTTP_CLIENT_UNAUTHORIZED);
		}
		$lValid = Auth::IsJwtValid($cToken) ;
		if(!$lValid) {
			//return MVC::Response(null,MVC::HTTP_CLIENT_UNAUTHORIZED,"TOKEN TIDAK VALIDS");
		}
		
		$vaRequest = $this->getRequestData();
		if($vaRequest['PRODUK'] == "nasabah"){
			$vaResponse = Nasabah::Proses($vaRequest) ;
		}else if($vaRequest['PRODUK'] == "kredit"){
			$vaResponse = Kredit::Proses($vaRequest) ;//sssss
		}else if($vaRequest['PRODUK'] == "tabungan"){
			$vaResponse = TabunganCBS::Proses($vaRequest) ;
		}else if($vaRequest['PRODUK'] == "deposito"){
			$vaResponse = DepositoCBS::Proses($vaRequest) ;
		}else if($vaRequest['PRODUK'] == "system"){
			$vaResponse = SystemCBS::Proses($vaRequest) ;
		}
		$vaResponse = $this->array_change_key_case_recursive($vaResponse, CASE_LOWER);
		echo json_encode($vaResponse) ;
	}
	
	function array_change_key_case_recursive($array, $case = CASE_LOWER) {
		$result = [];
		foreach ($array as $key => $value) {
			$key = ($case == CASE_UPPER) ? strtoupper($key) : strtolower($key);
			if (is_array($value)) {
				$value = $this->array_change_key_case_recursive($value, $case);
			}
			$result[$key] = $value;
		}
		return $result;
	}
	function getRequestData() {
	  $requestData = array();
		// Get data from different request types
	 	if ($_SERVER['REQUEST_METHOD'] === 'GET') {
		  $requestData = $_GET;
		} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
		  $requestData =  $_POST;//$_POST;
		} elseif ($_SERVER['REQUEST_METHOD'] === 'PUT' || $_SERVER['REQUEST_METHOD'] === 'DELETE') {
		  $requestData = file_get_contents("php://input",true) ;//$_POST ;
		}
		return $requestData;
	}
}
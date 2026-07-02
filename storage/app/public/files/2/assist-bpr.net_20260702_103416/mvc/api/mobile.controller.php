 <?php
/* 
Berisi Script Controller dengan method yang bisa kita definisikan ataralain
1. GET = index_get()
2. POST = index_post()
3. PUT = index_put()
4. DELETE = index_delete()
*/
class mobile_Controller extends MVC_Controller {
	private static $MTI = array(
		"GCOLLECTION"          	=> "001", 
		"DIGITAL_BANK"         	=> "002", 
		"MOBILE_MODAL"         	=> "003",  
		"DIGITAL_BANK_NON_ISO" 	=> "004",
		"MESSAGING_SERVICE"    	=> "005",
		"DGB_V3"							 	=> "006",
		"H2H_CURL"             	=> "010",
		"AAPRO_CEKSALDO"       	=> "100",
		"H2H_VA_DANAMON"			 	=> "DNMON",
		"SNAPVA_DANAMON"			 	=> "SNAPDANAMON",
		"MTI_PPOBLOG" 					=> "SISPPOB",
	);
	
	function index(){ 
		$request 		= "";
		$vaMSGJSON	= array();
		$vaHeaders	= apache_request_headers();
		parse_str(file_get_contents("php://input"), $data);
		$data = (object) $data;
		$vaReq = "";
		if (isset($data->cCode)) {
			$request = $data->cCode;
			if(strtoupper(substr($request,0,11)) == "GCOLLECTION"){
				$vaReq = $request;
			} else {
				$vaReq = json_decode($request,true);
			}
		} else {
			$request 		= file_get_contents("php://input");
			$vaReq 			= isset(json_decode($request,true)["cCode"]) ? json_decode($request,true)["cCode"] : "";
			$vaMSGJSON 	= json_decode($request,1);
		}

		if($request != ""){
			if(isset($vaReq['MTI'])){ 

				switch($vaReq['MTI']){
				case self::$MTI["MOBILE_MODAL"]:
						RequestModal::$vaRequest = $vaReq; 
						$cResponse  = RequestModal::Process();
						//var_dump($cResponse);
					break;
				case self::$MTI["GCOLLECTION"]:
						RequestMcoll::$vaRequest = $vaReq;
						$cResponse  = RequestMcoll::Process();
					break;
				case self::$MTI["DIGITAL_BANK_NON_ISO"]:
					RequestMBanking::$vaRequest = $vaReq; 
					$cResponse  = RequestMBanking::Process();
					break;
				case self::$MTI["DGB_V3"]:
					RequestMBankingRG::$vaRequest = $vaReq; 
					$cResponse  = RequestMBankingRG::Process();
					break;
				case self::$MTI["H2H_CURL"]:
					# SUB 010: sub-bpr-h2h-curl
					$cResponse = H2HCurlClass::ReceiverHome($vaReq);
					break;
				case self::$MTI["MESSAGING_SERVICE"]:
					# SUB 005: sub-bpr-messaging
					$cResponse = MessagingServiceClass::ReceiverHome($vaReq);
					break;
				case self::$MTI["AAPRO_CEKSALDO"]:
					# SUB 100: sub-bpr-mbanking
					$cResponse = AProServiceClass::CekSaldo($vaReq);
					break;
				case self::$MTI["H2H_VA_DANAMON"]:
					# SUB DNMON: sub-bpr-mbanking
					$cResponse = DanamonVAClass::ProsesVA($request, $vaHeaders);
					break;
				case self::$MTI["MTI_PPOBLOG"]:
					# SISPPOB: sub-bpr-mbanking
					$cResponse = AProServiceClass::TrxPPOB($vaReq);
					break;
				}
			}else if(substr($request,0,4) == "0200"){
				RequestMBanking::$vaRequest = $_POST["cCode"]; 
				$cResponse  = RequestMBanking::Process(true);
			}else if(substr($request,0,4) == "0600"){
				RequestMBankingRG::$vaRequest = $_POST["cCode"]; 
				$cResponse  = RequestMBankingRG::Process(true);
			}else if(substr($request,0,4) == "0300"){
				RequestModal::$vaRequest =  isset($data->cCode2) ? $request . $data->cCode2 . $data->cCode3 : $_POST["cCode"]; 
				$cResponse = RequestModal::Process(true);
			} else if (isset($vaMSGJSON['virtualAccountNo'])) {
				# SUB Permata SNAP: sub-bpr-mbanking
				$cResponse  = SNAPPermataVAClass::ProsesVA($request, $vaHeaders);
			}else if(isset($vaMSGJSON['MTI'])){
				if($vaMSGJSON['MTI'] == self::$MTI["SNAPVA_DANAMON"]){ 
					# SUB Danamon SNAP: sub-bpr-mbanking
					# echo 'tesdanamonakhir'; exit;
					$cResponse = SNAPDanamonVAClass::ProsesVA($request, $vaHeaders);
				}
			}else{
				$cMessage     = isset($request) ? $request : file_get_contents('php://input'); 
				$vaMessage    = explode(" ",$cMessage);  
					if(strtoupper(trim($vaMessage [0])) == "GCOLLECTION"){
						RequestMcoll::$vaRequest = $cMessage; 
						$cResponse  = RequestMcoll::Process();
					}else{
						$cResponse = "Request tidak dikenali!!";
					}   
			}
		}else{
			if(isset($_POST['Kode'])){
					RequestMcoll::$vaRequest = $_POST['Kode'];
					$cResponse  = RequestMcoll::Process();
			}else if(isset($_POST['ModalUpload'])){
				RequestModal::$vaRequest = $_POST; 
				$cResponse = RequestModal::UploadGambar();
			}else{
				$cResponse = "Request tidak dikenali!";
				//RequestMBanking::$vaRequest = "";//$vaReq; 
				//$cResponse  = RequestMBanking::Process();
			}
  	}
		
		  echo $cResponse;
	}

	function tes($data) {
		return $data;
	}
}
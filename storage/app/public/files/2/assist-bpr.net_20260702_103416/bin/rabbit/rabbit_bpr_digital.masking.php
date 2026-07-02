<?php
  include 'df.php';

	class RabbitSMSMasking {
		static function OrderTrx($vaRequest) {
			$nTime  		= time();
			$cKodeAgen	= aCfg::Get("msKodeH2H");
			$vaQueue		= array();
			$dTgl   		= date("Y-m-d");
			$cWhere 		= "Tgl = '$dTgl' AND Status = '0' AND JenisTRX = 'MASKING'";
			$dbData 		= objData::Browse("sms_log","Message",$cWhere,"","","ID","0,30");
			if (objData::Rows($dbData) > 0) {
				while ($dbRow = objData::GetRow($dbData)) {
					array_push($vaQueue, $dbRow["Message"]);
					// objData::Edit("sms_log",array("Status"=>"1","SendDateTime"=>time()),"ID = " . $dbRow ['ID']);
				}
			}
			
			$nQueueCount	= count($vaQueue);
			$vaResponse 	= array(
				"responseCode"			=> "00",
				"partnerCode"				=> $cKodeAgen,
				"transactionQueue"	=> $vaQueue,
			);
			return $vaResponse;
		}
		
		static function StatusTrx($vaRequest) {
			//
		}
		
		static function StatusTrxOld($vaRequest) {
			$nTime    = time() ; 
			$dTgl     = date("Y-m-d");
			$vaFaktur = array();
			$cWhere   = "(StatusSMS = '0' or StatusSMS = '' or StatusSMS = 'P') and JenisTRX = 'MASKING' and Nomor <> '' and Status = '1'";
			$dbData   = objData::Browse("sms_log","Nomor",$cWhere,"","","ID desc","0,50");
			if (objData::Rows($dbData) > 0) {
				while ($dbRow = objData::GetRow($dbData)) {
					array_push($vaFaktur, $dbRow['Nomor']);
				}

				/*$vaRequest = array(
					"MTI"		=> ConfigTrx::CURL_TO_CBS,
					"KT"		=> ConfigTrx::CBS_CEK_STATUS_SMS,
					"Agen"	=> $cKodeAgen,
					"MSG"		=> implode("~~",$vaFaktur),
				);
				$cRequest   = json_encode($vaRequest);
				$cURL				= ConfigTrx::URL_SWITCHING;
				$cResponse	= FuncMBanking::SendHTTPPost("cCode=$cRequest",$cURL);
				$cResponse	= stripslashes($cResponse);
				$vaResponse	= json_decode($cResponse, true);

				if (isset($vaResponse['RC'])) {
					$vaRequest = array(
						"MTI"			=> ConfigTrx::CURL_TO_CBS,
						"KT"			=> ConfigTrx::CBS_TRX_MASKING,
						"Faktur"	=> $vaFaktur,
						"RC"			=> isset($vaRes['RC']) ? $vaRes['RC'] : "ZZ",
						"MSG"			=> isset($vaRes['MSG']) ? $vaRes['MSG'] : "",
					);
					$cRequest = json_encode($vaRequest);
					$cURL			= aCfg::Get("msH2H_CBSHost");
					$cURL			= sprintf("http://%s/api/mobile", $cURL);
					FuncMBanking::SendHTTPPost("cCode=$cRequest",$cURL);
				}*/
			}
			
			$vaResponse = array("status"=>"sukses","MSG"=>$vaFaktur);
			return $vaResponse;
		}
	}
?>
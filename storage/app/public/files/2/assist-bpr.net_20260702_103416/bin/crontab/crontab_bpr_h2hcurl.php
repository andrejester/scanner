<?php
	$cPath = __DIR__ . '/../connect.php';
	require_once($cPath);
	
	$cProsesName 	= basename(__FILE__);
	$lStatus  		= CekProses($cProsesName);
  $lErrorIdle		= H2HCurlGetErrorIdle($cProsesName);

	# CASE: status true  & error idle false  = kill
	# CASE: status false & error idle true   = kill
	# CASE: status false & error idle false  = service OK
	if ($lStatus || $lErrorIdle) {
		UpdProses($cProsesName,"prosess");
		// SetMobileModal::SendNotifTRansaksi();
		
		# cara ambil URL ini sementara saja yaa, untuk mengakali supaya H2H bisa running ...
		$cCBSHost = isset($arg1) ? $arg1 : "http_host";
		# kill pid saat terjadi double proses
		H2HCurlKillPID($cCBSHost);
		
		while (true) {
			PostingH2HCurlClass::SetCBSHost($cCBSHost);
			PostingH2HCurlClass::GetAntrian();
			PostingH2HCurlClass::SetStatus();
			
			UpdProses($cProsesName,"prosess");
			sleep(20); # sleep selama beberapa detik
		}
		
		// PostingH2HCurlClass::GetFirebase();
		UpdProses($cProsesName,"ok");
	}

	function H2HCurlGetErrorIdle($cProsesName) {
		# cek apakah idle >= 1 jam
		$dbDataIdle = objData::Browse("`rep_check`.`rep_gwctrl`","IDLE","NAMESERVICE = '$cProsesName'");
		if ($dbRowIdle = objData::GetRow($dbDataIdle)) {
			$nEpochTime = time();
			$nIdleTime	= !empty($dbRowIdle["IDLE"]) ? $dbRowIdle["IDLE"] : 0;
			$nDiffTime	= $nEpochTime - $nIdleTime;
			if ($nIdleTime == 0) {
				return true;
			} else if ($nDiffTime >= 3600) {
				return true;
			}
		}
		
		return false;
	}

	# function: untuk kill pid
	function H2HCurlKillPID($cHostName="") {
		$nCurrentPID	= getmypid();
		$cCommandPID 	= 'pgrep -f "php '. __FILE__ .' '. $cHostName .'"';
		$vaRunPID 		= [];
		exec($cCommandPID, $vaRunPID);
		if (!empty($vaRunPID)) {
			foreach ($vaRunPID as $cPID) {
				if ($cPID <> $nCurrentPID) {
					if (posix_kill($cPID, 0)) {
						echo "kill PID: $cPID" . PHP_EOL;
						exec("kill $cPID");
					}
				}
			}
		}
	}

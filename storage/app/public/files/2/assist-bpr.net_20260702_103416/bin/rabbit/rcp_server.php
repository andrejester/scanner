<?php
	
	//$lNoJS = true ;
	//$lFuncOnly = true ;
	
	//$argv[1] = "demo.assistindo.id";
	//$argv[1] = "bpr.mvc.sis2.net";
	//$argv[1] = "bpr.danagung.sis1.net";
	$argv[1] 	= "bpr.danagung.sis1.net";
	$cCBSHost = isset($argv[1]) ? $argv[1] : "http_host";
	$_SERVER['HTTP_HOST'] 	= $cCBSHost;
	$_SERVER['REQUEST_URI'] = "";
	
	$cPath = __DIR__ . '/../connect.php';
	require_once($cPath) ;
	
	// Load Autoload RabbitMQ
	//require_once __DIR__ . '/../../../sub-bpr-mmodal/include/rabbitmq/vendor/autoload.php';
	require_once __DIR__ . '/../../../sub-bpr/sub-bpr-mmodal/include/rabbitmq/vendor/autoload.php';
	
	// Import library RabbitMQ
	use PhpAmqpLib\Connection\AMQPStreamConnection;
	use PhpAmqpLib\Message\AMQPMessage;
	
	// Konfigurasi RabbitMQ
	
	$rabbitmq_port 	= 5672;
	$rabbitmq_user 	= 'Rivaldo';
	$rabbitmq_pass 	= 'rivaldo';
	$rabbitmq_host 	= 'rabbitmq.sis2.net';
	$rabbitmq_vhost = 'switching-mmodal';
	
	// Membuat koneksi RabbitMQ
	$connection = new AMQPStreamConnection($rabbitmq_host, $rabbitmq_port, $rabbitmq_user, $rabbitmq_pass, $rabbitmq_vhost);
	$channel = $connection->channel();
	$channel->queue_declare('rpc_queue', false, false, false, false);
	
	echo " [x] Awaiting RPC requests\n";
	
	// Callback untuk menangani request RPC
	$callback = function ($req) {
		$vaReq = $req->getBody();
		$cResponse = ""; 
		if(substr($vaReq,0,4) == "0300"){
			RequestModal::$vaRequest = $vaReq;    
			$cResponse = RequestModal::Process(true);
		} else {
			RequestModal::$vaRequest = json_decode($vaReq,true); 
			$cResponse  = RequestModal::Process(); 
		}
		
		$msg = new AMQPMessage(
			(string) $cResponse,
			array('correlation_id' => $req->get('correlation_id'))
		);

		$req->getChannel()->basic_publish(
			$msg,
			'', 
			$req->get('reply_to')
		);
		$req->ack();
	};

	$channel->basic_qos(null, 1, false);
	$channel->basic_consume('rpc_queue', '', false, false, false, false, $callback);

	try {
		$channel->consume();
	} catch (\Throwable $exception) {
		echo $exception->getMessage();
	}
	
	$channel->close();
	$connection->close();
	
	function RCPGetErrorIdle($cProsesName) {
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
	function RCPKillPID($cHostName="") {
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
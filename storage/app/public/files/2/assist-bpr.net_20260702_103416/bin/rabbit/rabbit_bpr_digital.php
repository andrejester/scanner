<?php
	$lNoJS = true;
	require_once(__DIR__ . "/../connect.php");
	require_once("rabbit_bpr_digital.masking.php");

	use Classes\RabbitMQ\PhpAmqpLib\Connection\AMQPStreamConnection;
	use Classes\RabbitMQ\PhpAmqpLib\Message\AMQPMessage;
	use Classes\RabbitMQ\PhpAmqpLib\Exchange\AMQPExchangeType;

	$cProsesName 	= basename(__FILE__);
	$cDNSName			= isset($arg1) ? $arg1 : "http_host";
	
	/*
	---------- pakai yang dari componen masih error karena ada custom isi fungsi ----------
	
	RabbitMQ::Connect();
	
	RabbitMQ::$RabbitMQ->exchange_declare("exchange_switching_bprdemo", AMQPExchangeType::TOPIC, false, true, false);
	RabbitMQ::$RabbitMQ->queue_declare("rpc.queue.ppob.unprocessed", false, false, true, false, false, [
		"x-message-ttl" => ["I", 60000],
	]);
	RabbitMQ::$RabbitMQ->queue_bind("rpc.queue.ppob.unprocessed", "exchange_switching_bprdemo", "#");
	RabbitMQ::$RabbitMQ->basic_qos(0, 1, false);
	RabbitMQ::$RabbitMQ->basic_consume("rpc.queue.ppob.unprocessed", "", false, false, false, false, "messageCallback");
	while (RabbitMQ::$RabbitMQ->is_consuming()) {
		RabbitMQ::$RabbitMQ->wait();
	}
	
	RabbitMQ::CloseConnection();
	*/
	
	#----------------------------------------------------------------------------------------------------#
	try {
		RabbitBPRDigital::init("10.1.13.98", 5672, "Assist", "123456");
		RabbitBPRDigital::consume("messageCallback");
	} catch (Exception $e) {
		die("Error: " . $e->getMessage());
	} finally {
		RabbitBPRDigital::close();
	}
	#----------------------------------------------------------------------------------------------------#
	function messageCallback(AMQPMessage $vaMessage) {
		$vaRequest 	= json_decode($vaMessage->body, true);
		$vaResponse	= array("status"=>"error","message"=>"request tidak dikenali");
		
		if (isset($vaRequest["method"])) {
			switch ($vaRequest["method"]) {
				case "pulsaprah.queue":
				case "emoney.queue":
				case "emoneyopd.queue":
				case "plnprah.queue":
				case "plnpasch.queue":
				case "bpjsks.queue":
				case "pdam.queue":
					$vaResponse = array("status"=>"sukses","message"=>"ini transaksi PPOB");
					break;
				case "smsmasking.queue":
					$vaResponse = RabbitSMSMasking::OrderTrx($vaRequest);
					break;
				case "whatsapp.queue":
					$vaResponse = array("status"=>"sukses","message"=>"ini transaksi WhatsApp");
					break;
				case "ppob.status":
					$vaResponse = array("status"=>"sukses","message"=>"ini status PPOB");
					break;
				case "smsmasking.status":
					$vaResponse = array("status"=>"sukses","message"=>"ini status smsmasking");
					break;
				case "whatsapp.status":
					$vaResponse = array("status"=>"sukses","message"=>"ini status WhatsApp");
					break;
			}
		}
		
		$vaResponse = new AMQPMessage(json_encode($vaResponse), array(
			"correlation_id" => $vaMessage->get("correlation_id")
		));
		$vaMessage->getChannel()->basic_publish($vaResponse, "", $vaMessage->get("reply_to"));
		$vaMessage->ack();
	}
	#----------------------------------------------------------------------------------------------------#
	class RabbitBPRDigital {
		private static $objConnection;
    private static $objChannel;
		
		static function init($cHost, $nPort, $cUser, $cPassword) {
			try {
				self::$objConnection = new AMQPStreamConnection($cHost, $nPort, $cUser, $cPassword);
				self::$objChannel    = self::$objConnection->channel();
			} catch (Exception $objException) {
				die("Connection failure: " . $objException->getMessage() . PHP_EOL);
			}
		}
		
		public static function consume($objCallback) {
			self::$objChannel->exchange_declare("exchange_bprdemo", AMQPExchangeType::TOPIC, false, true, false);
			list($queueName, , ) = self::$objChannel->queue_declare("", false, false, true, false, false, [
				"x-message-ttl" => ["I", 60000],
			]);
			self::$objChannel->queue_bind($queueName, "exchange_bprdemo", "#");
			
			self::$objChannel->basic_qos(0, 1, false);
			self::$objChannel->basic_consume($queueName, "", false, false, false, false, $objCallback);
			
			while (self::$objChannel->is_consuming()) {
				self::$objChannel->wait();
			}
		}
		
		static function close() {
			if (self::$objChannel) self::$objChannel->close();
			if (self::$objConnection) self::$objConnection->close();
    }
	}
?>

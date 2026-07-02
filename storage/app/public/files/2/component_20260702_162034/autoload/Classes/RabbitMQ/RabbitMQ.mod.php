<?php
/* 
Standart Class Autoload
1. Pastikan File Berapa di Folder project/include/autoload/Classes/
2. Pastikan Nama Class Sama Dengan Nama File
3. Tidak Boleh Ada nama Class Kembar di semua Subdir nya
*/

class RabbitMQ {
	private static $lConnect  = false ;
  private static $connection ;
  private static $RabbitMQ ;
	
	static function Connect(){
		$cIP 				= Svr::GetConfig("rabbitmq_ip","10.1.13.98") ; 
		$cPort			= Svr::GetConfig("rabbitmq_port","5672") ; 
		$cUserName	= Svr::GetConfig("rabbitmq_username","Assist") ; 
		$cPassword	= Svr::GetConfig("rabbitmq_password","123456") ; 
		$cvHost			= Svr::GetConfig("rabbitmq_vhost","/") ; 
		
		/*$cIP       = "10.2.2.169";   // kalau servernya di PC lokal
		$cPort     = 5672;          // port default AMQP
		$cUserName = "devuser";     // user RabbitMQ (bukan guest kalau akses remote)
		$cPassword = "devpass";
		$cvHost    = "/";*/
		//Koneksi Ke RabbitMQ
		self::$connection = new \Classes\RabbitMQ\PhpAmqpLib\Connection\AMQPStreamConnection($cIP, $cPort, $cUserName, $cPassword, $cvHost);
		self::$RabbitMQ = self::$connection->channel();
		self::$lConnect = true ;
	}
	
	static function SendMessage($cQueueName,$cMessage){
		if(!self::$lConnect) self::Connect() ;
		self::$RabbitMQ->queue_declare($cQueueName, false, false, false, false);

		$cMessage = new \Classes\RabbitMQ\PhpAmqpLib\Message\AMQPMessage($cMessage);
		self::$RabbitMQ->basic_publish($cMessage, '', $cQueueName);
	}
	
	static function ConsumeMessage($cQueueName,$callBack){
		if(!self::$lConnect) self::Connect() ;
		
		self::$RabbitMQ->queue_declare($cQueueName, false, false, false, false);
		self::$RabbitMQ->basic_consume($cQueueName, '', false, true, false, false, $callBack);
		try {
			while (self::$RabbitMQ->is_consuming()) {
				self::$RabbitMQ->wait();
			}
		} catch (\Throwable $exception) {
			echo $exception->getMessage();
		}
	}
	
	static function CloseConnection(){
		self::$RabbitMQ->close();
		self::$connection->close();
		self::$lConnect = false ;
	}
}

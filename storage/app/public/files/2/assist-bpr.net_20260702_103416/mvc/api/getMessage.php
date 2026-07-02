<?php
	/*RabbitMQ::ConsumeMessage("ReqAssistTeamtoCBS",function($cMessage){
	$vaData = json_decode($cMessage->body,true);
	$cTable = $vaData["table"];
	$vaResponse = objData::ABrowse($cTable,"*");
	RabbitMQ::SendMessage("RespCBStoAssistTeam","json_encode($vaResponse)");*/y
	RabbitMQ::SendMessage("RespCBStoAssistTeam","sssss");
?>

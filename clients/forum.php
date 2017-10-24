#!/usr/bin/php
<?php
require_once('../Ini/path.inc');
require_once('../Ini/get_host_info.inc');
require_once('../Ini/rabbitMQLib.inc');

function getAllUsers($type)
{
  $client = new rabbitMQClient("../Ini/queryRabbitMQ.ini","testServer");
  $request = array();
  $request['type'] = $type;
  $response = $client->send_request($request);

  echo "client received response" . PHP_EOL;
  print_r($response);
  echo "\n\n";
  return $response;
}

function getMessage($type,$userName)
{
  $client = new rabbitMQClient("../Ini/queryRabbitMQ.ini","testServer");
  $request = array();
  $request['type'] = $type;
  $request['userName'] = $userName;
  $response = $client->send_request($request);

  echo "client received response: " . PHP_EOL;
  print_r($response);
  echo "\n\n";
  return $response;
}

function sendMessage($type,$userName,$message)
{
  $client = new rabbitMQClient("../Ini/queryRabbitMQ.ini","testServer");
  $request = array();
  $request['type'] = $type;
  $request['userName'] = $userName;
  $request['message'] = $message;
  $response = $client->publish($request);
}


#sendMessage("sendMessage","semo","I want to trade now");
#getMessage("getMessage","semo");
getAllUsers("getAllUsers");



?>

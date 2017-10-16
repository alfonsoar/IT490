#!/usr/bin/php
<?php
require_once('../Ini/path.inc');
require_once('../Ini/get_host_info.inc');
require_once('../Ini/rabbitMQLib.inc');

function getUid($type,$userName)
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

function getMessage($type,$uid,$message)
{
  $client = new rabbitMQClient("../Ini/queryRabbitMQ.ini","testServer");
  $request = array();
  $request['type'] = $type;
  $request['uid'] = $uid;
  $request['message'] = $message;
  $response = $client->send_request($request);

  echo "client received response: " . PHP_EOL;
  print_r($response);
  echo "\n\n";
  return $response;
}

function sendMessage($type,$uid,$message)
{
  $client = new rabbitMQClient("../Ini/queryRabbitMQ.ini","testServer");
  $request = array();
  $request['type'] = $type;
  $request['uid'] = $uid;
  $request['message'] = $message;
  $response = $client->publish($request);
}

#sendMessage("sendMessage",$uid,$message)
#getMessage("getMessage",$uid,$message)
getUid("getUid","lol");


?>

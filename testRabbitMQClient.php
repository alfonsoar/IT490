#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');
require_once('logscript.php');

function sendtoServer($type,$username,$password)
{
  $client = new rabbitMQClient("testRabbitMQ.ini","testServer");
  $request = array();
  $request['type'] = $type;
  $request['username'] = $username;
  $request['password'] = $password;
  //$request['message'] = $msg;
  $response = $client->send_request($request);
  //$response = $client->publish($request);

  //LogMsg("client received response: " . $response);
  echo "client received response: ".PHP_EOL;
  print_r($response);
  echo "\n\n";
  return $response;
}

sendtoServer("login","root","12345678");

echo $argv[0]." END".PHP_EOL;
?>

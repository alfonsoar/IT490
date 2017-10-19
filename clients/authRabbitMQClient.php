#!/usr/bin/php
<?php
require_once('../Ini/path.inc');
require_once('../Ini/get_host_info.inc');
require_once('../Ini/rabbitMQLib.inc');
require_once('../logscript.php');

function sendtoServer($type,$username,$password)
{
  $client = new rabbitMQClient("../Ini/authRabbitMQ.ini","testServer");
  $request = array();
  $request['type'] = $type;
  $request['username'] = $username;
  $request['password'] = $password;
  $response = $client->send_request($request);
  //$response = $client->publish($request);

  //LogMsg("client received response: " . $response);
  echo "client received response: ".PHP_EOL;
  print_r($response);
  echo "\n\n";
  return $response;
}

sendtoServer("register","jill","pop");

echo $argv[0]." END".PHP_EOL;
?>

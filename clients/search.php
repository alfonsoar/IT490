#!/usr/bin/php
<?php
require_once('../Ini/path.inc');
require_once('../Ini/get_host_info.inc');
require_once('../Ini/rabbitMQLib.inc');

function searchCards($type,$val)
{
  $client = new rabbitMQClient("../Ini/queryRabbitMQ.ini","testServer");
  $request = array();
  $request['type'] = $type;
  $request['val'] = $val;
  $response = $client->send_request($request);

  echo "client received response: " . PHP_EOL;
  print_r($response);
  echo "\n\n";
  return $response;
}

function getCard()
{
  $client = new rabbitMQClient("../Ini/queryRabbitMQ.ini","searchServer");
  

}

searchCards("searchAll","magician");

?>

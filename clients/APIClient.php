#!/usr/bin/php
<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');
require_once('logscript.php');
require_once('deck_class.php');

function getAllCards()
{
  $client = new rabbitMQClient("APIRabbit.ini","APIServer");
  $request = array();
  $request['type'] = "all_cards";
  //$request['message'] = $msg;
  $response = $client->send_request($request);
  //$response = $client->publish($request);

  $response = json_decode($response,true);
  LogMsg("client received response: ");
  echo "client received response: ".PHP_EOL;
  print_r($response);
  echo "\n\n";
  return $response;
}

function getCard($tag,$name)
{
  $client = new rabbitMQClient("APIRabbit.ini","APIServer");
  $request = array();
  $request['type'] = "get_cards";
  $request['tag'] = $tag;
  $request['name'] = $name;
  //$request['message'] = $msg;
  $response = $client->send_request($request);
  //$response = $client->publish($request);

  $card = json_decode($response,true);
  LogMsg("client received response: ");
  echo "client received response: ".PHP_EOL;
  print_r($card);
  echo "\n\n";
  return $card;
}

$all_cards = getAllCards();

var_dump($all_cards);


//echo $argv[0]." END".PHP_EOL;
?>

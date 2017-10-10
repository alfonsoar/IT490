<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

//create the function to check if message is critical
function ifCrit($msg)
{
  $msg = strtolower($msg);
  if (preg_match('/error/',$msg) | preg_match('/critical/',$msg))
  {
    return True;
  }
  return false;
}

// Create the error logging function
function LogMsg($e)
{
  $logmsg = array();
  $logmsg['date'] = date("Y-m-d");
  $logmsg['day'] = date("l");
  $logmsg['time'] = date("h:i:sa");
  $logmsg['text'] = $e;

  //log the message
  $msg = implode(" - ",$logmsg);
  //check if msg is critical
  if (ifCrit($msg))
  {
    //send to log server
    $client = new rabbitMQClient("logRabbitMQ.ini","testServer");
    echo 'send to log server' .PHP_EOL;
    $client->publish($msg);
  }
  //log the message
  error_log($msg.PHP_EOL,3,"./logs/logfile.log");
}

function LogServerMsg($e)
{
  error_log($e.PHP_EOL,3,"./logs/logfile.log");
}
?>

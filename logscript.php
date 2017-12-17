<?php
/**
* This is the deploy server client
* This app has 3 functions New | Deploy | Rollback
* This file containsmany functions used in the proccess of deploying to a server
*
* PHP version 7.0.22
*
* @license MIT http://opensource.org/license/might
* @author Aneesh Muthiyan <asm63@njit.edu>
* @author Alfonso Austin <aga23@njit.edu>
* @since 1.0
*/
require_once('Ini/path.inc');
require_once('Ini/get_host_info.inc');
require_once('Ini/rabbitMQLib.inc');

/**
 * gets and returns the hostname of this machine
 *
 * @return string $Hname the hostname of this machine
 */
function getHost(){
  $IP = shell_exec("ifconfig | grep -Eo 'inet (addr:)?([0-9]*\.){3}[0-9]*' |
                    grep -Eo '([0-9]*\.){3}[0-9]*' | grep -v '127.0.0.1' ");
  $Hname = shell_exec("cat DNS.conf | grep -i $IP | awk '{ print $2 }'");

  return $Hname;
}


/**
* Checks wheter the function is critical
*
* @param string $msg the string to parse
* @return bool returns ethier true or false
*/
function ifCrit($msg){
  $msg = strtolower($msg);
  if (preg_match('/error/',$msg) | preg_match('/critical/',$msg)
      | preg_match('/failed/',$msg) | preg_match('/successful/',$msg))
  {
    return True;
  }
  return false;
}


/**
* takes a default message and adds to depending on the flag sendtoServer
* meant to be used in conjunction with other function
*
* @param int $status determines which if statment to fall under
* @param string $message the original message to be logged
* @param string $path the file where the error occured
* @param string $user the user generating the error
* @param string $Host the machine where the error originated from
*/
function Logger($status,$message,$path,$user,$Host){
  if($status == 1){
    LogMsg($message." successful",$path,$user,$Host);
  }
  else{
    LogMsg($message." failed",$path,$user,$Host);
  }
}

/**
* This function logs the message locally and check to see wheter to send it
* to the central logging server, depending on severity
*
* @param string $msg the original message to be logged
* @param string $path the file where the error occured
* @param string $user the user generating the error
* @param string $Host the machine where the error originated from
*/
function LogMsg($msg,$path,$user,$Host){
  $file = basename($_SERVER['PHP_SELF']);
  $Ffile = trim(preg_replace('/\s+/', ' ', $path));

  $logmsg = array();
  $logmsg['date'] = date("Y-m-d");
  $logmsg['day'] = date("l");
  $logmsg['time'] = date("h:i:sa");
  $logmsg['user'] = $user;
  $logmsg['machine'] = $Host;
  $logmsg['text'] = $msg;
  $logmsg['file'] = $Ffile;

  //log the message
  $msg = implode(" - ",$logmsg);
  //check if msg is critical
  if (ifCrit($msg))
  {
    //send to log server
    $client = new rabbitMQClient("logRabbitMQ.ini","testServer");
    $client->publish($msg);
  }
  //log the message
  error_log($msg.PHP_EOL,3,"../logs/logfile.log");
}

/**
* logs the message locally only
*
* @param string $e determines which if statment to fall under
*/
function LogServerMsg($e)
{
  error_log($e.PHP_EOL,3,"../logs/logfile.log");
}
?>

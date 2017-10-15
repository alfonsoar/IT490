#!/usr/bin/php
<?php
require_once('../Ini/path.inc');
require_once('../Ini/get_host_info.inc');
require_once('../Ini/rabbitMQLib.inc');
require_once('../Ini/login.php.inc');
require_once('../logscript.php');

function doLogin($username,$password)
{
    $file = __FILE__.PHP_EOL;
    $PathArray = explode("/",$file);
    // lookup username in database
    // check password
    $login = new loginDB();
    LogMsg("tried to login",$PathArray[8]);
    echo "tried to login".PHP_EOL;
    $login_status = $login->validateLogin($username,$password);
    if($login_status)
    {
      LogMsg("Login Successful: ".$username,$PathArray[8]);
    }
    else
    {
      LogMsg("Login Failed: ".$username,$PathArray[8]);
    }
    echo $login_status.PHP_EOL;
    return $login_status;
}

function doRegister($username,$password)
{
  $options = [
    'cost' => 11,
  ];
  $file = __FILE__.PHP_EOL;
  $PathArray = explode("/",$file);
  $register = new loginDB();
  LogMsg("tried to register",$PathArray[8]);
  echo "tried to register".PHP_EOL;
  $hash = password_hash($password, PASSWORD_BCRYPT, $options);
  $register_status = $register->registerUser($username,$hash);
  if($register_status)
  {
    LogMsg("Registration Successful: ".$username,$PathArray[8]);
  }
  else
  {
    LogMsg("Registration Failed: ".$username,$PathArray[8]);
  }
  echo $register_status.PHP_EOL;
  return $register_status;
}

function requestProcessor($request)
{
  $file = __FILE__.PHP_EOL;
  $PathArray = explode("/",$file);
  LogMsg("received request",$PathArray[8]);
  echo "received request".PHP_EOL;
  var_dump($request);
  if(!isset($request['type']))
  {
    LogMsg("ERROR: Type is not set",$PathArray[8]);
    return "ERROR: Type is not set";
  }
  switch ($request['type'])
  {
    case "login":
      return doLogin($request['username'],$request['password']);
    case "validate_session":
      return doValidate($request['sessionId']);
    case "register":
      return doRegister($request['username'],$request['password']);
  }
  LogMsg("ERROR: Type is not supported",$PathArray[8]);
  return array("returnCode" => '0', 'message'=>"Server received request and processed");
}

$server = new rabbitMQServer("../Ini/authRabbitMQ.ini","testServer");
$server->process_requests('requestProcessor');
exit();
?>

<?php
require_once('../Ini/path.inc');
require_once('../Ini/get_host_info.inc');
require_once('../Ini/rabbitMQLib.inc');
//include_once('testRabbitMQClient.php')

if (!isset($_POST))
{
	$msg = "NO POST MESSAGE SET, POLITELY FUCK OFF";
	echo json_encode($msg);
	exit(0);
}
$request = $_POST;
$response = "unsupported request type, politely FUCK OFF";

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
  return $response;
}

switch ($request["type"])
{
	case "login":
		//$response = "login, yeah we can do that";
		$response = sendtoServer($request['type'],$request['uname'],$request['pword']);
	case "register":
		//$response = "register, yeah we can do that";
		$response = sendtoServer($request["type"],$request['uname'],$request['pword']);

	break;
}

if($response){
	echo json_encode("Heading online now!<p><img src='./praisethesun.gif'/>");
}
else{
	echo json_encode("Incorrect Username or Password<p>");
}

//echo json_encode($response);
exit(0);

?>

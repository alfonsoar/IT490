<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');
require_once('APIClient.php');

if(isset($_POST))
{
  $request = $_POST;
  $response = "unsupported request type";
  switch($request['type'])
  {
    case "get_card":
      $card = getCard($request['type'],$request['tag'],$request['name']);
    break;
  }
}
 ?>


//creatediffrent cases

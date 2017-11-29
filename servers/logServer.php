#!/usr/bin/php
<?php
require_once('../Ini/path.inc');
require_once('../Ini/get_host_info.inc');
require_once('../Ini/rabbitMQLib.inc');
require_once('../logscript.php');

function requestProcessor($request)
{
      return LogServerMsg($request);
}
$server = new rabbitMQServer("../Ini/logRabbitMQ.ini","testServer");
$server->process_requests('requestProcessor');
exit();
?>

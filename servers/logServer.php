#!/usr/bin/php
<?php
require_once('../path.inc');
require_once('../get_host_info.inc');
require_once('../rabbitMQLib.inc');
require_once('../login.php.inc');
require_once('../logscript.php');

function requestProcessor($request)
{
      return LogServerMsg($request);
}
$server = new rabbitMQServer("logRabbitMQ.ini","testServer");
$server->process_requests('requestProcessor');
exit();
?>

#!/usr/bin/php
<?php
/**
 * This is the crntralized log Server
 * This file logs all recived messages to its local log file.
 *
 * PHP version 7.0.22
 *
 * @license MIT http://opensource.org/license/might
 * @author Alfonso Austin <aga23@njit.edu>
 * @since 1.0
 */
require_once('../Ini/path.inc');
require_once('../Ini/get_host_info.inc');
require_once('../Ini/rabbitMQLib.inc');
require_once('../logscript.php');


/**
 * This function just sits and listing for messgaes to be logged
 * any messgae recived by this function will be logged
 *
 * @param array $request This is an array contaig the message to be logged.
 * @return string this function returns the a 1 or 0 deeping wheter it was sucesful or not
 */
function requestProcessor($request)
{
      return LogServerMsg($request);
}
$server = new rabbitMQServer("../Ini/logRabbitMQ.ini","testServer");
$server->process_requests('requestProcessor');
exit();
?>

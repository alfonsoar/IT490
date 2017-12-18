#!/usr/bin/php
<?php
/**
 * This is the deploy Server, this listiner sits and waits for deployment
 * requests, this server can be quired via the deployClient
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
require_once('../Ini/deploy.php.inc');
require_once('../logscript.php');


/**
 * This function esentiall is the switch statments for all the diffrent
 * request the client can make, depeing on the case a diffrent functions
 * is called and appropiately logged
 *
 * @param string $func the type of request the client is making
 * @param string $vid the version id of a specific Package
 * @param string $dip the destination machine being trader_cdlgravestonedoji
 * @param string $dir the destination directory on the targed machine
 * @param string $machine the name of the machine requesting making the requesting
 * @param string $user the user making the request
 * @param string $msg the "layer" or the type of machine where the request come from
 * @return mixed[] This function return various diffent outputs depending on the funtion called
 */
function doLayer($func,$vid,$dip,$dir,$machine,$user,$layer)
{
    $file = basename($_SERVER['PHP_SELF']);

    $deploy = new deployDB();

    switch ($func)
    {
      case "new":
        echo "Tried to recive new Package".PHP_EOL;
        $deploy_status = validate($vid,$dip,$dir,$layer,$user,"0");
        logger($deploy_status,"new package uploaded",$file,$user,$machine);
        echo $deploy_status.PHP_EOL;
        return $deploy_status;
      case 'chkVer':
        echo "Tried to check if the version exis".PHP_EOL;
        $deploy_status = $deploy->versionExists($machine,$vid);
        logger($deploy_status,"The version check was",$file,$user,$machine);
        echo $deploy_status.PHP_EOL;
        return $deploy_status;
      case 'chkRoll':
        return $deploy->rollbackVersion($machine,$vid);
      case 'chkDbVer':
          return $deploy->newVersion($machine);
      case "chkDep":
          return $deploy->checkDep($machine,$vid);
      case "chkLtsVer":
          return $deploy->checkCurrentVersion($machine);
      case "deprecate":
          return $deploy->versionDep($machine,$vid);
      case "deployExt":
        echo "I WILL deploy only".PHP_EOL;
        deployServ($vid,$dip,$dir,$layer,$user);
        $deploy_status = validate($vid,$dip,$dir,$layer,$user,"1");
        logger($deploy_status,"deploy was",$file,$user,$machine);
        echo $deploy_status.PHP_EOL;
        return $deploy_status;
    }
}

/**
 * This is the main request proccessor for the deployserver
 * this function is always listing for request and routes the request's accordingly
 * depending from what layer it is originating
 *
 * @param array $request an array contaning all the diffrent variable sent by the client
 * @return mixed[] This function return various diffent outputs depending on the funtion called
 */
function requestProcessor($request)
{
  $file = basename($_SERVER['PHP_SELF']);
  echo "received request".PHP_EOL;
  var_dump($request);
  if(!isset($request['type']))
  {
    return "ERROR: Type is not set";
  }
  switch ($request['type'])
  {
    case "comm":
      return doLayer($request['func'],$request['vid'],$request['dip'],$request['dir'],$request['machine'],"aga23","comm");
    case "front":
      return doLayer($request['func'],$request['vid'],$request['dip'],$request['dir'],$request['machine'],"afv4","front");
    case "dmz":
      return doLayer($request['func'],$request['vid'],$request['dip'],$request['dir'],$request['machine'],"amuthiyan","dmz");
    case "back":
      return doLayer($request['func'],$request['vid'],$request['dip'],$request['dir'],$request['machine'],"christian","back");
  }
  logger('0',"ERROR: Type is not supported",$file,"n/a",$machine);
  return array("returnCode" => '0', 'message'=>"ERROR: Type is not supported");
}

$server = new rabbitMQServer("../Ini/deploy.ini","testServer");
$server->process_requests('requestProcessor');
exit();
?>

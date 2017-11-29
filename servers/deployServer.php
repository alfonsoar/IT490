#!/usr/bin/php
<?php
require_once('../Ini/path.inc');
require_once('../Ini/get_host_info.inc');
require_once('../Ini/rabbitMQLib.inc');
require_once('../Ini/deploy.php.inc');
require_once('../logscript.php');

function doLayer($func,$vid,$dip,$dir,$machine,$user,$layer)
{
    $file = basename($_SERVER['PHP_SELF']);

    $deploy = new deployDB();

    switch ($func)
    {
      case "new":
        echo "Tried to recive new Package".PHP_EOL;
        $deploy_status = validate($vid,$dip,$dir,$layer,$user,"0");
        logger($deploy_status,"new package uploaded",$file,$user);
        echo $deploy_status.PHP_EOL;
        return $deploy_status;
      case 'chkVer':
        echo "Tried to check if the version exis".PHP_EOL;
        $deploy_status = $deploy->versionExists($machine,$vid);
        logger($deploy_status,"The version check was",$file,$user);
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
        logger($deploy_status,"deploy was",$file,$user);
        echo $deploy_status.PHP_EOL;
        return $deploy_status;
    }
}

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
  logger('0',"ERROR: Type is not supported",$file,"n/a");
  return array("returnCode" => '0', 'message'=>"ERROR: Type is not supported");
}

$server = new rabbitMQServer("../Ini/deploy.ini","testServer");
$server->process_requests('requestProcessor');
exit();
?>

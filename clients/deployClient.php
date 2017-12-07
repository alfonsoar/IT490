#!/usr/bin/php
<?php
require_once('../Ini/path.inc');
require_once('../Ini/get_host_info.inc');
require_once('../Ini/rabbitMQLib.inc');
require_once('../logscript.php');

$destIP = '';
$user = '';
$dServDir = '';
$layer = '';

$string = shell_exec("cat ../Ini/conf.ini | grep -i user | awk '{ print $3 }'");
$user = trim(preg_replace('/\s+/', ' ', $string));
$string1 = shell_exec("cat ../Ini/conf.ini | grep -i deployserver | awk '{ print $3 }'");
$dServDir = trim(preg_replace('/\s+/', ' ', $string1));
$string2 = shell_exec("cat ../Ini/conf.ini | grep -i layer | awk '{ print $3 }'");
$layer = trim(preg_replace('/\s+/', ' ', $string2));
$string3 = shell_exec("cat ../Ini/conf.ini | grep -i localmachine | awk '{ print $3 }'");
$machine = trim(preg_replace('/\s+/', ' ', $string3));

//check to make sure the user eneters a function and Version ID
if(empty($argv[1])){
  echo "Please enter a Valid function".PHP_EOL;
  echo "Functions: New | Deploy | Rollback | Deprecate".PHP_EOL;
  echo "Usage: ".$argv[0]." <Function>".PHP_EOL;
  exit(0);
}

$caseD = strtolower($argv[1]);

function checkLtsVer($layer,$machine){
  $out = sendtoServer($layer,"chkLtsVer","n/a","n/a","n/a",$machine);
  return $out;
}

function checkDepC($layer,$machine,$vid){
  $out = sendtoServer($layer,"chkDep",$vid,"n/a","n/a",$machine);
  return $out;
}

function checkDBVer($layer,$machine){
  $out = sendtoServer($layer,"chkDbVer","n/a","n/a","n/a",$machine);
  return $out;
}
//This function will make a bundle and send it to your approprite folder on
//the deploy server
function deployNew($version_id,$user,$dir){
  shell_exec("cd ../
              mkdir $version_id;
              rsync -Rr * $version_id;
              rmdir $version_id/$version_id;
              tar -cvzf $version_id.tar.gz $version_id;
              rm -r $version_id;
              scp $version_id.tar.gz $user@192.168.43.114:$dir;
              rm $version_id.tar.gz;");
            }

//this function sends a command to the deploy server
//this whill deploy an alredy existing bundle
function deploy($vid,$dip,$dir,$layer,$machine){
  $check = checkVer($vid,$layer,$machine);

  if($check == 1){
    echo "THE VERSION IM SENDING IF IT's EXITS is: ".$vid.PHP_EOL;
    $depc = checkDepC($layer,$machine,$vid);
    if ($depc == 1){
      echo "THIS version is depecrated fuck off".PHP_EOL;
    }else{
      sendtoServer($layer,"deployExt",$vid,$dip,$dir,"n/a");
  }
  }else{
    $vid = checkLtsVer($layer,$machine);
    echo "THE VERSION IM SENDING IF DOES NOT IT's EXITS is: ".$vid.PHP_EOL;
    $depc = checkDepC($layer,$machine,$vid);
    if ($depc == 1){
      echo "THIS version is depecrated fuck off".PHP_EOL;
    }else{
      sendtoServer($layer,"deployExt",$vid,$dip,$dir,"n/a");
    }
  }
}

function rollback($vid,$dip,$dir,$layer){
  echo "THE VERSION is ".$vid.PHP_EOL;
  sendtoServer($layer,"deployExt",$vid,$dip,$dir,"n/a");
}

function checkRoll($layer,$machine){
  $ltsVer = checkLtsVer($layer,$machine);
  $out = sendtoServer($layer,"chkRoll",$ltsVer,"n/a","n/a",$machine);
  return $out;
}
//check to see if the version alredy enchant_broker_dict_exists
//this might be taken out if we use a DB to track this
function checkVer($vid,$layer,$machine){
  $stat = sendtoServer($layer,"chkVer",$vid,"n/a","n/a",$machine);
  if($stat == 1){
    echo $stat.PHP_EOL;
    return $stat;
  }
  return 0;
}

//send a meesage to the deploy server, make sure the transfer was Successful
//Also pass the destination IP so the server knows who to send it to
function sendtoServer($type,$func,$vid,$dip,$dir,$machine){
  $client = new rabbitMQClient("../Ini/deploy.ini","testServer");
  $request = array();
  $request['type'] = $type;
  $request['func'] = $func;
  $request['vid'] = $vid;
  $request['dip'] = $dip;
  $request['dir'] = $dir;
  $request['machine'] = $machine;
  $response = $client->send_request($request);

  echo "client received response: ".PHP_EOL;
  print_r($response);
  echo "\n\n";
  return $response;
}

//this will only send data to deploy server will not deploy it
function doPush($vid,$caseD,$user,$dServDir,$layer,$machine){
  //send the data to deploy server
  deployNew($vid,$user,$dServDir);

  //call the function
  sendtoServer($layer,$caseD,$vid,"N/A","N/A",$machine);
}

switch ($caseD)
{
  case "new":
    $vid = checkDBVer($layer,$machine);
    doPush($vid,$caseD,$user,$dServDir,$layer,$machine);
    break;
  case "deploy":
    $vid = "";
    //check to make sure you eneter an destination IP
    //this will be used by the deploy server to send the bundle to the right VM
    if(empty($argv[2]) || empty($argv[3])){
      echo "Please enter a destination Machine and Directory".PHP_EOL;
      echo "Usage: .deplyClient.php "."<Function> <Destination Machine> <Destination Directory> <VersionID>".PHP_EOL;
      exit(0);
    }
    // this is the "DNS" check a flat file and get the IP for that VM
    //if not found exit and tell user why
    if(isset($argv[2])){
      $output = shell_exec("cd ../; cat DNS.conf | grep -i $argv[2] | awk '{ print $1 }'");
      if(isset($output)){
        $string = trim(preg_replace('/\s+/', ' ', $output));
        $destIP = $string;
      }else{
        echo "Hostname not found in DNS, please add record or check spelling".PHP_EOL;
        exit(0);
      }
    }
    if(isset($argv[4])){
      $vid = $argv[4];
    }else{
      $vid = checkLtsVer($layer,$machine);
    }
    $dir= $argv[3];
    deploy($vid,$destIP,$dir,$layer,$machine);
    break;
  case "rollback":
    $vid = "";
    //check to make sure you eneter an destination IP
    //this will be used by the deploy server to send the bundle to the right VM
    if(empty($argv[2]) || empty($argv[3])){
      echo "Please enter a destination Machine and Directory".PHP_EOL;
      echo "Usage: .deplyClient.php "."<Function> <Destination Machine> <Destination Directory> <VersionID>".PHP_EOL;
      exit(0);
    }
    // this is the "DNS" check a flat file and get the IP for that VM
    //if not found exit and tell user why
    if(isset($argv[2])){
      $output = shell_exec("cd ../; cat DNS.conf | grep -i $argv[2] | awk '{ print $1 }'");
      if(isset($output)){
        $string = trim(preg_replace('/\s+/', ' ', $output));
        $destIP = $string;
      }else{
        echo "Hostname not found in DNS, please add record or check spelling".PHP_EOL;
        exit(0);
      }
    }
    $dir= $argv[3];
    $vid = checkRoll($layer,$machine);
    rollback($vid,$destIP,$dir,$layer);
    break;
  case "deprecate":
    if(empty($argv[2])){
      echo "Please enter a Version ID to deprecate".PHP_EOL;
      echo "Usage: .deplyClient.php deprecate <Version ID>".PHP_EOL;
      exit(0);
    }
    $vid = $argv[3];
    sendtoServer($layer,"deprecate",$vid,"n/a","n/a",$machine);
    break;
  default:
    echo "Please enter a valid function".PHP_EOL;
    break;
}
echo $argv[0]." END".PHP_EOL;
?>

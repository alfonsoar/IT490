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
$caseD = strtolower($argv[1]);
$vid = $argv[2];

$string = shell_exec("cat conf.ini | grep -i user | awk '{ print $3 }'");
$user = trim(preg_replace('/\s+/', ' ', $string));
$string1 = shell_exec("cat conf.ini | grep -i deployserver | awk '{ print $3 }'");
$dServDir = trim(preg_replace('/\s+/', ' ', $string1));
$string2 = shell_exec("cat conf.ini | grep -i layer | awk '{ print $3 }'");
$layer = trim(preg_replace('/\s+/', ' ', $string2));

//check to make sure the user eneters a function and Version ID
if(empty($argv[1]) || empty($argv[2])){
  echo "Please enter a Valid function".PHP_EOL;
  echo "Functions: New | Deploy | Rollback".PHP_EOL;
  echo "Usage: ".$argv[0]." <Function> <Version ID>".PHP_EOL;
  exit(0);
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
function deploy($vid,$dip,$dir,$layer){
  $check = checkVer($vid,$layer);

  if($check == 1){
    sendtoServer($layer,"deployExt",$vid,$dip,$dir);

  }else{
    echo "The version ".$vid." does not exist in the deploy server".PHP_EOL;
    exit(0);
  }
}
//check to see if the version alredy enchant_broker_dict_exists
//this might be taken out if we use a DB to track this
function checkVer($vid,$layer){
  $stat = sendtoServer($layer,"chkVer",$vid,"n/a","n/a");
  if($stat == 1){
    echo $stat.PHP_EOL;
    return $stat;
  }
  return 0;
}


//send a meesage to the deploy server, make sure the transfer was Successful
//Also pass the destination IP so the server knows who to send it to
function sendtoServer($type,$func,$vid,$dip,$dir){
  $client = new rabbitMQClient("../Ini/deploy.ini","testServer");
  $request = array();
  $request['type'] = $type;
  $request['func'] = $func;
  $request['vid'] = $vid;
  $request['dip'] = $dip;
  $request['dir'] = $dir;
  $response = $client->send_request($request);

  echo "client received response: ".PHP_EOL;
  print_r($response);
  echo "\n\n";
  return $response;
}

//this will only send data to deploy server will not deploy it
function doPush($vid,$caseD,$user,$dServDir,$layer){
  //send the data to deploy server
  deployNew($vid,$user,$dServDir);

  //call the function
  sendtoServer($layer,$caseD,$vid,"N/A","N/A");
}

switch ($caseD)
{
  case "new":
    doPush($vid,$caseD,$user,$dServDir,$layer);
    break;
  case "deploy":
    //check to make sure you eneter an destination IP
    //this will be used by the deploy server to send the bundle to the right VM
    if(empty($argv[3]) || empty($argv[4])){
      echo "Please enter a destination IP".PHP_EOL;
      echo "Usage: .deplyClient.php "."<Function> <Version ID> <Destination Machine> <Destination Directory>".PHP_EOL;
      exit(0);
    }
    // this is the "DNS" check a flat file and get the IP for that VM
    //if not found exit and tell user why
    if(isset($argv[3])){
      $output = shell_exec("cd ../;
                            cat DNS.conf | grep -i $argv[3] | awk '{ print $1 }'");
      if(isset($output)){
        $string = trim(preg_replace('/\s+/', ' ', $output));
        $destIP = $string;
      }else{
        echo "Hostname not found in DNS, please add record or check spelling".PHP_EOL;
        exit(0);
      }
    }
    $dir= $argv[4];
    deploy($vid,$destIP,$dir,$layer);
    break;
  case "rollback":
    //check to make sure you eneter an destination IP
    //this will be used by the deploy server to send the bundle to the right VM
    if(empty($argv[3]) || empty($argv[4])){
      echo "Please enter a destination IP".PHP_EOL;
      echo "Usage: .deplyClient.php "."<Function> <Version ID> <Destination IP> <Destination Directory>".PHP_EOL;
      exit(0);
    }
    // this is the "DNS" check a flat file and get the IP for that VM
    //if not found exit and tell user why
    if(isset($argv[3])){
      $output = shell_exec("cd ../;
                            cat DNS.conf | grep -i $argv[3] | awk '{ print $1 }'");
      if(isset($output)){
        $string = trim(preg_replace('/\s+/', ' ', $output));
        $destIP = $string;
      }else{
        echo "Hostname not found in DNS, please add record or check spelling".PHP_EOL;
        exit(0);
      }
    }
    $dir= $argv[4];
    deploy($vid,$destIP,$dir,$layer);
    break;
  default:
    echo "Please enter a valid function".PHP_EOL;
    break;
}

echo $argv[0]." END".PHP_EOL;
?>

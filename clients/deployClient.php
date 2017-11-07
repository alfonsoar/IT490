#!/usr/bin/php
<?php
require_once('../Ini/path.inc');
require_once('../Ini/get_host_info.inc');
require_once('../Ini/rabbitMQLib.inc');
require_once('../logscript.php');

if(empty($argv[1])){
  echo "Please enter a Valid function".PHP_EOL;
  echo "Functions: Push | Deploy | Rollback".PHP_EOL;
  echo "Usage: ".$argv[0]."<Function> <Version ID> <Destination IP> <Destination Directory> ".PHP_EOL;
  exit(0);
}

//check to make sure you enter a version ID
if(empty($argv[2])){
  echo "Please enter a Version ID".PHP_EOL;
  echo "Usage: ".$argv[0]."<Function> <Version ID> <Destination IP> <Destination Directory> ".PHP_EOL;
  exit(0);
}

$caseD = strtolower($argv[1]);
$vid = $argv[2];

//This function will make a bundle and send it to your approprite folder on
//the deploy server
function deployNew($version_id){
  shell_exec("cd ../
              mkdir $version_id;
              rsync -Rr * $version_id;
              rmdir $version_id/$version_id;
              tar -cvzf $version_id.tar.gz $version_id;
              rm -r $version_id;
              scp $version_id.tar.gz aga23@192.168.43.114:/local/0/comm;
              rm $version_id.tar.gz;");
            }

function deploy($vid,$dip,$dir){
  sendtoServer("comm","deployExt",$vid,$dip,$dir);
}

function checkVer($vid){
  $stat = sendtoServer("comm","chkVer",$vid,"n/a","n/a");
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

function doDeploy($vid,$CaseD,$dip,$dir){

  $check = checkVer($vid);
  if($check == 1){
    echo "THIS VERSION ALREDY EXIST JUST DEPLOY IT".PHP_EOL;
    deploy($vid,$dip,$dir);
  }else{
    echo "NEW VERSION ZIP AND DEPLOY".PHP_EOL;
    //send the data to deploy server
    deployNew($vid);

    //call the function
    sendtoServer("comm",$CaseD,$vid,$dip,$dir);
  }
}

function doPush($vid,$caseD){
  //send the data to deploy server
  deploy($vid);

  //call the function
  sendtoServer("comm",$caseD,$vid,"N/A","N/A");
}

switch ($caseD)
{
  case "push":
    doPush($vid,$caseD);
    break;
  case "deploy":
    $dip= $argv[3];
    $dir= $argv[4];
    //check to make sure you eneter an destination IP
    //this will be used by the deploy server to send the bundle to the right VM
    if(empty($dip)){
      echo "Please enter a destination IP".PHP_EOL;
      echo "Usage: .deplyClient.php "."<Function> <Version ID> <Destination IP> <Destination Directory>".PHP_EOL;
      exit(0);
    }
    //check to make sure the ser provides a directory to deploy the new files
    if(empty($dir)){
      echo "Please the Destination Directory".PHP_EOL;
      echo "Usage: deplyClient.php "."<Function> <Version ID> <Destination IP> <Destination Directory>".PHP_EOL;
      exit(0);
    }
    doDeploy($vid,$caseD,$dip,$dir);
    break;
  case "rollback":
    echo "I will rollback".PHP_EOL;
    break;
}

echo $argv[0]." END".PHP_EOL;
?>

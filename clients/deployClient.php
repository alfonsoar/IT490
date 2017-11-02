#!/usr/bin/php
<?php
require_once('../Ini/path.inc');
require_once('../Ini/get_host_info.inc');
require_once('../Ini/rabbitMQLib.inc');
require_once('../logscript.php');


function deploy($version_id)
{
  shell_exec("mkdir $version_id;
              rsync -Rr * $version_id;
              rmdir $version_id/$version_id;
              tar -cvzf $version_id.tar.gz $version_id;
              rm -r $version_id;
              scp $version_id.tar.gz aga23@192.168.43.114:/local/0/comm;
              rm $version_id.tar.gz;");
            }

deploy($argv[1]);

function sendtoServer($type,$vid)
{
  $client = new rabbitMQClient("../Ini/deploy.ini","testServer");
  $request = array();
  $request['type'] = $type;
  $request['vid'] = $vid;
  $response = $client->send_request($request);

  echo "client received response: ".PHP_EOL;
  print_r($response);
  echo "\n\n";
  return $response;
}

sendtoServer("comm",$argv[1]);

echo $argv[0]." END".PHP_EOL;
?>

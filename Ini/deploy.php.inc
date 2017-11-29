<?php
require_once('path.inc');
require_once('get_host_info.inc');
require_once('rabbitMQLib.inc');

function deployServ($vid,$dip,$dir,$pat,$user){
    shell_exec("cd /local/0/$pat;
                ssh $user@$dip 'cd $dir ; rm -rf *;'
                scp $vid.tar.gz $user@$dip:$dir;");
    shell_exec("ssh $user@$dip 'cd $dir ;
                                tar -zxvf $vid.tar.gz ;
                                rm $vid.tar.gz ;
                                cd $vid ;
                                cp -R * ../ ;
                                cd ../ ;
                                rm -rf $vid ;'");
    $out=shell_exec("ssh $user@$dip 'cd $dir;
                      cat `find * -name conf.ini` | grep -i bounce;'");
    if (isset($out)){
      $pieces = str_replace("\n","",$out);
      $pieces1 = str_replace("Bounce="," ",$pieces);
      $bounce = preg_split('/\s+/', ltrim($pieces1));
      foreach($bounce as $item){
        echo "I WILL TRY to bounce $item";
        shell_exec("ssh $user@$dip 'sudo systemctl restart $item;'");
      }
    }
}

function validate($vid,$dip,$dir,$pat,$user,$option)
{
    if($option == 1){
      $output = shell_exec("ssh $user@$dip 'cd $dir ; ls -l $dir | grep logscript.php'");
    }else {
      $output = shell_exec("ls -l /local/0/$pat | grep $vid");
    }

    $out = preg_split('/\s+/', $output);
    $nDate = date('M d H:i');
    $pkgDate = $out[5]." ".$out[6]." ".$out[7];
    if (strtotime($pkgDate) == strtotime($nDate)){
        return true;
    } else {
        return 0;
    }
}
function validateVer($vid,$pat){
  $output = shell_exec("ls -l /local/0/$pat | grep $vid");

  if(isset($output)){
    return 1;
  }
  return 0;
}

class deployDB
{
private $deploydb;

public function __construct()
{
	//connection is attempted here
	$this->deploydb = new mysqli("127.0.0.1","root","password123","dbDeploy");

	if ($this->deploydb->connect_errno != 0)
	{

		exit(1);
	}
	echo "correctly connected to database".PHP_EOL;
}

public function newVersion($machine)
{

	$m = $this->deploydb->real_escape_string($machine);
  $m = strtolower($m);
	//print_r("this should be LOWER CASE -->: " . $m);
  //insert into db
  $query = "select vNumber from currentVersions where machine='$m';";
	$pull = $this->deploydb->query($query);

  if(mysqli_num_rows($pull) > 0 )
  {
            while ($row = mysqli_fetch_array($pull))
             {
                 $val = $row['vNumber'];
                 //print_r("SHOULD BE DB PULL 1.0--> " . $val);
              }
  }
  // convert to float first
	$newVersion = 0.1 + (float)$val;
  //make sure a decimal is shown
  $newVersion = number_format($newVersion, 1, '.', ' ');
  //convert back to a string
  $strVersion = "" . $newVersion;

	print_r("Your Old Version was: " . $val . " Your new version is: " . $strVersion);

  $query2 = "update currentVersions set vNumber = '$strVersion' where machine='$machine';";
	$send = $this->deploydb->query($query2);

  //SEND NEW VERSION TO VERSIONS ALSO
  $query3 = "insert into versions(machine, vNumber) values('$machine', '$strVersion');";
  $send2 = $this->deploydb->query($query3);

  return $strVersion;

}

public function checkCurrentVersion($machine)
{
     $m = $this->deploydb->real_escape_string($machine);

     $query = "select vNumber from currentVersions where machine='$machine';";
		 $pull = $this->deploydb->query($query);
     if(mysqli_num_rows($pull) > 0 )
     {
               while ($row = mysqli_fetch_array($pull))
                {
                    $val = $row['vNumber'];
                    //print_r("SHOULD BE DB PULL 1.0--> " . $val);
                 }
     }

     $deployVersion = $val;

		 return $deployVersion;

}

public function versionExists($machine, $version)
{
     $m = $this->deploydb->real_escape_string($machine);

     $query = "select vNumber from versions where machine='$machine' and vNumber='$version';";
		 $pull = $this->deploydb->query($query);

     if(mysqli_num_rows($pull) > 0 )
     {
        return 1;
     }
     else {
       return 0;
     }


}

public function checkDep($machine, $version)
{
     $m = $this->deploydb->real_escape_string($machine);
     $v = $this->deploydb->real_escape_string($version);

     $query = "select dep from versions where machine='$machine' and vNumber='$v';";
     $pull = $this->deploydb->query($query);
     if(mysqli_num_rows($pull) > 0 )
     {
               while ($row = mysqli_fetch_array($pull))
                {
                    $val = $row['dep'];
                 }
     }
     if(strcmp($val, "no") == 0)
     {
        print_r("IT IS NOT DEPRECATED");
        return 0;
     }
     else {
       print_r("IT IS DEPRECATED");
       return 1;
     }


}

//$cur =
public function rollbackVersion($machine, $cur)
{

     //$m = $this->deploydb->real_escape_string($machine);
     $newVersion = "";

		 $query = "select vNumber,dep from versions where machine='$machine';";
		 $pull = $this->deploydb->query($query);
     if(mysqli_num_rows($pull) > 0 )
     {
               while ($row = mysqli_fetch_array($pull))
                {

                    $version = $row['vNumber'];
                    $dep = $row['dep'];
                    if($dep == "no" & $version != $cur)
                    {
                      $newVersion = $version;
                    }
                 }
     }
		 return $newVersion;

}
//deprectate query update versions set dep='yes' where machine='devback' and versions='1.3'

public function versionDep($machine, $version)
{
  $m = $this->deploydb->real_escape_string($machine);
  $v = $this->deploydb->real_escape_string($version);

  $query = "update versions set dep='yes' where machine='$m' and vNumber='$v';";
  $pull = $this->deploydb->query($query);
  $val="";
  $query2 = "select dep from versions where machine='$m' and vNumber='$v';";
  $pull2 = $this->deploydb->query($query2);
  if(mysqli_num_rows($pull2) > 0 )
  {
            while ($row = mysqli_fetch_array($pull2))
             {
                 $val = $row['dep'];
                 echo $val.PHP_EOL;
              }
  }
  if(strcmp($val, "no") == 0)
  {
     print_r("IT IS NOT DEPRECATED");
     return 0;
  }
  else {
    print_r("IT IS DEPRECATED");
    return 1;
  }
return 0;
}


}

?>

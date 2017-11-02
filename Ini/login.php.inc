<?php

class loginDB
{
private $logindb;

public function __construct()
{
	//connection is attempted here
	$this->logindb = new mysqli("127.0.0.1","root","password123","login");

	if ($this->logindb->connect_errno != 0)
	{
		echo "Error connecting to database: ".$this->logindb->connect_error.PHP_EOL;
		exit(1);
	}
	echo "correctly connected to database".PHP_EOL;
}

public function validateLogin($username,$password)
{
	$un = $this->logindb->real_escape_string($username);
	$pw = $this->logindb->real_escape_string($password);
	$statement = "select * from users where screenname = '$un';";
	$response = $this->logindb->query($statement);

	while ($row = $response->fetch_assoc())
	{
		echo "checking password for $username".PHP_EOL;
		if (password_verify($pw, $row["pass"]))
		{
			echo "passwords match for $username".PHP_EOL;
			return 1;// password match
		}
		echo "passwords did not match for $username".PHP_EOL;
	}
	return 0;//no users matched username
}

public function registerUser($username,$password)
{
	$un = $this->logindb->real_escape_string($username);
	$pw = $this->logindb->real_escape_string($password);
	//check if user is in the database
	$statement = "select * from users where screenname = '$un';";
	$response = $this->logindb->query($statement);
	if ($response->num_rows != 0)
	{
		echo "Username already exists";
		return 0;
	}

	//add user to database
	$statement = "insert into users(screenname,pass) values('$un','$pw');";
	$response = $this->logindb->query($statement);

	//Check if insert was successful
	$statement = "select * from users where screenname = '$un';";
	$response = $this->logindb->query($statement);
	if ($response->num_rows != 0)
	{
		echo "Registration successful";
		return 1;
	}
	return 0;
}

}
?>

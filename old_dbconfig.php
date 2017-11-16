<?php

$servername ="imc.kean.edu";
$username = "seds2017";
$password = "2017fall";
$dbname = "seds2017db";


//create connection
$conn = mysqli_connect($servername, $username, $password, $dbname );

//check connection
if (!$conn){
	die("Connection Failed: " . mysqli_connect_error());
}

?>

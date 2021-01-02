<?php

$dbServername = "db";
$dbUsername = "chris";
$dbPassword = "chrispass";
$dbName = "ANPR";

$conn = mysqli_connect($dbServername, $dbUsername, $dbPassword, $dbName);
if($conn->connect_error){
	echo 'Connection Failed' . $conn->connect_error;
}
?>

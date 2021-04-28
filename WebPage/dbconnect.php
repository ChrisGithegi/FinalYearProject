<?php

$dbServername = "db";
$dbUsername = "root";
$dbPassword = "root";
$dbName = "ANPR";

$conn = mysqli_connect($dbServername, $dbUsername, $dbPassword, $dbName);
if($conn->connect_error){
	echo 'Connection Failed' . $conn->connect_error;
}
?>

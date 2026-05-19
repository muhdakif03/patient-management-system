<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "pms_db";

$conn = mysqli_connect($host, $user, $pass, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

include_once('logger.php'); 
?>
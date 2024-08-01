<?php
// Set database connection parameters
$host = 'soctify.cp5nnrmvt3en.ap-northeast-1.rds.amazonaws.com';
$port = 3306; // replace with your port number
$username = 'admin';
$password = 'FjQpB2%4tt%4';

$database = 'soctify-db';

// Create a mysqli object
$mysqli = new mysqli($host, $username, $password, $database, $port);

// Check if there are any errors in the connection
if ($mysqli->connect_error) {
    die('Connection error: (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
}
?>
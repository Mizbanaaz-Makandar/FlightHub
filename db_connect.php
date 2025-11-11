<?php
// db_connect.php

// Database configuration
$db_host = 'localhost';
$db_user = 'root'; // Your MySQL username
$db_pass = 'root@123';     // Your MySQL password
$db_name = 'airline12';
// Create a database connection
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Start the session on every page that includes this file
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
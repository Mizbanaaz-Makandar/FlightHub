<?php
$host = "localhost";
$user = "root";  // default in XAMPP
$pass = "roo";      // default empty
$db   = "airline12";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
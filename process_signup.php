<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "airline12";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

$first_name = $_POST['first_name'];
$last_name = $_POST['last_name'];
$email = $_POST['email'];
$phone = $_POST['phone'];
$password = $_POST['password'];
$confirm_password = $_POST['confirm_password'];
$dob = $_POST['dob'];

if ($password !== $confirm_password) {
    die("Passwords do not match!");
}

$hashed_password = md5($password);

$sql = "INSERT INTO users (first_name, last_name, email, phone, password, dob, role)
        VALUES ('$first_name', '$last_name', '$email', '$phone', '$hashed_password', '$dob', 'user')";

if ($conn->query($sql) === TRUE) {
    echo "Account created successfully! <a href='signin.php'>Login Now</a>";
} else {
    echo "Error: " . $conn->error;
}

$conn->close();
?>

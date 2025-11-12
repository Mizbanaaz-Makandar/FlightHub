 <?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "airline12";

$conn = new mysqli($servername, $username, $password, $dbname);




if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

$email = $_POST['email'];
$password = md5($_POST['password']);

$sql = "SELECT * FROM users WHERE email='$email' AND password='$password'";
$result = $conn->query($sql);

if ($result->num_rows == 1) {
    $row = $result->fetch_assoc();
    $_SESSION['email'] = $row['email'];
    $_SESSION['role'] = $row['role'];

    if ($row['role'] == 'admin') {
        header("Location: admin_dashboard.php");
    } else {
        header("Location: index.php");
    }
} else {
    echo "Invalid login credentials!";
}

$conn->close();
?>

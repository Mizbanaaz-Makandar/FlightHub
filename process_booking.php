<?php
session_start();
require 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['action'])) {
    header('Location: index.php');
    exit();
}

$action = $_POST['action'];

switch ($action) {
    // Step 1: User selects a flight and class from booking.php
    case 'select_flight':
        if (isset($_POST['flight_id'], $_POST['seat_type'])) {
            $flight_id = $_POST['flight_id'];
            $seat_type = $_POST['seat_type'];

            $stmt = $conn->prepare("SELECT f.*, a.name AS airline_name FROM flights f JOIN airlines a ON f.airline_id = a.id WHERE f.id = ?");
            $stmt->bind_param("i", $flight_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                // Store flight and seat choice in session
                $_SESSION['booking_details'] = [
                    'flight_details' => $result->fetch_assoc(),
                    'seat_type' => $seat_type
                ];
                header('Location: passenger_details.php');
                exit();
            } else {
                $_SESSION['error_message'] = "The selected flight is no longer available. Please try again.";
                header('Location: booking.php');
                exit();
            }
        }
        break;

    // Step 2: User submits passenger details from passenger_details.php
    case 'submit_passengers':
        if (isset($_POST['passengers'])) {
            $_SESSION['passenger_details'] = $_POST['passengers'];
            header('Location: payment.php');
            exit();
        }
        break;

    // Step 3: User confirms payment from payment.php
    case 'submit_payment':
        // In a real application, payment processing would happen here.
        // We simulate a successful payment and proceed to confirmation.
        if (isset($_SESSION['booking_details'], $_SESSION['passenger_details'])) {
             header('Location: confirmation.php');
             exit();
        }
        break;
}

// Fallback if any action fails
$_SESSION['error_message'] = "An unexpected error occurred. Please start your search again.";
header('Location: index.php');
exit();
?>


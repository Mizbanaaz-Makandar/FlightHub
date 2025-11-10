<?php
session_start();
require 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reservation_id'])) {
    $reservation_id = $_POST['reservation_id'];

    $conn->begin_transaction();

    try {
        // Step 1: Get reservation details (flight_id and seat_type)
        $stmt = $conn->prepare("SELECT flight_id, seat_type FROM reservations WHERE id = ? AND reservation_status = 'Confirmed'");
        $stmt->bind_param("i", $reservation_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("Booking not found or already cancelled.");
        }
        
        $reservation = $result->fetch_assoc();
        $flight_id = $reservation['flight_id'];
        $seat_type = $reservation['seat_type'];

        // Step 2: Update reservation status to 'Cancelled'
        $stmt = $conn->prepare("UPDATE reservations SET reservation_status = 'Cancelled' WHERE id = ?");
        $stmt->bind_param("i", $reservation_id);
        $stmt->execute();

        // Step 3: Increment the available seats count in the flights table
        $seat_column = '';
        switch ($seat_type) {
            case 'Economy':
                $seat_column = 'available_economy_seats';
                break;
            case 'Business':
                $seat_column = 'available_business_seats';
                break;
            case 'First-Class':
                $seat_column = 'available_first_class_seats';
                break;
        }

        if ($seat_column) {
            // We increment by 1 because each reservation is for one passenger
            $stmt = $conn->prepare("UPDATE flights SET $seat_column = $seat_column + 1 WHERE id = ?");
            $stmt->bind_param("i", $flight_id);
            $stmt->execute();
        }

        $conn->commit();
        $_SESSION['message'] = "Booking successfully cancelled.";

    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error_message'] = "Error cancelling booking: " . $e->getMessage();
    }

    // Redirect back to the bookings page
    header("Location: my_bookings.php");
    exit();
} else {
    header("Location: index.php");
    exit();
}
?>

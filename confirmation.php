<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmed - FlightHub</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .ticket-container { margin-bottom: 30px; page-break-inside: avoid; }
        @media print { .confirmation-actions, .main-header { display: none; } }
    </style>
</head>
<body>
    <?php 
    session_start();
    // A safety check - if session is cleared or booking wasn't made, redirect home.
    if (!isset($_SESSION['booking_details'], $_SESSION['passenger_details'], $_SESSION['search_criteria'])) {
        header("Location: index.php");
        exit();
    }
    
    // Database connection to save the final booking
    require 'db_connect.php';

    $booking = $_SESSION['booking_details'];
    $flight = $booking['flight_details'];
    $passengers = $_SESSION['passenger_details'];
    $passenger_count = $_SESSION['search_criteria']['passengers'];
    $seat_type = $booking['seat_type'];
    
    $price_column = strtolower(str_replace('-', '_', $seat_type)) . '_price';
    $price_per_ticket = $flight[$price_column];
    $total_amount = $price_per_ticket * $passenger_count;
    
    $booking_reference = "SKY-" . strtoupper(bin2hex(random_bytes(3)));

    // --- START OF THE FIX ---
    // Save booking to database, checking for existing passengers first
    foreach ($passengers as $p) {
        $passenger_id = null;

        // 1. Check if passenger exists by email
        $stmt_check = $conn->prepare("SELECT id FROM passengers WHERE email = ?");
        $stmt_check->bind_param("s", $p['email']);
        $stmt_check->execute();
        $result = $stmt_check->get_result();

        if ($result->num_rows > 0) {
            // Passenger exists, get their ID
            $existing_passenger = $result->fetch_assoc();
            $passenger_id = $existing_passenger['id'];
        } else {
            // Passenger is new, insert them
            $stmt_insert = $conn->prepare("INSERT INTO passengers (first_name, last_name, email, phone) VALUES (?, ?, ?, ?)");
            $stmt_insert->bind_param("ssss", $p['first_name'], $p['last_name'], $p['email'], $p['phone']);
            $stmt_insert->execute();
            $passenger_id = $stmt_insert->insert_id;
            $stmt_insert->close();
        }
        $stmt_check->close();

        // 2. Create the reservation with the correct passenger ID
        if ($passenger_id) {
            $stmt_reserve = $conn->prepare("INSERT INTO reservations (passenger_id, flight_id, seat_type) VALUES (?, ?, ?)");
            $stmt_reserve->bind_param("iis", $passenger_id, $flight['id'], $seat_type);
            $stmt_reserve->execute();
            $stmt_reserve->close();
        }
    }
    // --- END OF THE FIX ---

    // Clear session data after booking is complete
    session_unset();
    session_destroy();
    ?>
    <!-- <header class="main-header header-static">
        <div class="container">
            <a href="index.php" class="logo"><i class="fas fa-plane-departure"></i> Skyline Airways</a>
        </div>
    </header> -->

    <main class="page-container">
        <div class="container">
            <div class="confirmation-container">
                <div class="confirmation-icon"><i class="fas fa-check-circle"></i></div>
                <h2>Booking Confirmed!</h2>
                <p>Your flight has been successfully booked. Your e-ticket is below.</p>

                <div class="ticket-container">
                    <div class="ticket-header">
                        <div class="airline-logo"><i class="fas fa-plane-departure"></i> Skyline Airways</div>
                        <div class="booking-ref">BOOKING REFERENCE: <strong><?php echo $booking_reference; ?></strong></div>
                    </div>
                    
                    <div class="ticket-flight-details">
                        <h4>Flight Details</h4>
                        <p><strong><?php echo htmlspecialchars($flight['airline_name']); ?></strong> - Flight <?php echo htmlspecialchars($flight['flight_number']); ?></p>
                        <div class="flight-route">
                           <div>
                                <strong><?php echo htmlspecialchars($flight['origin']); ?></strong>
                                <p><?php echo date("H:i", strtotime($flight['departure_time'])); ?></p>
                                <p><?php echo date("D, M j, Y", strtotime($flight['departure_time'])); ?></p>
                           </div>
                           <div class="route-arrow"><i class="fas fa-long-arrow-alt-right"></i></div>
                           <div>
                               <strong><?php echo htmlspecialchars($flight['destination']); ?></strong>
                               <p><?php echo date("H:i", strtotime($flight['arrival_time'])); ?></p>
                               <p><?php echo date("D, M j, Y", strtotime($flight['arrival_time'])); ?></p>
                           </div>
                        </div>
                         <p>Class: <strong><?php echo htmlspecialchars($seat_type); ?></strong></p>
                    </div>

                    <div class="ticket-passenger-details">
                        <h4>Passengers</h4>
                        <?php foreach($passengers as $passenger): ?>
                            <p><?php echo htmlspecialchars($passenger['title']) . '. ' . htmlspecialchars($passenger['first_name']) . ' ' . htmlspecialchars($passenger['last_name']); ?></p>
                        <?php endforeach; ?>
                        <hr>
                        <p><strong>Total Amount Paid:</strong> ₹<?php echo number_format($total_amount, 2); ?></p>
                    </div>
                </div>

                <div class="confirmation-actions">
                    <button onclick="window.print()" class="btn-primary"><i class="fas fa-print"></i> Print Ticket</button>
                    <a href="index.php" class="btn-secondary">Book Another Flight</a>
                </div>
            </div>
        </div>
    </main>
</body>
</html>


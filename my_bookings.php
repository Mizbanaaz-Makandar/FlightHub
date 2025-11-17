<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - FlightHub</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        /* Style for the success message */
        .success-message {
            background-color: #2ecc71;
            color: white;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px; /* Matches other elements */
            text-align: center; /* Centers the message text */
            font-weight: 500;
        }

        /* Improve visibility of ticket details */
        .booking-ticket-card .ticket-body p {
            font-size: 1.05em; /* Slightly larger font */
            margin-bottom: 12px; /* A bit more spacing */
            color: black;
        }
    </style>
</head>
<body>
    <?php session_start(); ?>
    <header class="main-header header-static">
        <div class="container">
            <a href="index.php" class="logo"><i class="fas fa-plane-departure"></i> FlightHub</a>
            <nav class="main-nav">
                
            </nav>
        </div>
    </header>

    <main class="page-container">
        <div class="container">
            <div class="page-header">
                <h2>My Bookings</h2>
                <p>Enter your email to find and manage your bookings.</p>
            </div>

            <?php
            // Display success or error messages from the session
            if (isset($_SESSION['message'])): ?>
                <div class="success-message">
                    <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
                </div>
            <?php endif; ?>
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="error-message">
                    <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
                </div>
            <?php endif; ?>

            <div class="booking-search-form">
                <form action="my_bookings.php" method="POST">
                    <div class="form-group">
                        <input type="email" name="email" placeholder="Enter your email address" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    </div><br>
                    <button type="submit" class="btn-primary">Find Bookings</button>
                </form>
            </div>

            <?php
            if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['email'])) {
                require 'db_connect.php';
                $email = $_POST['email'];

                // This query joins reservations, passengers, and flights to get all necessary details
                $stmt = $conn->prepare("
                    SELECT 
                        r.id as reservation_id,
                        r.reservation_status,
                        r.seat_type,
                        f.flight_number,
                        f.origin,
                        f.destination,
                        f.departure_time,
                        f.arrival_time,
                        a.name as airline_name,
                        p.first_name,
                        p.last_name
                    FROM reservations r
                    JOIN passengers p ON r.passenger_id = p.id
                    JOIN flights f ON r.flight_id = f.id
                    JOIN airlines a ON f.airline_id = a.id
                    WHERE p.email = ?
                    ORDER BY f.departure_time DESC
                ");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    echo "<h3>Bookings found for " . htmlspecialchars($email) . "</h3>";
                    while ($booking = $result->fetch_assoc()) {
            ?>
                        <div class="booking-ticket-card">
                            <div class="ticket-header">
                                <h4><?php echo htmlspecialchars($booking['origin']); ?> <i class="fas fa-arrow-right"></i> <?php echo htmlspecialchars($booking['destination']); ?></h4>
                                <span class="status-<?php echo strtolower($booking['reservation_status']); ?>"><?php echo htmlspecialchars($booking['reservation_status']); ?></span>
                            </div>
                            <div class="ticket-body">
                                <p><strong>Passenger:</strong> <?php echo htmlspecialchars($booking['first_name'] . ' ' . $booking['last_name']); ?></p>
                                <p><strong>Flight:</strong> <?php echo htmlspecialchars($booking['airline_name'] . ' ' . $booking['flight_number']); ?></p>
                                <p><strong>Departs:</strong> <?php echo date("D, M j, Y H:i", strtotime($booking['departure_time'])); ?></p>
                                <p><strong>Booking ID:</strong> <?php echo $booking['reservation_id']; ?></p>
                            </div>
                            <div class="ticket-actions">
                                <?php if ($booking['reservation_status'] == 'Confirmed'): ?>
                                    <form action="cancel_booking.php" method="POST" onsubmit="return confirm('Are you sure you want to cancel this booking?');">
                                        <input type="hidden" name="reservation_id" value="<?php echo $booking['reservation_id']; ?>">
                                        <button type="submit" class="btn-cancel">Cancel Ticket</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
            <?php
                    }
                } else {
                    echo "<p style='text-align: center; margin-top: 20px;'>No bookings found for this email address.</p>";
                }
                $stmt->close();
                $conn->close();
            }
            ?>
        </div>
    </main>
</body>
</html>


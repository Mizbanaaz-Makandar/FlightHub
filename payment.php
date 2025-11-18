<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - Skyline Airways</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php session_start();
    if (!isset($_SESSION['booking_details'], $_SESSION['passenger_details'])) {
        header("Location: index.php");
        exit();
    }
    $booking = $_SESSION['booking_details'];
    $flight = $booking['flight_details'];
    $passenger_count = count($_SESSION['passenger_details']);
    $seat_type = $booking['seat_type'];
    
    $price_column = strtolower(str_replace('-', '_', $seat_type)) . '_price';
    $price_per_ticket = $flight[$price_column];
    $total_price = $price_per_ticket * $passenger_count;
    ?>
    <header class="main-header header-static">
        <div class="container">
            <a href="index.php" class="logo">
                <i class="fas fa-plane-departure"></i> Skyline Airways
            </a>
        </div>
    </header>

    <main class="page-container">
        <div class="container payment-container">
            <div class="itinerary-summary">
                <h3><i class="fas fa-receipt"></i> Flight Itinerary</h3>
                <div class="summary-card">
                    <div class="flight-details-summary">
                        <h4><?php echo htmlspecialchars($flight['origin']); ?> <i class="fas fa-long-arrow-alt-right"></i> <?php echo htmlspecialchars($flight['destination']); ?></h4>
                        <p><?php echo date("l, F j, Y", strtotime($flight['departure_time'])); ?></p>
                        <p><?php echo htmlspecialchars($flight['airline_name']); ?> - <?php echo htmlspecialchars($flight['flight_number']); ?></p>
                        <p>Class: <strong><?php echo htmlspecialchars($seat_type); ?></strong></p>
                        <p>Passengers: <strong><?php echo $passenger_count; ?></strong></p>
                    </div>
                    <div class="price-summary">
                        <h4>Total Price</h4>
                        <p class="total-amount">₹<?php echo number_format($total_price, 2); ?></p>
                    </div>
                </div>
            </div><br><br>

            <div class="payment-form">
                <h3><i class="fas fa-credit-card"></i> Choose Payment Method</h3><br>
                <form action="process_booking.php" method="POST" id="paymentForm">
                    <input type="hidden" name="action" value="submit_payment">
                    <div class="payment-options">
                        <label>
                            <input type="radio" name="payment_method" value="card" checked> Credit/Debit Card
                        </label>
                        <label>
                            <input type="radio" name="payment_method" value="upi"> UPI
                        </label>
                    </div>

                    <div id="card-details" class="payment-details">
                        <div class="form-group">
                            <label for="card_number">Card Number</label>
                            <input type="text" id="card_number" name="card_number" placeholder="1111 2222 3333 4444" pattern="\d{16}" title="16-digit card number">
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="expiry_date">Expiry Date</label>
                                <input type="text" id="expiry_date" name="expiry_date" placeholder="MM/YY">
                            </div>
                            <div class="form-group">
                                <label for="cvv">CVV</label>
                                <input type="text" id="cvv" name="cvv" placeholder="123">
                            </div>
                        </div>
                    </div>

                    <div id="upi-details" class="payment-details" style="display: none;">
                        <div class="form-group">
                            <label for="upi_id">UPI ID</label>
                            <input type="text" id="upi_id" name="upi_id" placeholder="yourname@bank">
                        </div>
                    </div><br>

                    <button type="submit" class="btn-primary btn-block">Pay Now</button>
                </form>
            </div>
        </div>
    </main>
    <script src="script.js"></script>
</body>
</html>


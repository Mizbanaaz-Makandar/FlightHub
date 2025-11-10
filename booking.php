<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Flights - FlightHub</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php session_start();
    // Redirect home if search results aren't in session
    if (!isset($_SESSION['search_results'], $_SESSION['search_criteria'])) {
        header("Location: index.php");
        exit();
    }
    $flights = $_SESSION['search_results'];
    $search_criteria = $_SESSION['search_criteria'];
    ?>
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
                <h2>Available Flights: <?php echo htmlspecialchars($search_criteria['origin']) . " to " . htmlspecialchars($search_criteria['destination']); ?></h2>
                <p><?php echo date("l, F j, Y", strtotime($search_criteria['departure_date'])); ?> &middot; <?php echo htmlspecialchars($search_criteria['passengers']); ?> Passenger(s)</p>
            </div>

            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="error-message">
                    <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
                </div>
            <?php endif; ?>

            <?php if (empty($flights)): ?>
                <div class="no-flights-found">
                    <h3><i class="fas fa-exclamation-triangle"></i> No Flights Found</h3>
                    <p>We couldn't find any flights for the selected date and route. Please try another search.</p>
                    <a href="index.php" class="btn-primary">Back to Search</a>
                </div>
            <?php else: ?>
                <?php foreach ($flights as $flight): ?>
                    <div class="flight-card">
                        <div class="flight-info">
                            <div class="airline-details">
                                <i class="fas fa-plane"></i>
                                <span><?php echo htmlspecialchars($flight['airline_name']); ?></span>
                                <small><?php echo htmlspecialchars($flight['flight_number']); ?></small>
                            </div>
                            <div class="flight-time">
                                <div>
                                    <strong><?php echo date("H:i", strtotime($flight['departure_time'])); ?></strong>
                                    <p><?php echo htmlspecialchars($flight['origin']); ?></p>
                                </div>
                                <div class="duration">
                                    <hr>
                                    <small>
                                        <?php
                                        $departure = new DateTime($flight['departure_time']);
                                        $arrival = new DateTime($flight['arrival_time']);
                                        $interval = $departure->diff($arrival);
                                        echo $interval->format('%hh %im');
                                        ?>
                                    </small>
                                </div>
                                <div>
                                    <strong><?php echo date("H:i", strtotime($flight['arrival_time'])); ?></strong>
                                    <p><?php echo htmlspecialchars($flight['destination']); ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="flight-pricing">
                            <div class="price-option">
                                <b><p>Economy</p></b>
                                <small class="seats-available <?php if($flight['available_economy_seats'] < 10) echo 'low-seats'; ?>"><?php echo $flight['available_economy_seats']; ?> seats left</small>
                                <strong>₹<?php echo number_format($flight['economy_price'], 2); ?></strong>
                                <form action="process_booking.php" method="POST">
                                    <input type="hidden" name="action" value="select_flight">
                                    <input type="hidden" name="flight_id" value="<?php echo $flight['id']; ?>">
                                    <input type="hidden" name="seat_type" value="Economy">
                                    <button type="submit" class="btn-select" <?php if($flight['available_economy_seats'] < $search_criteria['passengers']) echo 'disabled'; ?>>
                                        <?php echo ($flight['available_economy_seats'] < $search_criteria['passengers']) ? 'Sold Out' : 'Select'; ?>
                                    </button>
                                </form>
                            </div>
                            <div class="price-option">
                                <b><p>Business</p></b>
                                <small class="seats-available <?php if($flight['available_business_seats'] < 10) echo 'low-seats'; ?>"><?php echo $flight['available_business_seats']; ?> seats left</small>
                                <strong>₹<?php echo number_format($flight['business_price'], 2); ?></strong>
                                <form action="process_booking.php" method="POST">
                                    <input type="hidden" name="action" value="select_flight">
                                    <input type="hidden" name="flight_id" value="<?php echo $flight['id']; ?>">
                                    <input type="hidden" name="seat_type" value="Business">
                                    <button type="submit" class="btn-select" <?php if($flight['available_business_seats'] < $search_criteria['passengers']) echo 'disabled'; ?>>
                                        <?php echo ($flight['available_business_seats'] < $search_criteria['passengers']) ? 'Sold Out' : 'Select'; ?>
                                    </button>
                                </form>
                            </div>
                            <div class="price-option">
                                <b><p>First-Class</p></b>
                                <small class="seats-available <?php if($flight['available_first_class_seats'] < 10) echo 'low-seats'; ?>"><?php echo $flight['available_first_class_seats']; ?> seats left</small>
                                <strong>₹<?php echo number_format($flight['first_class_price'], 2); ?></strong>
                                <form action="process_booking.php" method="POST">
                                    <input type="hidden" name="action" value="select_flight">
                                    <input type="hidden" name="flight_id" value="<?php echo $flight['id']; ?>">
                                    <input type="hidden" name="seat_type" value="First-Class">
                                    <button type="submit" class="btn-select" <?php if($flight['available_first_class_seats'] < $search_criteria['passengers']) echo 'disabled'; ?>>
                                        <?php echo ($flight['available_first_class_seats'] < $search_criteria['passengers']) ? 'Sold Out' : 'Select'; ?>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>


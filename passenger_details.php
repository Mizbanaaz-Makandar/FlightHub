<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Passenger Details - FlightHub</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php session_start();
    if (!isset($_SESSION['booking_details'], $_SESSION['search_criteria'])) {
        header("Location: index.php");
        exit();
    }
    $passenger_count = $_SESSION['search_criteria']['passengers'];
    ?>
    <header class="main-header header-static">
        <div class="container">
            <a href="index.php" class="logo"><i class="fas fa-plane-departure"></i> FlightHub</a>
        </div>
    </header>

    <main class="page-container">
        <div class="container">
            <div class="page-header">
                <h2>Enter Passenger Details</h2>
            </div>
            <form action="process_booking.php" method="POST" class="passenger-form">
                <input type="hidden" name="action" value="submit_passengers">
                <?php for ($i = 0; $i < $passenger_count; $i++): ?>
                    <div class="passenger-card">
                        <h4 style="text-align: center;"><i class="fas fa-user"></i>  Passenger <?php echo $i + 1; ?></h4>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="title-<?php echo $i; ?>">Title</label>
                                <select id="title-<?php echo $i; ?>" name="passengers[<?php echo $i; ?>][title]" required>
                                    <option value="Mr">Mr</option>
                                    <option value="Mrs">Mrs</option>
                                    <option value="Ms">Ms</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="first-name-<?php echo $i; ?>">First Name</label>
                                <input type="text" id="first-name-<?php echo $i; ?>" name="passengers[<?php echo $i; ?>][first_name]" placeholder="e.g., John" required>
                            </div>
                            <div class="form-group">
                                <label for="last-name-<?php echo $i; ?>">Last Name</label>
                                <input type="text" id="last-name-<?php echo $i; ?>" name="passengers[<?php echo $i; ?>][last_name]" placeholder="e.g., Doe" required>
                            </div>
                        </div>
                        <div class="form-row">
                           <div class="form-group">
                                <label for="gender-<?php echo $i; ?>">Gender</label>
                                <select id="gender-<?php echo $i; ?>" name="passengers[<?php echo $i; ?>][gender]" required>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="email-<?php echo $i; ?>">Email</label>
                                <input type="email" id="email-<?php echo $i; ?>" name="passengers[<?php echo $i; ?>][email]" placeholder="e.g., john.doe@example.com" required>
                            </div>
                            <div class="form-group">
                                <label for="phone-<?php echo $i; ?>">Phone</label>
                                <input type="tel" id="phone-<?php echo $i; ?>" name="passengers[<?php echo $i; ?>][phone]" placeholder="e.g., 9876543210" required>
                            </div>
                        </div>
                    </div>
                <?php endfor; ?>
                <div class="form-actions">
                    <button type="reset" class="btn-secondary">Reset</button>
                    <button type="submit" class="btn-primary">Proceed to Payment</button>
                </div>
            </form>
        </div>
    </main>
</body>
</html>


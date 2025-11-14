<?php
session_start();
if ($_SESSION['role'] != 'user') { header("Location: signin.php"); exit; }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Skyline Airways - Book a Flight</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header class="main-header">
        <div class="container">
            <a href="index.php" class="logo">
                <i class="fas fa-plane-departure"></i> FlightHub
            </a>
            <nav class="main-nav">
                <a href="my_bookings.php">My Bookings</a>
                <a href="#">Logout</a>
                
            </nav>
        </div>
    </header>

    <main>
        <section class="hero-section">
            <div class="hero-overlay"></div>
            <div class="container hero-content">
                <h1>Find Your Next Adventure</h1>
                <p>Book your flights with ease and discover amazing destinations.</p>
                <div class="booking-form-container" id="book">
                    <form action="search_flights.php" method="POST" class="booking-form" id="bookingForm">
                        <div class="form-tabs">
                            <button type="button" class="tab-btn active" data-type="round-trip">
                                <i class="fas fa-retweet"></i> Round-trip
                            </button>
                            <button type="button" class="tab-btn" data-type="one-way">
                                <i class="fas fa-arrow-right"></i> One-way
                            </button>
                        </div>
                        <input type="hidden" name="flight_type" id="flight_type" value="round-trip">

                        <div class="form-row">
                            <div class="form-group">
                                <label for="origin"><i class="fas fa-plane-departure"></i> From</label>
                                <input type="text" id="origin" name="origin" placeholder="e.g., New York (JFK)" required>
                            </div>
                            <div class="form-group">
                                <label for="destination"><i class="fas fa-plane-arrival"></i> To</label>
                                <input type="text" id="destination" name="destination" placeholder="e.g., London (LHR)" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="departure-date"><i class="fas fa-calendar-alt"></i> Depart</label>
                                <input type="date" id="departure-date" name="departure_date" required>
                            </div>
                            <div class="form-group" id="return-date-group">
                                <label for="return-date"><i class="fas fa-calendar-alt"></i> Return</label>
                                <input type="date" id="return-date" name="return_date">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="passengers"><i class="fas fa-user-friends"></i> Passengers</label>
                                <input type="number" id="passengers" name="passengers" value="1" min="1" max="9" required>
                            </div>
                        </div>

                        <button type="submit" class="btn-submit">
                            <i class="fas fa-search"></i> Search Flights
                        </button>
                    </form>
                </div>
            </div>
        </section>
    </main>
    <script src="script.js"></script>
</body>
</html>


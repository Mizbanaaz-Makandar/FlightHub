<?php
session_start();
require 'db_connect.php'; // Use require to ensure the script stops if DB connection fails

// Always clear previous search results and errors
unset($_SESSION['search_results']);
unset($_SESSION['error_message']);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Store search criteria in session for use on the booking page
    $_SESSION['search_criteria'] = [
        'flight_type' => $_POST['flight_type'],
        'origin' => $_POST['origin'],
        'destination' => $_POST['destination'],
        'departure_date' => $_POST['departure_date'],
        'return_date' => $_POST['return_date'] ?? null, // Handle optional return date
        'passengers' => $_POST['passengers']
    ];

    $origin = $_POST['origin'];
    $destination = $_POST['destination'];
    $departure_date = $_POST['departure_date'];

    // Prepare a statement to prevent SQL injection
    $stmt = $conn->prepare(
        "SELECT f.*, a.name AS airline_name 
         FROM flights f 
         JOIN airlines a ON f.airline_id = a.id 
         WHERE f.origin = ? 
         AND f.destination = ? 
         AND DATE(f.departure_time) = ?"
    );

    // Bind parameters to the statement
    $stmt->bind_param("sss", $origin, $destination, $departure_date);
    
    // Execute the query
    $stmt->execute();
    $result = $stmt->get_result();

    $flights = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $flights[] = $row;
        }
    }

    // Store results in session and redirect to booking page
    $_SESSION['search_results'] = $flights;
    header("Location: booking.php");
    exit();

} else {
    // Redirect to home if the page is accessed directly without a POST request
    header("Location: index.php");
    exit();
}
?>


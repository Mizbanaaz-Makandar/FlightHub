<?php
// Session management for simple authentication
session_start();

// Hardcoded database connection details - for demonstration only
// In a real-world app, these would be in a separate, secure file
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "airline12";

// Check if a session is active. If not, redirect to the login page.
// This is a basic form of access control.
if ($_SESSION['role'] != 'admin') { header("Location: signin.php"); exit; }

// Function to get a database connection
function getDbConnection() {
    global $servername, $username, $password, $dbname;
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    return $conn;
}

// --- PHP logic for all CRUD operations ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $conn = getDbConnection();

    // Add New Airline
    if (isset($_POST['add_airline'])) {
        $airline_name = $_POST['airline_name'];
        $stmt = $conn->prepare("INSERT INTO airlines (name) VALUES (?)");
        $stmt->bind_param("s", $airline_name);
        $stmt->execute();
        $stmt->close();
    }
    
    // Delete Airline
    if (isset($_POST['delete_airline'])) {
        $airline_id = $_POST['airline_id'];
        $stmt = $conn->prepare("DELETE FROM airlines WHERE id = ?");
        $stmt->bind_param("i", $airline_id);
        $stmt->execute();
        $stmt->close();
    }
    
    // Schedule New Flight (Create)
    if (isset($_POST['add_flight'])) {
        $flight_number = $_POST['flight_number'];
        $airline_id = $_POST['airline_id'];
        $origin = $_POST['origin'];
        $destination = $_POST['destination'];
        $departure_time = $_POST['departure_time'];
        $arrival_time = $_POST['arrival_time'];
        $economy_seats = $_POST['economy_seats'];
        $business_seats = $_POST['business_seats'];
        $first_class_seats = $_POST['first_class_seats'];
        $economy_price = $_POST['economy_price'];
        $business_price = $_POST['business_price'];
        $first_class_price = $_POST['first_class_price'];

        // Initially, all seats are available and status is Scheduled
        $available_economy = $economy_seats;
        $available_business = $business_seats;
        $available_first = $first_class_seats;
        $status = 'Scheduled';

        $stmt = $conn->prepare("INSERT INTO flights (flight_number, airline_id, origin, destination, departure_time, arrival_time, economy_seats, business_seats, first_class_seats, available_economy_seats, available_business_seats, available_first_class_seats, economy_price, business_price, first_class_price, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sissssiiiiiiidds", $flight_number, $airline_id, $origin, $destination, $departure_time, $arrival_time, $economy_seats, $business_seats, $first_class_seats, $available_economy, $available_business, $available_first, $economy_price, $business_price, $first_class_price, $status);
        $stmt->execute();
        $stmt->close();
    }
    
    // Edit Flight (Update)
    if (isset($_POST['edit_flight'])) {
        $flight_id = $_POST['flight_id'];
        $flight_number = $_POST['flight_number'];
        $airline_id = $_POST['airline_id'];
        $origin = $_POST['origin'];
        $destination = $_POST['destination'];
        $departure_time = $_POST['departure_time'];
        $arrival_time = $_POST['arrival_time'];

        $stmt = $conn->prepare("UPDATE flights SET flight_number=?, airline_id=?, origin=?, destination=?, departure_time=?, arrival_time=? WHERE id=?");
        $stmt->bind_param("sissssi", $flight_number, $airline_id, $origin, $destination, $departure_time, $arrival_time, $flight_id);
        $stmt->execute();
        $stmt->close();
    }

    // Update Flight Status (NEW AJAX LOGIC - NO REDIRECT HERE)
    if (isset($_POST['update_flight_status_ajax'])) {
        $flight_id = $_POST['flight_id'];
        // Trim status before use to prevent any whitespace issues in the database update
        $status = trim($_POST['status']); 

        $stmt = $conn->prepare("UPDATE flights SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $flight_id);
        $success = $stmt->execute();
        $stmt->close();
        
        // Output a simple response for the AJAX call
        echo json_encode(['success' => $success, 'status' => $status]);
        $conn->close();
        exit(); // Crucial: Stop execution to prevent outputting the whole HTML page
    }


    // Delete Flight
    if (isset($_POST['delete_flight'])) {
        $flight_id = $_POST['flight_id'];
        $stmt = $conn->prepare("DELETE FROM flights WHERE id = ?");
        $stmt->bind_param("i", $flight_id);
        $stmt->execute();
        $stmt->close();
    }
    
    // Delete Passenger (FIXED FOREIGN KEY CONSTRAINT ISSUE)
    if (isset($_POST['delete_passenger'])) {
        $passenger_id = $_POST['passenger_id'];
        
        $conn->begin_transaction();
        try {
            // 1. Delete dependent feedback records
            $stmt_feedback = $conn->prepare("DELETE FROM feedback WHERE passenger_id = ?");
            $stmt_feedback->bind_param("i", $passenger_id);
            $stmt_feedback->execute();
            $stmt_feedback->close();
            
            // 2. Delete dependent reservation records (This should be adjusted if you want to handle seat count adjustment on passenger deletion)
            $stmt_reservations = $conn->prepare("DELETE FROM reservations WHERE passenger_id = ?");
            $stmt_reservations->bind_param("i", $passenger_id);
            $stmt_reservations->execute();
            $stmt_reservations->close();

            // 3. Delete the passenger record
            $stmt_passenger = $conn->prepare("DELETE FROM passengers WHERE id = ?");
            $stmt_passenger->bind_param("i", $passenger_id);
            $stmt_passenger->execute();
            $stmt_passenger->close();

            $conn->commit();
            // Optional: Set a success message
            // $message['success'] = "Passenger and associated records deleted.";

        } catch (mysqli_sql_exception $e) {
            $conn->rollback();
            // Optional: Set an error message
            // $message['error'] = "Failed to delete passenger: " . $e->getMessage();
        }
    }
    
    // Advanced: Delete Reservation and update seat counts
    if (isset($_POST['cancel_reservation'])) {
        $reservation_id = $_POST['reservation_id'];
        
        // 1. Get the reservation details before deleting
        $stmt = $conn->prepare("SELECT flight_id, seat_type FROM reservations WHERE id = ?");
        $stmt->bind_param("i", $reservation_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $reservation_details = $result->fetch_assoc();
        $stmt->close();
        
        if ($reservation_details) {
            $flight_id = $reservation_details['flight_id'];
            $seat_type = $reservation_details['seat_type'];
            
            // 2. Delete the reservation record
            $stmt = $conn->prepare("DELETE FROM reservations WHERE id = ?");
            $stmt->bind_param("i", $reservation_id);
            $stmt->execute();
            $stmt->close();
            
            // 3. Increment the available seat count for the specific flight
            $seat_column = '';
            if ($seat_type === 'economy') {
                $seat_column = 'available_economy_seats';
            } elseif ($seat_type === 'business') {
                $seat_column = 'available_business_seats';
            } elseif ($seat_type === 'first_class') {
                $seat_column = 'available_first_class_seats';
            }
            
            if ($seat_column) {
                $stmt = $conn->prepare("UPDATE flights SET {$seat_column} = {$seat_column} + 1 WHERE id = ?");
                $stmt->bind_param("i", $flight_id);
                $stmt->execute();
                $stmt->close();
            }
        }
    }

    // Advanced: Modify Reservation and update seat counts
    if (isset($_POST['modify_reservation'])) {
        $reservation_id = $_POST['reservation_id'];
        $new_seat_type = $_POST['new_seat_type'];

        // 1. Get old reservation details
        $stmt = $conn->prepare("SELECT flight_id, seat_type FROM reservations WHERE id = ?");
        $stmt->bind_param("i", $reservation_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $old_reservation = $result->fetch_assoc();
        $stmt->close();

        if ($old_reservation) {
            $flight_id = $old_reservation['flight_id'];
            $old_seat_type = $old_reservation['seat_type'];

            // 2. Update the reservation to the new seat type
            $stmt = $conn->prepare("UPDATE reservations SET seat_type = ? WHERE id = ?");
            $stmt->bind_param("si", $new_seat_type, $reservation_id);
            $stmt->execute();
            $stmt->close();

            // 3. Adjust seat counts on the flight
            // Decrement the old seat type
            $old_seat_column = '';
            if ($old_seat_type === 'economy') {
                $old_seat_column = 'available_economy_seats';
            } elseif ($old_seat_type === 'business') {
                $old_seat_column = 'available_business_seats';
            } elseif ($old_seat_type === 'first_class') {
                $old_seat_column = 'available_first_class_seats';
            }
            if ($old_seat_column) {
                $stmt = $conn->prepare("UPDATE flights SET {$old_seat_column} = {$old_seat_column} + 1 WHERE id = ?");
                $stmt->bind_param("i", $flight_id);
                $stmt->execute();
                $stmt->close();
            }

            // Increment the new seat type
            $new_seat_column = '';
            if ($new_seat_type === 'economy') {
                $new_seat_column = 'available_economy_seats';
            } elseif ($new_seat_type === 'business') {
                $new_seat_column = 'available_business_seats';
            } elseif ($new_seat_type === 'first_class') {
                $new_seat_column = 'available_first_class_seats';
            }
            if ($new_seat_column) {
                $stmt = $conn->prepare("UPDATE flights SET {$new_seat_column} = {$new_seat_column} - 1 WHERE id = ?");
                $stmt->bind_param("i", $flight_id);
                $stmt->execute();
                $stmt->close();
            }
        }
    }

    // Add New Admin Member
    if (isset($_POST['add_member'])) {
        $new_admin_username = $_POST['new_admin_username'];
        $new_admin_password = password_hash($_POST['new_admin_password'], PASSWORD_DEFAULT); // Hash the password
        
        $stmt = $conn->prepare("INSERT INTO admins (username, password) VALUES (?, ?)");
        $stmt->bind_param("ss", $new_admin_username, $new_admin_password);
        $stmt->execute();
        $stmt->close();
    }
    
    // Update Admin Profile
    if (isset($_POST['update_profile'])) {
        $admin_id = $_SESSION['admin_id']; // Get admin ID from session
        $first_name = $_POST['first_name'];
        $last_name = $_POST['last_name'];
        $email = $_POST['email'];

        $stmt = $conn->prepare("UPDATE admins SET first_name=?, last_name=?, email=? WHERE id=?");
        $stmt->bind_param("sssi", $first_name, $last_name, $email, $admin_id);
        $stmt->execute();
        $stmt->close();
    }
    
    // Change Admin Password
    if (isset($_POST['change_password'])) {
        $admin_id = $_SESSION['admin_id'];
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];

        // Get the current hashed password from the database
        $stmt = $conn->prepare("SELECT password FROM admins WHERE id = ?");
        $stmt->bind_param("i", $admin_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $admin_data = $result->fetch_assoc();
        $stmt->close();

        // Verify the current password
        if ($admin_data && password_verify($current_password, $admin_data['password'])) {
            // Hash and update the new password
            $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE admins SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $hashed_new_password, $admin_id);
            $stmt->execute();
            $stmt->close();
        } else {
            // You might want to log this or provide an error message in a real app
        }
    }

    $conn->close();
    // Redirect to prevent form resubmission on refresh
    // Only redirect if it's NOT an AJAX status update request
    if (!isset($_POST['update_flight_status_ajax'])) {
        header("Location: dashboard.php");
        exit();
    }
}

// --- PHP logic to fetch all data for display ---
$conn = getDbConnection();

// Fetch Dashboard Stats

// NEW: Available Flights (Scheduled, Delayed, Departed)
$available_flights_res = $conn->query("SELECT COUNT(*) AS total FROM flights WHERE status IN ('Scheduled', 'Delayed', 'Departed')");
$available_flights = $available_flights_res->fetch_assoc()['total'];

// Available Airlines
$total_airlines_res = $conn->query("SELECT COUNT(*) AS total FROM airlines");
$total_airlines = $total_airlines_res->fetch_assoc()['total'];

// Total Bookings
$total_bookings_res = $conn->query("SELECT COUNT(*) AS total FROM reservations");
$total_bookings = $total_bookings_res->fetch_assoc()['total'];


// Calculate Total Earnings (Total Revenue)
$total_revenue = 0;
$booked_seats_res = $conn->query("SELECT r.seat_type, COUNT(*) AS count, f.economy_price, f.business_price, f.first_class_price
FROM reservations r
JOIN flights f ON r.flight_id = f.id
GROUP BY r.seat_type, f.economy_price, f.business_price, f.first_class_price;");

while ($row = $booked_seats_res->fetch_assoc()) {
    // FIX: Added isset() checks to prevent 'Undefined array key' warnings
    $eco_price = $row['economy_price'] ?? 0;
    $bus_price = $row['business_price'] ?? 0;
    $first_price = $row['first_class_price'] ?? 0;
    
    $total_revenue += ($row['count'] * $eco_price);
    $total_revenue += ($row['count'] * $bus_price);
    $total_revenue += ($row['count'] * $first_price);
}


// Fetch All Flights
$flights_res = $conn->query("SELECT f.*, a.name AS airline_name FROM flights f JOIN airlines a ON f.airline_id = a.id ORDER BY departure_time DESC");
$all_flights = $flights_res->fetch_all(MYSQLI_ASSOC);

// Fetch All Passengers
$passengers_res = $conn->query("SELECT * FROM passengers");
$all_passengers = $passengers_res->fetch_all(MYSQLI_ASSOC);

// Fetch All Reservations
$reservations_res = $conn->query("SELECT r.*, p.first_name, p.last_name, f.flight_number FROM reservations r JOIN passengers p ON r.passenger_id = p.id JOIN flights f ON r.flight_id = f.id ORDER BY reservation_date DESC");
$all_reservations = $reservations_res->fetch_all(MYSQLI_ASSOC);

// Fetch data for the flights per airline pie chart (Ensures ALL airlines are fetched, count will be 0 if no flights)
$flights_per_airline_res = $conn->query("SELECT a.id, a.name AS airline_name, COUNT(f.id) AS flight_count FROM airlines a LEFT JOIN flights f ON a.id = f.airline_id GROUP BY a.id, a.name ORDER BY a.name ASC");
$flights_per_airline_data = $flights_per_airline_res->fetch_all(MYSQLI_ASSOC);

// For the Airlines table display, create a map for easy lookup
$airline_counts_map = [];
foreach ($flights_per_airline_data as $data) {
    $airline_counts_map[$data['id']] = $data['flight_count'];
}

// Fetch All Airlines (simple list for form dropdowns)
$airlines_res_simple = $conn->query("SELECT * FROM airlines ORDER BY name ASC");
$all_airlines = $airlines_res_simple->fetch_all(MYSQLI_ASSOC);

// Merge simple airlines data with flight counts for the management table
$airlines_for_table = [];
foreach ($all_airlines as $airline) {
    $airline['flight_count'] = $airline_counts_map[$airline['id']] ?? 0;
    $airlines_for_table[] = $airline;
}


// --- Fetch All Feedback Data ---
$all_feedback = []; 
$feedback_res = $conn->query("SELECT f.*, p.first_name, p.last_name FROM feedback f JOIN passengers p ON f.passenger_id = p.id ORDER BY feedback_date DESC");

if ($feedback_res) {
    $all_feedback = $feedback_res->fetch_all(MYSQLI_ASSOC);
}


// --- Fetch Flight Status Counts for Bar Chart ---
$flight_status_counts = [
    'not_departed' => 0, // Scheduled, Canceled
    'in_flight' => 0,    // Departed, Delayed
    'arrived' => 0       // Arrived
];

$status_res = $conn->query("SELECT status, COUNT(*) as count FROM flights GROUP BY status");

if ($status_res) {
    while ($row = $status_res->fetch_assoc()) {
        $status = $row['status'];
        $count = (int)$row['count'];
        
        if ($status === 'Scheduled' || $status === 'Canceled') {
            $flight_status_counts['not_departed'] += $count;
        } elseif ($status === 'Departed' || $status === 'Delayed') {
            $flight_status_counts['in_flight'] += $count;
        } elseif ($status === 'Arrived') {
            $flight_status_counts['arrived'] += $count;
        }
    }
}


// Fetch current admin's data for the profile page
$admin_profile_data = [
    'first_name' => 'Admin',
    'last_name' => 'User',
    'email' => 'admin@example.com'
];
if (isset($_SESSION['admin_id'])) {
    $stmt = $conn->prepare("SELECT first_name, last_name, email FROM admins WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['admin_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $fetched_data = $result->fetch_assoc();
    if ($fetched_data) {
        $admin_profile_data = $fetched_data;
    }
    $stmt->close();
}

$conn->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Airline Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary-color: #0d47a1;
            --secondary-color: #1976d2;
            --background-color: #f4f7f9;
            --card-bg-color: #ffffff;
            --text-color: #333333;
            --text-secondary-color: #666666;
            --border-color: #e0e0e0;
            --accent-color: #4CAF50;
            --danger-color: #f44336;
            --warning-color: #ff9800;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif;
        }

        body {
            background-color: var(--background-color);
            color: var(--text-color);
            display: flex;
            min-height: 100vh;
        }

        /* --- Sidebar Styles --- */
        .sidebar {
            width: 250px;
            background-color: #1a237e;
            color: #fff;
            padding: 20px;
            display: flex;
            flex-direction: column;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            position: fixed; /* Make the sidebar static */
            height: 100vh; /* Set height to full viewport height */
            top: 0;
            left: 0;
        }

        .sidebar-header {
            text-align: center;
            padding-bottom: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 20px;
        }

        .sidebar-header h2 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #fff;
        }

        .sidebar-nav {
            list-style: none;
        }

        .sidebar-nav-item {
            margin-bottom: 10px;
        }

        .sidebar-nav-link {
            display: flex;
            align-items: center;
            padding: 12px;
            border-radius: 8px;
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .sidebar-nav-link:hover, .sidebar-nav-link.active {
            background-color: rgba(255, 255, 255, 0.1);
            color: #fff;
            transform: translateX(5px);
        }

        .sidebar-nav-link .fas {
            margin-right: 15px;
            font-size: 1.2rem;
        }

        /* --- Main Content Styles --- */
        .main-content {
            flex-grow: 1;
            padding: 30px;
            display: flex;
            flex-direction: column;
            margin-left: 250px; /* Offset to account for fixed sidebar */
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--border-color);
            margin-bottom: 30px;
        }

        .header h1 {
            font-size: 2rem;
            font-weight: 700;
        }
        
        .header-actions {
            display: flex;
            gap: 10px;
        }

        .dashboard-section {
            display: none;
            animation: fadeIn 0.5s ease-in-out;
        }
        
        .dashboard-section.active {
            display: block;
        }

        .stats-grid {
            display: grid;
            /* Changed to 4 columns for fixed layout */
            grid-template-columns: repeat(4, 1fr); 
            gap: 20px;
            margin-bottom: 30px;
        }
        /* Mobile adjustment for stats grid */
        @media (max-width: 1024px) {
            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            }
        }

        .stat-card {
            background-color: var(--card-bg-color);
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
            gap: 20px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
        }

        .stat-icon {
            font-size: 2.5rem;
            color: var(--primary-color);
        }
        
        .stat-content h3 {
            font-size: 1rem;
            color: var(--text-secondary-color);
            margin-bottom: 5px;
        }
        
        .stat-content p {
            font-size: 2rem;
            font-weight: 700;
        }

        .chart-card {
            background-color: var(--card-bg-color);
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        }
        
        /* Ensure chart containers take up space properly */
        .chart-container {
            position: relative;
            height: 350px; /* Set a standard height for visual balance on the grid */
            width: 100%;
        }


        .chart-card h3 {
            margin-bottom: 20px;
        }
        
        /* --- Chart Grid Styles for 2 Charts (Flights/Month and Flights/Airline) --- */
        .chart-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr); /* Two columns for charts */
            gap: 20px;
            margin-bottom: 30px;
        }

        /* --- Bottom Chart Container Styles (REMOVED) --- */
        .bottom-chart-container {
            display: none; /* Removed the container for the Flight Status chart */
        }
        
        .bottom-chart-container .chart-card {
            width: 100%;
            max-width: 800px;
        }
        
        /* --- Form and Table Styles --- */
        .card {
            background-color: var(--card-bg-color);
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
        }
        
        .card h2 {
            font-size: 1.5rem;
            margin-bottom: 20px;
            border-bottom: 2px solid var(--border-color);
            padding-bottom: 10px;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 20px;
        }
        @media (min-width: 600px) {
            .form-grid {
                grid-template-columns: 1fr 1fr;
            }
        }
        @media (min-width: 900px) {
            .form-grid {
                grid-template-columns: 1fr 1fr 1fr;
            }
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            margin-bottom: 8px;
            font-weight: 600;
        }

        .form-group input, .form-group select {
            padding: 12px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            outline: none;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus, .form-group select:focus {
            border-color: var(--primary-color);
        }

        .btn {
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            color: #fff;
        }
        
        .btn-primary:hover {
            background-color: var(--secondary-color);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .btn-danger {
            background-color: #e53935;
            color: #fff;
        }

        .btn-danger:hover {
            background-color: #d32f2f;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            table-layout: fixed;
        }

        th, td {
            text-align: left;
            padding: 12px;
            border-bottom: 1px solid var(--border-color);
            word-wrap: break-word;
        }
        
        th {
            background-color: #f0f0f0;
            font-weight: 600;
        }
        
        tr:hover {
            background-color: #f9f9f9;
        }
        
        .table-actions .btn {
            font-size: 0.9rem;
            padding: 6px 10px;
        }
        
        /* Status Specific Styles */
        .status-dropdown {
            padding: 5px 8px;
            border-radius: 5px;
            font-weight: 600;
            border: 1px solid var(--border-color);
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            /* Default appearance */
            background-color: #ffffff;
            color: var(--text-color);
        }
        /* Dynamic classes for status colorization */
        .status-Scheduled { background-color: #e3f2fd; color: #1565c0; border-color: #1565c0; }
        .status-Departed { background-color: #fff3e0; color: #f57f17; border-color: #f57f17; }
        .status-Delayed { background-color: #fff8e1; color: #ff6f00; border-color: #ff6f00; }
        .status-Canceled { background-color: #ffebee; color: #d32f2f; border-color: #d32f2f; }
        .status-Arrived { background-color: #e8f5e9; color: #2e7d32; border-color: #2e7d32; }


        /* --- Search Input Styling (NEW) --- */
        .search-container {
            position: relative;
            margin-bottom: 15px;
            width: 100%;
            max-width: 400px;
        }

        .search-container .fas {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-secondary-color);
            font-size: 1rem;
        }

        .search-input {
            width: 100%;
            padding: 12px 12px 12px 40px; /* Adjust padding for icon */
            border: 1px solid var(--border-color);
            border-radius: 8px;
            outline: none;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        .search-input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(13, 71, 161, 0.2);
        }
        
        /* --- Animations --- */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* --- Responsive Design --- */
        @media (max-width: 1024px) {
            .sidebar {
                width: 200px;
            }
            .main-content {
                margin-left: 200px;
            }
            .stats-grid {
                grid-template-columns: 1fr 1fr;
            }
            .chart-grid {
                grid-template-columns: 1fr;
            }
            .chart-container {
                height: 300px;
            }
        }

        @media (max-width: 768px) {
            body {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                height: auto;
                flex-direction: row;
                justify-content: space-around;
                padding: 10px;
                position: static;
            }

            .sidebar-header {
                display: none;
            }
            
            .sidebar-nav {
                display: flex;
                flex-direction: row;
                justify-content: center;
                gap: 5px;
            }
            
            .sidebar-nav-link {
                flex-direction: column;
                padding: 8px;
            }

            .sidebar-nav-link .fas {
                margin-right: 0;
                margin-bottom: 5px;
            }

            .main-content {
                padding: 20px;
                margin-left: 0;
            }

            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            .stats-grid {
                grid-template-columns: 1fr;
            }
            .data-table th, .data-table td {
                padding: 10px;
            }
            .footer {
                margin-left: 0;
            }
        }

        /* --- Modal Styles --- */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .modal-content {
            background-color: var(--card-bg-color);
            padding: 30px;
            border-radius: 12px;
            width: 90%;
            max-width: 600px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
            position: relative;
            animation: fadeIn 0.3s ease-in-out;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-close-btn {
            position: absolute;
            top: 15px;
            right: 15px;
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--text-secondary-color);
            cursor: pointer;
            transition: color 0.3s ease;
        }

        .modal-close-btn:hover {
            color: var(--text-color);
        }

        .modal-content h2 {
            margin-bottom: 20px;
        }

        .modal-content .form-grid {
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        }

        .progress-bar-container {
            width: 100%;
            background-color: #e0e0e0;
            border-radius: 8px;
            overflow: hidden;
            margin-top: 5px;
        }

        .progress {
            height: 20px;
            background-color: var(--primary-color);
            width: 0;
            transition: width 0.5s ease-in-out;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .footer {
            margin-top: auto; /* Push footer to the bottom */
            padding-top: 20px;
            border-top: 1px solid var(--border-color);
            text-align: center;
            font-size: 0.9rem;
            color: var(--text-secondary-color);
        }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-header">
        <h2>Admin Panel</h2>
    </div>
    <ul class="sidebar-nav">
        <li class="sidebar-nav-item"><a href="#dashboard" class="sidebar-nav-link active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
        <li class="sidebar-nav-item"><a href="#flights" class="sidebar-nav-link"><i class="fas fa-plane"></i> Flights</a></li>
        <li class="sidebar-nav-item"><a href="#passengers" class="sidebar-nav-link"><i class="fas fa-users"></i> Passengers</a></li>
        <li class="sidebar-nav-item"><a href="#reservations" class="sidebar-nav-link"><i class="fas fa-ticket-alt"></i> Reservations</a></li>
        <li class="sidebar-nav-item"><a href="#airlines" class="sidebar-nav-link"><i class="fas fa-building"></i> Airlines</a></li>
        <li class="sidebar-nav-item"><a href="#feedback" class="sidebar-nav-link"><i class="fas fa-comments"></i> Feedback</a></li>
        <li class="sidebar-nav-item"><a href="#members" class="sidebar-nav-link"><i class="fas fa-user-plus"></i> Add Members</a></li>
        <li class="sidebar-nav-item"><a href="#profile" class="sidebar-nav-link"><i class="fas fa-user-circle"></i> Profile</a></li>
        <li class="sidebar-nav-item"><a href="login.html" class="sidebar-nav-link"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
    </ul>
</div>

<div class="main-content">
    <div class="header">
        <h1>Dashboard Overview</h1>
        <div class="header-actions">
            <button class="btn btn-primary" onclick="window.location.reload();">
                <i class="fas fa-sync-alt"></i> Refresh Data
            </button>
        </div>
    </div>

    <!-- Dashboard Section -->
    <div id="dashboard" class="dashboard-section active">
        <!-- SIMPLIFIED STATS GRID (4 BOXES) -->
        <div class="stats-grid">
            <div class="stat-card">
                <i class="fas fa-plane-up stat-icon"></i>
                <div class="stat-content">
                    <h3>Available Flights</h3>
                    <p><?php echo $available_flights; ?></p>
                </div>
            </div>
            <div class="stat-card">
                <i class="fas fa-building stat-icon"></i>
                <div class="stat-content">
                    <h3>Available Airlines</h3>
                    <p><?php echo $total_airlines; ?></p>
                </div>
            </div>
            <div class="stat-card">
                <i class="fas fa-ticket-alt stat-icon"></i>
                <div class="stat-content">
                    <h3>Total Bookings</h3>
                    <p><?php echo $total_bookings; ?></p>
                </div>
            </div>
            <div class="stat-card">
                <i class="fas fa-rupee-sign stat-icon"></i>
                <div class="stat-content">
                    <h3>Total Earnings</h3>
                    <p>₹<?php echo number_format($total_revenue, 2, '.', ','); ?></p>
                </div>
            </div>
        </div>
        
        <div class="chart-grid">
            <div class="chart-card">
                <h3>Flights per Month</h3>
                <div class="chart-container">
                    <canvas id="flightsChart"></canvas>
                </div>
            </div>
            
            <div class="chart-card">
                <h3>Flights per Airline</h3>
                <div class="chart-container">
                    <canvas id="flightsPerAirlineChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Flight Status Chart (Removed) -->
        <div class="bottom-chart-container">
        </div>
    </div>
    
    <!-- Flights Section -->
    <div id="flights" class="dashboard-section">
        <div class="card">
            <h2>Add New Flight</h2>
            <form action="dashboard.php" method="POST" class="form-grid">
                <input type="hidden" name="add_flight" value="1">
                <div class="form-group">
                    <label for="flight_number">Flight Number</label>
                    <input type="text" id="flight_number" name="flight_number" required>
                </div>
                <div class="form-group">
                    <label for="airline_id">Airline</label>
                    <select id="airline_id" name="airline_id" required>
                        <?php foreach ($all_airlines as $airline) { ?>
                            <option value="<?php echo htmlspecialchars($airline['id']); ?>"><?php echo htmlspecialchars($airline['name']); ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="origin">Origin</label>
                    <input type="text" id="origin" name="origin" required>
                </div>
                <div class="form-group">
                    <label for="destination">Destination</label>
                    <input type="text" id="destination" name="destination" required>
                </div>
                <div class="form-group">
                    <label for="departure_time">Departure Time</label>
                    <input type="datetime-local" id="departure_time" name="departure_time" required>
                </div>
                <div class="form-group">
                    <label for="arrival_time">Arrival Time</label>
                    <input type="datetime-local" id="arrival_time" name="arrival_time" required>
                </div>
                <div class="form-group">
                    <label for="economy_seats">Economy Seats</label>
                    <input type="number" id="economy_seats" name="economy_seats" min="0" required>
                </div>
                <div class="form-group">
                    <label for="business_seats">Business Seats</label>
                    <input type="number" id="business_seats" name="business_seats" min="0" required>
                </div>
                <div class="form-group">
                    <label for="first_class_seats">First-Class Seats</label>
                    <input type="number" id="first_class_seats" name="first_class_seats" min="0" required>
                </div>
                <div class="form-group">
                    <label for="economy_price">Economy Price (₹)</label>
                    <input type="number" id="economy_price" name="economy_price" min="0" step="0.01" required>
                </div>
                <div class="form-group">
                    <label for="business_price">Business Price (₹)</label>
                    <input type="number" id="business_price" name="business_price" min="0" step="0.01" required>
                </div>
                <div class="form-group">
                    <label for="first_class_price">First-Class Price (₹)</label>
                    <input type="number" id="first_class_price" name="first_class_price" min="0" step="0.01" required>
                </div>
                <button type="submit" class="btn btn-primary">Add Flight</button>
            </form>
        </div>
        
        <div class="card">
            <h2>View All Flights</h2>
            <div class="search-container">
                <i class="fas fa-search"></i>
                <input type="text" class="search-input" placeholder="Search flights...">
            </div>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Flight No.</th>
                        <th>Airline</th>
                        <th>Origin</th>
                        <th>Destination</th>
                        <th>Departure</th>
                        <th>Status</th> <!-- NEW COLUMN -->
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($all_flights as $flight) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($flight['id']); ?></td>
                            <td><?php echo htmlspecialchars($flight['flight_number']); ?></td>
                            <td><?php echo htmlspecialchars($flight['airline_name']); ?></td>
                            <td><?php echo htmlspecialchars($flight['origin']); ?></td>
                            <td><?php echo htmlspecialchars($flight['destination']); ?></td>
                            <td><?php echo htmlspecialchars(date('Y-m-d H:i', strtotime($flight['departure_time']))); ?></td>
                            <td class="status-cell">
                                <!-- Removed <form> tag to enable AJAX updates -->
                                <select 
                                    name="status" 
                                    class="status-dropdown status-<?php echo htmlspecialchars(trim($flight['status'])); ?>" 
                                    data-flight-id="<?php echo htmlspecialchars($flight['id']); ?>"
                                    onchange="updateFlightStatus(this)"
                                >
                                    <option value="Scheduled" <?php echo trim($flight['status']) == 'Scheduled' ? 'selected' : ''; ?>>Scheduled</option>
                                    <option value="Departed" <?php echo trim($flight['status']) == 'Departed' ? 'selected' : ''; ?>>Departed</option>
                                    <option value="Delayed" <?php echo trim($flight['status']) == 'Delayed' ? 'selected' : ''; ?>>Delayed</option>
                                    <option value="Canceled" <?php echo trim($flight['status']) == 'Canceled' ? 'selected' : ''; ?>>Canceled</option>
                                    <option value="Arrived" <?php echo trim($flight['status']) == 'Arrived' ? 'selected' : ''; ?>>Arrived</option>
                                </select>
                            </td>
                            <td class="table-actions">
                                <button class="btn btn-primary edit-btn"
                                    data-id="<?php echo htmlspecialchars($flight['id']); ?>"
                                    data-flight_number="<?php echo htmlspecialchars($flight['flight_number']); ?>"
                                    data-airline_id="<?php echo htmlspecialchars($flight['airline_id']); ?>"
                                    data-origin="<?php echo htmlspecialchars($flight['origin']); ?>"
                                    data-destination="<?php echo htmlspecialchars($flight['destination']); ?>"
                                    data-departure_time="<?php echo htmlspecialchars($flight['departure_time']); ?>"
                                    data-arrival_time="<?php echo htmlspecialchars($flight['arrival_time']); ?>"
                                    data-total_economy="<?php echo htmlspecialchars($flight['economy_seats']); ?>"
                                    data-total_business="<?php echo htmlspecialchars($flight['business_seats']); ?>"
                                    data-total_first="<?php echo htmlspecialchars($flight['first_class_seats']); ?>"
                                    data-available_economy="<?php echo htmlspecialchars($flight['available_economy_seats']); ?>"
                                    data-available_business="<?php echo htmlspecialchars($flight['available_business_seats']); ?>"
                                    data-available_first="<?php echo htmlspecialchars($flight['available_first_class_seats']); ?>"
                                >Edit</button>
                                <form action="dashboard.php" method="POST" style="display:inline-block;">
                                    <input type="hidden" name="delete_flight" value="1">
                                    <input type="hidden" name="flight_id" value="<?php echo htmlspecialchars($flight['id']); ?>">
                                    <button type="submit" class="btn btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Passengers Section -->
    <div id="passengers" class="dashboard-section">
        <div class="card">
            <h2>Manage Passengers</h2>
            <div class="search-container">
                <i class="fas fa-search"></i>
                <input type="text" class="search-input" placeholder="Search passengers...">
            </div>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($all_passengers as $passenger) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($passenger['id']); ?></td>
                            <td><?php echo htmlspecialchars($passenger['first_name'] . ' ' . $passenger['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($passenger['email']); ?></td>
                            <td><?php echo htmlspecialchars($passenger['phone']); ?></td>
                            <td class="table-actions">
                                <button class="btn btn-primary view-passenger-btn"
                                    data-id="<?php echo htmlspecialchars($passenger['id']); ?>"
                                    data-first_name="<?php echo htmlspecialchars($passenger['first_name']); ?>"
                                    data-last_name="<?php echo htmlspecialchars($passenger['last_name']); ?>"
                                    data-email="<?php echo htmlspecialchars($passenger['email']); ?>"
                                    data-phone="<?php echo htmlspecialchars($passenger['phone']); ?>"
                                >View</button>
                                <form action="dashboard.php" method="POST" style="display:inline-block;">
                                    <input type="hidden" name="delete_passenger" value="1">
                                    <input type="hidden" name="passenger_id" value="<?php echo htmlspecialchars($passenger['id']); ?>">
                                    <button type="submit" class="btn btn-danger">Remove</button>
                                </form>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Reservations Section -->
    <div id="reservations" class="dashboard-section">
        <div class="card">
            <h2>Manage Reservations</h2>
            <div class="search-container">
                <i class="fas fa-search"></i>
                <input type="text" class="search-input" placeholder="Search reservations...">
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Reservation ID</th>
                        <th>Passenger Name</th>
                        <th>Flight Number</th>
                        <th>Seat Type</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($all_reservations as $reservation) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($reservation['id']); ?></td>
                            <td><?php echo htmlspecialchars($reservation['first_name'] . ' ' . $reservation['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($reservation['flight_number']); ?></td>
                            <td><?php echo htmlspecialchars($reservation['seat_type']); ?></td>
                            <td><?php echo htmlspecialchars($reservation['reservation_status']); ?></td>
                            <td class="table-actions">
                                <button class="btn btn-primary view-reservation-btn"
                                    data-id="<?php echo htmlspecialchars($reservation['id']); ?>"
                                    data-passenger_name="<?php echo htmlspecialchars($reservation['first_name'] . ' ' . $reservation['last_name']); ?>"
                                    data-flight_number="<?php echo htmlspecialchars($reservation['flight_number']); ?>"
                                    data-seat_type="<?php echo htmlspecialchars($reservation['seat_type']); ?>"
                                    data-status="<?php echo htmlspecialchars($reservation['reservation_status']); ?>"
                                    data-date="<?php echo htmlspecialchars($reservation['reservation_date']); ?>"
                                >View</button>
                                <button class="btn btn-primary modify-reservation-btn"
                                    data-id="<?php echo htmlspecialchars($reservation['id']); ?>"
                                    data-seat_type="<?php echo htmlspecialchars($reservation['seat_type']); ?>"
                                >Modify</button>
                                <form action="dashboard.php" method="POST" style="display:inline-block;">
                                    <input type="hidden" name="cancel_reservation" value="1">
                                    <input type="hidden" name="reservation_id" value="<?php echo htmlspecialchars($reservation['id']); ?>">
                                    <button type="submit" class="btn btn-danger">Cancel</button>
                                </form>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Airlines Section -->
    <div id="airlines" class="dashboard-section">
        <div class="card">
            <h2>Add New Airline</h2>
            <form action="dashboard.php" method="POST" class="form-grid">
                <input type="hidden" name="add_airline" value="1">
                <div class="form-group">
                    <label for="airline_name">Airline Name</label>
                    <input type="text" id="airline_name" name="airline_name" required>
                </div>
                <button type="submit" class="btn btn-primary">Add Airline</button>
            </form>
        </div>
        <div class="card">
            <h2>View and Delete Airlines</h2>
            <!-- New Search Input for Airlines -->
            <div class="search-container">
                <i class="fas fa-search"></i>
                <input type="text" class="search-input" placeholder="Search airlines..." data-target-table="airlines-table">
            </div>
            <table id="airlines-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Airline Name</th>
                        <th>NO. OF FLIGHTS</th> <!-- New Column Header -->
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($airlines_for_table as $airline) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($airline['id']); ?></td>
                            <td><?php echo htmlspecialchars($airline['name']); ?></td>
                            <td><?php echo htmlspecialchars($airline['flight_count']); ?></td> <!-- Display Flight Count -->
                            <td class="table-actions">
                                <!-- Add Flight Button (client-side navigation) -->
                                <button class="btn btn-primary add-flight-btn" data-airline-id="<?php echo htmlspecialchars($airline['id']); ?>" data-airline-name="<?php echo htmlspecialchars($airline['name']); ?>">
                                    Add Flight
                                </button>
                                <form action="dashboard.php" method="POST" style="display:inline-block;">
                                    <input type="hidden" name="delete_airline" value="1">
                                    <input type="hidden" name="airline_id" value="<?php echo htmlspecialchars($airline['id']); ?>">
                                    <button type="submit" class="btn btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Feedback Section -->
    <div id="feedback" class="dashboard-section">
        <div class="card">
            <h2>Passenger Feedback</h2>
            <div class="search-container">
                <i class="fas fa-search"></i>
                <input type="text" class="search-input" placeholder="Search feedback...">
            </div>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Passenger</th>
                        <th>Rating</th>
                        <th>Comments</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($all_feedback as $feedback) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($feedback['id']); ?></td>
                            <td><?php echo htmlspecialchars($feedback['first_name'] . ' ' . $feedback['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($feedback['rating']); ?>/5</td>
                            <td><?php echo htmlspecialchars($feedback['comments']); ?></td>
                            <td><?php echo htmlspecialchars($feedback['feedback_date']); ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Members Section -->
    <div id="members" class="dashboard-section">
        <div class="card">
            <h2>Add New Admin Member</h2>
            <form action="dashboard.php" method="POST" class="form-grid">
                <input type="hidden" name="add_member" value="1">
                <div class="form-group">
                    <label for="new_admin_username">Username</label>
                    <input type="text" id="new_admin_username" name="new_admin_username" required>
                </div>
                <div class="form-group">
                    <label for="new_admin_password">Password</label>
                    <input type="password" id="new_admin_password" name="new_admin_password" required>
                </div>
                <button type="submit" class="btn btn-primary">Add Member</button>
            </form>
        </div>
    </div>

    <!-- Profile Section -->
    <div id="profile" class="dashboard-section">
        <div class="card">
            <h2>Update Profile Details</h2>
            <form action="dashboard.php" method="POST" class="form-grid">
                <input type="hidden" name="update_profile" value="1">
                <div class="form-group">
                    <label for="first_name">First Name</label>
                    <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($admin_profile_data['first_name']); ?>">
                </div>
                <div class="form-group">
                    <label for="last_name">Last Name</label>
                    <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($admin_profile_data['last_name']); ?>">
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($admin_profile_data['email']); ?>">
                </div>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </form>
        </div>
        <div class="card">
            <h2>Change Password</h2>
            <form action="dashboard.php" method="POST" class="form-grid">
                <input type="hidden" name="change_password" value="1">
                <div class="form-group">
                    <label for="current_password">Current Password</label>
                    <input type="password" id="current_password" name="current_password" required>
                </div>
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password" required>
                </div>
                <button type="submit" class="btn btn-primary">Change Password</button>
            </form>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; <?php echo date("Y"); ?> Airline Reservation System. All Rights Reserved.</p>
    </footer>
</div>

<!-- Edit Flight Modal -->
<div id="editModal" class="modal-overlay">
    <div class="modal-content">
        <button class="modal-close-btn">&times;</button>
        <h2>View/Edit Flight</h2>
        <form action="dashboard.php" method="POST" class="form-grid">
            <input type="hidden" name="edit_flight" value="1">
            <input type="hidden" id="edit-flight-id" name="flight_id">
            <div class="form-group">
                <label for="edit-flight-number">Flight Number</label>
                <input type="text" id="edit-flight-number" name="flight_number" required>
            </div>
            <div class="form-group">
                <label for="edit-airline-id">Airline</label>
                <select id="edit-airline-id" name="airline_id" required>
                    <?php foreach ($all_airlines as $airline) { ?>
                        <option value="<?php echo htmlspecialchars($airline['id']); ?>"><?php echo htmlspecialchars($airline['name']); ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="form-group">
                <label for="edit-origin">Origin</label>
                <input type="text" id="edit-origin" name="origin" required>
            </div>
            <div class="form-group">
                <label for="edit-destination">Destination</label>
                <input type="text" id="edit-destination" name="destination" required>
            </div>
            <div class="form-group">
                <label for="edit-departure-time">Departure Time</label>
                <input type="datetime-local" id="edit-departure-time" name="departure_time" required>
            </div>
            <div class="form-group">
                <label for="edit-arrival-time">Arrival Time</label>
                <input type="datetime-local" id="edit-arrival-time" name="arrival_time" required>
            </div>
            
            <div class="form-group">
                <h3>Seat Availability</h3>
                <p>Economy (<span id="avail-eco-text"></span>/<span id="total-eco-text"></span>)</p>
                <div class="progress-bar-container"><div class="progress" id="progress-eco"></div></div>
                <p class="mt-4">Business (<span id="avail-bus-text"></span>/<span id="total-bus-text"></span>)</p>
                <div class="progress-bar-container"><div class="progress" id="progress-bus"></div></div>
                <p class="mt-4">First-Class (<span id="avail-first-text"></span>/<span id="total-first-text"></span>)</p>
                <div class="progress-bar-container"><div class="progress" id="progress-first"></div></div>
            </div>
            <button type="submit" class="btn btn-primary">Save Changes</button>
        </form>
    </div>
</div>

<!-- Passenger View Modal -->
<div id="passengerModal" class="modal-overlay">
    <div class="modal-content">
        <button class="modal-close-btn">&times;</button>
        <h2>Passenger Details</h2>
        <div class="details-grid">
            <p><strong>Passenger ID:</strong> <span id="passenger-id"></span></p>
            <p><strong>Name:</strong> <span id="passenger-name"></span></p>
            <p><strong>Email:</strong> <span id="passenger-email"></span></p>
            <p><strong>Phone:</strong> <span id="passenger-phone"></span></p>
        </div>
    </div>
</div>

<!-- Reservation View Modal -->
<div id="reservationModal" class="modal-overlay">
    <div class="modal-content">
        <button class="modal-close-btn">&times;</button>
        <h2>Reservation Details</h2>
        <div class="details-grid">
            <p><strong>Reservation ID:</strong> <span id="reservation-id"></span></p>
            <p><strong>Passenger Name:</strong> <span id="reservation-passenger-name"></span></p>
            <p><strong>Flight Number:</strong> <span id="reservation-flight-number"></span></p>
            <p><strong>Seat Type:</strong> <span id="reservation-seat-type"></span></p>
            <p><strong>Status:</strong> <span id="reservation-status"></span></p>
            <p><strong>Reservation Date:</strong> <span id="reservation-date"></span></p>
        </div>
    </div>
</div>

<!-- Modify Reservation Modal -->
<div id="modifyReservationModal" class="modal-overlay">
    <div class="modal-content">
        <button class="modal-close-btn">&times;</button>
        <h2>Modify Reservation</h2>
        <form action="dashboard.php" method="POST">
            <input type="hidden" name="modify_reservation" value="1">
            <input type="hidden" id="modify-reservation-id" name="reservation_id">
            <div class="form-group">
                <label for="modify-seat-type">Change Seat Type</label>
                <select id="modify-seat-type" name="new_seat_type">
                    <option value="economy">Economy</option>
                    <option value="business">Business</option>
                    <option value="first_class">First-Class</option>
                </select>
            </div>
            <p>Changing the seat type will automatically update seat counts.</p>
            <button type="submit" class="btn btn-primary">Save Changes</button>
        </form>
    </div>
</div>

<script>
    // Utility function for setting status colors
    function setStatusColor(selectElement, statusValue) {
        // Ensure statusValue is trimmed and converted to a safe class name
        const safeStatus = statusValue.trim();

        // Remove all status classes first
        selectElement.className = 'status-dropdown';
        
        // Add the new status class
        selectElement.classList.add('status-' + safeStatus);
        
        // Set the value 
        selectElement.value = safeStatus;
    }

    // New AJAX function to update status without full page reload
    function updateFlightStatus(selectElement) {
        const flightId = selectElement.getAttribute('data-flight-id');
        const newStatus = selectElement.value;
        const currentUrl = window.location.href;

        // 1. Immediately update the visual color on the client side
        setStatusColor(selectElement, newStatus);
        
        // 2. Prepare the data for the server
        const formData = new URLSearchParams();
        formData.append('update_flight_status_ajax', '1'); // New flag for AJAX handler
        formData.append('flight_id', flightId);
        formData.append('status', newStatus);

        // 3. Send AJAX request
        fetch('dashboard.php', {
            method: 'POST',
            body: formData,
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log(`Flight ${flightId} status updated to ${data.status} successfully.`);
                // We rely on the setStatusColor call (step 1) for immediate visual feedback.
                // The next full page load/refresh will pull the corrected data.
            } else {
                console.error('Failed to update status on server.');
                // Handle failure (e.g., revert color, show error message)
            }
        })
        .catch(error => {
            console.error('Network error during status update:', error);
            alert('Failed to update status due to network error.');
            // Revert status on critical failure
        });
    }
    window.updateFlightStatus = updateFlightStatus; // Make function globally accessible

    document.addEventListener('DOMContentLoaded', () => {
        // --- Initialize all existing status dropdowns with correct color on load ---
        document.querySelectorAll('.status-dropdown').forEach(select => {
            // Trim the status value before passing it to setStatusColor 
            // to eliminate any potential leading/trailing whitespace from PHP output.
            const initialStatus = select.value.trim();
            setStatusColor(select, initialStatus);
        });

        // --- Navigation and Section Toggling ---
        const navLinks = document.querySelectorAll('.sidebar-nav-link');
        const sections = document.querySelectorAll('.dashboard-section');

        navLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                
                const targetId = e.currentTarget.getAttribute('href').substring(1);

                navLinks.forEach(item => item.classList.remove('active'));
                sections.forEach(item => item.classList.remove('active'));
                
                e.currentTarget.classList.add('active');
                document.getElementById(targetId).classList.add('active');

                const headerTitle = document.querySelector('.header h1');
                const titleText = e.currentTarget.textContent.trim();
                headerTitle.textContent = (titleText === 'Dashboard') ? 'Dashboard Overview' : titleText + ' Management';
            });
        });

        // --- Handle Edit Button Clicks for Flights (Modal) ---
        const editButtons = document.querySelectorAll('.edit-btn');
        const editModal = document.getElementById('editModal');
        const modalCloseBtns = document.querySelectorAll('.modal-close-btn');

        editButtons.forEach(button => {
            button.addEventListener('click', () => {
                const data = button.dataset;
                
                // Populate the modal form fields
                document.getElementById('edit-flight-id').value = data.id;
                document.getElementById('edit-flight-number').value = data.flight_number;
                document.getElementById('edit-airline-id').value = data.airline_id;
                document.getElementById('edit-origin').value = data.origin;
                document.getElementById('edit-destination').value = data.destination;

                // Format datetime-local for input field
                function formatDate(dateTimeStr) {
                    if (!dateTimeStr) return '';
                    const dt = new Date(dateTimeStr);
                    const y = dt.getFullYear();
                    const m = String(dt.getMonth() + 1).padStart(2, '0');
                    const d = String(dt.getDate()).padStart(2, '0');
                    const h = String(dt.getHours()).padStart(2, '0');
                    const min = String(dt.getMinutes()).padStart(2, '0');
                    return `${y}-${m}-${d}T${h}:${min}`;
                }
                document.getElementById('edit-departure-time').value = formatDate(data.departure_time);
                document.getElementById('edit-arrival-time').value = formatDate(data.arrival_time);
                
                // Populate and animate seat availability
                const totalEconomy = parseInt(data.total_economy);
                const availableEconomy = parseInt(data.available_economy);
                const totalBusiness = parseInt(data.total_business);
                const availableBusiness = parseInt(data.available_business);
                const totalFirst = parseInt(data.total_first);
                const availableFirst = parseInt(data.available_first);

                document.getElementById('avail-eco-text').textContent = availableEconomy;
                document.getElementById('total-eco-text').textContent = totalEconomy;
                document.getElementById('progress-eco').style.width = ((totalEconomy - availableEconomy) / totalEconomy * 100) + '%';
                
                document.getElementById('avail-bus-text').textContent = availableBusiness;
                document.getElementById('total-bus-text').textContent = totalBusiness;
                document.getElementById('progress-bus').style.width = ((totalBusiness - availableBusiness) / totalBusiness * 100) + '%';
                
                document.getElementById('avail-first-text').textContent = availableFirst;
                document.getElementById('total-first-text').textContent = totalFirst;
                document.getElementById('progress-first').style.width = ((totalFirst - availableFirst) / totalFirst * 100) + '%';
                
                editModal.style.display = 'flex';
            });
        });

        // --- Handle View Button Clicks for Passengers ---
        const viewPassengerButtons = document.querySelectorAll('.view-passenger-btn');
        const passengerModal = document.getElementById('passengerModal');

        viewPassengerButtons.forEach(button => {
            button.addEventListener('click', () => {
                const data = button.dataset;

                document.getElementById('passenger-id').textContent = data.id;
                document.getElementById('passenger-name').textContent = data.first_name + ' ' + data.last_name;
                document.getElementById('passenger-email').textContent = data.email;
                document.getElementById('passenger-phone').textContent = data.phone;

                passengerModal.style.display = 'flex';
            });
        });

        // --- Handle View Button Clicks for Reservations ---
        const viewReservationButtons = document.querySelectorAll('.view-reservation-btn');
        const reservationModal = document.getElementById('reservationModal');
        
        viewReservationButtons.forEach(button => {
            button.addEventListener('click', () => {
                const data = button.dataset;

                document.getElementById('reservation-id').textContent = data.id;
                document.getElementById('reservation-passenger-name').textContent = data.passenger_name;
                document.getElementById('reservation-flight-number').textContent = data.flight_number;
                document.getElementById('reservation-seat-type').textContent = data.seat_type;
                document.getElementById('reservation-status').textContent = data.status;
                document.getElementById('reservation-date').textContent = new Date(data.date).toLocaleString();

                reservationModal.style.display = 'flex';
            });
        });

        // --- Handle Modify Reservation Button Clicks ---
        const modifyReservationButtons = document.querySelectorAll('.modify-reservation-btn');
        const modifyReservationModal = document.getElementById('modifyReservationModal');

        modifyReservationButtons.forEach(button => {
            button.addEventListener('click', () => {
                const data = button.dataset;
                
                document.getElementById('modify-reservation-id').value = data.id;
                document.getElementById('modify-seat-type').value = data.seat_type;
                
                modifyReservationModal.style.display = 'flex';
            });
        });

        // --- Close Modals ---
        modalCloseBtns.forEach(button => {
            button.addEventListener('click', () => {
                button.closest('.modal-overlay').style.display = 'none';
            });
        });

        window.addEventListener('click', (e) => {
            if (e.target.classList.contains('modal-overlay')) {
                e.target.style.display = 'none';
            }
        });

        // --- Table Search Functionality ---
        document.querySelectorAll('.search-input').forEach(input => {
            input.addEventListener('keyup', (e) => {
                const searchTerm = e.target.value.toLowerCase();
                // Determine the target table based on the data attribute, or assume parent's next sibling
                const parent = e.target.closest('.search-container');
                const table = parent.nextElementSibling;
                
                if (!table || table.tagName !== 'TABLE') return;

                const rows = table.querySelectorAll('tbody tr');
                
                rows.forEach(row => {
                    const rowText = row.textContent.toLowerCase();
                    if (rowText.includes(searchTerm)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        });
        
        // --- Custom Logic: Add Flight Button Navigation ---
        document.querySelectorAll('.add-flight-btn').forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                const airlineId = button.getAttribute('data-airline-id');

                // 1. Simulate a click on the Flights sidebar link to switch tab
                const flightsLink = document.querySelector('.sidebar-nav-link[href="#flights"]');
                if (flightsLink) {
                    flightsLink.click();
                }

                // 2. Automatically select the airline in the "Add New Flight" form
                const airlineSelect = document.getElementById('airline_id');
                if (airlineSelect) {
                    airlineSelect.value = airlineId;
                }
            });
        });


        // --- Chart.js Initialization ---
        // Flights per Month
        const ctxFlights = document.getElementById('flightsChart');
        if (ctxFlights) {
            const php_flight_data = <?php 
                $conn_chart = getDbConnection();
                $chart_data_res = $conn_chart->query("SELECT DATE_FORMAT(departure_time, '%Y-%m') AS month, COUNT(*) AS flight_count FROM flights GROUP BY month ORDER BY month");
                $chart_data = $chart_data_res->fetch_all(MYSQLI_ASSOC);
                $conn_chart->close();
                echo json_encode($chart_data);
            ?>;
            
            const labels = php_flight_data.map(row => row.month);
            const data = php_flight_data.map(row => row.flight_count);

            new Chart(ctxFlights, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Flights Scheduled',
                        data: data,
                        backgroundColor: '#1976d2',
                        borderColor: '#0d47a1',
                        borderWidth: 1,
                        borderRadius: 5
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        }
        
        // Flights per Airline Pie Chart
        const ctxPie = document.getElementById('flightsPerAirlineChart');
        if (ctxPie) {
            const php_pie_data = <?php echo json_encode($flights_per_airline_data); ?>;
            const pie_labels = php_pie_data.map(row => row.airline_name);
            const pie_data = php_pie_data.map(row => row.flight_count);

            new Chart(ctxPie, {
                type: 'pie',
                data: {
                    labels: pie_labels,
                    datasets: [{
                        data: pie_data,
                        backgroundColor: [
                            '#0d47a1', '#1565c0', '#1976d2', '#2196f3', '#42a5f5', '#64b5f6', '#90caf9', '#bbdefb'
                        ],
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    // Maintain aspect ratio true for circular charts, but we use a fixed container height
                    maintainAspectRatio: false, 
                    plugins: {
                        legend: {
                            position: 'right', // Place legend on the right for better space usage
                        },
                        title: {
                            display: true,
                            text: 'Number of Flights by Airline'
                        }
                    }
                }
            });
        }
        
        // REMOVED: Flight Status Bar Chart initialization logic (was here)

    });

</script>

</body>
</html>




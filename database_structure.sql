-- 1. Create the database if it doesn't exist
CREATE DATABASE IF NOT EXISTS airline12 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE airline12;

-- Drop tables if they exist (optional for a clean slate, caution: this deletes data)
DROP TABLE IF EXISTS feedback;
DROP TABLE IF EXISTS reservations;
DROP TABLE IF EXISTS passengers;
DROP TABLE IF EXISTS flights;
DROP TABLE IF EXISTS airlines;
DROP TABLE IF EXISTS admins;
DROP TABLE IF EXISTS users;

-- 2. Create admins table
CREATE TABLE admins (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(255) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  first_name VARCHAR(255) DEFAULT 'Admin',
  last_name VARCHAR(255) DEFAULT 'User',
  email VARCHAR(255) DEFAULT 'admin@example.com'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Insert default admin (override or ignore duplicates)
INSERT INTO admins (username, password) VALUES ('admin', 'admin123')
  ON DUPLICATE KEY UPDATE password=VALUES(password);

-- 4. Airlines Table
CREATE TABLE airlines (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(191) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Flights Table
CREATE TABLE flights (
  id INT AUTO_INCREMENT PRIMARY KEY,
  flight_number VARCHAR(50) NOT NULL UNIQUE,
  airline_id INT,
  origin VARCHAR(255) NOT NULL,
  destination VARCHAR(255) NOT NULL,
  departure_time DATETIME NOT NULL,
  arrival_time DATETIME NOT NULL,
  status ENUM('Scheduled', 'On Time', 'Delayed', 'Cancelled') DEFAULT 'Scheduled',
  economy_seats INT NOT NULL,
  business_seats INT NOT NULL,
  first_class_seats INT NOT NULL,
  available_economy_seats INT NOT NULL,
  available_business_seats INT NOT NULL,
  available_first_class_seats INT NOT NULL,
  economy_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  business_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  first_class_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  FOREIGN KEY (airline_id) REFERENCES airlines(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. Passengers Table
CREATE TABLE passengers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  first_name VARCHAR(255) NOT NULL,
  last_name VARCHAR(255) NOT NULL,
  email VARCHAR(255) NOT NULL UNIQUE,
  phone VARCHAR(50)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. Reservations Table
CREATE TABLE reservations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  passenger_id INT,
  flight_id INT,
  seat_type ENUM('Economy', 'Business', 'First-Class') NOT NULL,
  reservation_status ENUM('Confirmed', 'Pending', 'Cancelled') DEFAULT 'Confirmed',
  reservation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (passenger_id) REFERENCES passengers(id),
  FOREIGN KEY (flight_id) REFERENCES flights(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 8. Feedback Table
CREATE TABLE feedback (
  id INT AUTO_INCREMENT PRIMARY KEY,
  passenger_id INT,
  rating INT,
  comments TEXT,
  feedback_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (passenger_id) REFERENCES passengers(id),
  CHECK (rating >= 1 AND rating <= 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 9. Users Table
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  first_name VARCHAR(100) NOT NULL,
  last_name VARCHAR(100) NOT NULL,
  email VARCHAR(150) UNIQUE NOT NULL,
  phone VARCHAR(20) NOT NULL,
  password VARCHAR(255) NOT NULL,
  dob DATE NOT NULL,
  role ENUM('admin', 'user') DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 10. Insert dummy data (make sure airlines and passengers are created first)
INSERT IGNORE INTO airlines (id, name) VALUES (1, 'Air India'), (2, 'Lufthansa'), (3, 'British Airways');

INSERT IGNORE INTO flights (flight_number, airline_id, origin, destination, departure_time, arrival_time,
  economy_seats, business_seats, first_class_seats, available_economy_seats, available_business_seats,
  available_first_class_seats, economy_price, business_price, first_class_price)
VALUES
('AI201', 1, 'DEL', 'BOM', '2024-12-15 10:00:00', '2024-12-15 12:00:00', 150, 20, 10, 145, 18, 9, 5000, 15000, 25000),
('BA777', 3, 'LHR', 'JFK', '2024-12-16 14:00:00', '2024-12-16 22:00:00', 200, 30, 15, 195, 28, 14, 8000, 25000, 40000);

INSERT IGNORE INTO passengers (id, first_name, last_name, email, phone) VALUES
(1, 'John', 'Doe', 'john.doe@example.com', '123-456-7890'),
(2, 'Jane', 'Smith', 'jane.smith@example.com', '987-654-3210');

INSERT IGNORE INTO reservations (passenger_id, flight_id, seat_type, reservation_status)
VALUES
(1, 1, 'Economy', 'Confirmed'),
(2, 2, 'Business', 'Confirmed');

INSERT IGNORE INTO feedback (passenger_id, rating, comments)
VALUES
(1, 5, 'Great flight! Smooth and on time.'),
(2, 4, 'Comfortable seats, but food could be better.');

-- 11. Insert default user/admin to users table with MD5 (not secure, for demo only)
INSERT IGNORE INTO users (first_name, last_name, email, phone, password, dob, role)
VALUES ('Admin', 'User', 'admin@airline.com', '9999999999', MD5('admin123'), '1990-01-01', 'admin');

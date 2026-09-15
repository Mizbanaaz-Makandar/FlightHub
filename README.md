### FlightHub (Airline Reservation System)

Project Overview

The Airline Reservation System is a software application designed to automate airline ticket booking, flight scheduling, passenger management, and payment processing.
It provides a user-friendly interface for customers and an admin panel for airline staff to manage operations efficiently.

There are several open-source projects named FlightHub that implement airline reservation systems, but they use different technology stacks and architectural patterns. The search results include a Java Spring Boot full-stack application, a PHP CQRS/Event Sourcing system, and a Node.js API backend.

Overview of FlightHub Projects
Project	Tech Stack	Architecture/Approach	Key Features
Java Spring Boot (DS Group 40)	Java, Spring Boot, MySQL, HTML/JS/CSS	Traditional Layered Architecture	User registration, flight search, booking, history, edit/cancel 
PHP CQRS/ES (akondas)	PHP, Docker, Swagger	CQRS + Event Sourcing	Flight management, seat blocking, reservations, customer management 
Node.js API (KumariAnjali40)	Node.js, Swagger	REST API Backend	User auth, flight management, booking CRUD endpoints 

 Java Spring Boot FlightHub (Most Comprehensive)
This is a full-stack application built for a Data Structures project, featuring a complete web interface.

Core Features:

User Management: Register and login functionality .

Flight Search: Search by date, source, and destination; includes finding the cheapest flights .

Booking Management: Book flights, view booking history, edit booking information, and cancel bookings .

Technical Highlights:

Custom Data Structures: Implements custom MyLinkedList and MyQueue classes, likely for managing bookings or flight lists .

Tech Stack: Java Spring Boot backend, MySQL database (phpMyAdmin), and HTML/JavaScript/CSS frontend .

Getting Started (README Excerpt):

Prerequisites: Maven (in PATH), JDK 17+, and an IDE (VS Code/IntelliJ) .

Run Commands:

bash
cd com.ft.flight
mvn clean install
mvn spring-boot:run
Database Config: Set DB_USERNAME and DB_PASSWORD environment variables .

Access: Navigate to localhost:8080/login (the default port; change if needed) .

Note: The sample data in the README only covers specific routes for February 2024 .

PHP FlightHub (CQRS/Event Sourcing)
This project showcases advanced architectural patterns, focusing on concurrency control.

Patterns: Implements CQRS (Command Query Responsibility Segregation) and Event Sourcing, with locking strategies like Optimistic, Pessimistic, and Implicit locks .

Commands: Handles adding flights, reserving tickets, blocking seats, confirming/canceling reservations, and registering customers .

Running: Uses Docker (docker-compose up -d) and includes a Swagger UI at localhost:8080/swagger/index.html .

Node.js FlightHub API
This is a backend-only project, described as an "Air Ticket Booking API."

Endpoints: Provides RESTful endpoints for user registration/login, fetching/adding flights, and booking management (create, read, update, delete) .

Documentation: Includes Swagger API docs at localhost:4500/apidocs/ .

If you are looking for a specific project, the Java Spring Boot version is likely the most complete "project" with a user-facing website, while the PHP version is best for studying CQRS.

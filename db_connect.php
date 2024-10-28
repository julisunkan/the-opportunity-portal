<?php
require_once 'error_handler.php';

// Database connection parameters
$host = 'localhost';
$dbname = 'opportunity_portal';
$username = 'your_username';
$password = 'your_password';

// Attempt to establish the database connection
try {
    $conn = new mysqli($host, $username, $password, $dbname);

    // Check the connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Set the character set to utf8mb4
    if (!$conn->set_charset("utf8mb4")) {
        throw new Exception("Error setting character set: " . $conn->error);
    }

    log_message("Database connection established successfully");
} catch (Exception $e) {
    log_message("Database connection error: " . $e->getMessage(), 'ERROR');
    die(display_error("We're experiencing technical difficulties. Please try again later."));
}

// If no exceptions were thrown, the connection is established successfully
// You can now use $conn in your other PHP files to interact with the database
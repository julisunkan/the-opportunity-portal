<?php
// Set error reporting level
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Custom error handler function
function custom_error_handler($errno, $errstr, $errfile, $errline) {
    $error_message = date("[Y-m-d H:i:s]") . " Error: [$errno] $errstr in $errfile on line $errline" . PHP_EOL;
    error_log($error_message, 3, __DIR__ . "/error.log");

    // Don't execute PHP's internal error handler
    return true;
}

// Set the custom error handler
set_error_handler("custom_error_handler");

// Custom exception handler function
function custom_exception_handler($exception) {
    $error_message = date("[Y-m-d H:i:s]") . " Uncaught Exception: " . $exception->getMessage() . " in " . $exception->getFile() . " on line " . $exception->getLine() . PHP_EOL;
    error_log($error_message, 3, __DIR__ . "/error.log");

    // Display a user-friendly error message
    echo "An unexpected error occurred. Please try again later.";
}

// Set the custom exception handler
set_exception_handler("custom_exception_handler");

// Function to log custom messages
function log_message($message, $level = 'INFO') {
    $log_message = date("[Y-m-d H:i:s]") . " [$level] $message" . PHP_EOL;
    error_log($log_message, 3, __DIR__ . "/application.log");
}

// Function to display user-friendly error messages
function display_error($message) {
    return "<p class='error'>$message</p>";
}
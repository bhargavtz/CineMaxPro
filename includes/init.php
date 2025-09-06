<?php
// --- Environment & Debugging ---
// In a production environment, error reporting should be turned off or logged to a file.
// Consider using environment variables to control this setting.
if (isset($_SERVER['APP_ENV']) && $_SERVER['APP_ENV'] === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0); // Turn off error reporting in production
    ini_set('display_errors', 0);
    ini_set('log_errors', 1); // Log errors to a file
}

// --- Session Management ---
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    // Set session cookie parameters for security
    $sessionCookieParams = session_get_cookie_params();
    session_set_cookie_params(
        $sessionCookieParams["lifetime"],
        $sessionCookieParams["path"],
        $sessionCookieParams["domain"],
        // Set to true if using HTTPS, false otherwise. For local development, false is typical.
        // In production, this should be true if your site uses HTTPS.
        false, // Use true for HTTPS
        true   // HttpOnly flag
    );
    session_start();

    // Enable strict session mode to prevent session fixation
    ini_set('session.use_strict_mode', 1);
}

// --- Include Core Files ---

// Include auth functions
require_once __DIR__ . '/auth_functions.php';

// Include database configuration
try {
    require_once __DIR__ . '/../config.php';
    if (!isset($pdo) || !($pdo instanceof PDO)) {
        // Log the error and provide a generic message to the user
        error_log("Database connection not established in init.php");
        throw new Exception("Database connection failed.");
    }
} catch (Exception $e) {
    error_log("Database Error in init.php: " . $e->getMessage());
    // Display a user-friendly error message
    die("A critical error occurred with the database. Please try again later or contact support.");
}

// Include utility functions
require_once __DIR__ . '/functions.php';

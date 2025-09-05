<?php
// Error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include auth functions
require_once __DIR__ . '/auth_functions.php';

// Include database configuration
try {
    require_once __DIR__ . '/../config.php';
    if (!isset($pdo) || !($pdo instanceof PDO)) {
        throw new Exception("Database connection not established");
    }
} catch (Exception $e) {
    error_log("Database Error in init.php: " . $e->getMessage());
    die("Database connection error. Please verify your database settings.");
}

// Include utility functions
require_once __DIR__ . '/functions.php';

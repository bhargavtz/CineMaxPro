<?php
// Initialize everything that needs to be done before any output
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include database configuration
require_once __DIR__ . '/../config.php';

// Include utility functions
require_once __DIR__ . '/functions.php';

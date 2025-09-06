<?php
// File: includes/auth_functions.php

/**
 * Start a new user session
 * @param array $user User data from database
 * @return void
 */
function startUserSession($user) {
    // Regenerate session ID to prevent session fixation
    session_regenerate_id(true);
    
    // Set user session data
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['first_name'] = $user['first_name'];
    $_SESSION['user_role'] = $user['role']; // Dynamically set role
    $_SESSION['last_activity'] = time();
}

/**
 * Start a new admin session
 * @param array $admin Admin data from database
 * @return void
 */
function startAdminSession($admin) {
    // Regenerate session ID to prevent session fixation
    session_regenerate_id(true);
    
    // Set admin session data
    $_SESSION['staff_id'] = $admin['staff_id'];
    $_SESSION['user_id'] = $admin['user_id'];
    $_SESSION['username'] = $admin['username'];
    // $_SESSION['role'] = $admin['role']; // Redundant, $_SESSION['user_role'] is used for checks
    $_SESSION['user_role'] = $admin['role']; // Dynamically set role
    $_SESSION['last_activity'] = time();
}

/**
 * Check if user is logged in
 * @return bool
 */
function isUserLoggedIn() {
    return isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'user';
}

/**
 * Check if admin is logged in
 * @return bool
 */
function isAdminLoggedIn() {
    return isset($_SESSION['staff_id']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Require user login for protected pages
 * @return void
 */
function requireUserLogin() {
    if (!isUserLoggedIn()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header('Location: login.php');
        exit;
    }
}

/**
 * Require admin login for protected pages
 * @return void
 */
function requireAdminLogin() {
    if (!isAdminLoggedIn()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header('Location: admin_login.php');
        exit;
    }
}

/**
 * Require staff login for protected pages
 * @return void
 */
function requireStaffLogin() {
    if (!isStaffLoggedIn()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header('Location: admin_staff_login.php');
        exit;
    }
}

/**
 * End user session and redirect
 * @return void
 */
function logout() {
    // Determine the redirect URL based on user role
    $redirect_url = 'login.php'; // Default for users
    if (isset($_SESSION['user_role'])) {
        switch ($_SESSION['user_role']) {
            case 'admin':
                $redirect_url = 'admin_login.php';
                break;
            case 'staff':
                $redirect_url = 'admin_staff_login.php';
                break;
            case 'user':
            default:
                $redirect_url = 'login.php';
                break;
        }
    }

    // Unset all session variables
    $_SESSION = array();

    // Destroy the session cookie
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }

    // Destroy the session
    session_destroy();

    // Redirect to the appropriate login page
    header('Location: ' . $redirect_url);
    exit;
}

/**
 * Check if staff is logged in
 * @return bool
 */
function isStaffLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'staff';
}

?>

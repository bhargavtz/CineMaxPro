<?php
/**
 * Function to check if the current logged-in staff member has admin access
 * @return bool
 */
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'Admin';
}

/**
 * Function to require admin login for protected pages
 * Redirects to login page if not logged in or not an admin
 */
function requireAdminLogin() {
    if (!isset($_SESSION['staff_id'])) {
        header('Location: admin_login.php');
        exit;
    }
    
    if (!isAdmin()) {
        // If logged in but not admin, show access denied
        http_response_code(403);
        die('Access Denied: Admin privileges required');
    }
}

/**
 * Function to require staff login (any staff member)
 * Redirects to login page if not logged in
 */
function requireStaffLogin() {
    if (!isset($_SESSION['staff_id'])) {
        header('Location: admin_login.php');
        exit;
    }
}

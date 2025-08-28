<?php

// --- Database Connection Check ---
// Ensure PDO connection is available. This file should be included after config.php.
if (!isset($pdo) || !($pdo instanceof PDO)) {
    // Log error and die if PDO connection is not established
    error_log("PDO connection not available in functions.php");
    die("A critical error occurred. Please contact support.");
}

// --- Input Sanitization and Validation ---

/**
 * Sanitize string input.
 * @param string $data
 * @return string
 */
function sanitize_string($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate email format.
 * @param string $email
 * @return bool
 */
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate phone number format (basic).
 * Allows digits, spaces, hyphens, parentheses.
 * @param string $phone
 * @return bool
 */
function validate_phone($phone) {
    // Allow empty phone number as it's optional
    if (empty($phone)) {
        return true;
    }
    return preg_match('/^[0-9\s\-()]+$/', $phone) !== false;
}

/**
 * Validate password strength.
 * Requires at least 8 characters, one uppercase, one lowercase, one number, one special character.
 * @param string $password
 * @return bool
 */
function validate_password($password) {
    if (empty($password) || strlen($password) < 8) {
        return false;
    }
    if (!preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password) || !preg_match('/[!@#$%^&*()_+=-]/', $password)) {
        return false;
    }
    return true;
}

// --- Password Hashing ---

/**
 * Hash a password using bcrypt.
 * @param string $password
 * @return string Hashed password
 */
function hash_password($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verify a password against a hash.
 * @param string $password
 * @param string $hash
 * @return bool
 */
function verify_password($password, $hash) {
    return password_verify($password, $hash);
}

// --- Session Management ---

/**
 * Check if a user is logged in.
 * Assumes user ID is stored in $_SESSION['user_id'].
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Get the logged-in user's ID.
 * @return int|null User ID or null if not logged in.
 */
function getUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Log in a user.
 * @param int $userId
 * @param string $username (or email/phone)
 */
function loginUser($userId, $username) {
    $_SESSION['user_id'] = $userId;
    $_SESSION['username'] = $username; // Store username or identifier
    // Regenerate session ID to prevent session fixation
    session_regenerate_id(true);
}

/**
 * Log out a user.
 */
function logoutUser() {
    // Unset all session variables
    $_SESSION = array();

    // If it's desired to kill the session, also delete the session cookie.
    // Note: This will destroy the session, and not just the session data!
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    // Finally, destroy the session.
    session_destroy();
}

/**
 * Check if a staff member is logged in. If not, redirect to the staff login page.
 * Assumes staff ID is stored in $_SESSION['staff_id'].
 */
function requireStaffLogin() {
    if (!isset($_SESSION['staff_id']) || empty($_SESSION['staff_id'])) {
        redirect('admin_staff_login.php');
    }
}

// --- Password Reset Functionality ---

/**
 * Generate a secure password reset token.
 * @return string
 */
function generateResetToken() {
    return bin2hex(random_bytes(32)); // 32 bytes = 64 hex characters
}

/**
 * Get user by email for password reset.
 * @param PDO $pdo
 * @param string $email
 * @return array|false User data or false if not found.
 */
function getUserByEmail($pdo, $email) {
    $stmt = $pdo->prepare("SELECT user_id, email, first_name, password_reset_token, reset_token_expiry FROM users WHERE email = :email");
    $stmt->execute([':email' => $email]);
    return $stmt->fetch();
}

/**
 * Update user's password reset token and expiry.
 * @param PDO $pdo
 * @param int $userId
 * @param string $token
 * @param int $expiryTimestamp
 * @return bool
 */
function updateResetToken($pdo, $userId, $token, $expiryTimestamp) {
    $stmt = $pdo->prepare("UPDATE users SET password_reset_token = :token, reset_token_expiry = :expiry WHERE user_id = :userId");
    return $stmt->execute([
        ':token' => $token,
        ':expiry' => date('Y-m-d H:i:s', $expiryTimestamp), // Store as DATETIME
        ':userId' => $userId
    ]);
}

/**
 * Get user by reset token.
 * @param PDO $pdo
 * @param string $token
 * @return array|false User data or false if not found or token expired.
 */
function getUserByResetToken($pdo, $token) {
    $stmt = $pdo->prepare("SELECT user_id, email, first_name, reset_token_expiry FROM users WHERE password_reset_token = :token AND reset_token_expiry > NOW()");
    $stmt->execute([':token' => $token]);
    return $stmt->fetch();
}

/**
 * Update user's password and clear reset token.
 * @param PDO $pdo
 * @param int $userId
 * @param string $newPasswordHash
 * @return bool
 */
function updatePassword($pdo, $userId, $newPasswordHash) {
    $stmt = $pdo->prepare("UPDATE users SET password_hash = :password_hash, password_reset_token = NULL, reset_token_expiry = NULL WHERE user_id = :userId");
    return $stmt->execute([
        ':password_hash' => $newPasswordHash,
        ':userId' => $userId
    ]);
}

// --- Email Sending Placeholder ---
// In a real application, you would use a library like PHPMailer or SwiftMailer.

/**
 * Placeholder function to send an email.
 * @param string $to
 * @param string $subject
 * @param string $body
 * @return bool True if email was "sent" (simulated), false otherwise.
 */
function sendEmail($to, $subject, $body) {
    // In a real application, this would involve SMTP or an email API.
    // For this example, we'll just log it or simulate success.
    error_log("--- SIMULATING EMAIL SEND ---");
    error_log("To: " . $to);
    error_log("Subject: " . $subject);
    error_log("Body: " . $body);
    error_log("-----------------------------");
    return true; // Simulate success
}

// --- Redirection ---

/**
 * Redirect to a specified URL.
 * @param string $url
 */
function redirect($url) {
    header("Location: " . $url);
    exit();
}

?>

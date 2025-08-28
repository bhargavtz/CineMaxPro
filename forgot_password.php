<?php
require_once __DIR__ . '/includes/header.php';

// --- CSRF Token Generation ---
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// --- Form Submission Handling ---
$message = ''; // To display success or error messages

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $message = '<div class="alert alert-danger alert-dismissible fade show" role="alert">Invalid request. CSRF token mismatch.</div>';
    } else {
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);

        // --- Input Validation ---
        if (empty($email) || !validate_email($email)) {
            $message = '<div class="alert alert-danger alert-dismissible fade show" role="alert">Please enter a valid email address.</div>';
        } else {
            try {
                // Check if user exists and get their details
                $user = getUserByEmail($pdo, $email);

                if ($user) {
                    // Generate reset token and expiry
                    $token = generateResetToken();
                    $expiryTimestamp = time() + (60 * 60); // Token valid for 1 hour

                    // Update user's token and expiry in the database
                    if (updateResetToken($pdo, $user['user_id'], $token, $expiryTimestamp)) {
                        // Construct the reset link
                        // The reset_password.php script will need to parse this token from the URL
                        $resetLink = "http://localhost/reset_password.php?token=" . urlencode($token); // Assuming localhost setup

                        // Prepare email body
                        $emailSubject = "Password Reset Request";
                        $emailBody = "Hello " . htmlspecialchars($user['first_name'] ?: $email) . ",\n\n";
                        $emailBody .= "We received a request to reset your password. Please click the link below to reset your password:\n";
                        $emailBody .= $resetLink . "\n\n";
                        $emailBody .= "This link will expire in 1 hour.\n\n";
                        $emailBody .= "If you did not request this, please ignore this email.\n\n";
                        $emailBody .= "Sincerely,\nYour Cinema App Team";

                        // Send the email (using the placeholder function)
                        if (sendEmail($email, $emailSubject, $emailBody)) {
                            $message = '<div class="alert alert-success alert-dismissible fade show" role="alert">Password reset instructions have been sent to your email. Please check your inbox (and spam folder).</div>';
                        } else {
                            $message = '<div class="alert alert-danger alert-dismissible fade show" role="alert">Failed to send password reset email. Please try again later.</div>';
                        }
                    } else {
                        $message = '<div class="alert alert-danger alert-dismissible fade show" role="alert">Failed to update reset token. Please try again later.</div>';
                    }
                } else {
                    // Email not found, but we don't want to reveal that to prevent enumeration attacks
                    $message = '<div class="alert alert-info alert-dismissible fade show" role="alert">If an account with that email exists, a password reset link will be sent.</div>';
                }
            } catch (\PDOException $e) {
                error_log("Forgot Password DB Error: " . $e->getMessage());
                $message = '<div class="alert alert-danger alert-dismissible fade show" role="alert">An unexpected error occurred. Please try again later.</div>';
            }
        }
    }
}
?>

<!-- Forgot Password Form -->
<div class="form-signin">
    <h1 class="h3 mb-3 fw-normal text-center">Forgot Password</h1>

    <?php echo $message; ?>

    <form action="forgot_password.php" method="POST" novalidate>
        <!-- CSRF Token -->
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

        <div class="form-floating">
            <input type="email" class="form-control <?php echo isset($message) && strpos($message, 'email') !== false ? 'is-invalid' : ''; ?>" id="email" name="email" placeholder="Enter your email" required autofocus>
            <label for="email">Email address</label>
            <?php if (isset($message) && strpos($message, 'email') !== false): ?>
                <!-- The alert message itself handles the error display -->
            <?php endif; ?>
        </div>

        <button class="btn btn-primary w-100 py-2 mt-3" type="submit">Send Reset Link</button>
    </form>

    <p class="mt-5 mb-3 text-body-secondary text-center">Remember your password? <a href="login.php" class="link-offset-2">Log In</a></p>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

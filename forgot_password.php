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

<div class="min-h-screen flex items-center justify-center bg-gray-100 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 p-10 bg-white rounded-xl shadow-lg z-10">
        <div class="text-center">
            <h2 class="mt-6 text-3xl font-extrabold text-gray-900">
                Forgot Password
            </h2>
        </div>

        <?php 
        // Custom logic to convert Bootstrap alerts to Tailwind CSS alerts
        if (!empty($message)) {
            $tailwind_message = str_replace('alert alert-danger', 'bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4', $message);
            $tailwind_message = str_replace('alert alert-success', 'bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4', $tailwind_message);
            $tailwind_message = str_replace('alert alert-info', 'bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative mb-4', $tailwind_message);
            $tailwind_message = str_replace('alert-dismissible fade show', '', $tailwind_message); // Remove dismissible classes
            $tailwind_message = str_replace('<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>', '', $tailwind_message); // Remove close button
            echo $tailwind_message;
        }
        ?>

        <form class="mt-8 space-y-6" action="forgot_password.php" method="POST">
            <!-- CSRF Token -->
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

            <div class="rounded-md shadow-sm -space-y-px">
                <div>
                    <label for="email" class="sr-only">Email address</label>
                    <input id="email" name="email" type="email" autocomplete="email" required autofocus
                           class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
                           placeholder="Email address">
                </div>
            </div>

            <div>
                <button type="submit"
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Send Reset Link
                </button>
            </div>
        </form>

        <p class="mt-5 text-center text-sm text-gray-600">
            Remember your password?
            <a href="login.php" class="font-medium text-indigo-600 hover:text-indigo-500">
                Log In
            </a>
        </p>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

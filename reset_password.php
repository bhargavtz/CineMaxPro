<?php
require_once __DIR__ . '/includes/header.php';

// --- CSRF Token Generation ---
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// --- Form Submission Handling ---
$message = ''; // To display success or error messages
$show_form = false; // Flag to control whether to show the password reset form

// Check if token is provided in the URL
if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Validate the token
    try {
        $user = getUserByResetToken($pdo, $token);

        if ($user) {
            // Token is valid and not expired
            $show_form = true;
        } else {
            // Token is invalid or expired
            $message = '<div class="alert alert-danger alert-dismissible fade show" role="alert">Invalid or expired password reset token. Please request a new one.</div>';
        }
    } catch (\PDOException $e) {
        error_log("Reset Password Token Validation Error: " . $e->getMessage());
        $message = '<div class="alert alert-danger alert-dismissible fade show" role="alert">An error occurred while validating the token. Please try again later.</div>';
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle form submission for new password
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $message = '<div class="alert alert-danger alert-dismissible fade show" role="alert">Invalid request. CSRF token mismatch.</div>';
    } else {
        $token = $_POST['token'] ?? ''; // Get token from hidden field
        $new_password = $_POST['new_password'] ?? '';
        $confirm_new_password = $_POST['confirm_new_password'] ?? '';

        // --- Input Validation ---
        $errors = [];

        if (empty($token)) {
            $errors['token'] = 'Missing reset token.';
        }
        if (empty($new_password)) {
            $errors['new_password'] = 'New password is required.';
        } elseif (!validate_password($new_password)) {
            $errors['new_password'] = 'Password must be at least 8 characters long and contain an uppercase letter, a lowercase letter, a number, and a special character.';
        }
        if (empty($confirm_new_password)) {
            $errors['confirm_new_password'] = 'Please confirm your new password.';
        } elseif ($new_password !== $confirm_new_password) {
            $errors['confirm_new_password'] = 'Passwords do not match.';
        }

        if (empty($errors)) {
            try {
                // Get user by token to ensure it's still valid
                $user = getUserByResetToken($pdo, $token);

                if ($user) {
                    // Hash the new password
                    $hashed_password = hash_password($new_password);

                    // Update the user's password and clear the token
                    if (updatePassword($pdo, $user['user_id'], $hashed_password)) {
                        $message = '<div class="alert alert-success alert-dismissible fade show" role="alert">Your password has been successfully reset. You can now log in.</div>';
                        // Redirect to login page after successful reset
                        // header("Location: login.php?message=" . urlencode("Password reset successful. Please log in."));
                        // exit();
                    } else {
                        $message = '<div class="alert alert-danger alert-dismissible fade show" role="alert">Failed to reset password. Please try again later.</div>';
                    }
                } else {
                    // Token invalid or expired after form submission (unlikely but possible)
                    $message = '<div class="alert alert-danger alert-dismissible fade show" role="alert">Invalid or expired password reset token. Please request a new one.</div>';
                }
            } catch (\PDOException $e) {
                error_log("Reset Password DB Error: " . $e->getMessage());
                $message = '<div class="alert alert-danger alert-dismissible fade show" role="alert">An unexpected error occurred during password reset. Please try again later.</div>';
            }
        } else {
            // If there are validation errors, set the flag to show the form again with errors
            $show_form = true;
            $message = 'Please correct the following errors:'; // Generic message, specific errors shown below
        }
    }
} else {
    // If no token is provided and it's not a POST request, show an error or redirect
    $message = '<div class="alert alert-warning alert-dismissible fade show" role="alert">No password reset token provided. Please request a password reset.</div>';
}
?>

<!-- Password Reset Form -->
<div class="form-signin">
    <h1 class="h3 mb-3 fw-normal text-center">Reset Password</h1>

    <?php echo $message; ?>

    <?php if ($show_form): ?>
        <form action="reset_password.php" method="POST" novalidate>
            <!-- CSRF Token and original token -->
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($_GET['token'] ?? $_POST['token'] ?? ''); ?>">

            <div class="form-floating position-relative">
                <input type="password" class="form-control <?php echo isset($errors['new_password']) ? 'is-invalid' : ''; ?>" id="new_password" name="new_password" placeholder="New Password" required>
                <label for="new_password">New Password</label>
                <span class="toggle-password position-absolute top-50 end-0 translate-middle-y me-3" style="cursor: pointer;"><i class="fas fa-eye"></i></span>
                <?php if (isset($errors['new_password'])): ?>
                    <div class="invalid-feedback"><?php echo $errors['new_password']; ?></div>
                <?php endif; ?>
            </div>
            <div class="form-floating position-relative">
                <input type="password" class="form-control <?php echo isset($errors['confirm_new_password']) ? 'is-invalid' : ''; ?>" id="confirm_new_password" name="confirm_new_password" placeholder="Confirm New Password" required>
                <label for="confirm_new_password">Confirm New Password</label>
                <span class="toggle-password position-absolute top-50 end-0 translate-middle-y me-3" style="cursor: pointer;"><i class="fas fa-eye"></i></span>
                <?php if (isset($errors['confirm_new_password'])): ?>
                    <div class="invalid-feedback"><?php echo $errors['confirm_new_password']; ?></div>
                <?php endif; ?>
            </div>

            <button class="btn btn-primary w-100 py-2 mt-3" type="submit">Reset Password</button>
        </form>
    <?php else: ?>
        <p class="text-center">
            <a href="forgot_password.php" class="link-offset-2">Request another password reset</a>
        </p>
    <?php endif; ?>

    <p class="mt-5 mb-3 text-body-secondary text-center">Already have an account? <a href="login.php" class="link-offset-2">Log In</a></p>
</div>

<!-- Font Awesome for password toggle icon -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
<script>
    // Ensure Font Awesome icons are correctly displayed
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.toggle-password').forEach(function(element) {
            // Check if the Font Awesome icon is already present
            if (!element.querySelector('i')) {
                const icon = document.createElement('i');
                icon.className = 'fas fa-eye'; // Default to eye icon
                element.appendChild(icon);
            }
        });
    });
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

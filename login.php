<?php
require_once __DIR__ . '/includes/init.php';

// --- CSRF Token Generation ---
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// --- Form Submission Handling ---
$login_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $login_error = 'Invalid request. CSRF token mismatch.';
    } else {
        // Sanitize and validate inputs
    $identifier = filter_input(INPUT_POST, 'identifier', FILTER_SANITIZE_STRING); // Now only email
        $password = $_POST['password'] ?? '';

        // --- Input Validation ---
        $errors = [];

        if (empty($identifier)) {
            $errors['identifier'] = 'Please enter your email address.';
        }
        if (empty($password)) {
            $errors['password'] = 'Password is required.';
        }

        // --- Database Check and Login ---
        if (empty($errors)) {
            try {
                // Fetch user by email only
                $stmt = $pdo->prepare("SELECT user_id, email, password_hash, first_name FROM users WHERE email = :identifier");
                $stmt->execute([':identifier' => $identifier]);
                $user = $stmt->fetch();

                if ($user && password_verify($password, $user['password_hash'])) {
                    // Password is correct, log the user in
                    loginUser($user['user_id'], $user['first_name'] ?: $user['email']);
                    $_SESSION['user_role'] = 'user'; // Set a role for regular users
                    $login_error = 'Login successful! Welcome, ' . htmlspecialchars($user['first_name'] ?: $user['email']) . '.';
                    unset($_POST['password']);
                    header("Location: index.php"); // Redirect to home page after successful login
                    exit();
                } else {
                    // Invalid credentials
                    $login_error = 'Invalid email or password. Please try again.';
                }
            } catch (\PDOException $e) {
                // Log the error for debugging
                error_log("Login DB Error: " . $e->getMessage());
                $login_error = 'An unexpected error occurred during login. Please try again later.';
            }
        } else {
            // If there are validation errors, display them
            $login_error = 'Please correct the following errors:';
        }
    }
}

require_once __DIR__ . '/includes/header.php'; // Include header AFTER PHP logic
?>

<div class="auth-container">
    <div class="form-signin">
        <h1 class="h3 mb-3 fw-normal text-center">Log In</h1>

        <?php if (!empty($login_error)): ?>
            <div class="alert alert-<?php echo (strpos($login_error, 'successful') !== false ? 'success' : 'danger'); ?> alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($login_error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST" novalidate>
            <!-- CSRF Token -->
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

            <div class="form-floating">
                <input type="email" class="form-control <?php echo isset($errors['identifier']) ? 'is-invalid' : ''; ?>" id="identifier" name="identifier" placeholder="Email address" value="<?php echo isset($_POST['identifier']) ? htmlspecialchars($_POST['identifier']) : ''; ?>" required autofocus autocomplete="username">
                <label for="identifier">Email address</label>
                <?php if (isset($errors['identifier'])): ?>
                    <div class="invalid-feedback"><?php echo $errors['identifier']; ?></div>
                <?php endif; ?>
            </div>

            <div class="form-floating position-relative">
                <input type="password" class="form-control <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>" id="password" name="password" placeholder="Password" required autocomplete="current-password">
                <label for="password">Password</label>
                <span class="toggle-password position-absolute top-50 end-0 translate-middle-y me-3" style="cursor: pointer;"><i class="fas fa-eye"></i></span> <!-- Font Awesome icon for toggle -->
                <?php if (isset($errors['password'])): ?>
                    <div class="invalid-feedback"><?php echo $errors['password']; ?></div>
                <?php endif; ?>
            </div>

            <div class="form-check text-start mb-3">
                <input class="form-check-input" type="checkbox" value="remember-me" id="flexCheckDefault">
                <label class="form-check-label" for="flexCheckDefault">
                    Remember me
                </label>
            </div>

            <button class="btn btn-primary w-100 py-2" type="submit">Log In</button>
        </form>

        <p class="mt-5 mb-3 text-body-secondary text-center">Don't have an account? <a href="signup.php" class="link-offset-2">Sign Up</a></p>
        <p class="mt-3 mb-3 text-body-secondary text-center"><a href="forgot_password.php" class="link-offset-2">Forgot Password?</a></p>
    </div>
</div>

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

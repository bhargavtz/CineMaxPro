<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/functions.php'; // Assuming functions.php will be created next

// --- CSRF Token Generation ---
// Generate a CSRF token if one doesn't exist in the session
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// --- Form Submission Handling ---
$signup_error = '';
$signup_success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $signup_error = 'Invalid request. CSRF token mismatch.';
    } else {
        // Sanitize and validate inputs
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING); // Basic sanitization for phone
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        // --- Input Validation ---
        $errors = [];

        // Email validation
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Please enter a valid email address.';
        }

        // Phone validation (basic check for digits and optional hyphens/spaces)
        // More robust validation might be needed depending on expected formats
        if (empty($phone)) {
            $errors['phone'] = 'Please enter your phone number.';
        } else {
            // Example: Allow digits, spaces, hyphens, parentheses
            if (!preg_match('/^[0-9\s\-()]+$/', $phone)) {
                $errors['phone'] = 'Please enter a valid phone number.';
            }
        }

        // Password validation
        if (empty($password)) {
            $errors['password'] = 'Password is required.';
        } elseif (strlen($password) < 8) {
            $errors['password'] = 'Password must be at least 8 characters long.';
        } elseif (!preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password) || !preg_match('/[!@#$%^&*()_+=-]/', $password)) {
            $errors['password'] = 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.';
        }

        // Confirm password validation
        if (empty($confirm_password)) {
            $errors['confirm_password'] = 'Please confirm your password.';
        } elseif ($password !== $confirm_password) {
            $errors['confirm_password'] = 'Passwords do not match.';
        }

        // --- Database Check and Insertion ---
        if (empty($errors)) {
            try {
                // Check if email or phone already exists
                $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = :email OR phone_number = :phone");
                $stmt->execute([':email' => $email, ':phone' => $phone]);
                $userExists = $stmt->fetch();

                if ($userExists) {
                    $signup_error = 'Email or phone number already registered. Please log in or use a different one.';
                } else {
                    // Hash the password
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                    // Prepare and execute the INSERT statement
                    $stmt = $pdo->prepare("INSERT INTO users (email, phone_number, password_hash, first_name, last_name) VALUES (:email, :phone, :password_hash, :first_name, :last_name)");
                    
                    // Fetching first_name and last_name from POST, if they exist in the form
                    $first_name = filter_input(INPUT_POST, 'first_name', FILTER_SANITIZE_STRING) ?: null;
                    $last_name = filter_input(INPUT_POST, 'last_name', FILTER_SANITIZE_STRING) ?: null;

                    $insertSuccess = $stmt->execute([
                        ':email' => $email,
                        ':phone' => $phone,
                        ':password_hash' => $hashed_password,
                        ':first_name' => $first_name,
                        ':last_name' => $last_name
                    ]);

                    if ($insertSuccess) {
                        $signup_success = 'Registration successful! You can now log in.';
                        // Optionally, clear the form fields or redirect to login page
                        // header("Location: login.php?message=" . urlencode($signup_success));
                        // exit();
                    } else {
                        $signup_error = 'Registration failed. Please try again.';
                    }
                }
            } catch (\PDOException $e) {
                // Log the error for debugging
                error_log("Signup DB Error: " . $e->getMessage());
                $signup_error = 'An unexpected error occurred during registration. Please try again later.';
            }
        } else {
            // If there are validation errors, display them
            $signup_error = 'Please correct the following errors:';
            // The errors array will be used below to display specific field errors
        }
    }
}
?>

<!-- Signup Form -->
<div class="form-signin">
    <h1 class="h3 mb-3 fw-normal text-center">Sign Up</h1>

    <?php if (!empty($signup_error)): ?>
        <div class="alert alert-<?php echo ($signup_success ? 'success' : 'danger'); ?> alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($signup_error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <form action="signup.php" method="POST" novalidate>
        <!-- CSRF Token -->
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

        <div class="form-floating">
            <input type="text" class="form-control <?php echo isset($errors['first_name']) ? 'is-invalid' : ''; ?>" id="first_name" name="first_name" placeholder="First name" value="<?php echo isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : ''; ?>">
            <label for="first_name">First Name</label>
            <?php if (isset($errors['first_name'])): ?>
                <div class="invalid-feedback"><?php echo $errors['first_name']; ?></div>
            <?php endif; ?>
        </div>
        <div class="form-floating">
            <input type="text" class="form-control <?php echo isset($errors['last_name']) ? 'is-invalid' : ''; ?>" id="last_name" name="last_name" placeholder="Last name" value="<?php echo isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : ''; ?>">
            <label for="last_name">Last Name</label>
            <?php if (isset($errors['last_name'])): ?>
                <div class="invalid-feedback"><?php echo $errors['last_name']; ?></div>
            <?php endif; ?>
        </div>

        <div class="form-floating">
            <input type="email" class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" id="email" name="email" placeholder="name@example.com" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
            <label for="email">Email address</label>
            <?php if (isset($errors['email'])): ?>
                <div class="invalid-feedback"><?php echo $errors['email']; ?></div>
            <?php endif; ?>
        </div>
        <div class="form-floating">
            <input type="tel" class="form-control <?php echo isset($errors['phone']) ? 'is-invalid' : ''; ?>" id="phone" name="phone" placeholder="Phone Number" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
            <label for="phone">Phone Number (Optional)</label>
            <?php if (isset($errors['phone'])): ?>
                <div class="invalid-feedback"><?php echo $errors['phone']; ?></div>
            <?php endif; ?>
        </div>

        <div class="form-floating position-relative">
            <input type="password" class="form-control <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>" id="password" name="password" placeholder="Password" required>
            <label for="password">Password</label>
            <span class="toggle-password position-absolute top-50 end-0 translate-middle-y me-3" style="cursor: pointer;"><i class="fas fa-eye"></i></span> <!-- Font Awesome icon for toggle -->
            <?php if (isset($errors['password'])): ?>
                <div class="invalid-feedback"><?php echo $errors['password']; ?></div>
            <?php endif; ?>
        </div>
        <div class="form-floating position-relative">
            <input type="password" class="form-control <?php echo isset($errors['confirm_password']) ? 'is-invalid' : ''; ?>" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required>
            <label for="confirm_password">Confirm Password</label>
            <span class="toggle-password position-absolute top-50 end-0 translate-middle-y me-3" style="cursor: pointer;"><i class="fas fa-eye"></i></span> <!-- Font Awesome icon for toggle -->
            <?php if (isset($errors['confirm_password'])): ?>
                <div class="invalid-feedback"><?php echo $errors['confirm_password']; ?></div>
            <?php endif; ?>
        </div>

        <button class="btn btn-primary w-100 py-2 mt-3" type="submit">Sign Up</button>
    </form>

    <p class="mt-5 mb-3 text-body-secondary text-center">Already have an account? <a href="login.php" class="link-offset-2">Log In</a></p>
    <p class="mt-3 mb-3 text-body-secondary text-center"><a href="forgot_password.php" class="link-offset-2">Forgot Password?</a></p>
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

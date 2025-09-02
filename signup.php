<?php
// First, include init.php for database and session setup
require_once __DIR__ . '/includes/init.php';

// After successful database connection, include header.php
require_once __DIR__ . '/includes/header.php';

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
    // $phone removed
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        // --- Input Validation ---
        $errors = [];

        // Email validation
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Please enter a valid email address.';
        }

    // Phone validation removed

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
                $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = :email");
                $stmt->execute([':email' => $email]);
                $userExists = $stmt->fetch();

                if ($userExists) {
                    $signup_error = 'Email already registered. Please log in or use a different one.';
                } else {
                    // Hash the password
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                    // Prepare and execute the INSERT statement
                    $stmt = $pdo->prepare("INSERT INTO users (email, password_hash, first_name, last_name) VALUES (:email, :password_hash, :first_name, :last_name)");
                    
                    // Fetching first_name and last_name from POST, if they exist in the form
                    $first_name = isset($_POST['first_name']) ? htmlspecialchars(trim($_POST['first_name']), ENT_QUOTES, 'UTF-8') : null;
                    $last_name = isset($_POST['last_name']) ? htmlspecialchars(trim($_POST['last_name']), ENT_QUOTES, 'UTF-8') : null;

                    $insertSuccess = $stmt->execute([
                        ':email' => $email,
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

require_once __DIR__ . '/includes/header.php'; // Include header AFTER PHP logic
?>

<div class="min-h-screen flex items-center justify-center bg-gray-100 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 p-10 bg-white rounded-xl shadow-lg z-10">
        <div class="text-center">
            <h2 class="mt-6 text-3xl font-extrabold text-gray-900">
                Sign Up
            </h2>
        </div>

        <?php if (!empty($signup_error)): ?>
            <div class="bg-<?php echo ($signup_success ? 'green' : 'red'); ?>-100 border border-<?php echo ($signup_success ? 'green' : 'red'); ?>-400 text-<?php echo ($signup_success ? 'green' : 'red'); ?>-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold"><?php echo ($signup_success ? 'Success!' : 'Error!'); ?></strong>
                <span class="block sm:inline"><?php echo htmlspecialchars($signup_error); ?></span>
            </div>
        <?php endif; ?>

        <form class="mt-8 space-y-6" action="signup.php" method="POST">
            <!-- CSRF Token -->
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

            <div class="rounded-md shadow-sm -space-y-px">
                <div>
                    <label for="first_name" class="sr-only">First Name</label>
                    <input id="first_name" name="first_name" type="text" autocomplete="given-name"
                           class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm <?php echo isset($errors['first_name']) ? 'border-red-500' : ''; ?>"
                           placeholder="First Name" value="<?php echo isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : ''; ?>">
                    <?php if (isset($errors['first_name'])): ?>
                        <p class="text-red-500 text-xs italic mt-1"><?php echo $errors['first_name']; ?></p>
                    <?php endif; ?>
                </div>
                <div>
                    <label for="last_name" class="sr-only">Last Name</label>
                    <input id="last_name" name="last_name" type="text" autocomplete="family-name"
                           class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm <?php echo isset($errors['last_name']) ? 'border-red-500' : ''; ?>"
                           placeholder="Last Name" value="<?php echo isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : ''; ?>">
                    <?php if (isset($errors['last_name'])): ?>
                        <p class="text-red-500 text-xs italic mt-1"><?php echo $errors['last_name']; ?></p>
                    <?php endif; ?>
                </div>
                <div>
                    <label for="email" class="sr-only">Email address</label>
                    <input id="email" name="email" type="email" autocomplete="email" required
                           class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm <?php echo isset($errors['email']) ? 'border-red-500' : ''; ?>"
                           placeholder="Email address" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    <?php if (isset($errors['email'])): ?>
                        <p class="text-red-500 text-xs italic mt-1"><?php echo $errors['email']; ?></p>
                    <?php endif; ?>
                </div>
                <div>
                    <label for="password" class="sr-only">Password</label>
                    <input id="password" name="password" type="password" autocomplete="new-password" required
                           class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm <?php echo isset($errors['password']) ? 'border-red-500' : ''; ?>"
                           placeholder="Password">
                    <?php if (isset($errors['password'])): ?>
                        <p class="text-red-500 text-xs italic mt-1"><?php echo $errors['password']; ?></p>
                    <?php endif; ?>
                </div>
                <div>
                    <label for="confirm_password" class="sr-only">Confirm Password</label>
                    <input id="confirm_password" name="confirm_password" type="password" autocomplete="new-password" required
                           class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm <?php echo isset($errors['confirm_password']) ? 'border-red-500' : ''; ?>"
                           placeholder="Confirm Password">
                    <?php if (isset($errors['confirm_password'])): ?>
                        <p class="text-red-500 text-xs italic mt-1"><?php echo $errors['confirm_password']; ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <div>
                <button type="submit"
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Sign Up
                </button>
            </div>
        </form>

        <p class="mt-5 text-center text-sm text-gray-600">
            Already have an account?
            <a href="login.php" class="font-medium text-indigo-600 hover:text-indigo-500">
                Log In
            </a>
        </p>
        <p class="mt-3 text-center text-sm text-gray-600">
            <a href="forgot_password.php" class="font-medium text-indigo-600 hover:text-indigo-500">
                Forgot Password?
            </a>
        </p>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

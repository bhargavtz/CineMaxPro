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
                    header("Location: user_dashboard.php"); // Redirect to user dashboard after successful login
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

<div class="min-h-screen flex items-center justify-center bg-gray-100 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 p-10 bg-white rounded-xl shadow-lg z-10">
        <div class="text-center">
            <h2 class="mt-6 text-3xl font-extrabold text-gray-900">
                Log In
            </h2>
        </div>

        <?php if (!empty($login_error)): ?>
            <div class="bg-<?php echo (strpos($login_error, 'successful') !== false ? 'green' : 'red'); ?>-100 border border-<?php echo (strpos($login_error, 'successful') !== false ? 'green' : 'red'); ?>-400 text-<?php echo (strpos($login_error, 'successful') !== false ? 'green' : 'red'); ?>-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold"><?php echo (strpos($login_error, 'successful') !== false ? 'Success!' : 'Error!'); ?></strong>
                <span class="block sm:inline"><?php echo htmlspecialchars($login_error); ?></span>
            </div>
        <?php endif; ?>

        <form class="mt-8 space-y-6" action="login.php" method="POST">
            <!-- CSRF Token -->
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

            <div class="rounded-md shadow-sm -space-y-px">
                <div>
                    <label for="identifier" class="sr-only">Email address</label>
                    <input id="identifier" name="identifier" type="email" autocomplete="email" required autofocus
                           class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm <?php echo isset($errors['identifier']) ? 'border-red-500' : ''; ?>"
                           placeholder="Email address" value="<?php echo isset($_POST['identifier']) ? htmlspecialchars($_POST['identifier']) : ''; ?>">
                    <?php if (isset($errors['identifier'])): ?>
                        <p class="text-red-500 text-xs italic mt-1"><?php echo $errors['identifier']; ?></p>
                    <?php endif; ?>
                </div>
                <div>
                    <label for="password" class="sr-only">Password</label>
                    <input id="password" name="password" type="password" autocomplete="current-password" required
                           class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm <?php echo isset($errors['password']) ? 'border-red-500' : ''; ?>"
                           placeholder="Password">
                    <?php if (isset($errors['password'])): ?>
                        <p class="text-red-500 text-xs italic mt-1"><?php echo $errors['password']; ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input id="remember-me" name="remember-me" type="checkbox"
                           class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                    <label for="remember-me" class="ml-2 block text-sm text-gray-900">
                        Remember me
                    </label>
                </div>

                <div class="text-sm">
                    <a href="forgot_password.php" class="font-medium text-indigo-600 hover:text-indigo-500">
                        Forgot your password?
                    </a>
                </div>
            </div>

            <div>
                <button type="submit"
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Log In
                </button>
            </div>
        </form>

        <p class="mt-5 text-center text-sm text-gray-600">
            Don't have an account?
            <a href="signup.php" class="font-medium text-indigo-600 hover:text-indigo-500">
                Sign Up
            </a>
        </p>
    </div>
</div>


<?php require_once __DIR__ . '/includes/footer.php'; ?>

<?php
// Include config.php first to establish PDO connection
require_once __DIR__ . '/config.php';
// Then include functions.php which depends on the PDO connection
require_once __DIR__ . '/includes/functions.php';
// Now include the header (HTML output)
require_once __DIR__ . '/includes/header.php';

// --- Login Logic ---
$login_error = ''; // Variable to store login error messages

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve username and password from the form
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!empty($username) && !empty($password)) {
        try {
            // Prepare and execute the SQL query to find the staff member by joining users and staff tables
            $stmt = $pdo->prepare("SELECT s.*, u.username, u.password_hash, s.position FROM staff s JOIN users u ON s.user_id = u.user_id WHERE u.username = :username");
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            $staff_data = $stmt->fetch(); // Fetch data from the joined tables

            // Verify the staff member and password
            // Check if staff_data is not false and if the password matches
            if ($staff_data && password_verify($password, $staff_data['password_hash'])) {
                // Password is correct, start session
                session_regenerate_id(true); // Regenerate session ID for security
                $_SESSION['staff_id'] = $staff_data['staff_id']; // Get staff_id from staff table
                $_SESSION['username'] = $staff_data['username']; // Get username from users table
                $_SESSION['user_role'] = $staff_data['position']; // Use 'position' from staff table as role

                // Redirect to the admin dashboard
                header("Location: admin_dashboard.php");
                exit();
            } else {
                $login_error = "Invalid username or password.";
            }
        } catch (PDOException $e) {
            // Log the error and show a generic message
            error_log("Login Error: " . $e->getMessage());
            $login_error = "An error occurred during login. Please try again.";
        }
    } else {
        $login_error = "Username and password are required.";
    }
}
?>

<!-- Login Form -->
<div class="auth-container">
    <h2 class="text-center mb-4">Staff Login</h2>
    <?php if (!empty($login_error)): ?>
        <div class="alert alert-danger" role="alert">
            <?php echo $login_error; ?>
        </div>
    <?php endif; ?>
    <form class="form-signin" action="admin_staff_login.php" method="post">
        <div class="form-floating">
            <input type="text" class="form-control" id="username" name="username" placeholder="Username" required autofocus>
            <label for="username">Username</label>
        </div>
        <div class="form-floating">
            <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
            <label for="password">Password</label>
        </div>

        <button class="w-100 btn btn-lg btn-primary mt-3" type="submit">Sign in</button>
    </form>
    <p class="mt-3 mb-3 text-center">
        <a href="forgot_password.php" class="link-offset-2">Forgot password?</a>
    </p>
    <p class="mt-1 mb-3 text-center">
        <a href="signup.php" class="link-offset-2">Don't have an account? Sign up</a>
    </p>
</div>

<?php
// Include the footer (if you have one, otherwise this part can be omitted or adjusted)
// require_once __DIR__ . '/includes/footer.php';
?>

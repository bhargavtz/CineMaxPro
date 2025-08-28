<![CDATA[<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include database configuration
require_once __DIR__ . '/config.php';

// Include utility functions
require_once __DIR__ . '/includes/functions.php';

// Include the header
require_once __DIR__ . '/includes/header.php';
?>

    <div class="auth-container">
        <h1 class="text-center mb-4">Welcome to CineMaxPro</h1>
        <p class="text-center mb-4">Your gateway to movie magic!</p>
        <div class="d-grid gap-2">
            <a href="login.php" class="btn btn-primary btn-lg">Login</a>
            <a href="signup.php" class="btn btn-outline-secondary btn-lg">Sign Up</a>
        </div>
    </div>

<?php
// Include the footer (assuming a footer.php exists, though not explicitly mentioned in the provided files, it's good practice)
// If footer.php does not exist, this line can be removed or adjusted.
// require_once __DIR__ . '/includes/footer.php';
?>
</body>
</html>

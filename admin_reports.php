<?php
// Include the header which also includes config.php and starts the session
require_once __DIR__ . '/includes/header.php';

// --- Authentication Check ---
// Check if the user is logged in. If not, redirect to the login page.
if (!isset($_SESSION['staff_id']) || empty($_SESSION['staff_id'])) {
    header("Location: admin_staff_login.php");
    exit();
}

// --- Role Check for Access ---
// Define roles that can access reports. Adjust based on your staff positions.
$allowed_roles = ['Manager', 'Admin']; // Roles that can access reports
$current_role = $_SESSION['role'] ?? 'unknown';

if (!in_array($current_role, $allowed_roles)) {
    // If the user is not authorized, redirect to dashboard
    header("Location: admin_dashboard.php");
    exit();
}

?>

<!-- Reports Hub Section -->
<div class="auth-container">
    <h2 class="text-center mb-4">Admin Reports</h2>

    <div class="list-group">
        <a href="revenue_report.php" class="list-group-item list-group-item-action">
            Revenue Reports (Daily, Weekly, Monthly)
        </a>
        <a href="movie_popularity_report.php" class="list-group-item list-group-item-action">
            Movie Popularity Report
        </a>
        <a href="top_users_report.php" class="list-group-item list-group-item-action">
            Top Users Report
        </a>
        <!-- Add links for CSV/PDF export if they are separate pages, or integrate export buttons into report pages -->
    </div>

    <div class="text-center mt-4">
        <a href="admin_dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>
</div>

<?php
// Include the footer (if you have one, otherwise this part can be omitted or adjusted)
// require_once __DIR__ . '/includes/footer.php';
?>

<?php
// Include the header which also includes config.php and starts the session
require_once __DIR__ . '/includes/header.php';

// --- Authentication Check ---
// Check if the user is logged in. If not, redirect to the login page.
if (!isset($_SESSION['staff_id']) || empty($_SESSION['staff_id'])) {
    header("Location: admin_staff_login.php");
    exit();
}

// Get staff details from session
$username = $_SESSION['username'] ?? 'Guest';
// Use the 'position' from the staff table as the role for the dashboard
$role_display = $_SESSION['role'] ?? 'unknown'; // For display purposes

// --- Role-based Navigation ---
// Define navigation links based on the actual staff position
$nav_links = [];

// All staff roles can see basic info and potentially their own profile
$nav_links['Profile'] = '#'; // Placeholder for profile link

// Map staff positions to dashboard roles and define navigation links
// Adjust these mappings based on actual 'position' values in your staff table
$staff_position = $_SESSION['role']; // This is the actual position from the DB

switch ($staff_position) {
    case 'Manager': // Matches the 'position' value from staff table
        $nav_links['View Bookings'] = 'admin_bookings.php'; // Assuming this file will be created
        $nav_links['View Refunds'] = 'admin_refunds.php'; // Assuming this file will be created
        $nav_links['Verify Tickets'] = 'verify_ticket.php'; // Assuming this file will be created
        break;
    case 'Usher': // Matches the 'position' value from staff table
        // Ushers might have limited access, e.g., only ticket verification
        $nav_links['Verify Tickets'] = 'verify_ticket.php';
        // Optionally, they might view bookings but not manage them
        // $nav_links['View Bookings'] = 'admin_bookings.php';
        break;
    case 'Admin': // Assuming 'Admin' is a possible position value
        $nav_links['Manage Staff'] = 'manage_staff.php'; // Assuming this file will be created
        $nav_links['View All Bookings'] = 'admin_bookings.php'; // Assuming this file will be created
        $nav_links['View All Refunds'] = 'admin_refunds.php'; // Assuming this file will be created
        $nav_links['Verify Tickets'] = 'verify_ticket.php'; // Assuming this file will be created
        break;
    // Add other roles/positions as needed, e.g., 'Support', 'Cashier'
    // case 'Support':
    //     $nav_links['View Refunds'] = 'admin_refunds.php';
    //     $nav_links['Manage Bookings'] = 'admin_bookings.php';
    //     break;
    default:
        // Handle unknown positions or provide a generic dashboard
        // For example, if a position is not explicitly handled, show a message or limited options.
        break;
}

?>

<!-- Dashboard Content -->
<div class="auth-container">
    <h2 class="text-center mb-4">Welcome, <?php echo htmlspecialchars($username); ?>!</h2>
    <p class="text-center mb-4">Your role: <?php echo htmlspecialchars($role_display); ?></p>

    <div class="list-group">
        <?php foreach ($nav_links as $text => $href): ?>
            <?php if (!empty($href)): // Only display links that are defined ?>
                <a href="<?php echo htmlspecialchars($href); ?>" class="list-group-item list-group-item-action">
                    <?php echo htmlspecialchars($text); ?>
                </a>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>

    <div class="text-center mt-4">
        <a href="logout.php" class="btn btn-danger">Logout</a>
    </div>
</div>

<?php
// Include the footer (if you have one, otherwise this part can be omitted or adjusted)
// require_once __DIR__ . '/includes/footer.php';
?>

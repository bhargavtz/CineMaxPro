<?php
require_once __DIR__ . '/includes/init.php';
require_once __DIR__ . '/includes/functions.php';

// Ensure user is logged in
if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

// Check if a specific page is requested
$page = $_GET['page'] ?? 'movies_user_view'; // Default to movies_user_view

// Define allowed pages to prevent directory traversal
$allowed_pages = [
    'movies_user_view',
    'booking_flow',
    'cancel_booking',
    'verify_ticket',
    'generate_ticket',
    'profile',
    // Add other user-specific pages here
];

if (!in_array($page, $allowed_pages)) {
    $page = 'movies_user_view'; // Fallback to default if page is not allowed
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-3">
            <div class="list-group">
                <a href="user_dashboard.php?page=movies_user_view" class="list-group-item list-group-item-action <?php echo ($page === 'movies_user_view') ? 'active' : ''; ?>">Movies</a>
                <a href="user_dashboard.php?page=booking_flow" class="list-group-item list-group-item-action <?php echo ($page === 'booking_flow') ? 'active' : ''; ?>">Book Tickets</a>
                <a href="user_dashboard.php?page=cancel_booking" class="list-group-item list-group-item-action <?php echo ($page === 'cancel_booking') ? 'active' : ''; ?>">Cancel Booking</a>
                <a href="user_dashboard.php?page=verify_ticket" class="list-group-item list-group-item-action <?php echo ($page === 'verify_ticket') ? 'active' : ''; ?>">Verify Ticket</a>
                <a href="user_dashboard.php?page=generate_ticket" class="list-group-item list-group-item-action <?php echo ($page === 'generate_ticket') ? 'active' : ''; ?>">Generate Ticket</a>
                <a href="logout.php" class="list-group-item list-group-item-action text-danger">Logout</a>
            </div>
        </div>
        <div class="col-md-9">
            <div class="card">
                <div class="card-body">
                    <?php
                    // Include the requested page content
                    $file_to_include = __DIR__ . '/' . $page . '.php';
                    if (file_exists($file_to_include)) {
                        include $file_to_include;
                    } else {
                        echo '<div class="alert alert-danger">Page not found.</div>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>


<?php require_once __DIR__ . '/includes/footer.php'; ?>

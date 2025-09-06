<?php
require_once __DIR__ . '/includes/init.php';
require_once __DIR__ . '/includes/functions.php';

// Ensure user is logged in and has the 'user' role
require_once __DIR__ . '/includes/auth_functions.php'; // Ensure auth_functions is included for requireUserLogin
requireUserLogin();

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

<div class="container mx-auto px-4 py-8">
    <!-- Sidebar for navigation -->
    <div class="flex flex-col md:flex-row gap-8">
        <div class="w-full md:w-1/4">
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="p-4 bg-indigo-600 text-white">
                    <h2 class="text-xl font-semibold">Menu</h2>
                </div>
                <nav class="p-2">
                    <a href="user_dashboard.php?page=movies_user_view" 
                       class="flex items-center p-3 rounded-lg mb-1 <?php echo ($page === 'movies_user_view') ? 'bg-indigo-100 text-indigo-700' : 'hover:bg-gray-100'; ?>">
                        <i class="fas fa-film mr-3"></i>
                        <span>Movies</span>
                    </a>
                    
                    <div class="p-3 text-gray-600 font-medium border-t">Tickets</div>
                    <a href="user_dashboard.php?page=booking_flow" 
                       class="flex items-center p-3 rounded-lg mb-1 <?php echo ($page === 'booking_flow') ? 'bg-indigo-100 text-indigo-700' : 'hover:bg-gray-100'; ?>">
                        <i class="fas fa-ticket-alt mr-3"></i>
                        <span>Book Tickets</span>
                    </a>
                    <a href="user_dashboard.php?page=generate_ticket" 
                       class="flex items-center p-3 rounded-lg mb-1 <?php echo ($page === 'generate_ticket') ? 'bg-indigo-100 text-indigo-700' : 'hover:bg-gray-100'; ?>">
                        <i class="fas fa-qrcode mr-3"></i>
                        <span>Generate Ticket</span>
                    </a>
                    
                    <div class="p-3 text-gray-600 font-medium border-t">Management</div>
                    <a href="user_dashboard.php?page=cancel_booking" 
                       class="flex items-center p-3 rounded-lg mb-1 <?php echo ($page === 'cancel_booking') ? 'bg-indigo-100 text-indigo-700' : 'hover:bg-gray-100'; ?>">
                        <i class="fas fa-ban mr-3"></i>
                        <span>Cancel Booking</span>
                    </a>
                    <a href="user_dashboard.php?page=verify_ticket" 
                       class="flex items-center p-3 rounded-lg mb-1 <?php echo ($page === 'verify_ticket') ? 'bg-indigo-100 text-indigo-700' : 'hover:bg-gray-100'; ?>">
                        <i class="fas fa-check-circle mr-3"></i>
                        <span>Verify Ticket</span>
                    </a>
                    
                    <div class="p-3 text-gray-600 font-medium border-t">Account</div>
                    <a href="user_dashboard.php?page=profile" 
                       class="flex items-center p-3 rounded-lg mb-1 <?php echo ($page === 'profile') ? 'bg-indigo-100 text-indigo-700' : 'hover:bg-gray-100'; ?>">
                        <i class="fas fa-user mr-3"></i>
                        <span>My Profile</span>
                    </a>
                    <a href="logout.php" 
                       class="flex items-center p-3 rounded-lg mb-1 text-red-600 hover:bg-red-50">
                        <i class="fas fa-sign-out-alt mr-3"></i>
                        <span>Logout</span>
                    </a>
                </nav>
            </div>
        </div>
        
        <!-- Main content area -->
        <div class="w-full md:w-3/4">
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="p-6">
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

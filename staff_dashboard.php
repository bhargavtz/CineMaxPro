<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard</title>
    <!-- Tailwind CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gray-100">
<?php
require_once __DIR__ . '/includes/init.php';
require_once __DIR__ . '/includes/functions.php';

// --- Staff Login Check ---
// Ensure user is logged in and has a staff ID.
// Admins will be redirected to admin_dashboard.php by their login process.
// This check is for any logged-in staff member who might land here.
if (!isset($_SESSION['staff_id']) || $_SESSION['user_role'] === 'admin') {
    // If not logged in as staff, or if they are an admin (who should go to admin dashboard)
    // redirect them to the appropriate login page or dashboard.
    if ($_SESSION['user_role'] === 'admin') {
        header('Location: admin_dashboard.php');
    } else {
        header('Location: admin_login.php'); // Or login.php if staff use general login
    }
    exit;
}

// Get staff details from session
$username = $_SESSION['username'] ?? 'Staff Member';
$role = $_SESSION['user_role'] ?? 'Staff'; // Use the role set during login

$pageTitle = "Staff Dashboard";
?>

<div class="min-h-screen bg-gray-100">
    <!-- Top Navigation Bar -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <span class="text-xl font-semibold text-gray-800">CineMaxPro Staff Panel</span>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-gray-600">Welcome, <?php echo htmlspecialchars($username); ?>! Your role: <?php echo htmlspecialchars($role); ?></span>
                    <a href="logout.php" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-md text-sm font-medium">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <!-- Dashboard Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Placeholder for staff-specific links/cards -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-info-circle text-2xl text-blue-500"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-medium text-gray-900">Staff Information</h3>
                        </div>
                    </div>
                    <div class="mt-4">
                        <p class="text-gray-700">This is your staff dashboard. You can access specific functionalities here.</p>
                        <!-- Add links to staff-specific pages if they exist -->
                        <!-- Example: <a href="staff_bookings.php" class="...">View My Bookings</a> -->
                    </div>
                </div>
            </div>
            
            <!-- Add more staff-specific cards/links as needed -->

        </div>
    </div>
</div>

<footer class="bg-gray-800 text-white py-4 mt-8">
    <div class="max-w-7xl mx-auto px-4 text-center">
        <p>&copy; <?php echo date('Y'); ?> CineMaxPro. All rights reserved.</p>
    </div>
</footer>

</body>
</html>

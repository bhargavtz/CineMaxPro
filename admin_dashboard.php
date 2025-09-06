<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <!-- Tailwind CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gray-100">
<?php
require_once __DIR__ . '/includes/init.php';
require_once __DIR__ . '/includes/functions.php';

// Require admin login before any output
requireAdminLogin();

// Get staff details from session
$staff_id = $_SESSION['staff_id'] ?? null;
if (!$staff_id) {
    // This check might be redundant if requireAdminLogin() is robust, but good for safety
    header('Location: admin_login.php');
    exit;
}

// Fetch staff details from database
try {
    $stmt = $pdo->prepare("
        SELECT s.*, u.username, u.email 
        FROM staff s 
        JOIN users u ON s.user_id = u.user_id 
        WHERE s.staff_id = ?
    ");
    $stmt->execute([$staff_id]);
    $staff = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$staff) {
        // If staff details not found, redirect to login
        header('Location: admin_login.php');
        exit;
    }
    
    $username = $staff['username'];
    // Use the role from the session, which is set during login
    // $_SESSION['role'] is set in auth_functions.php startAdminSession
    // $_SESSION['user_role'] is also set, but $_SESSION['role'] is more specific for admin context
    $role = $_SESSION['role'] ?? $staff['role']; // Fallback to fetched role if session is somehow missing
} catch (PDOException $e) {
    error_log($e->getMessage());
    $username = 'Unknown';
    $role = 'Admin'; // Default to Admin if error occurs, or handle appropriately
}

// Remove the hardcoded role override
// $role = 'Admin'; 

$pageTitle = "Admin Dashboard";
// require_once __DIR__ . '/includes/header.php'; // Removed to avoid conflicting styles

// Define navigation links based on role
$nav_links = [];

// Common links for all staff
$nav_links['View Bookings'] = 'admin_bookings.php';

// Role-specific links
if ($role === 'Admin') {
    $nav_links['Manage Movies'] = 'movies_crud.php';
    $nav_links['Manage Shows'] = 'theater_screen_show_crud.php';
    $nav_links['Manage Users'] = 'admin_users.php';
    $nav_links['View Reports'] = 'admin_reports.php';
    $nav_links['Handle Refunds'] = 'admin_refunds.php';
    $nav_links['Manage Cancellations'] = 'admin_cancellations.php';
    $nav_links['Verify Tickets'] = 'verify_ticket.php';
} elseif ($role === 'Manager') {
    $nav_links['Manage Movies'] = 'movies_crud.php';
    $nav_links['Manage Shows'] = 'theater_screen_show_crud.php';
    $nav_links['View Reports'] = 'admin_reports.php';
}
?>

<div class="min-h-screen bg-gray-100">
    <!-- Top Navigation Bar -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <span class="text-xl font-semibold text-gray-800">CineMaxPro Admin</span>
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
            <?php foreach ($nav_links as $title => $link): ?>
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <?php 
                            $icon = 'calendar';
                            if (strpos($title, 'Movies') !== false) $icon = 'film';
                            elseif (strpos($title, 'Shows') !== false) $icon = 'tv';
                            elseif (strpos($title, 'Users') !== false) $icon = 'users';
                            elseif (strpos($title, 'Reports') !== false) $icon = 'chart-bar';
                            elseif (strpos($title, 'Refunds') !== false) $icon = 'money-bill';
                            ?>
                            <i class="fas fa-<?php echo $icon; ?> text-2xl text-blue-500"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-medium text-gray-900"><?php echo htmlspecialchars($title); ?></h3>
                        </div>
                    </div>
                    <div class="mt-4">
                        <a href="<?php echo htmlspecialchars($link); ?>" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Manage
                            <i class="fas fa-arrow-right ml-2"></i>
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
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

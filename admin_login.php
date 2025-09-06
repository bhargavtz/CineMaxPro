<?php
require_once __DIR__ . '/includes/init.php';

// If already logged in, redirect to dashboard
if (isset($_SESSION['staff_id'])) {
    header('Location: admin_dashboard.php');
    exit;
}

$error = '';

// Process login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    
    // Validate credentials against users and staff tables
    $sql = "SELECT u.*, s.staff_id, s.role 
            FROM users u 
            INNER JOIN staff s ON u.user_id = s.user_id 
            WHERE u.email = ?";
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$email]);
        $staff = $stmt->fetch();

        if ($staff && password_verify($password, $staff['password_hash'])) {
            // Check if the user has the 'admin' role
            if ($staff['role'] === 'admin') {
                // Start admin session
                startAdminSession($staff);
                
                // Redirect to intended page or dashboard
                $redirect = $_SESSION['redirect_after_login'] ?? 'admin_dashboard.php';
                unset($_SESSION['redirect_after_login']);
                header('Location: ' . $redirect);
                exit;
            } else {
                // User is staff, not admin
                $error = "You do not have administrator privileges.";
            }
        } else {
            // Invalid username or password
            $error = "Invalid username or password";
        }
    } catch (PDOException $e) {
        $error = "A system error occurred. Please try again later.";
        error_log("Admin login error: " . $e->getMessage());
    }
}

// Include header
$pageTitle = "Admin Login";
require_once __DIR__ . '/includes/header.php';
?>

<div class="min-h-screen flex items-center justify-center bg-gray-100 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 p-10 bg-white rounded-xl shadow-lg z-10">
        <div class="text-center">
            <h2 class="mt-6 text-3xl font-extrabold text-gray-900">
                Admin Login
            </h2>
        </div>
        <?php if (isset($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <strong class="font-bold">Error!</strong>
                <span class="block sm:inline"><?php echo $error; ?></span>
            </div>
        <?php endif; ?>
        <form class="mt-8 space-y-6" method="POST" action="">
            <div class="rounded-md shadow-sm -space-y-px">
                <div>
                    <label for="email" class="sr-only">Email address</label>
                    <input id="email" name="email" type="email" autocomplete="email" required
                           class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
                           placeholder="Email address">
                </div>
                <div>
                    <label for="password" class="sr-only">Password</label>
                    <input id="password" name="password" type="password" autocomplete="current-password" required
                           class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
                           placeholder="Password">
                </div>
            </div>

            <div>
                <button type="submit"
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Login
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

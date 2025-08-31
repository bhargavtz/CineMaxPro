<?php
require_once __DIR__ . '/includes/init.php';

// If already logged in, redirect to dashboard
if (isset($_SESSION['staff_id'])) {
    header('Location: admin_dashboard.php');
    exit;
}

// Process login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Validate credentials against users and staff tables
    $sql = "SELECT u.*, s.staff_id, s.role 
            FROM users u 
            INNER JOIN staff s ON u.user_id = s.user_id 
            WHERE u.username = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$username]);
    $staff = $stmt->fetch();

    if ($staff && password_verify($password, $staff['password_hash'])) {
        // Set session variables
        $_SESSION['staff_id'] = $staff['staff_id'];
        $_SESSION['user_id'] = $staff['user_id'];
        $_SESSION['username'] = $staff['username'];
        $_SESSION['role'] = $staff['role'];
        
        // Redirect to dashboard
        header('Location: admin_dashboard.php');
        exit;
    } else {
        $error = "Invalid username or password";
    }
}

// Include header
$pageTitle = "Admin Login";
require_once __DIR__ . '/includes/header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="text-center">Admin Login</h3>
                </div>
                <div class="card-body">
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="form-group mb-3">
                            <label for="username">Username</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="form-group mb-3">
                            <label for="password">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block w-100">Login</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

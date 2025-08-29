<?php
require_once __DIR__ . '/includes/init.php';
requireStaffLogin(); // Ensure only logged-in staff can access

// Check if the logged-in staff has the 'Admin' role
if ($_SESSION['role'] !== 'Admin') {
    // Redirect or show an error if not an Admin
    header('Location: admin_dashboard.php');
    exit();
}

require_once __DIR__ . '/includes/header.php';

// Fetch all users from the database
$users = [];
try {
    $stmt = $pdo->query("SELECT user_id, username, email, created_at FROM users ORDER BY created_at DESC");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching users: " . $e->getMessage());
    // Optionally, set an error message to display to the admin
    $error_message = "Could not retrieve user data.";
}
?>

<div class="auth-container">
    <h2 class="text-center mb-4">Manage Users</h2>

    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger" role="alert">
            <?php echo htmlspecialchars($error_message); ?>
        </div>
    <?php endif; ?>

    <?php if (empty($users)): ?>
        <p class="text-center">No users found.</p>
    <?php else: ?>
        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Registered On</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['user_id']); ?></td>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo htmlspecialchars($user['created_at']); ?></td>
                        <td>
                            <!-- Add action links here, e.g., edit, delete, view details -->
                            <a href="edit_user.php?id=<?php echo htmlspecialchars($user['user_id']); ?>" class="btn btn-sm btn-primary">Edit</a>
                            <a href="delete_user.php?id=<?php echo htmlspecialchars($user['user_id']); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <div class="text-center mt-4">
        <a href="admin_dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>
</div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>

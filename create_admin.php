<?php
require_once __DIR__ . '/includes/init.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/header.php';

// Set page title
$pageTitle = "Create Admin Account";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    try {
        // Start transaction
        $pdo->beginTransaction();

        // Insert into users table
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash) VALUES (:username, :email, :password_hash)");
        $stmt->execute([
            ':username' => $username, 
            ':email' => $email, 
            ':password_hash' => $hashed_password
        ]);
        $user_id = $pdo->lastInsertId();

        // Insert into staff table with Admin role
        $stmt = $pdo->prepare("INSERT INTO staff (user_id, role, hire_date) VALUES (:user_id, 'Admin', CURRENT_DATE)");
        $stmt->execute([':user_id' => $user_id]);

        // Commit transaction
        $pdo->commit();
        
        echo '<div class="alert alert-success" role="alert">
                Admin account created successfully!
              </div>';
    } catch (PDOException $e) {
        // Rollback transaction
        $pdo->rollBack();
        
        if ($e->getCode() == 23000 && strpos($e->getMessage(), "username") !== false) {
            echo '<div class="alert alert-danger" role="alert">
                    Username already exists. Please choose a different username.
                  </div>';
        } else {
            echo '<div class="alert alert-danger" role="alert">
                    Error: ' . htmlspecialchars($e->getMessage()) . '
                  </div>';
        }
    }
}
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h3 class="text-center mb-0">Create Admin Account</h3>
                </div>
                <div class="card-body">
                    <form method="POST" class="needs-validation" novalidate>
                        <div class="form-group mb-3">
                            <label for="username">Username</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="form-group mb-3">
                            <label for="email">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="form-group mb-3">
                            <label for="password">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Create Admin Account</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
]]>

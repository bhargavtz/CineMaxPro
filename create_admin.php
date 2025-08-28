<![CDATA[<?php
require_once __DIR__ . '/includes/functions.php'; // Include functions first
require_once __DIR__ . '/includes/header.php';    // Then include header

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING); // Added username
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    $admin_level = $_POST['admin_level'] ?? 'SuperAdmin';

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    try {
        // Insert into users table
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash) VALUES (:username, :email, :password_hash)"); // Added username to INSERT
        $stmt->execute([':username' => $username, ':email' => $email, ':password_hash' => $hashed_password]); // Added username to execute
        $user_id = $pdo->lastInsertId();

        // Insert into admins table
        $stmt = $pdo->prepare("INSERT INTO admins (user_id, admin_level) VALUES (:user_id, :admin_level)");
        $stmt->execute([':user_id' => $user_id, ':admin_level' => $admin_level]);

        echo "Admin account created successfully!";
    } catch (PDOException $e) {
        // Check for duplicate entry error specifically for username
        if ($e->getCode() == 23000 && strpos($e->getMessage(), "username") !== false) {
            echo "Error: Username already exists. Please choose a different username.";
        } else {
            echo "Error: " . $e->getMessage();
        }
    }
}
?>

<form method="POST">
    <input type="text" name="username" placeholder="Username" required>
    <input type="email" name="email" placeholder="Admin Email" required>
    <input type="password" name="password" placeholder="Password" required>
    <input type="text" name="admin_level" placeholder="Admin Level (e.g. SuperAdmin)" required>
    <button type="submit">Create Admin</button>
</form>
]]>

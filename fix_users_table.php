<?php
require_once __DIR__ . '/includes/init.php';

try {
    // Drop the existing users table
    $pdo->exec("DROP TABLE IF EXISTS users");
    
    // Create the users table with proper AUTO_INCREMENT
    $sql = "CREATE TABLE users (
        user_id INT(11) NOT NULL AUTO_INCREMENT,
        email VARCHAR(100) NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        first_name VARCHAR(50) DEFAULT NULL,
        last_name VARCHAR(50) DEFAULT NULL,
        registration_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (user_id),
        UNIQUE KEY email (email)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
    
    $pdo->exec($sql);
    echo "Users table recreated successfully with AUTO_INCREMENT.";
} catch (PDOException $e) {
    error_log("Error fixing users table: " . $e->getMessage());
    echo "Error fixing users table: " . htmlspecialchars($e->getMessage());
}
?>

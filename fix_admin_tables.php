<?php
require_once __DIR__ . '/includes/init.php';

try {
    // Add AUTO_INCREMENT to users table if not exists
    $pdo->exec("ALTER TABLE users MODIFY user_id int(11) NOT NULL AUTO_INCREMENT");
    
    // Add AUTO_INCREMENT to staff table if not exists
    $pdo->exec("ALTER TABLE staff MODIFY staff_id int(11) NOT NULL AUTO_INCREMENT");
    
    // Add necessary indexes if not exist
    $pdo->exec("ALTER TABLE staff ADD PRIMARY KEY IF NOT EXISTS (staff_id)");
    $pdo->exec("ALTER TABLE staff ADD UNIQUE KEY IF NOT EXISTS (user_id)");
    
    echo "Database tables updated successfully.";
} catch (PDOException $e) {
    echo "Error updating tables: " . $e->getMessage();
}
?>

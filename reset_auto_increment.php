<?php
require_once __DIR__ . '/includes/init.php'; // To get $pdo

try {
    $sql = "ALTER TABLE users AUTO_INCREMENT = 2;";
    $pdo->exec($sql);
    echo "AUTO_INCREMENT for users table reset to 2 successfully.";
} catch (PDOException $e) {
    error_log("Error resetting AUTO_INCREMENT: " . $e->getMessage());
    echo "Error resetting AUTO_INCREMENT: " . htmlspecialchars($e->getMessage());
}
?>

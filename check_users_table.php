<?php
require_once __DIR__ . '/includes/init.php';

// Query all user_ids
echo "--- User IDs in 'users' table ---\n";
try {
    $stmt = $pdo->query("SELECT user_id FROM users ORDER BY user_id");
    $users = $stmt->fetchAll(PDO::FETCH_COLUMN);
    if ($users) {
        echo implode("\n", $users);
    } else {
        echo "No users found.\n";
    }
} catch (PDOException $e) {
    echo "Error querying user IDs: " . $e->getMessage() . "\n";
}
echo "\n----------------------------------\n";

// Query AUTO_INCREMENT value
echo "--- AUTO_INCREMENT value for 'users' table ---\n";
try {
    // This query is specific to MySQL/MariaDB
    $stmt = $pdo->query("SHOW TABLE STATUS LIKE 'users'");
    $status = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($status && isset($status['Auto_increment'])) {
        echo "Auto_increment: " . $status['Auto_increment'] . "\n";
    } else {
        echo "Could not retrieve AUTO_INCREMENT status.\n";
    }
} catch (PDOException $e) {
    echo "Error querying AUTO_INCREMENT status: " . $e->getMessage() . "\n";
}
echo "-------------------------------------------\n";
?>

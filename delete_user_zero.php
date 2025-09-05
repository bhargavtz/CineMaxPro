<?php
require_once __DIR__ . '/includes/init.php'; // To get $pdo

try {
    // Delete the user with user_id = 0
    $sql = "DELETE FROM users WHERE user_id = 0;";
    $affected_rows = $pdo->exec($sql);

    if ($affected_rows > 0) {
        echo "Successfully deleted user with user_id = 0. Affected rows: " . $affected_rows;
    } else {
        echo "No user found with user_id = 0, or deletion failed.";
    }
} catch (PDOException $e) {
    error_log("Error deleting user_id 0: " . $e->getMessage());
    echo "Error deleting user_id 0: " . htmlspecialchars($e->getMessage());
}
?>

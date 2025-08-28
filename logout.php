<?php
require_once __DIR__ . '/includes/functions.php'; // Ensure functions are loaded for logoutUser()

// Log the user out
logoutUser();

// Redirect to the login page after logout
// You might want to add a success message to the login page via query parameter
// header("Location: login.php?message=" . urlencode("You have been logged out successfully."));
header("Location: login.php");
exit();
?>

<?php

// --- Database Configuration ---
// IMPORTANT: Replace these with your actual database credentials.
// It is recommended to store sensitive information like passwords outside of version control,
// for example, in environment variables or a separate secure configuration file.
define('DB_HOST', 'localhost');       // Database host (e.g., localhost, 127.0.0.1)
define('DB_NAME', 'cinema');       // Database name - CORRECTED to 'cinema'
define('DB_USER', 'root');            // Database username
define('DB_PASS', '');                // Database password (leave empty if no password)
define('DB_CHARSET', 'utf8mb4');      // Database charset

// --- PDO Connection ---
try {
    // Data Source Name (DSN)
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;

    // PDO options for security and performance
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Throw exceptions on errors
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Fetch associative arrays by default
        PDO::ATTR_EMULATE_PREPARES   => false,                  // Use native prepared statements
    ];

    // Create a PDO instance (database connection)
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);

} catch (\PDOException $e) {
    // In a production environment, you would log this error instead of displaying it.
    // For development, displaying the error can be helpful for debugging.
    // echo "Database Connection Error: " . $e->getMessage();
    // exit; // Terminate script if connection fails

    // For a more user-friendly error message in production:
    error_log("Database Connection Error: " . $e->getMessage());
    die("A database connection error occurred. Please try again later.");
}


// --- Reusable Functions/Classes (Optional but recommended for complex apps) ---
// For a simple config file, the PDO object $pdo is directly available.
// For more complex applications, you might encapsulate this in a class or
// provide functions to get the PDO instance.

// Example: A function to get the PDO instance
/*
function getDbConnection() {
    global $pdo; // Access the globally defined $pdo object
    return $pdo;
}
*/

// You can now use the $pdo object in other PHP files to interact with the database.
// Example usage in another file (e.g., index.php):
/*
require_once 'config.php'; // Include this file

// Fetch data
$stmt = $pdo->query("SELECT * FROM movies");
$movies = $stmt->fetchAll();

foreach ($movies as $movie) {
    echo $movie['title'] . "<br>";
}
*/

?>

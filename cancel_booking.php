<?php
// Include necessary files and functions
require_once 'includes/functions.php'; // Assuming functions.php has DB connection and other helpers
require_once 'config.php'; // Assuming config.php has DB credentials

// Check if the request method is POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get booking ID from the form submission
    $booking_id = $_POST['booking_id'] ?? null;

    // Validate booking ID
    if (empty($booking_id)) {
        echo "Error: Booking ID is required.";
        exit;
    }

    // --- Database Interaction ---
    // Assuming you have a PDO connection established in functions.php or config.php
    global $pdo; // Assuming $pdo is a global variable for the database connection

    try {
        // Prepare a statement to update the booking status
        // We'll add a 'cancellation_status' column to the bookings table
        // Possible statuses: 'pending_cancellation', 'cancelled', 'rejected'
        // Only allow cancellation if the booking is 'Confirmed' and not already processed for cancellation.
        $sql = "UPDATE bookings SET cancellation_status = 'pending_cancellation' WHERE booking_id = :booking_id AND status = 'Confirmed' AND cancellation_status IS NULL";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':booking_id', $booking_id, PDO::PARAM_INT);

        // Execute the statement
        if ($stmt->execute()) {
            // Check if any row was affected
            if ($stmt->rowCount() > 0) {
                echo "Your cancellation request has been submitted and is pending approval.";
                // --- Notification to Admin ---
                // In a real application, you would trigger an email or notification to the admin here.
                // For now, we'll just echo a message.
                echo " An administrator will review your request shortly.";
            } else {
                echo "Error: Booking not found or already processed for cancellation.";
            }
        } else {
            echo "Error: Failed to submit cancellation request. Please try again later.";
        }
    } catch (PDOException $e) {
        // Log the error in a real application
        error_log("Cancellation request failed: " . $e->getMessage());
        echo "An internal error occurred. Please try again later.";
    }
} else {
    // If not a POST request, redirect or show an error
    echo "Invalid request method.";
}
?>

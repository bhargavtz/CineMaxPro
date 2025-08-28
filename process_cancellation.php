<?php
// Include necessary files and functions
require_once 'includes/functions.php'; // Assuming functions.php has DB connection and other helpers
require_once 'config.php'; // Assuming config.php has DB credentials

// Check if the request method is POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get booking ID and action from the form submission
    $booking_id = $_POST['booking_id'] ?? null;
    $action = $_POST['action'] ?? null; // 'approve' or 'reject'

    // Validate input
    if (empty($booking_id) || ($action !== 'approve' && $action !== 'reject')) {
        echo "Invalid request data.";
        exit;
    }

    global $pdo; // Assuming $pdo is a global variable for the database connection

    // Placeholder function for refund processing
    // In a real application, this would interact with a payment gateway.
    function process_refund($payment_id, $amount, $booking_id, $pdo) {
        // Simulate refund process
        // For demonstration, we'll assume it's always successful.
        // In a real scenario, you'd handle API calls, error checking, etc.
        $refund_successful = true; // Assume success for now

        if ($refund_successful) {
            try {
                // Insert into refunds table
                $sql_insert_refund = "INSERT INTO refunds (payment_id, amount, reason, status) VALUES (:payment_id, :amount, :reason, 'Processed')";
                $stmt_insert_refund = $pdo->prepare($sql_insert_refund);
                $stmt_insert_refund->bindParam(':payment_id', $payment_id, PDO::PARAM_INT);
                $stmt_insert_refund->bindParam(':amount', $amount, PDO::PARAM_STR);
                $stmt_insert_refund->bindParam(':reason', $reason = "Booking ID {$booking_id} cancelled", PDO::PARAM_STR);
                $stmt_insert_refund->execute();
                return true;
            } catch (PDOException $e) {
                error_log("Failed to insert refund record: " . $e->getMessage());
                return false; // Refund process failed due to DB error
            }
        }
        return false; // Refund process failed
    }

    try {
        // Start a transaction for atomic updates
        $pdo->beginTransaction();

        // Fetch payment details for the booking
        $sql_get_payment = "SELECT payment_id, amount FROM payments WHERE booking_id = :booking_id AND status = 'Success'";
        $stmt_get_payment = $pdo->prepare($sql_get_payment);
        $stmt_get_payment->bindParam(':booking_id', $booking_id, PDO::PARAM_INT);
        $stmt_get_payment->execute();
        $payment = $stmt_get_payment->fetch(PDO::FETCH_ASSOC);

        if (!$payment) {
            throw new Exception("Payment details not found for booking ID {$booking_id}.");
        }

        $payment_id = $payment['payment_id'];
        $amount = $payment['amount'];

        $new_cancellation_status = '';
        $new_refund_status = NULL;
        $message = '';

        if ($action === 'approve') {
            $new_cancellation_status = 'cancelled';
            // Attempt to process the refund
            if (process_refund($payment_id, $amount, $booking_id, $pdo)) {
                $new_refund_status = 'refunded';
                $message = "Cancellation approved. Refund processed successfully.";
            } else {
                $new_refund_status = 'refund_failed';
                $message = "Cancellation approved, but refund failed. Please check logs.";
                // Consider sending an alert to admin here
            }
        } elseif ($action === 'reject') {
            $new_cancellation_status = 'rejected';
            $new_refund_status = NULL; // No refund if rejected
            $message = "Cancellation rejected.";
        }

        // Prepare statement to update booking status and refund status
        $sql_update_booking = "UPDATE bookings SET cancellation_status = :cancellation_status, refund_status = :refund_status WHERE booking_id = :booking_id AND cancellation_status = 'pending_cancellation'";
        $stmt_update_booking = $pdo->prepare($sql_update_booking);
        $stmt_update_booking->bindParam(':cancellation_status', $new_cancellation_status, PDO::PARAM_STR);
        $stmt_update_booking->bindParam(':refund_status', $new_refund_status, PDO::PARAM_STR);
        $stmt_update_booking->bindParam(':booking_id', $booking_id, PDO::PARAM_INT);

        if ($stmt_update_booking->execute()) {
            if ($stmt_update_booking->rowCount() > 0) {
                // Success message is already set
            } else {
                // This case should ideally be caught by the payment check or previous status check
                throw new Exception("Booking not found or not in pending cancellation state.");
            }
        } else {
            throw new Exception("Failed to update booking status.");
        }

        // Commit the transaction if everything was successful
        $pdo->commit();
        echo $message;

    } catch (Exception $e) {
        // Rollback the transaction if any error occurred
        $pdo->rollBack();
        error_log("Cancellation processing failed: " . $e->getMessage());
        echo "An error occurred while processing the cancellation. Please try again.";
    }
} else {
    // If not a POST request, redirect or show an error
    echo "Invalid request method.";
}
?>

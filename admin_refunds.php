<?php
// Include the header which also includes config.php and starts the session
require_once __DIR__ . '/includes/header.php';

// --- Authentication Check ---
// Check if the user is logged in. If not, redirect to the login page.
if (!isset($_SESSION['staff_id']) || empty($_SESSION['staff_id'])) {
    header("Location: admin_staff_login.php");
    exit();
}

// --- Role Check for Access ---
// Only allow users with 'Manager' or 'Admin' roles to access this page
$allowed_roles = ['Manager', 'Admin']; // Adjust based on your staff positions
if (!in_array($_SESSION['role'], $allowed_roles)) {
    // Optionally redirect to dashboard or show an access denied message
    // For now, let's redirect to dashboard
    header("Location: admin_dashboard.php");
    exit();
}

// --- Refund Management Logic ---
$refund_requests = [];
$error_message = '';
$success_message = '';

// Fetch pending refund requests
try {
    $stmt = $pdo->prepare("
        SELECT
            r.refund_id, r.payment_id, r.amount AS refund_amount, r.reason, r.status AS refund_status,
            p.booking_id, p.amount AS payment_amount, p.payment_method, p.status AS payment_status,
            b.booking_id, b.user_id, b.show_id, b.booking_time, b.status AS booking_status,
            u.username, u.first_name, u.last_name,
            m.title AS movie_title
        FROM refunds r
        JOIN payments p ON r.payment_id = p.payment_id
        JOIN bookings b ON p.booking_id = b.booking_id
        JOIN users u ON b.user_id = u.user_id
        JOIN shows s ON b.show_id = s.show_id
        JOIN movies m ON s.movie_id = m.movie_id
        WHERE r.status = 'Pending'
        ORDER BY r.refund_id ASC
    ");
    $stmt->execute();
    $refund_requests = $stmt->fetchAll();

} catch (PDOException $e) {
    error_log("Refund Request Fetch Error: " . $e->getMessage());
    $error_message = "An error occurred while fetching refund requests. Please try again.";
}

// Handle refund approval/denial
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['approve_refund'])) {
        $refund_id_to_process = $_POST['refund_id'];
        $booking_id_to_update = $_POST['booking_id'];

        try {
            // Start transaction for atomic updates
            $pdo->beginTransaction();

            // Update refund status to 'Processed'
            $update_refund_stmt = $pdo->prepare("UPDATE refunds SET status = 'Processed' WHERE refund_id = :refund_id");
            $update_refund_stmt->bindParam(':refund_id', $refund_id_to_process);
            $update_refund_stmt->execute();

            // Update booking status to 'refunded'
            $update_booking_stmt = $pdo->prepare("UPDATE bookings SET refund_status = 'refunded', status = 'Cancelled' WHERE booking_id = :booking_id");
            $update_booking_stmt->bindParam(':booking_id', $booking_id_to_update);
            $update_booking_stmt->execute();

            $pdo->commit();
            $success_message = "Refund approved successfully!";
            // Refresh the page to show updated status
            header("Location: admin_refunds.php");
            exit();

        } catch (PDOException $e) {
            $pdo->rollBack();
            error_log("Refund Approval Error: " . $e->getMessage());
            $error_message = "An error occurred while approving the refund. Please try again.";
        }
    } elseif (isset($_POST['deny_refund'])) {
        $refund_id_to_process = $_POST['refund_id'];
        $booking_id_to_update = $_POST['booking_id'];

        try {
            // Start transaction for atomic updates
            $pdo->beginTransaction();

            // Update refund status to 'Rejected'
            $update_refund_stmt = $pdo->prepare("UPDATE refunds SET status = 'Rejected' WHERE refund_id = :refund_id");
            $update_refund_stmt->bindParam(':refund_id', $refund_id_to_process);
            $update_refund_stmt->execute();

            // Update booking status to 'refund_rejected'
            $update_booking_stmt = $pdo->prepare("UPDATE bookings SET refund_status = 'refund_rejected' WHERE booking_id = :booking_id");
            $update_booking_stmt->bindParam(':booking_id', $booking_id_to_update);
            $update_booking_stmt->execute();

            $pdo->commit();
            $success_message = "Refund denied successfully!";
            // Refresh the page to show updated status
            header("Location: admin_refunds.php");
            exit();

        } catch (PDOException $e) {
            $pdo->rollBack();
            error_log("Refund Denial Error: " . $e->getMessage());
            $error_message = "An error occurred while denying the refund. Please try again.";
        }
    }
}
?>

<!-- Refund Management Section -->
<div class="auth-container">
    <h2 class="text-center mb-4">Refund Requests</h2>

    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger" role="alert">
            <?php echo $error_message; ?>
        </div>
    <?php endif; ?>
    <?php if (!empty($success_message)): ?>
        <div class="alert alert-success" role="alert">
            <?php echo $success_message; ?>
        </div>
    <?php endif; ?>

    <?php if (empty($refund_requests)): ?>
        <p class="text-center">No pending refund requests.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>User</th>
                        <th>Movie</th>
                        <th>Booking ID</th>
                        <th>Refund Amount</th>
                        <th>Reason</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($refund_requests as $request): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($request['refund_id']); ?></td>
                            <td><?php echo htmlspecialchars($request['username']); ?> (<?php echo htmlspecialchars($request['first_name'] . ' ' . $request['last_name']); ?>)</td>
                            <td><?php echo htmlspecialchars($request['movie_title']); ?></td>
                            <td><?php echo htmlspecialchars($request['booking_id']); ?></td>
                            <td>$<?php echo htmlspecialchars(number_format($request['refund_amount'], 2)); ?></td>
                            <td><?php echo htmlspecialchars($request['reason']); ?></td>
                            <td>
                                <form method="post" action="admin_refunds.php" style="display:inline;">
                                    <input type="hidden" name="refund_id" value="<?php echo $request['refund_id']; ?>">
                                    <input type="hidden" name="booking_id" value="<?php echo $request['booking_id']; ?>">
                                    <button type="submit" name="approve_refund" class="btn btn-success btn-sm">Approve</button>
                                </form>
                                <form method="post" action="admin_refunds.php" style="display:inline;">
                                    <input type="hidden" name="refund_id" value="<?php echo $request['refund_id']; ?>">
                                    <input type="hidden" name="booking_id" value="<?php echo $request['booking_id']; ?>">
                                    <button type="submit" name="deny_refund" class="btn btn-danger btn-sm">Deny</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php
// Include the footer (if you have one, otherwise this part can be omitted or adjusted)
// require_once __DIR__ . '/includes/footer.php';
?>

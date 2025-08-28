<?php
// First include init.php for session and database setup
require_once __DIR__ . '/includes/init.php';

// Require staff login before any output
requireStaffLogin();

// Now include the header (HTML output)
require_once __DIR__ . '/includes/header.php';

// --- Role Check for Access ---
// Define roles that can manage bookings. Adjust based on your staff positions.
// For example, Managers and Admins might approve/cancel, while Cashiers might only view.
$manage_booking_roles = ['Manager', 'Admin']; // Roles that can approve/cancel bookings
$view_booking_roles = ['Manager', 'Admin', 'Usher']; // Roles that can view bookings (changed 'Cashier' to 'Usher' based on schema)

$current_role = $_SESSION['role'] ?? 'unknown';
$can_manage_bookings = in_array($current_role, $manage_booking_roles);
$can_view_bookings = in_array($current_role, $view_booking_roles);

if (!$can_view_bookings) {
    // If the user cannot even view bookings, redirect to dashboard
    redirect('admin_dashboard.php');
}

// --- Booking Management Logic ---
$bookings = [];
$error_message = '';
$success_message = '';

// Fetch bookings
try {
    // Base query to fetch booking details
    $sql = "
        SELECT
            b.booking_id, b.booking_time, b.status AS booking_status, b.cancellation_status, b.refund_status,
            u.username, u.first_name, u.last_name,
            m.title AS movie_title,
            s.start_time,
            t.name AS theater_name, t.location AS theater_location,
            sc.screen_number
        FROM bookings b
        JOIN users u ON b.user_id = u.user_id
        JOIN shows s ON b.show_id = s.show_id
        JOIN movies m ON s.movie_id = m.movie_id
        JOIN screens sc ON s.screen_id = sc.screen_id
        JOIN theaters t ON sc.theater_id = t.theater_id
    ";

    // Add filtering or ordering if needed, e.g., by date, status, etc.
    // For now, let's fetch all bookings and order by booking time.
    $sql .= " ORDER BY b.booking_time DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $bookings = $stmt->fetchAll();

} catch (PDOException $e) {
    error_log("Booking Fetch Error: " . $e->getMessage());
    $error_message = "An error occurred while fetching bookings. Please try again.";
}

// Handle booking approval/cancellation
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $booking_id_to_process = $_POST['booking_id'] ?? null;

    if ($booking_id_to_process) {
        try {
            $pdo->beginTransaction();

            if (isset($_POST['approve_booking'])) {
                // Approve booking: change status to 'Confirmed'
                $update_stmt = $pdo->prepare("UPDATE bookings SET status = 'Confirmed' WHERE booking_id = :booking_id AND status = 'Pending'");
                $update_stmt->bindParam(':booking_id', $booking_id_to_process);
                $update_stmt->execute();

                if ($update_stmt->rowCount() > 0) {
                    $pdo->commit();
                    $success_message = "Booking approved successfully!";
                    header("Location: admin_bookings.php"); // Redirect to refresh
                    exit();
                } else {
                    $pdo->rollBack();
                    $error_message = "Booking could not be approved (it might already be confirmed or cancelled).";
                }

            } elseif (isset($_POST['cancel_booking'])) {
                // Cancel booking: change status to 'Cancelled' and cancellation_status to 'cancelled'
                // Ensure we don't cancel already cancelled bookings or bookings that are pending cancellation
                $update_stmt = $pdo->prepare("UPDATE bookings SET status = 'Cancelled', cancellation_status = 'cancelled' WHERE booking_id = :booking_id AND status = 'Confirmed'");
                $update_stmt->bindParam(':booking_id', $booking_id_to_process);
                $update_stmt->execute();

                if ($update_stmt->rowCount() > 0) {
                    $pdo->commit();
                    $success_message = "Booking cancelled successfully!";
                    header("Location: admin_bookings.php"); // Redirect to refresh
                    exit();
                } else {
                    $pdo->rollBack();
                    $error_message = "Booking could not be cancelled (it might already be cancelled or pending cancellation).";
                }
            }
        } catch (PDOException $e) {
            $pdo->rollBack();
            error_log("Booking Management Error: " . $e->getMessage());
            $error_message = "An error occurred during booking management. Please try again.";
        }
    } else {
        $error_message = "Invalid booking ID provided.";
    }
}
?>

<!-- Booking Management Section -->
<div class="auth-container">
    <h2 class="text-center mb-4">Bookings Management</h2>

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

    <?php if (empty($bookings)): ?>
        <p class="text-center">No bookings found.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Booking ID</th>
                        <th>User</th>
                        <th>Movie</th>
                        <th>Theater</th>
                        <th>Show Time</th>
                        <th>Status</th>
                        <?php if ($can_manage_bookings): ?>
                            <th>Actions</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bookings as $booking): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($booking['booking_id']); ?></td>
                            <td><?php echo htmlspecialchars($booking['username']); ?> (<?php echo htmlspecialchars($booking['first_name'] . ' ' . $booking['last_name']); ?>)</td>
                            <td><?php echo htmlspecialchars($booking['movie_title']); ?></td>
                            <td><?php echo htmlspecialchars($booking['theater_name']); ?> (Screen <?php echo htmlspecialchars($booking['screen_number']); ?>)</td>
                            <td><?php echo htmlspecialchars($booking['start_time']); ?></td>
                            <td>
                                <?php
                                // Display status clearly
                                $status_display = htmlspecialchars($booking['booking_status']);
                                if ($booking['cancellation_status'] === 'cancelled') {
                                    $status_display = 'Cancelled';
                                } elseif ($booking['refund_status'] === 'refunded') {
                                    $status_display = 'Refunded';
                                } elseif ($booking['refund_status'] === 'refund_rejected') {
                                    $status_display = 'Refund Rejected';
                                }
                                echo $status_display;
                                ?>
                            </td>
                            <?php if ($can_manage_bookings): ?>
                                <td>
                                    <?php if ($booking['booking_status'] === 'Pending'): ?>
                                        <form method="post" action="admin_bookings.php" style="display:inline;">
                                            <input type="hidden" name="booking_id" value="<?php echo $booking['booking_id']; ?>">
                                            <button type="submit" name="approve_booking" class="btn btn-success btn-sm">Approve</button>
                                        </form>
                                    <?php endif; ?>
                                    <?php if ($booking['booking_status'] === 'Confirmed'): ?>
                                        <form method="post" action="admin_bookings.php" style="display:inline;">
                                            <input type="hidden" name="booking_id" value="<?php echo $booking['booking_id']; ?>">
                                            <button type="submit" name="cancel_booking" class="btn btn-danger btn-sm">Cancel</button>
                                        </form>
                                    <?php endif; ?>
                                    <?php // Add logic for other statuses if needed ?>
                                </td>
                            <?php endif; ?>
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

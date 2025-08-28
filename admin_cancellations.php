<?php
// Include necessary files and functions
require_once 'includes/functions.php'; // Assuming functions.php has DB connection and other helpers
require_once 'config.php'; // Assuming config.php has DB credentials

// --- Admin Authentication ---
// In a real application, you would have a robust admin authentication system.
// For this example, we'll assume the user is already logged in as an admin.
// You might want to check session variables here.
// Example:
// if (!is_admin_logged_in()) {
//     header("Location: admin_staff_login.php");
//     exit;
// }

// --- Fetch Pending Cancellations ---
global $pdo; // Assuming $pdo is a global variable for the database connection

$pending_cancellations = [];
try {
    // Select bookings that are pending cancellation
    // We'll need to join with other tables (e.g., users, movies, bookings) to get relevant details
    // For simplicity, let's assume a 'bookings' table with at least 'booking_id', 'user_id', 'movie_id', 'showtime_id', 'cancellation_status'
    // And 'users' table with 'user_id', 'name', 'email'
    // And 'movies' table with 'movie_id', 'title'
    // And 'showtimes' table with 'showtime_id', 'showtime_datetime'

    // This SQL query is a placeholder and needs to be adjusted based on your actual schema.
    $sql = "
        SELECT
            b.booking_id,
            u.name AS user_name,
            u.email AS user_email,
            m.title AS movie_title,
            s.showtime_datetime,
            b.booking_date,
            b.cancellation_status,
            b.refund_status
        FROM bookings b
        JOIN users u ON b.user_id = u.user_id
        JOIN movies m ON b.movie_id = m.movie_id
        JOIN showtimes s ON b.showtime_id = s.showtime_id
        WHERE b.cancellation_status = 'pending_cancellation'
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $pending_cancellations = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Error fetching pending cancellations: " . $e->getMessage());
    // Handle error appropriately, maybe display an error message to the admin
    $error_message = "Could not retrieve cancellation requests.";
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Cancellation Requests</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1 { color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: #f2f2f2; }
        .actions form { display: inline-block; margin-left: 5px; }
        .actions button { padding: 5px 10px; cursor: pointer; }
        .approve { background-color: #4CAF50; color: white; border: none; }
        .reject { background-color: #f44336; color: white; border: none; }
        .status-pending { color: orange; }
        .status-approved { color: green; } /* Not used for cancellation status, but good to have */
        .status-rejected { color: red; }
        .status-cancelled { color: green; } /* For cancellation status */
        .status-refunded { color: blue; } /* For refund status */
        .status-refund_failed { color: purple; } /* For refund status */
    </style>
</head>
<body>

    <h1>Pending Cancellation Requests</h1>

    <?php if (isset($error_message)): ?>
        <p style="color: red;"><?php echo $error_message; ?></p>
    <?php elseif (empty($pending_cancellations)): ?>
        <p>No pending cancellation requests at the moment.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Booking ID</th>
                    <th>User Name</th>
                    <th>User Email</th>
                    <th>Movie Title</th>
                    <th>Showtime</th>
                    <th>Booking Date</th>
            <th>Status</th>
            <th>Refund Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($pending_cancellations as $request): ?>
            <tr>
                <td><?php echo htmlspecialchars($request['booking_id']); ?></td>
                <td><?php echo htmlspecialchars($request['user_name']); ?></td>
                <td><?php echo htmlspecialchars($request['user_email']); ?></td>
                <td><?php echo htmlspecialchars($request['movie_title']); ?></td>
                <td><?php echo htmlspecialchars($request['showtime_datetime']); ?></td>
                <td><?php echo htmlspecialchars($request['booking_date']); ?></td>
                <td>
                    <span class="status-<?php echo strtolower($request['cancellation_status']); ?>">
                        <?php echo ucfirst(str_replace('_', ' ', $request['cancellation_status'])); ?>
                    </span>
                </td>
                <td>
                    <?php if (!empty($request['refund_status'])): ?>
                        <span class="status-<?php echo strtolower($request['refund_status']); ?>">
                            <?php echo ucfirst(str_replace('_', ' ', $request['refund_status'])); ?>
                        </span>
                    <?php else: ?>
                        N/A
                    <?php endif; ?>
                </td>
                <td class="actions">
                    <!-- Form for approving cancellation -->
                    <form action="process_cancellation.php" method="POST">
                        <input type="hidden" name="booking_id" value="<?php echo $request['booking_id']; ?>">
                        <input type="hidden" name="action" value="approve">
                        <button type="submit" class="approve">Approve</button>
                    </form>
                    <!-- Form for rejecting cancellation -->
                    <form action="process_cancellation.php" method="POST">
                        <input type="hidden" name="booking_id" value="<?php echo $request['booking_id']; ?>">
                        <input type="hidden" name="action" value="reject">
                        <button type="submit" class="reject">Reject</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

</body>
</html>

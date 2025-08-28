<?php
// Include the header which also includes config.php and starts the session
require_once __DIR__ . '/includes/header.php';

// --- Authentication Check ---
// Check if the user is logged in. If not, redirect to the login page.
if (!isset($_SESSION['staff_id']) || empty($_SESSION['staff_id'])) {
    header("Location: admin_staff_login.php");
    exit();
}

// --- Ticket Verification Logic ---
$ticket_info = null;
$booking_info = null;
$error_message = '';
$success_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ticket_id'])) {
    $ticket_id_to_verify = $_POST['ticket_id'];

    if (!empty($ticket_id_to_verify)) {
        try {
            // Fetch ticket and related booking information
            // We need to join tickets, bookings, shows, movies, users tables to get comprehensive info
            $stmt = $pdo->prepare("
                SELECT
                    t.ticket_id, t.seat_number,
                    b.booking_id, b.user_id, b.show_id, b.booking_time, b.status AS booking_status,
                    u.username, u.first_name, u.last_name,
                    s.start_time, s.end_time, s.price,
                    m.title AS movie_title
                FROM tickets t
                JOIN bookings b ON t.booking_id = b.booking_id
                JOIN users u ON b.user_id = u.user_id
                JOIN shows s ON b.show_id = s.show_id
                JOIN movies m ON s.movie_id = m.movie_id
                WHERE t.ticket_id = :ticket_id
            ");
            $stmt->bindParam(':ticket_id', $ticket_id_to_verify);
            $stmt->execute();
            $ticket_info = $stmt->fetch();

            if ($ticket_info) {
                // Check if the ticket has already been used or is part of a cancelled booking
                // For simplicity, we'll just check the booking status. A more robust system might have a 'used' flag on the ticket.
                if ($ticket_info['booking_status'] === 'Confirmed') {
                    $success_message = "Ticket Verified Successfully!";
                } elseif ($ticket_info['booking_status'] === 'Cancelled') {
                    $error_message = "This ticket belongs to a cancelled booking.";
                } else {
                    $error_message = "Ticket status is not confirmed.";
                }
            } else {
                $error_message = "Ticket ID not found.";
            }

        } catch (PDOException $e) {
            error_log("Ticket Verification Error: " . $e->getMessage());
            $error_message = "An error occurred while verifying the ticket. Please try again.";
        }
    } else {
        $error_message = "Please enter a Ticket ID.";
    }
}
?>

<!-- Ticket Verification Form -->
<div class="auth-container">
    <h2 class="text-center mb-4">Verify Ticket</h2>

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

    <form class="form-signin" action="verify_ticket.php" method="post">
        <div class="form-floating mb-3">
            <input type="text" class="form-control" id="ticket_id" name="ticket_id" placeholder="Enter Ticket ID" required autofocus>
            <label for="ticket_id">Ticket ID</label>
        </div>
        <button class="w-100 btn btn-lg btn-primary" type="submit">Verify</button>
    </form>

    <?php if ($ticket_info): ?>
        <div class="mt-4">
            <h4>Ticket Details:</h4>
            <ul class="list-group">
                <li class="list-group-item"><strong>Ticket ID:</strong> <?php echo htmlspecialchars($ticket_info['ticket_id']); ?></li>
                <li class="list-group-item"><strong>Seat Number:</strong> <?php echo htmlspecialchars($ticket_info['seat_number']); ?></li>
                <li class="list-group-item"><strong>Movie:</strong> <?php echo htmlspecialchars($ticket_info['movie_title']); ?></li>
                <li class="list-group-item"><strong>Booking ID:</strong> <?php echo htmlspecialchars($ticket_info['booking_id']); ?></li>
                <li class="list-group-item"><strong>User:</strong> <?php echo htmlspecialchars($ticket_info['username']); ?> (<?php echo htmlspecialchars($ticket_info['first_name'] . ' ' . $ticket_info['last_name']); ?>)</li>
                <li class="list-group-item"><strong>Show Time:</strong> <?php echo htmlspecialchars($ticket_info['start_time']); ?></li>
                <li class="list-group-item"><strong>Booking Status:</strong> <?php echo htmlspecialchars($ticket_info['booking_status']); ?></li>
                <li class="list-group-item"><strong>Ticket Price:</strong> $<?php echo htmlspecialchars(number_format($ticket_info['price'], 2)); ?></li>
            </ul>
        </div>
    <?php endif; ?>
</div>

<?php
// Include the footer (if you have one, otherwise this part can be omitted or adjusted)
// require_once __DIR__ . '/includes/footer.php';
?>

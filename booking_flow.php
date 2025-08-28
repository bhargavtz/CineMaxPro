<?php
// Include necessary files
require_once 'config.php'; // For database connection
// require_once 'includes/header.php'; // Assuming a user-facing header

// --- Database Connection ---
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
} catch (Exception $e) {
    die("Database connection error: " . $e->getMessage());
}

// --- Booking Flow Logic ---

$show_id = isset($_GET['show_id']) ? intval($_GET['show_id']) : 0;
$selected_seats = isset($_POST['selected_seats']) ? $_POST['selected_seats'] : [];
$movie_details = null;
$show_details = null;
$available_seats = [];
$total_price = 0;
$booking_message = '';
$booking_id = null;

// Fetch movie and show details if show_id is provided
if ($show_id > 0) {
    // Fetch movie details
    $movie_sql = "SELECT m.* FROM movies m JOIN shows s ON m.movie_id = s.movie_id WHERE s.show_id = ?";
    $stmt_movie = $conn->prepare($movie_sql);
    $stmt_movie->bind_param("i", $show_id);
    $stmt_movie->execute();
    $result_movie = $stmt_movie->get_result();
    $movie_details = $result_movie->fetch_assoc();
    $stmt_movie->close();

    // Fetch show details
    $show_sql = "SELECT s.*, sc.screen_number, t.name as theater_name
                 FROM shows s
                 JOIN screens sc ON s.screen_id = sc.screen_id
                 JOIN theaters t ON sc.theater_id = t.theater_id
                 WHERE s.show_id = ?";
    $stmt_show = $conn->prepare($show_sql);
    $stmt_show->bind_param("i", $show_id);
    $stmt_show->execute();
    $result_show = $stmt_show->get_result();
    $show_details = $result_show->fetch_assoc();
    $stmt_show->close();

    // Simulate fetching available seats (for fake implementation)
    // In a real app, you'd query the 'tickets' table to see which seats are booked for this show.
    // For now, let's assume a fixed number of seats per screen and simulate availability.
    if ($show_details) {
        $total_seats_in_screen = $show_details['capacity']; // Assuming capacity is from screens table
        // For simplicity, let's just generate seat numbers A1, A2, ...
        $seat_rows = ceil($total_seats_in_screen / 10); // Example: 10 seats per row
        for ($i = 1; $i <= $total_seats_in_screen; $i++) {
            $row_char = chr(ord('A') + floor(($i - 1) / 10));
            $seat_num = ($i - 1) % 10 + 1;
            $seat_id = $row_char . $seat_num;
            // Simulate some seats being already booked (e.g., first 5 seats)
            $is_booked = ($i <= 5);
            $available_seats[$seat_id] = ['booked' => $is_booked, 'price' => $show_details['price']];
        }
    }
}

// --- Handle Seat Selection and Booking Confirmation ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'confirm_seats') {
    $selected_seats_input = $_POST['selected_seats']; // Array of seat IDs
    $selected_seats = [];
    $total_price = 0;

    if ($show_id > 0 && $show_details) {
        // Validate selected seats against available seats and calculate price
        foreach ($selected_seats_input as $seat_id) {
            if (isset($available_seats[$seat_id]) && !$available_seats[$seat_id]['booked']) {
                $selected_seats[] = $seat_id;
                $total_price += $available_seats[$seat_id]['price'];
            } else {
                // Handle invalid or already booked seat selection
                $booking_message = "Invalid or already booked seat selected: " . htmlspecialchars($seat_id);
                // Clear selected seats if any are invalid
                $selected_seats = [];
                $total_price = 0;
                break; // Stop processing if an invalid seat is found
            }
        }
    } else {
        $booking_message = "Invalid show selected.";
    }

    // If seats are valid and selected, proceed to payment simulation
    if (!empty($selected_seats) && empty($booking_message)) {
        // Store selected seats and total price in session for payment page
        $_SESSION['booking_details'] = [
            'show_id' => $show_id,
            'movie_title' => $movie_details['title'],
            'show_time' => $show_details['start_time'],
            'theater_name' => $show_details['theater_name'],
            'screen_number' => $show_details['screen_number'],
            'selected_seats' => $selected_seats,
            'total_price' => $total_price
        ];
        // Redirect to payment page or display payment form
        header("Location: booking_flow.php?action=payment&show_id=" . $show_id);
        exit();
    }
}

// --- Handle Payment Simulation ---
if (isset($_GET['action']) && $_GET['action'] == 'payment') {
    if (!isset($_SESSION['booking_details'])) {
        // Redirect back if no booking details are found
        header("Location: booking_flow.php?show_id=" . $show_id);
        exit();
    }
    $booking_details = $_SESSION['booking_details'];
    $movie_title = $booking_details['movie_title'];
    $total_price = $booking_details['total_price'];
    $selected_seats = $booking_details['selected_seats'];
}

// --- Handle Payment Success/Failure (Fake) ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'process_payment') {
    if (!isset($_SESSION['booking_details'])) {
        header("Location: booking_flow.php?show_id=" . $show_id);
        exit();
    }

    $booking_details = $_SESSION['booking_details'];
    $payment_method = $_POST['payment_method']; // e.g., 'stripe', 'razorpay', 'paypal'
    $payment_successful = false;

    // --- Fake Payment Simulation ---
    // In a real scenario, you would integrate with payment gateways here.
    // For this fake implementation, we'll simulate success based on a button click.
    // We can add a simple condition, e.g., if a specific button is clicked.
    if (isset($_POST['confirm_payment'])) {
        $payment_successful = true; // Simulate successful payment
        $transaction_id = 'TXN_' . uniqid(); // Fake transaction ID
    } else {
        $booking_message = "Payment process cancelled or failed.";
        // Rollback: In a real app, you'd unmark seats or remove temporary holds.
        // For this fake implementation, we just don't proceed with booking.
    }

    if ($payment_successful) {
        // --- Create Booking Record ---
        $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1; // Assuming user is logged in, default to 1
        $show_id_db = $booking_details['show_id'];
        $total_amount_db = $booking_details['total_price'];
        $status_db = 'Confirmed';

        // Start transaction for atomicity
        $conn->begin_transaction();

        try {
            // Insert into bookings table
            $insert_booking_sql = "INSERT INTO bookings (user_id, show_id, total_amount, status)
                                   VALUES (?, ?, ?, ?)";
            $stmt_booking = $conn->prepare($insert_booking_sql);
            $stmt_booking->bind_param("idss", $user_id, $show_id_db, $total_amount_db, $status_db);
            $stmt_booking->execute();
            $booking_id = $conn->insert_id;
            $stmt_booking->close();

            // Insert into tickets table for each selected seat
            $insert_ticket_sql = "INSERT INTO tickets (booking_id, seat_number) VALUES (?, ?)";
            $stmt_ticket = $conn->prepare($insert_ticket_sql);
            foreach ($booking_details['selected_seats'] as $seat) {
                $stmt_ticket->bind_param("is", $booking_id, $seat);
                $stmt_ticket->execute();
            }
            $stmt_ticket->close();

            // Insert into payments table (optional, but good practice)
            $insert_payment_sql = "INSERT INTO payments (booking_id, amount, payment_method, transaction_id, status)
                                   VALUES (?, ?, ?, ?, ?)";
            $stmt_payment = $conn->prepare($insert_payment_sql);
            $payment_method_db = htmlspecialchars($_POST['payment_method']); // e.g., 'Stripe (Simulated)'
            $payment_status_db = 'Success';
            $stmt_payment->bind_param("idsss", $booking_id, $total_amount_db, $payment_method_db, $transaction_id, $payment_status_db);
            $stmt_payment->execute();
            $stmt_payment->close();

            // Commit transaction
            $conn->commit();
            $booking_message = "Booking confirmed successfully! Your booking ID is: " . $booking_id;

            // Clear session details after successful booking
            unset($_SESSION['booking_details']);

        } catch (mysqli_sql_exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            $booking_message = "Booking failed: " . $e->getMessage();
            // In a real app, you might want to log this error.
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Movie Booking</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .seat {
            width: 40px;
            height: 40px;
            margin: 5px;
            border: 1px solid #ccc;
            display: inline-block;
            text-align: center;
            line-height: 40px;
            cursor: pointer;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        .seat.booked {
            background-color: #dc3545; /* Red for booked */
            color: white;
            cursor: not-allowed;
        }
        .seat.selected {
            background-color: #28a745; /* Green for selected */
            color: white;
        }
        .seat-grid {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            margin-top: 20px;
        }
        .seat-info {
            margin-top: 20px;
            padding: 15px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            background-color: #fdfdfd;
        }
        .payment-options button {
            margin: 5px;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4 text-center">Movie Booking</h1>

        <?php if (!empty($booking_message)): ?>
            <div class="alert alert-<?php echo ($booking_id || (isset($payment_successful) && $payment_successful)) ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
                <?php echo $booking_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if ($show_id > 0 && $movie_details && $show_details): ?>

            <?php if (!isset($_GET['action']) || $_GET['action'] == 'select_seats'): // Seat Selection Page ?>
                <div class="card mb-4">
                    <div class="card-header">
                        Booking Details
                    </div>
                    <div class="card-body">
                        <h3><?php echo htmlspecialchars($movie_details['title']); ?></h3>
                        <p><strong>Genre:</strong> <?php echo htmlspecialchars($movie_details['genre']); ?></p>
                        <p><strong>Show Time:</strong> <?php echo htmlspecialchars($show_details['start_time']); ?></p>
                        <p><strong>Theater:</strong> <?php echo htmlspecialchars($show_details['theater_name']); ?> (Screen <?php echo htmlspecialchars($show_details['screen_number']); ?>)</p>
                        <p><strong>Price per Seat:</strong> $<?php echo htmlspecialchars($show_details['price']); ?></p>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        Select Your Seats
                    </div>
                    <div class="card-body">
                        <form action="booking_flow.php?show_id=<?php echo $show_id; ?>" method="POST" id="seat-selection-form">
                            <input type="hidden" name="action" value="confirm_seats">
                            <div class="seat-grid">
                                <?php foreach ($available_seats as $seat_id => $seat_info): ?>
                                    <div class="seat <?php echo $seat_info['booked'] ? 'booked' : ''; ?>"
                                         data-seat-id="<?php echo $seat_id; ?>"
                                         data-seat-price="<?php echo $seat_info['price']; ?>">
                                        <?php echo $seat_id; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="seat-info text-center mt-3">
                                <p>Selected Seats: <span id="selected-seats-display">None</span></p>
                                <p>Total Price: $<span id="total-price-display">0.00</span></p>
                            </div>
                            <div class="text-center mt-4">
                                <button type="submit" class="btn btn-primary" id="confirm-seats-btn" disabled>Confirm Seats & Proceed to Payment</button>
                            </div>
                            <!-- Hidden input to store selected seats -->
                            <input type="hidden" name="selected_seats[]" id="selected-seats-input">
                        </form>
                    </div>
                </div>

            <?php elseif (isset($_GET['action']) && $_GET['action'] == 'payment'): // Payment Page ?>
                <div class="card mb-4">
                    <div class="card-header">
                        Payment Confirmation
                    </div>
                    <div class="card-body">
                        <h4>Order Summary</h4>
                        <p><strong>Movie:</strong> <?php echo htmlspecialchars($movie_title); ?></p>
                        <p><strong>Selected Seats:</strong> <?php echo implode(', ', $selected_seats); ?></p>
                        <p><strong>Total Amount:</strong> $<span id="final-total-price"><?php echo number_format($total_price, 2); ?></span></p>

                        <hr>

                        <h4>Choose Payment Method (Simulated)</h4>
                        <form action="booking_flow.php?show_id=<?php echo $show_id; ?>" method="POST" class="payment-options">
                            <input type="hidden" name="action" value="process_payment">
                            <input type="hidden" name="selected_seats_for_payment" value="<?php echo htmlspecialchars(implode(',', $selected_seats)); ?>">
                            <input type="hidden" name="total_price_for_payment" value="<?php echo $total_price; ?>">

                            <button type="submit" name="payment_method" value="stripe" class="btn btn-primary">Pay with Stripe (Fake)</button>
                            <button type="submit" name="payment_method" value="razorpay" class="btn btn-success">Pay with Razorpay (Fake)</button>
                            <button type="submit" name="payment_method" value="paypal" class="btn btn-info">Pay with PayPal (Fake)</button>
                            <button type="submit" name="confirm_payment" value="true" class="btn btn-warning ms-2">Simulate Payment Success</button>
                            <a href="booking_flow.php?show_id=<?php echo $show_id; ?>&action=cancel_booking" class="btn btn-secondary ms-2">Cancel Booking</a>
                        </form>
                    </div>
                </div>
            <?php endif; ?>

        <?php else: // If no show_id or details are found ?>
            <div class="alert alert-warning" role="alert">
                Please select a movie and show time first.
                <a href="movies_user_view.php" class="alert-link">Go to Movies</a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const seatGrid = document.querySelector('.seat-grid');
            const selectedSeatsInput = document.getElementById('selected-seats-input');
            const selectedSeatsDisplay = document.getElementById('selected-seats-display');
            const totalPriceDisplay = document.getElementById('total-price-display');
            const confirmSeatsBtn = document.getElementById('confirm-seats-btn');
            let selectedSeats = [];
            let currentTotalPrice = 0;

            if (seatGrid) {
                seatGrid.addEventListener('click', function(event) {
                    const target = event.target;
                    if (target.classList.contains('seat') && !target.classList.contains('booked')) {
                        const seatId = target.dataset.seatId;
                        const seatPrice = parseFloat(target.dataset.seatPrice);

                        if (target.classList.contains('selected')) {
                            // Deselect seat
                            target.classList.remove('selected');
                            selectedSeats = selectedSeats.filter(seat => seat !== seatId);
                            currentTotalPrice -= seatPrice;
                        } else {
                            // Select seat
                            target.classList.add('selected');
                            selectedSeats.push(seatId);
                            currentTotalPrice += seatPrice;
                        }

                        // Update hidden input and display
                        selectedSeatsInput.value = selectedSeats.join(',');
                        selectedSeatsDisplay.textContent = selectedSeats.length > 0 ? selectedSeats.join(', ') : 'None';
                        totalPriceDisplay.textContent = currentTotalPrice.toFixed(2);

                        // Enable confirm button if at least one seat is selected
                        confirmSeatsBtn.disabled = selectedSeats.length === 0;
                    }
                });
            }
        });
    </script>
</body>
</html>

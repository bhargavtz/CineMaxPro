<?php
require_once 'config.php';
require_once 'includes/init.php';

// Ensure user is logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Get booking ID
$booking_id = isset($_GET['booking_id']) ? intval($_GET['booking_id']) : 0;

if (!$booking_id) {
    // Show booking ID input form if none provided
    ?>
    <div class="max-w-2xl mx-auto p-4">
        <h2 class="text-2xl font-bold mb-4">Generate Ticket</h2>
        
        <form action="generate_ticket.php" method="get" class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="booking_id">
                    Booking ID
                </label>
                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                       id="booking_id" name="booking_id" type="number" min="1" required
                       placeholder="Enter your booking ID">
            </div>
            <div class="flex items-center justify-between">
                <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
                        type="submit">
                    Generate Ticket
                </button>
            </div>
        </form>
    </div>
    <?php
    exit;
}

// Fetch booking details
$sql = "SELECT 
            b.booking_id,
            b.booking_time,
            b.total_amount,
            b.status,
            u.email as user_email,
            m.title as movie_title,
            s.start_time,
            s.end_time,
            sc.screen_number,
            t.name as theater_name,
            GROUP_CONCAT(tk.seat_number) as seats
        FROM bookings b
        JOIN users u ON b.user_id = u.user_id
        JOIN shows s ON b.show_id = s.show_id
        JOIN movies m ON s.movie_id = m.movie_id
        JOIN screens sc ON s.screen_id = sc.screen_id
        JOIN theaters t ON sc.theater_id = t.theater_id
        JOIN tickets tk ON b.booking_id = tk.booking_id
        WHERE b.booking_id = ? AND b.user_id = ?
        GROUP BY b.booking_id";

$stmt = $pdo->prepare($sql);
$stmt->execute([$booking_id, $_SESSION['user_id']]);
$booking = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$booking) {
    ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
        <strong class="font-bold">Error:</strong>
        <span class="block sm:inline">Invalid Booking ID or Booking not found.<br>
        Please ensure you have a valid booking ID.</span>
        <p class="mt-2">You can try booking a movie first from the <a href="user_dashboard.php?page=movies_user_view" class="underline">Movies page</a>.</p>
    </div>
    <?php
    exit;
}

// Display ticket in HTML format for now
?>
<div class="max-w-2xl mx-auto p-4">
    <div class="bg-white shadow-lg rounded-lg overflow-hidden">
        <!-- Ticket Header -->
        <div class="bg-blue-600 text-white text-center py-4">
            <h1 class="text-3xl font-bold">CineMaxPro</h1>
            <h2 class="text-xl"><?php echo htmlspecialchars($booking['movie_title']); ?></h2>
        </div>
        
        <!-- Ticket Details -->
        <div class="p-6">
            <div class="grid grid-cols-2 gap-4">
                <div class="text-gray-600">Booking ID:</div>
                <div class="font-bold"><?php echo htmlspecialchars($booking['booking_id']); ?></div>
                
                <div class="text-gray-600">Theater:</div>
                <div class="font-bold"><?php echo htmlspecialchars($booking['theater_name']); ?></div>
                
                <div class="text-gray-600">Screen:</div>
                <div class="font-bold"><?php echo htmlspecialchars($booking['screen_number']); ?></div>
                
                <div class="text-gray-600">Show Time:</div>
                <div class="font-bold"><?php echo date('d M Y h:i A', strtotime($booking['start_time'])); ?></div>
                
                <div class="text-gray-600">Seats:</div>
                <div class="font-bold"><?php echo htmlspecialchars($booking['seats']); ?></div>
                
                <div class="text-gray-600">Amount Paid:</div>
                <div class="font-bold">₹<?php echo number_format($booking['total_amount'], 2); ?></div>
            </div>
            
            <!-- Temporary QR Code Placeholder -->
            <div class="mt-6 p-4 bg-gray-100 text-center rounded">
                <p class="text-gray-600">Booking Reference Code:</p>
                <p class="font-mono text-lg font-bold mt-2"><?php echo strtoupper(substr(md5($booking['booking_id'] . $booking['user_email']), 0, 12)); ?></p>
            </div>
        </div>
        
        <!-- Ticket Footer -->
        <div class="bg-gray-50 px-6 py-4 text-center text-gray-600 text-sm">
            <p>Thank you for choosing CineMaxPro!</p>
            <p>Please show this ticket at the entrance.</p>
            <p class="mt-2">
                <a href="javascript:window.print()" class="inline-flex items-center px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                    </svg>
                    Print Ticket
                </a>
            </p>
        </div>
    </div>
</div>
<style>
@media print {
    body * {
        visibility: hidden;
    }
    .max-w-2xl, .max-w-2xl * {
        visibility: visible;
    }
    .max-w-2xl {
        position: absolute;
        left: 0;
        top: 0;
    }
    a[href="javascript:window.print()"] {
        display: none;
    }
}
</style>

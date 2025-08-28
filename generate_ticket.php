<?php
// Include necessary files
require_once 'config.php'; // For database connection

// --- Library Includes ---
// Attempt to use Dompdf, provide dummy if not found
$dompdf_class_exists = false; // Flag to track if Dompdf class is available

if (!class_exists('Dompdf\Dompdf')) {
    // Dummy Dompdf class if not installed
    class Dompdf {
        public function __construct() {}
        public function loadHtml($html) {}
        public function setPaper($paper, $orientation = 'portrait') {}
        public function render() {}
        public function stream($filename, $options = []) {}
    }
    echo "<p style='color:red;'>Warning: Dompdf library not found. PDF generation will be simulated.</p>";
} else {
    // Use actual Dompdf class
    use Dompdf\Dompdf;
    $dompdf_class_exists = true;
}

// Handle PHP QR Code library
if (!function_exists('QRcode::png')) {
    // Dummy QRcode function if not installed
    class QRcode {
        public static function png($text, $outfile = false, $level = 'L', $size = 3, $margin = 4, $saveandprint = false) {
            // Simulate QR code by displaying text
            echo "<div style='border: 1px solid black; padding: 10px; background-color: #eee;'>QR Code for: <strong>" . htmlspecialchars($text) . "</strong></div>";
            return false; // Indicate no file was generated
        }
    }
    echo "<p style='color:red;'>Warning: PHP QR Code library not found. QR Code will be simulated.</p>";
}

// --- Database Connection ---
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
} catch (Exception $e) {
    die("Database connection error: " . $e->getMessage());
}

// --- Ticket Generation Logic ---

$booking_id = isset($_GET['booking_id']) ? intval($_GET['booking_id']) : 0;
$ticket_data = null;
$qr_code_data = '';

if ($booking_id > 0) {
    // Fetch all necessary data for the ticket
    $ticket_sql = "SELECT
                        b.booking_id,
                        b.booking_time,
                        b.total_amount,
                        b.status AS booking_status,
                        m.title AS movie_title,
                        m.genre AS movie_genre,
                        m.description AS movie_description,
                        s.start_time AS show_start_time,
                        s.end_time AS show_end_time,
                        sc.screen_number,
                        t.name AS theater_name,
                        GROUP_CONCAT(tkt.seat_number SEPARATOR ', ') AS booked_seats
                    FROM bookings b
                    JOIN shows s ON b.show_id = s.show_id
                    JOIN movies m ON s.movie_id = m.movie_id
                    JOIN screens sc ON s.screen_id = sc.screen_id
                    JOIN theaters t ON sc.theater_id = t.theater_id
                    JOIN tickets tkt ON b.booking_id = tkt.booking_id
                    WHERE b.booking_id = ?
                    GROUP BY b.booking_id, b.booking_time, b.total_amount, b.status, m.title, m.genre, m.description, s.start_time, s.end_time, sc.screen_number, t.name";

    $stmt = $conn->prepare($ticket_sql);
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $ticket_data = $result->fetch_assoc();
    $stmt->close();

    if ($ticket_data) {
        // Prepare data for QR code
        $qr_code_data = "Booking ID: " . $ticket_data['booking_id'] . "\n" .
                        "Movie: " . $ticket_data['movie_title'] . "\n" .
                        "Show Time: " . $ticket_data['show_start_time'] . "\n" .
                        "Seats: " . $ticket_data['booked_seats'];
    }
}

// --- PDF Generation ---
if ($ticket_data) {
    // Instantiate Dompdf
    // Use the correct class name based on whether the library is available
    $dompdf = $dompdf_class_exists ? new Dompdf() : new Dompdf();

    // HTML content for the ticket
    $html = '
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>E-Ticket - Booking ID: ' . htmlspecialchars($ticket_data['booking_id']) . '</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; }
            .ticket-container { width: 600px; margin: 20px auto; border: 1px solid #ccc; padding: 20px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
            .ticket-header { text-align: center; margin-bottom: 20px; }
            .ticket-header h1 { margin-bottom: 5px; }
            .ticket-details { display: flex; flex-wrap: wrap; justify-content: space-between; margin-bottom: 20px; }
            .ticket-details section { width: 48%; margin-bottom: 15px; }
            .ticket-details section h4 { margin-bottom: 10px; border-bottom: 1px solid #eee; padding-bottom: 5px; }
            .qr-code { text-align: center; margin-top: 30px; }
            .qr-code img { max-width: 150px; height: auto; border: 1px solid #ddd; }
            .footer { text-align: center; margin-top: 30px; font-size: 0.9em; color: #777; }
            .qr-code-placeholder {
                width: 150px; height: 150px; background-color: #eee; border: 1px solid #ccc;
                display: flex; align-items: center; justify-content: center; margin: 0 auto;
                font-size: 0.9em; color: #555; text-align: center;
            }
        </style>
    </head>
    <body>
        <div class="ticket-container">
            <div class="ticket-header">
                <h1>E-TICKET</h1>
                <h2>' . htmlspecialchars($ticket_data['movie_title']) . '</h2>
            </div>

            <div class="ticket-details">
                <section>
                    <h4>Booking Information</h4>
                    <p><strong>Booking ID:</strong> ' . htmlspecialchars($ticket_data['booking_id']) . '</p>
                    <p><strong>Booking Time:</strong> ' . htmlspecialchars($ticket_data['booking_time']) . '</p>
                    <p><strong>Status:</strong> ' . htmlspecialchars($ticket_data['booking_status']) . '</p>
                </section>

                <section>
                    <h4>Show Information</h4>
                    <p><strong>Show Time:</strong> ' . htmlspecialchars($ticket_data['show_start_time']) . '</p>
                    <p><strong>Theater:</strong> ' . htmlspecialchars($ticket_data['theater_name']) . '</p>
                    <p><strong>Screen:</strong> ' . htmlspecialchars($ticket_data['screen_number']) . '</p>
                </section>

                <section>
                    <h4>Ticket Details</h4>
                    <p><strong>Seats:</strong> ' . htmlspecialchars($ticket_data['booked_seats']) . '</p>
                    <p><strong>Total Amount:</strong> $' . htmlspecialchars(number_format($ticket_data['total_amount'], 2)) . '</p>
                </section>

                <section>
                    <h4>Movie Details</h4>
                    <p><strong>Genre:</strong> ' . htmlspecialchars($ticket_data['movie_genre']) . '</p>
                    <p><strong>Description:</strong> ' . nl2br(htmlspecialchars(substr($ticket_data['movie_description'], 0, 100))) . (strlen($ticket_data['movie_description']) > 100 ? '...' : '') . '</p>
                </section>
            </div>

            <div class="qr-code">
                <h4>Scan for details</h4>
                ';

    // Generate QR code and embed it
    if (function_exists('QRcode::png')) {
        // Generate QR code image data
        ob_start();
        QRcode::png($qr_code_data, false, 'L', 4, 2); // Output directly to buffer
        $qr_code_image_data = ob_get_contents();
        ob_end_clean();

        // Embed QR code image data into HTML using base64
        $qr_code_base64 = base64_encode($qr_code_image_data);
        $html .= '<img src="data:image/png;base64,' . $qr_code_base64 . '" alt="QR Code">';
    } else {
        // Display placeholder if QR code library is not available
        $html .= '<div class="qr-code-placeholder">QR Code Placeholder</div>';
    }

    $html .= '
            </div>

            <div class="footer">
                <p>Thank you for booking with us!</p>
                <p>Please present this ticket at the venue.</p>
            </div>
        </div>
    </body>
    </html>';

    // Load HTML into Dompdf
    $dompdf->loadHtml($html);

    // Set paper size and orientation
    $dompdf->setPaper('A4', 'portrait');

    // Render the HTML as PDF
    $dompdf->render();

    // Output the generated PDF (inline view or download)
    // For download, use 'D'
    $dompdf->stream("e-ticket-booking-" . $booking_id . ".pdf", array("Attachment" => 1));

} else {
    // If booking ID is invalid or not found
    echo "<h1 style='color:red;'>Error: Invalid Booking ID or Booking not found.</h1>";
    echo "<p>Please ensure you have a valid booking ID.</p>";
    echo "<p>You can try booking a movie first from the <a href='movies_user_view.php'>Movies page</a>.</p>";
}

$conn->close();
?>

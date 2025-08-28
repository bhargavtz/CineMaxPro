<?php
// Include the header which also includes config.php and starts the session
require_once __DIR__ . '/includes/header.php';

// --- Authentication and Authorization Check ---
if (!isset($_SESSION['staff_id']) || empty($_SESSION['staff_id'])) {
    header("Location: admin_staff_login.php");
    exit();
}

$allowed_roles = ['Manager', 'Admin'];
$current_role = $_SESSION['role'] ?? 'unknown';

if (!in_array($current_role, $allowed_roles)) {
    header("Location: admin_dashboard.php");
    exit();
}

// --- Export Logic ---
$report_type = $_GET['report'] ?? null;
$format = $_GET['format'] ?? null;
$data = [];
$filename = '';

if (!$report_type || !$format) {
    header("Location: admin_reports.php"); // Redirect if parameters are missing
    exit();
}

try {
    // Fetch data based on report type
    switch ($report_type) {
        case 'revenue':
            // Fetch data for revenue report based on type parameter
            $report_type_detail = $_GET['type'] ?? 'daily'; // Default to daily
            $stmt = null;
            $data_rows = [];

            switch ($report_type_detail) {
                case 'daily':
                    $stmt = $pdo->prepare("
                        SELECT DATE(payment_time) as payment_date, SUM(amount) as total_revenue
                        FROM payments
                        WHERE status = 'Success' AND payment_time >= CURDATE() - INTERVAL 7 DAY
                        GROUP BY DATE(payment_time)
                        ORDER BY payment_date ASC
                    ");
                    $filename = 'revenue_report_daily';
                    break;
                case 'weekly':
                    $stmt = $pdo->prepare("
                        SELECT YEARWEEK(payment_time, 1) as year_week, SUM(amount) as total_revenue
                        FROM payments
                        WHERE status = 'Success' AND payment_time >= CURDATE() - INTERVAL 4 WEEK
                        GROUP BY YEARWEEK(payment_time, 1)
                        ORDER BY year_week ASC
                    ");
                    $filename = 'revenue_report_weekly';
                    break;
                case 'monthly':
                    $stmt = $pdo->prepare("
                        SELECT DATE_FORMAT(payment_time, '%Y-%m') as payment_month, SUM(amount) as total_revenue
                        FROM payments
                        WHERE status = 'Success' AND YEAR(payment_time) = YEAR(CURDATE())
                        GROUP BY DATE_FORMAT(payment_time, '%Y-%m')
                        ORDER BY payment_month ASC
                    ");
                    $filename = 'revenue_report_monthly';
                    break;
                default:
                    header("Location: admin_reports.php");
                    exit();
            }

            if ($stmt) {
                $stmt->execute();
                $data_rows = $stmt->fetchAll();
            }
            $data = $data_rows; // Assign fetched data to $data
            break;

        case 'movie_popularity':
            // Fetch data for movie popularity report
            $stmt = $pdo->prepare("
                SELECT m.title AS movie_title, COUNT(b.booking_id) AS total_bookings
                FROM movies m
                JOIN shows s ON m.movie_id = s.movie_id
                JOIN bookings b ON s.show_id = b.show_id
                WHERE b.status = 'Confirmed'
                GROUP BY m.movie_id, m.title
                ORDER BY total_bookings DESC
                LIMIT 10
            ");
            $stmt->execute();
            $data = $stmt->fetchAll();
            $filename = 'movie_popularity_report';
            break;

        case 'top_users':
            // Fetch data for top users report
            $stmt = $pdo->prepare("
                SELECT u.username, u.first_name, u.last_name, COUNT(b.booking_id) AS total_bookings
                FROM users u
                JOIN bookings b ON u.user_id = b.user_id
                WHERE b.status = 'Confirmed'
                GROUP BY u.user_id, u.username, u.first_name, u.last_name
                ORDER BY total_bookings DESC
                LIMIT 10
            ");
            $stmt->execute();
            $data = $stmt->fetchAll();
            $filename = 'top_users_report';
            break;

        default:
            header("Location: admin_reports.php"); // Unknown report type
            exit();
    }

    // Add .csv or .pdf extension to filename
    $filename .= '.' . $format;

    // Generate output based on format
    if ($format === 'csv') {
        // --- CSV Export ---
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');

        // Write header row
        if (!empty($data)) {
            // Dynamically get keys for header row
            $header_keys = array_keys($data[0]);
            // Special handling for revenue report types to match display names
            if ($report_type === 'revenue') {
                if (isset($_GET['type']) && $_GET['type'] === 'weekly') {
                    $header_keys[array_search('year_week', $header_keys)] = 'Week';
                } elseif (isset($_GET['type']) && $_GET['type'] === 'monthly') {
                    $header_keys[array_search('payment_month', $header_keys)] = 'Month';
                } else {
                    $header_keys[array_search('payment_date', $header_keys)] = 'Date';
                }
                $header_keys[array_search('total_revenue', $header_keys)] = 'Total Revenue';
            } elseif ($report_type === 'movie_popularity') {
                $header_keys[array_search('movie_title', $header_keys)] = 'Movie Title';
                $header_keys[array_search('total_bookings', $header_keys)] = 'Total Bookings';
            } elseif ($report_type === 'top_users') {
                $header_keys[array_search('username', $header_keys)] = 'Username';
                $header_keys[array_search('first_name', $header_keys)] = 'First Name';
                $header_keys[array_search('last_name', $header_keys)] = 'Last Name';
                $header_keys[array_search('total_bookings', $header_keys)] = 'Total Bookings';
            }
            fputcsv($output, $header_keys);
        }

        // Write data rows
        foreach ($data as $row) {
            // Ensure the order of values matches the header keys
            $ordered_row = [];
            foreach ($header_keys as $key => $header_name) {
                // Map original keys to potentially renamed header names if needed, or just use original keys
                // For simplicity, we assume the keys in $row correspond to the original DB column names
                // and we are just renaming them for the header.
                // If the keys in $row are different from the header_keys, this needs adjustment.
                // For now, we'll rely on the order of keys from $data[0] and map them to header_keys.
                // A more robust approach would be to map header names back to original keys.
                // For now, let's assume the order is preserved or keys are directly usable.
                // If $row keys are different from $header_keys, this will fail.
                // Let's re-fetch the keys from the first row to ensure order.
                $current_row_values = [];
                foreach(array_keys($data[0]) as $original_key) {
                    $current_row_values[] = $row[$original_key];
                }
                fputcsv($output, $current_row_values);
            }
        }

        fclose($output);
        exit();

    } elseif ($format === 'pdf') {
        // --- PDF Export ---
        // This requires a PDF generation library like TCPDF or FPDF.
        // For this example, we'll just output a message indicating PDF generation is not fully implemented.
        // In a real application, you would include the library and generate the PDF here.

        header('Content-Type: text/html'); // Or appropriate content type for error message
        echo "<html><body>";
        echo "<p>PDF export for '$report_type' report is not fully implemented yet.</p>";
        echo "<p>Data fetched:</p>";
        echo "<pre>";
        print_r($data); // Display fetched data for debugging
        echo "</pre>";
        echo "<p><a href='admin_reports.php'>Back to Reports Hub</a></p>";
        echo "</body></html>";
        exit();

    } else {
        header("Location: admin_reports.php"); // Unknown format
        exit();
    }

} catch (PDOException $e) {
    error_log("Export Report Error: " . $e->getMessage());
    // Display a generic error message to the user
    header('Content-Type: text/html');
    echo "<html><body>";
    echo "<p>An error occurred during report export. Please try again later.</p>";
    echo "<p><a href='admin_reports.php'>Back to Reports Hub</a></p>";
    echo "</body></html>";
    exit();
}
?>

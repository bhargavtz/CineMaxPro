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

// --- Top Users Report Logic ---
$top_users = [];
$error_message = '';
$chart_data = []; // Data for Chart.js

try {
    // Fetch Top Users (users with the most confirmed bookings)
    $stmt_top_users = $pdo->prepare("
        SELECT
            u.user_id,
            u.username,
            u.first_name,
            u.last_name,
            COUNT(b.booking_id) AS total_bookings
        FROM users u
        JOIN bookings b ON u.user_id = b.user_id
        WHERE b.status = 'Confirmed' -- Only count confirmed bookings
        GROUP BY u.user_id, u.username, u.first_name, u.last_name
        ORDER BY total_bookings DESC
        LIMIT 10 -- Show top 10 users
    ");
    $stmt_top_users->execute();
    $top_users = $stmt_top_users->fetchAll();

    // Prepare data for Chart.js
    $chart_labels = [];
    $chart_values = [];
    foreach ($top_users as $row) {
        $chart_labels[] = htmlspecialchars($row['username'] . ' (' . $row['first_name'] . ' ' . $row['last_name'] . ')');
        $chart_values[] = $row['total_bookings'];
    }
    $chart_data = [
        'labels' => $chart_labels,
        'values' => $chart_values
    ];

} catch (PDOException $e) {
    error_log("Top Users Report Error: " . $e->getMessage());
    $error_message = "An error occurred while fetching top users data. Please try again.";
}
?>

<!-- Top Users Report Section -->
<div class="auth-container">
    <h2 class="text-center mb-4">Top Users Report</h2>

    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger" role="alert">
            <?php echo $error_message; ?>
        </div>
    <?php endif; ?>

    <?php if (empty($top_users)): ?>
        <p class="text-center">No top users data available.</p>
    <?php else: ?>
        <div class="table-responsive mb-5">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>User</th>
                        <th>Total Bookings</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $rank = 1; ?>
                    <?php foreach ($top_users as $row): ?>
                        <tr>
                            <td><?php echo $rank++; ?></td>
                            <td><?php echo htmlspecialchars($row['username']); ?> (<?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?>)</td>
                            <td><?php echo htmlspecialchars($row['total_bookings']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Chart.js Placeholder for Top Users -->
        <div class="chart-container" style="position: relative; height:40vh; width:80vw">
            <canvas id="topUsersChart"></canvas>
        </div>
    <?php endif; ?>

    <!-- Export Options -->
    <div class="text-center mt-4">
        <a href="export_report.php?report=top_users&format=csv" class="btn btn-info me-2">Export Top Users (CSV)</a>
        <a href="export_report.php?report=top_users&format=pdf" class="btn btn-warning me-2">Export Top Users (PDF)</a>
    </div>

    <div class="text-center mt-4">
        <a href="admin_reports.php" class="btn btn-secondary">Back to Reports Hub</a>
    </div>
</div>

<!-- Chart.js Integration -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Data for charts (will be populated by PHP)
    const chartData = <?php echo json_encode($chart_data); ?>;

    // Top Users Chart
    if (chartData.labels && chartData.labels.length > 0) {
        const ctxTopUsers = document.getElementById('topUsersChart').getContext('2d');
        new Chart(ctxTopUsers, {
            type: 'bar', // or 'pie', 'doughnut'
            data: {
                labels: chartData.labels,
                datasets: [{
                    label: 'Total Bookings',
                    data: chartData.values,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.6)', 'rgba(54, 162, 235, 0.6)', 'rgba(255, 206, 86, 0.6)',
                        'rgba(75, 192, 192, 0.6)', 'rgba(153, 102, 255, 0.6)', 'rgba(255, 159, 64, 0.6)',
                        'rgba(199, 199, 199, 0.6)', 'rgba(201, 203, 207, 0.6)', 'rgba(255, 99, 132, 0.6)',
                        'rgba(54, 162, 235, 0.6)'
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)', 'rgba(54, 162, 235, 1)', 'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)', 'rgba(153, 102, 255, 1)', 'rgba(255, 159, 64, 1)',
                        'rgba(199, 199, 199, 1)', 'rgba(201, 203, 207, 1)', 'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
</script>

<?php
// Include the footer (if you have one, otherwise this part can be omitted or adjusted)
// require_once __DIR__ . '/includes/footer.php';
?>

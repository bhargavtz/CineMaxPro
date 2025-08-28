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

// --- Revenue Report Logic ---
$daily_revenue = [];
$weekly_revenue = [];
$monthly_revenue = [];
$error_message = '';
$daily_chart_data = []; // Data for Chart.js - Daily
$weekly_chart_data = []; // Data for Chart.js - Weekly
$monthly_chart_data = []; // Data for Chart.js - Monthly

try {
    // Fetch Daily Revenue
    $stmt_daily = $pdo->prepare("
        SELECT DATE(payment_time) as payment_date, SUM(amount) as total_revenue
        FROM payments
        WHERE status = 'Success' AND payment_time >= CURDATE() - INTERVAL 7 DAY
        GROUP BY DATE(payment_time)
        ORDER BY payment_date ASC
    ");
    $stmt_daily->execute();
    $daily_revenue = $stmt_daily->fetchAll();

    // Fetch Weekly Revenue (sum of payments for each week of the current month)
    $stmt_weekly = $pdo->prepare("
        SELECT YEARWEEK(payment_time, 1) as year_week, SUM(amount) as total_revenue
        FROM payments
        WHERE status = 'Success' AND payment_time >= CURDATE() - INTERVAL 4 WEEK
        GROUP BY YEARWEEK(payment_time, 1)
        ORDER BY year_week ASC
    ");
    $stmt_weekly->execute();
    $weekly_revenue = $stmt_weekly->fetchAll();

    // Fetch Monthly Revenue (for the current year)
    $stmt_monthly = $pdo->prepare("
        SELECT DATE_FORMAT(payment_time, '%Y-%m') as payment_month, SUM(amount) as total_revenue
        FROM payments
        WHERE status = 'Success' AND YEAR(payment_time) = YEAR(CURDATE())
        GROUP BY DATE_FORMAT(payment_time, '%Y-%m')
        ORDER BY payment_month ASC
    ");
    $stmt_monthly->execute();
    $monthly_revenue = $stmt_monthly->fetchAll();

    // Prepare data for Chart.js - Daily Revenue
    $daily_chart_labels = [];
    $daily_chart_values = [];
    foreach ($daily_revenue as $row) {
        $daily_chart_labels[] = date('M d', strtotime($row['payment_date']));
        $daily_chart_values[] = $row['total_revenue'];
    }
    $daily_chart_data = [
        'labels' => $daily_chart_labels,
        'values' => $daily_chart_values
    ];

    // Prepare data for Chart.js - Weekly Revenue
    $weekly_chart_labels = [];
    $weekly_chart_values = [];
    foreach ($weekly_revenue as $row) {
        // Format year_week for better readability, e.g., "2024-W34"
        $year_week_formatted = substr($row['year_week'], 0, 4) . '-W' . substr($row['year_week'], 4);
        $weekly_chart_labels[] = $year_week_formatted;
        $weekly_chart_values[] = $row['total_revenue'];
    }
    $weekly_chart_data = [
        'labels' => $weekly_chart_labels,
        'values' => $weekly_chart_values
    ];

    // Prepare data for Chart.js - Monthly Revenue
    $monthly_chart_labels = [];
    $monthly_chart_values = [];
    foreach ($monthly_revenue as $row) {
        $monthly_chart_labels[] = date('M Y', strtotime($row['payment_month'] . '-01'));
        $monthly_chart_values[] = $row['total_revenue'];
    }
    $monthly_chart_data = [
        'labels' => $monthly_chart_labels,
        'values' => $monthly_chart_values
    ];

} catch (PDOException $e) {
    error_log("Revenue Report Error: " . $e->getMessage());
    $error_message = "An error occurred while fetching revenue data. Please try again.";
}
?>

<!-- Revenue Report Section -->
<div class="auth-container">
    <h2 class="text-center mb-4">Revenue Reports</h2>

    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger" role="alert">
            <?php echo $error_message; ?>
        </div>
    <?php endif; ?>

    <!-- Daily Revenue -->
    <div class="mb-5">
        <h4 class="text-center mb-3">Daily Revenue (Last 7 Days)</h4>
        <?php if (empty($daily_revenue)): ?>
            <p class="text-center">No daily revenue data available.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Total Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($daily_revenue as $row): ?>
                            <tr>
                                <td><?php echo htmlspecialchars(date('Y-m-d', strtotime($row['payment_date']))); ?></td>
                                <td>$<?php echo htmlspecialchars(number_format($row['total_revenue'], 2)); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <!-- Chart.js Placeholder for Daily Revenue -->
            <div class="chart-container" style="position: relative; height:40vh; width:80vw">
                <canvas id="dailyRevenueChart"></canvas>
            </div>
        <?php endif; ?>
    </div>

    <!-- Weekly Revenue -->
    <div class="mb-5">
        <h4 class="text-center mb-3">Weekly Revenue (Last 4 Weeks)</h4>
        <?php if (empty($weekly_revenue)): ?>
            <p class="text-center">No weekly revenue data available.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Week</th>
                            <th>Total Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($weekly_revenue as $row): ?>
                            <tr>
                                <td>Week <?php echo htmlspecialchars(substr($row['year_week'], 0, 4) . '-W' . substr($row['year_week'], 4)); ?></td>
                                <td>$<?php echo htmlspecialchars(number_format($row['total_revenue'], 2)); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <!-- Chart.js Placeholder for Weekly Revenue -->
            <div class="chart-container" style="position: relative; height:40vh; width:80vw">
                <canvas id="weeklyRevenueChart"></canvas>
            </div>
        <?php endif; ?>
    </div>

    <!-- Monthly Revenue -->
    <div class="mb-5">
        <h4 class="text-center mb-3">Monthly Revenue (Current Year)</h4>
        <?php if (empty($monthly_revenue)): ?>
            <p class="text-center">No monthly revenue data available.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Month</th>
                            <th>Total Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($monthly_revenue as $row): ?>
                            <tr>
                                <td><?php echo htmlspecialchars(date('M Y', strtotime($row['payment_month'] . '-01'))); ?></td>
                                <td>$<?php echo htmlspecialchars(number_format($row['total_revenue'], 2)); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <!-- Chart.js Placeholder for Monthly Revenue -->
            <div class="chart-container" style="position: relative; height:40vh; width:80vw">
                <canvas id="monthlyRevenueChart"></canvas>
            </div>
        <?php endif; ?>
    </div>

    <!-- Export Options -->
    <div class="text-center mt-4">
        <!-- CSV Export Buttons -->
        <a href="export_report.php?report=revenue&format=csv&type=daily" class="btn btn-info me-2">Export Daily Revenue (CSV)</a>
        <a href="export_report.php?report=revenue&format=csv&type=weekly" class="btn btn-info me-2">Export Weekly Revenue (CSV)</a>
        <a href="export_report.php?report=revenue&format=csv&type=monthly" class="btn btn-info me-2">Export Monthly Revenue (CSV)</a>
        <br><br>
        <!-- PDF Export Buttons -->
        <a href="export_report.php?report=revenue&format=pdf&type=daily" class="btn btn-warning me-2">Export Daily Revenue (PDF)</a>
        <a href="export_report.php?report=revenue&format=pdf&type=weekly" class="btn btn-warning me-2">Export Weekly Revenue (PDF)</a>
        <a href="export_report.php?report=revenue&format=pdf&type=monthly" class="btn btn-warning me-2">Export Monthly Revenue (PDF)</a>
    </div>

    <div class="text-center mt-4">
        <a href="admin_reports.php" class="btn btn-secondary">Back to Reports Hub</a>
    </div>
</div>

<!-- Chart.js Integration -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Daily Revenue Chart
    const dailyChartData = <?php echo json_encode($daily_chart_data); ?>;
    if (dailyChartData.labels && dailyChartData.labels.length > 0) {
        const ctxDaily = document.getElementById('dailyRevenueChart').getContext('2d');
        new Chart(ctxDaily, {
            type: 'bar',
            data: {
                labels: dailyChartData.labels,
                datasets: [{
                    label: 'Daily Revenue ($)',
                    data: dailyChartData.values,
                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                    borderColor: 'rgba(54, 162, 235, 1)',
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

    // Weekly Revenue Chart
    const weeklyChartData = <?php echo json_encode($weekly_chart_data); ?>;
    if (weeklyChartData.labels && weeklyChartData.labels.length > 0) {
        const ctxWeekly = document.getElementById('weeklyRevenueChart').getContext('2d');
        new Chart(ctxWeekly, {
            type: 'bar',
            data: {
                labels: weeklyChartData.labels,
                datasets: [{
                    label: 'Weekly Revenue ($)',
                    data: weeklyChartData.values,
                    backgroundColor: 'rgba(255, 159, 64, 0.6)',
                    borderColor: 'rgba(255, 159, 64, 1)',
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

    // Monthly Revenue Chart
    const monthlyChartData = <?php echo json_encode($monthly_chart_data); ?>;
    if (monthlyChartData.labels && monthlyChartData.labels.length > 0) {
        const ctxMonthly = document.getElementById('monthlyRevenueChart').getContext('2d');
        new Chart(ctxMonthly, {
            type: 'bar',
            data: {
                labels: monthlyChartData.labels,
                datasets: [{
                    label: 'Monthly Revenue ($)',
                    data: monthlyChartData.values,
                    backgroundColor: 'rgba(75, 192, 192, 0.6)',
                    borderColor: 'rgba(75, 192, 192, 1)',
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

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

// --- Movie Popularity Report Logic ---
$movie_popularity = [];
$error_message = '';
$chart_data = []; // Data for Chart.js

try {
    // Fetch Movie Popularity (most booked movies)
    // We'll count bookings per movie and order by the count descending.
    $stmt_popularity = $pdo->prepare("
        SELECT
            m.title AS movie_title,
            COUNT(b.booking_id) AS total_bookings
        FROM movies m
        JOIN shows s ON m.movie_id = s.movie_id
        JOIN bookings b ON s.show_id = b.show_id
        WHERE b.status = 'Confirmed' -- Only count confirmed bookings
        GROUP BY m.movie_id, m.title
        ORDER BY total_bookings DESC
        LIMIT 10 -- Show top 10 movies
    ");
    $stmt_popularity->execute();
    $movie_popularity = $stmt_popularity->fetchAll();

    // Prepare data for Chart.js
    $chart_labels = [];
    $chart_values = [];
    foreach ($movie_popularity as $row) {
        $chart_labels[] = htmlspecialchars($row['movie_title']);
        $chart_values[] = $row['total_bookings'];
    }
    $chart_data = [
        'labels' => $chart_labels,
        'values' => $chart_values
    ];

} catch (PDOException $e) {
    error_log("Movie Popularity Report Error: " . $e->getMessage());
    $error_message = "An error occurred while fetching movie popularity data. Please try again.";
}
?>

<!-- Movie Popularity Report Section -->
<div class="auth-container">
    <h2 class="text-center mb-4">Movie Popularity Report</h2>

    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger" role="alert">
            <?php echo $error_message; ?>
        </div>
    <?php endif; ?>

    <?php if (empty($movie_popularity)): ?>
        <p class="text-center">No movie popularity data available.</p>
    <?php else: ?>
        <div class="table-responsive mb-5">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>Movie Title</th>
                        <th>Total Bookings</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $rank = 1; ?>
                    <?php foreach ($movie_popularity as $row): ?>
                        <tr>
                            <td><?php echo $rank++; ?></td>
                            <td><?php echo htmlspecialchars($row['movie_title']); ?></td>
                            <td><?php echo htmlspecialchars($row['total_bookings']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Chart.js Placeholder for Movie Popularity -->
        <div class="chart-container" style="position: relative; height:40vh; width:80vw">
            <canvas id="moviePopularityChart"></canvas>
        </div>
    <?php endif; ?>

    <!-- Export Options -->
    <div class="text-center mt-4">
        <a href="export_report.php?report=movie_popularity&format=csv" class="btn btn-info me-2">Export Movie Popularity (CSV)</a>
        <a href="export_report.php?report=movie_popularity&format=pdf" class="btn btn-warning me-2">Export Movie Popularity (PDF)</a>
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

    // Movie Popularity Chart
    if (chartData.labels && chartData.labels.length > 0) {
        const ctxPopularity = document.getElementById('moviePopularityChart').getContext('2d');
        new Chart(ctxPopularity, {
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

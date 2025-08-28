<?php
require_once __DIR__ . '/includes/functions.php'; // Include functions first
require_once __DIR__ . '/includes/header.php';    // Then include header

// --- Helper: Store seat layout as JSON ---
function seatLayoutToJson($rows, $cols, $seat_types) {
    $layout = [];
    for ($r = 1; $r <= $rows; $r++) {
        for ($c = 1; $c <= $cols; $c++) {
            $seat_num = chr(64 + $r) . $c; // e.g., A1, B2
            $layout[$seat_num] = $seat_types[$r][$c] ?? 'Regular';
        }
    }
    return json_encode($layout);
}

// --- CRUD for Theaters ---
if (isset($_POST['add_theater'])) {
    $name = trim($_POST['theater_name'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $capacity = intval($_POST['capacity'] ?? 0);
    $stmt = $pdo->prepare("INSERT INTO theaters (name, location, capacity) VALUES (?, ?, ?)");
    $stmt->execute([$name, $location, $capacity]);
}
if (isset($_GET['delete_theater'])) {
    $stmt = $pdo->prepare("DELETE FROM theaters WHERE theater_id=?");
    $stmt->execute([intval($_GET['delete_theater'])]);
}
$theaters = $pdo->query("SELECT * FROM theaters ORDER BY theater_id DESC")->fetchAll(PDO::FETCH_ASSOC);

// --- CRUD for Screens ---
if (isset($_POST['add_screen'])) {
    $theater_id = intval($_POST['theater_id'] ?? 0);
    $screen_number = intval($_POST['screen_number'] ?? 1);
    $capacity = intval($_POST['screen_capacity'] ?? 0);
    $screen_type = trim($_POST['screen_type'] ?? '2D');
    $rows = intval($_POST['rows'] ?? 5);
    $cols = intval($_POST['cols'] ?? 10);
    $seat_types = $_POST['seat_types'] ?? [];
    $seat_layout = seatLayoutToJson($rows, $cols, $seat_types);
    $stmt = $pdo->prepare("INSERT INTO screens (theater_id, screen_number, capacity, screen_type, seat_layout) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$theater_id, $screen_number, $capacity, $screen_type, $seat_layout]);
}
if (isset($_GET['delete_screen'])) {
    $stmt = $pdo->prepare("DELETE FROM screens WHERE screen_id=?");
    $stmt->execute([intval($_GET['delete_screen'])]);
}
$screens = $pdo->query("SELECT s.*, t.name AS theater_name FROM screens s JOIN theaters t ON s.theater_id = t.theater_id ORDER BY s.screen_id DESC")->fetchAll(PDO::FETCH_ASSOC);

// --- CRUD for Shows ---
if (isset($_POST['add_show'])) {
    $screen_id = intval($_POST['screen_id'] ?? 0);
    $movie_id = intval($_POST['movie_id'] ?? 0);
    $start_time = $_POST['start_time'] ?? '';
    $end_time = $_POST['end_time'] ?? '';
    $price = floatval($_POST['price'] ?? 0);
    $stmt = $pdo->prepare("INSERT INTO shows (screen_id, movie_id, start_time, end_time, price) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$screen_id, $movie_id, $start_time, $end_time, $price]);
}
if (isset($_GET['delete_show'])) {
    $stmt = $pdo->prepare("DELETE FROM shows WHERE show_id=?");
    $stmt->execute([intval($_GET['delete_show'])]);
}
$shows = $pdo->query("SELECT sh.*, m.title AS movie_title, s.screen_number, t.name AS theater_name FROM shows sh JOIN movies m ON sh.movie_id = m.movie_id JOIN screens s ON sh.screen_id = s.screen_id JOIN theaters t ON s.theater_id = t.theater_id ORDER BY sh.show_id DESC")->fetchAll(PDO::FETCH_ASSOC);
$movies = $pdo->query("SELECT movie_id, title FROM movies ORDER BY title")->fetchAll(PDO::FETCH_ASSOC);

?>
<div class="container py-4">
    <h2 class="mb-4">Manage Theaters</h2>
    <form method="POST" class="mb-3">
        <div class="row g-2">
            <div class="col-md-3"><input type="text" name="theater_name" class="form-control" placeholder="Theater Name" required></div>
            <div class="col-md-3"><input type="text" name="location" class="form-control" placeholder="Location" required></div>
            <div class="col-md-2"><input type="number" name="capacity" class="form-control" placeholder="Capacity" required></div>
            <div class="col-md-2"><button type="submit" name="add_theater" class="btn btn-primary">Add Theater</button></div>
        </div>
    </form>
    <table class="table table-bordered"><thead><tr><th>ID</th><th>Name</th><th>Location</th><th>Capacity</th><th>Actions</th></tr></thead><tbody>
        <?php foreach ($theaters as $th): ?>
        <tr><td><?= $th['theater_id'] ?></td><td><?= htmlspecialchars($th['name']) ?></td><td><?= htmlspecialchars($th['location']) ?></td><td><?= $th['capacity'] ?></td>
            <td><a href="?delete_theater=<?= $th['theater_id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete theater?')">Delete</a></td></tr>
        <?php endforeach; ?></tbody></table>

    <h2 class="mb-4 mt-5">Manage Screens</h2>
    <form method="POST" class="mb-3">
        <div class="row g-2">
            <div class="col-md-2">
                <select name="theater_id" class="form-select" required>
                    <option value="">Select Theater</option>
                    <?php foreach ($theaters as $th): ?><option value="<?= $th['theater_id'] ?>"><?= htmlspecialchars($th['name']) ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-1"><input type="number" name="screen_number" class="form-control" placeholder="Screen #" required></div>
            <div class="col-md-1"><input type="number" name="screen_capacity" class="form-control" placeholder="Capacity" required></div>
            <div class="col-md-2"><input type="text" name="screen_type" class="form-control" placeholder="Type (2D/3D)" required></div>
            <div class="col-md-1"><input type="number" name="rows" class="form-control" placeholder="Rows" required></div>
            <div class="col-md-1"><input type="number" name="cols" class="form-control" placeholder="Cols" required></div>
            <div class="col-md-2"><input type="text" name="seat_types[1][1]" class="form-control" placeholder="Seat Types (A1:VIP,...)" ></div>
            <div class="col-md-2"><button type="submit" name="add_screen" class="btn btn-primary">Add Screen</button></div>
        </div>
        <small>For seat types, use format: seat_types[row][col]=Type (e.g. seat_types[1][1]=VIP)</small>
    </form>
    <table class="table table-bordered"><thead><tr><th>ID</th><th>Theater</th><th>Screen #</th><th>Type</th><th>Capacity</th><th>Actions</th></tr></thead><tbody>
        <?php foreach ($screens as $sc): ?>
        <tr><td><?= $sc['screen_id'] ?></td><td><?= htmlspecialchars($sc['theater_name']) ?></td><td><?= $sc['screen_number'] ?></td><td><?= htmlspecialchars($sc['screen_type']) ?></td><td><?= $sc['capacity'] ?></td>
            <td><a href="?delete_screen=<?= $sc['screen_id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete screen?')">Delete</a></td></tr>
        <?php endforeach; ?></tbody></table>

    <h2 class="mb-4 mt-5">Schedule Shows</h2>
    <form method="POST" class="mb-3">
        <div class="row g-2">
            <div class="col-md-2">
                <select name="screen_id" class="form-select" required>
                    <option value="">Select Screen</option>
                    <?php foreach ($screens as $sc): ?><option value="<?= $sc['screen_id'] ?>">Screen <?= $sc['screen_number'] ?> (<?= htmlspecialchars($sc['theater_name']) ?>)</option><?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select name="movie_id" class="form-select" required>
                    <option value="">Select Movie</option>
                    <?php foreach ($movies as $mv): ?><option value="<?= $mv['movie_id'] ?>"><?= htmlspecialchars($mv['title']) ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2"><input type="datetime-local" name="start_time" class="form-control" required></div>
            <div class="col-md-2"><input type="datetime-local" name="end_time" class="form-control" required></div>
            <div class="col-md-2"><input type="number" step="0.01" name="price" class="form-control" placeholder="Price" required></div>
            <div class="col-md-2"><button type="submit" name="add_show" class="btn btn-primary">Add Show</button></div>
        </div>
    </form>
    <table class="table table-bordered"><thead><tr><th>ID</th><th>Movie</th><th>Theater</th><th>Screen #</th><th>Start</th><th>End</th><th>Price</th><th>Actions</th></tr></thead><tbody>
        <?php foreach ($shows as $sh): ?>
        <tr><td><?= $sh['show_id'] ?></td><td><?= htmlspecialchars($sh['movie_title']) ?></td><td><?= htmlspecialchars($sh['theater_name']) ?></td><td><?= $sh['screen_number'] ?></td><td><?= $sh['start_time'] ?></td><td><?= $sh['end_time'] ?></td><td><?= $sh['price'] ?></td>
            <td><a href="?delete_show=<?= $sh['show_id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete show?')">Delete</a></td></tr>
        <?php endforeach; ?></tbody></table>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>

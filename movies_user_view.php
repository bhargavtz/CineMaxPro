<?php
require_once __DIR__ . '/includes/functions.php'; // Include functions first
require_once __DIR__ . '/includes/header.php';    // Then include header

// --- Search & Filter ---
$search = trim($_GET['search'] ?? '');
$genre = trim($_GET['genre'] ?? '');
$language = trim($_GET['language'] ?? '');
$where = [];
$params = [];
if ($search) { $where[] = "title LIKE ?"; $params[] = "%$search%"; }
if ($genre) { $where[] = "genre = ?"; $params[] = $genre; }
if ($language) { $where[] = "language = ?"; $params[] = $language; }
$where_sql = $where ? "WHERE " . implode(' AND ', $where) : "";

// --- Fetch Movies ---
$now_sql = "SELECT * FROM movies $where_sql AND release_date <= CURDATE() ORDER BY release_date DESC";
$upcoming_sql = "SELECT * FROM movies $where_sql AND release_date > CURDATE() ORDER BY release_date ASC";
$now_movies = $pdo->prepare($now_sql); $now_movies->execute($params); $now_movies = $now_movies->fetchAll(PDO::FETCH_ASSOC);
$upcoming_movies = $pdo->prepare($upcoming_sql); $upcoming_movies->execute($params); $upcoming_movies = $upcoming_movies->fetchAll(PDO::FETCH_ASSOC);

// --- Genre/Language options ---
$genres = $pdo->query("SELECT DISTINCT genre FROM movies WHERE genre IS NOT NULL AND genre != ''")->fetchAll(PDO::FETCH_COLUMN);
$languages = $pdo->query("SELECT DISTINCT language FROM movies WHERE language IS NOT NULL AND language != ''")->fetchAll(PDO::FETCH_COLUMN);

// --- Movie Details & Shows ---
$selected_movie = null;
$shows = [];
if (isset($_GET['movie_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM movies WHERE movie_id=?");
    $stmt->execute([intval($_GET['movie_id'])]);
    $selected_movie = $stmt->fetch(PDO::FETCH_ASSOC);
    $shows = $pdo->prepare("SELECT sh.*, s.screen_number, t.name AS theater_name FROM shows sh JOIN screens s ON sh.screen_id = s.screen_id JOIN theaters t ON s.theater_id = t.theater_id WHERE sh.movie_id=? AND sh.start_time >= NOW() ORDER BY sh.start_time ASC");
    $shows->execute([intval($_GET['movie_id'])]);
    $shows = $shows->fetchAll(PDO::FETCH_ASSOC);
}

// --- Seat Layout for Show ---
$seat_layout = [];
if (isset($_GET['show_id'])) {
    $stmt = $pdo->prepare("SELECT s.seat_layout FROM shows sh JOIN screens s ON sh.screen_id = s.screen_id WHERE sh.show_id=?");
    $stmt->execute([intval($_GET['show_id'])]);
    $seat_layout = json_decode($stmt->fetchColumn(), true) ?? [];
}
?>
<div class="container py-4">
    <h2 class="mb-4">Now Showing</h2>
    <form method="GET" class="row g-2 mb-4">
        <div class="col-md-4"><input type="text" name="search" class="form-control" placeholder="Search by title" value="<?= htmlspecialchars($search) ?>"></div>
        <div class="col-md-3">
            <select name="genre" class="form-select"><option value="">Genre</option><?php foreach ($genres as $g): ?><option value="<?= htmlspecialchars($g) ?>" <?= $genre==$g?'selected':'' ?>><?= htmlspecialchars($g) ?></option><?php endforeach; ?></select>
        </div>
        <div class="col-md-3">
            <select name="language" class="form-select"><option value="">Language</option><?php foreach ($languages as $l): ?><option value="<?= htmlspecialchars($l) ?>" <?= $language==$l?'selected':'' ?>><?= htmlspecialchars($l) ?></option><?php endforeach; ?></select>
        </div>
        <div class="col-md-2"><button type="submit" class="btn btn-primary w-100">Search</button></div>
    </form>
    <div class="row g-3 mb-5">
        <?php foreach ($now_movies as $mv): ?>
        <div class="col-md-3">
            <div class="card h-100">
                <?php if (!empty($mv['poster'])): ?><img src="<?= $mv['poster'] ?>" class="card-img-top" alt="Poster"><?php endif; ?>
                <div class="card-body">
                    <h5 class="card-title"><?= htmlspecialchars($mv['title']) ?></h5>
                    <p class="card-text">Genre: <?= htmlspecialchars($mv['genre']) ?><br>Language: <?= htmlspecialchars($mv['language']) ?></p>
                    <a href="?movie_id=<?= $mv['movie_id'] ?>" class="btn btn-outline-primary">View Details</a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <h2 class="mb-4">Upcoming Movies</h2>
    <div class="row g-3 mb-5">
        <?php foreach ($upcoming_movies as $mv): ?>
        <div class="col-md-3">
            <div class="card h-100">
                <?php if (!empty($mv['poster'])): ?><img src="<?= $mv['poster'] ?>" class="card-img-top" alt="Poster"><?php endif; ?>
                <div class="card-body">
                    <h5 class="card-title"><?= htmlspecialchars($mv['title']) ?></h5>
                    <p class="card-text">Genre: <?= htmlspecialchars($mv['genre']) ?><br>Language: <?= htmlspecialchars($mv['language']) ?></p>
                    <a href="?movie_id=<?= $mv['movie_id'] ?>" class="btn btn-outline-primary">View Details</a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php if ($selected_movie): ?>
        <h2 class="mb-4">Movie Details</h2>
        <div class="card mb-4">
            <div class="row g-0">
                <div class="col-md-3">
                    <?php if (!empty($selected_movie['poster'])): ?><img src="<?= $selected_movie['poster'] ?>" class="img-fluid rounded-start" alt="Poster"><?php endif; ?>
                </div>
                <div class="col-md-9">
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($selected_movie['title']) ?></h5>
                        <p class="card-text">Genre: <?= htmlspecialchars($selected_movie['genre']) ?><br>Language: <?= htmlspecialchars($selected_movie['language']) ?><br>Cast: <?= htmlspecialchars($selected_movie['cast']) ?><br>Age Rating: <?= htmlspecialchars($selected_movie['age_rating']) ?><br>Description: <?= htmlspecialchars($selected_movie['description']) ?></p>
                        <?php if (!empty($selected_movie['trailer_link'])): ?><a href="<?= $selected_movie['trailer_link'] ?>" target="_blank" class="btn btn-info">Watch Trailer</a><?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <h3 class="mb-3">Available Shows</h3>
        <table class="table table-bordered mb-4"><thead><tr><th>Theater</th><th>Screen</th><th>Start</th><th>End</th><th>Price</th><th>Book</th></tr></thead><tbody>
            <?php foreach ($shows as $sh): ?>
            <tr>
                <td><?= htmlspecialchars($sh['theater_name']) ?></td>
                <td><?= $sh['screen_number'] ?></td>
                <td><?= $sh['start_time'] ?></td>
                <td><?= $sh['end_time'] ?></td>
                <td>₹<?= $sh['price'] ?></td>
                <td><a href="?movie_id=<?= $selected_movie['movie_id'] ?>&show_id=<?= $sh['show_id'] ?>" class="btn btn-success btn-sm">Select Seats</a></td>
            </tr>
            <?php endforeach; ?>
        </tbody></table>
    <?php endif; ?>
    <?php if ($seat_layout): ?>
        <h3 class="mb-3">Select Your Seats</h3>
        <form method="POST" action="book_seats.php">
            <input type="hidden" name="show_id" value="<?= intval($_GET['show_id']) ?>">
            <div class="mb-3">
                <div class="d-flex flex-column gap-2">
                    <?php
                    $row_labels = [];
                    foreach ($seat_layout as $seat => $type) {
                        $row = substr($seat, 0, 1);
                        $col = substr($seat, 1);
                        $row_labels[$row][] = [$seat, $type];
                    }
                    foreach ($row_labels as $row => $seats): ?>
                        <div class="d-flex align-items-center mb-1">
                            <span class="me-2 fw-bold">Row <?= $row ?>:</span>
                            <?php foreach ($seats as [$seat, $type]): ?>
                                <label class="me-1">
                                    <input type="checkbox" name="seats[]" value="<?= $seat ?>">
                                    <span class="badge bg-<?= $type=='VIP'?'warning':'secondary' ?>"> <?= $seat ?> (<?= $type ?>) </span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Book Selected Seats</button>
        </form>
    <?php endif; ?>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>

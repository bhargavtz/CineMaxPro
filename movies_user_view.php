<?php
require_once __DIR__ . '/config.php'; // Include config.php for database connection
require_once __DIR__ . '/includes/init.php'; // Include init.php for database connection and functions

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
$now_sql = "SELECT * FROM movies " . ($where_sql ? $where_sql . " AND" : "WHERE") . " release_date <= CURDATE() ORDER BY release_date DESC";
$upcoming_sql = "SELECT * FROM movies " . ($where_sql ? $where_sql . " AND" : "WHERE") . " release_date > CURDATE() ORDER BY release_date ASC";
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
<?php require_once __DIR__ . '/includes/header.php'; ?>
<div class="container mx-auto px-4 py-16 mt-16">
    <h2 class="text-5xl font-extrabold text-center mb-12 text-red-500">Now Showing</h2>
    <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-8 items-end">
        <div class="md:col-span-2">
            <label for="search" class="block text-gray-400 text-sm font-bold mb-2">Search by title</label>
            <input type="text" name="search" id="search" class="shadow appearance-none border border-gray-700 rounded w-full py-3 px-4 bg-gray-800 text-white leading-tight focus:outline-none focus:shadow-outline focus:border-red-500" placeholder="Search by title" value="<?= htmlspecialchars($search) ?>">
        </div>
        <div>
            <label for="genre" class="block text-gray-400 text-sm font-bold mb-2">Genre</label>
            <select name="genre" id="genre" class="shadow appearance-none border border-gray-700 rounded w-full py-3 px-4 bg-gray-800 text-white leading-tight focus:outline-none focus:shadow-outline focus:border-red-500">
                <option value="">All Genres</option>
                <?php foreach ($genres as $g): ?>
                    <option value="<?= htmlspecialchars($g) ?>" <?= $genre==$g?'selected':'' ?>><?= htmlspecialchars($g) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label for="language" class="block text-gray-400 text-sm font-bold mb-2">Language</label>
            <select name="language" id="language" class="shadow appearance-none border border-gray-700 rounded w-full py-3 px-4 bg-gray-800 text-white leading-tight focus:outline-none focus:shadow-outline focus:border-red-500">
                <option value="">All Languages</option>
                <?php foreach ($languages as $l): ?>
                    <option value="<?= htmlspecialchars($l) ?>" <?= $language==$l?'selected':'' ?>><?= htmlspecialchars($l) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-6 rounded-lg transition duration-300 w-full">Search</button>
        </div>
    </form>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10 mb-16">
            <?php foreach ($now_movies as $mv): ?>
            <div class="bg-gray-800 rounded-xl shadow-lg overflow-hidden transform hover:scale-105 transition duration-300 border border-gray-700">
                <?php if (!empty($mv['poster_path'])): ?>
                    <img src="<?= getPosterPath($mv['poster_path']) ?>" class="w-full h-72 object-cover" alt="Poster">
                <?php else: ?>
                    <div class="w-full h-72 bg-gray-700 flex items-center justify-center text-gray-400 text-xl font-semibold"><?= getPosterPath(null) ?></div>
                <?php endif; ?>
                <div class="p-6">
                    <h5 class="text-2xl font-bold mb-2 text-white"><?= sanitize_string($mv['title']) ?></h5>
                    <p class="text-gray-400 text-sm mb-4">Genre: <?= sanitize_string($mv['genre']) ?><br>Language: <?= sanitize_string($mv['language']) ?></p>
                    <a href="?movie_id=<?= $mv['movie_id'] ?>" class="block w-full bg-red-600 hover:bg-red-700 text-white text-center font-bold py-3 px-4 rounded-lg transition duration-300">View Details</a>
                </div>
            </div>
            <?php endforeach; ?>
    </div>
    <h2 class="text-5xl font-extrabold text-center mb-12 text-red-500">Upcoming Movies</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10 mb-16">
        <?php foreach ($upcoming_movies as $mv): ?>
        <div class="bg-gray-800 rounded-xl shadow-lg overflow-hidden transform hover:scale-105 transition duration-300 border border-gray-700">
            <?php if (!empty($mv['poster_path'])): ?>
                <img src="<?= getPosterPath($mv['poster_path']) ?>" class="w-full h-72 object-cover" alt="Poster">
            <?php else: ?>
                <div class="w-full h-72 bg-gray-700 flex items-center justify-center text-gray-400 text-xl font-semibold"><?= getPosterPath(null) ?></div>
            <?php endif; ?>
            <div class="p-6">
                <h5 class="text-2xl font-bold mb-2 text-white"><?= sanitize_string($mv['title']) ?></h5>
                <p class="text-gray-400 text-sm mb-4">Genre: <?= sanitize_string($mv['genre']) ?><br>Language: <?= sanitize_string($mv['language']) ?></p>
                <a href="?movie_id=<?= $mv['movie_id'] ?>" class="block w-full bg-blue-600 hover:bg-blue-700 text-white text-center font-bold py-3 px-4 rounded-lg transition duration-300">View Details</a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php if ($selected_movie): ?>
        <h2 class="text-5xl font-extrabold text-center mb-12 text-red-500">Movie Details</h2>
        <div class="bg-gray-800 rounded-xl shadow-lg overflow-hidden mb-16 border border-gray-700">
            <div class="md:flex">
                <div class="md:flex-shrink-0">
                    <?php if (!empty($selected_movie['poster_path'])): ?>
                        <img src="<?= getPosterPath($selected_movie['poster_path']) ?>" class="w-full h-96 object-cover md:w-72" alt="Poster">
                    <?php else: ?>
                        <div class="w-full h-96 bg-gray-700 flex items-center justify-center text-gray-400 text-xl font-semibold md:w-72"><?= getPosterPath(null) ?></div>
                    <?php endif; ?>
                </div>
                <div class="p-8">
                    <h5 class="text-4xl font-bold mb-4 text-white"><?= sanitize_string($selected_movie['title']) ?></h5>
                    <p class="text-gray-400 text-lg mb-2"><strong>Genre:</strong> <?= sanitize_string($selected_movie['genre']) ?></p>
                    <p class="text-gray-400 text-lg mb-2"><strong>Language:</strong> <?= sanitize_string($selected_movie['language']) ?></p>
                    <p class="text-gray-400 text-lg mb-2"><strong>Cast:</strong> <?= sanitize_string($selected_movie['cast']) ?></p>
                    <p class="text-gray-400 text-lg mb-2"><strong>Age Rating:</strong> <?= sanitize_string($selected_movie['age_rating']) ?></p>
                    <p class="text-gray-400 text-lg mb-4"><strong>Description:</strong> <?= sanitize_string($selected_movie['description']) ?></p>
                    <?php if (!empty($selected_movie['trailer_link'])): ?>
                        <a href="<?= $selected_movie['trailer_link'] ?>" target="_blank" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition duration-300 inline-block">Watch Trailer</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <h3 class="text-4xl font-extrabold text-center mb-8 text-red-500">Available Shows</h3>
        <div class="overflow-x-auto bg-gray-800 rounded-xl shadow-lg border border-gray-700 mb-16">
            <table class="min-w-full divide-y divide-gray-700">
                <thead class="bg-gray-700">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Theater</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Screen</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Start</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">End</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Price</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Book</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    <?php foreach ($shows as $sh): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-300"><?= htmlspecialchars($sh['theater_name']) ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-300"><?= $sh['screen_number'] ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-300"><?= $sh['start_time'] ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-300"><?= $sh['end_time'] ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-300">₹<?= $sh['price'] ?></td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <a href="?movie_id=<?= $selected_movie['movie_id'] ?>&show_id=<?= $sh['show_id'] ?>" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg transition duration-300 text-sm">Select Seats</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
    <?php if ($seat_layout): ?>
        <h3 class="text-4xl font-extrabold text-center mb-8 text-red-500">Select Your Seats</h3>
        <form method="POST" action="book_seats.php" class="bg-gray-800 p-8 rounded-xl shadow-lg border border-gray-700 mb-16">
            <input type="hidden" name="show_id" value="<?= intval($_GET['show_id']) ?>">
            <div class="mb-6">
                <div class="flex flex-col gap-4">
                    <?php
                    $row_labels = [];
                    foreach ($seat_layout as $seat => $type) {
                        $row = substr($seat, 0, 1);
                        $col = substr($seat, 1);
                        $row_labels[$row][] = [$seat, $type];
                    }
                    foreach ($row_labels as $row => $seats): ?>
                        <div class="flex items-center mb-2">
                            <span class="mr-4 font-bold text-white text-xl">Row <?= $row ?>:</span>
                            <div class="flex flex-wrap gap-2">
                                <?php foreach ($seats as [$seat, $type]): ?>
                                    <label class="inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="seats[]" value="<?= $seat ?>" class="form-checkbox h-5 w-5 text-red-600 bg-gray-700 border-gray-600 rounded focus:ring-red-500">
                                        <span class="ml-2 text-lg font-medium <?= $type=='VIP'?'text-yellow-400':'text-gray-300' ?>"> <?= $seat ?> (<?= $type ?>) </span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-8 rounded-lg transition duration-300 w-full">Book Selected Seats</button>
        </form>
    <?php endif; ?>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>

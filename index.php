<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/functions.php';

// Fetch latest movies
$latest_movies = $pdo->query("SELECT * FROM movies WHERE release_date <= CURDATE() ORDER BY release_date DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

// Fetch upcoming movies
$upcoming_movies = $pdo->query("SELECT * FROM movies WHERE release_date > CURDATE() ORDER BY release_date ASC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Hero Section -->
<div class="hero-section bg-dark text-white py-5 mb-5" style="background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('assets/images/cinema-bg.jpg') center/cover;">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1 class="display-4 fw-bold mb-4">Welcome to CineMaxPro</h1>
                <p class="lead mb-4">Experience movies like never before with state-of-the-art screens and premium comfort.</p>
                <a href="movies_user_view.php" class="btn btn-primary btn-lg">Book Tickets Now</a>
            </div>
            <div class="col-md-6">
                <!-- Add a floating movie ticket or cinema illustration here -->
            </div>
        </div>
    </div>
</div>

<!-- Now Showing Section -->
<div class="container mb-5">
    <h2 class="mb-4">Now Showing</h2>
    <div class="row g-4">
        <?php foreach ($latest_movies as $movie): ?>
        <div class="col-md-3">
            <div class="card h-100 shadow-sm">
                <?php if (!empty($movie['poster'])): ?>
                    <img src="<?= htmlspecialchars($movie['poster']) ?>" class="card-img-top" alt="<?= htmlspecialchars($movie['title']) ?>">
                <?php else: ?>
                    <div class="bg-light text-center py-5">No Poster</div>
                <?php endif; ?>
                <div class="card-body">
                    <h5 class="card-title"><?= htmlspecialchars($movie['title']) ?></h5>
                    <p class="card-text">
                        <small class="text-muted">
                            <?= htmlspecialchars($movie['genre']) ?> | <?= htmlspecialchars($movie['language']) ?>
                        </small>
                    </p>
                    <a href="movies_user_view.php?movie_id=<?= $movie['movie_id'] ?>" class="btn btn-outline-primary">Book Now</a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Coming Soon Section -->
<div class="container mb-5">
    <h2 class="mb-4">Coming Soon</h2>
    <div class="row g-4">
        <?php foreach ($upcoming_movies as $movie): ?>
        <div class="col-md-3">
            <div class="card h-100 shadow-sm">
                <?php if (!empty($movie['poster'])): ?>
                    <img src="<?= htmlspecialchars($movie['poster']) ?>" class="card-img-top" alt="<?= htmlspecialchars($movie['title']) ?>">
                <?php else: ?>
                    <div class="bg-light text-center py-5">No Poster</div>
                <?php endif; ?>
                <div class="card-body">
                    <h5 class="card-title"><?= htmlspecialchars($movie['title']) ?></h5>
                    <p class="card-text">
                        <small class="text-muted">
                            Release Date: <?= date('d M Y', strtotime($movie['release_date'])) ?>
                        </small>
                    </p>
                    <a href="movies_user_view.php?movie_id=<?= $movie['movie_id'] ?>" class="btn btn-outline-secondary">View Details</a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Features Section -->
<div class="container-fluid bg-light py-5 mb-5">
    <div class="container">
        <h2 class="text-center mb-5">Why Choose CineMaxPro?</h2>
        <div class="row g-4">
            <div class="col-md-4 text-center">
                <div class="feature-box p-4">
                    <i class="fas fa-film fa-3x mb-3 text-primary"></i>
                    <h4>Premium Screens</h4>
                    <p>Experience movies in stunning clarity with our state-of-the-art projection systems.</p>
                </div>
            </div>
            <div class="col-md-4 text-center">
                <div class="feature-box p-4">
                    <i class="fas fa-couch fa-3x mb-3 text-primary"></i>
                    <h4>Luxurious Seating</h4>
                    <p>Relax in our comfortable premium seats designed for the ultimate movie experience.</p>
                </div>
            </div>
            <div class="col-md-4 text-center">
                <div class="feature-box p-4">
                    <i class="fas fa-ticket-alt fa-3x mb-3 text-primary"></i>
                    <h4>Easy Booking</h4>
                    <p>Book your tickets online with our simple and secure booking system.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Newsletter Section -->
<div class="container mb-5">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <h3>Stay Updated</h3>
            <p>Subscribe to our newsletter for the latest movie updates and special offers!</p>
            <form class="row g-3 justify-content-center">
                <div class="col-md-8">
                    <input type="email" class="form-control" placeholder="Enter your email">
                </div>
                <div class="col-md-auto">
                    <button type="submit" class="btn btn-primary">Subscribe</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add custom styles -->
<style>
.hero-section {
    min-height: 500px;
    display: flex;
    align-items: center;
}

.feature-box {
    background: white;
    border-radius: 10px;
    transition: transform 0.3s;
}

.feature-box:hover {
    transform: translateY(-10px);
}

.card {
    transition: transform 0.3s;
}

.card:hover {
    transform: translateY(-5px);
}
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
require_once __DIR__ . '/includes/functions.php';

// Include the header
require_once __DIR__ . '/includes/header.php';
?>

    <div class="auth-container">
        <h1 class="text-center mb-4">Welcome to CineMaxPro</h1>
        <p class="text-center mb-4">Your gateway to movie magic!</p>
        <div class="d-grid gap-2">
            <a href="login.php" class="btn btn-primary btn-lg">Login</a>
            <a href="signup.php" class="btn btn-outline-secondary btn-lg">Sign Up</a>
        </div>
    </div>

<?php
// Include the footer (assuming a footer.php exists, though not explicitly mentioned in the provided files, it's good practice)
// If footer.php does not exist, this line can be removed or adjusted.
// require_once __DIR__ . '/includes/footer.php';
?>
</body>
</html>

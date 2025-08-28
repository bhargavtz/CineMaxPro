<?php
require_once __DIR__ . '/config.php'; // Include config for PDO
require_once __DIR__ . '/includes/functions.php'; // Include functions

// Fetch latest movies
$latest_movies = $pdo->query("SELECT * FROM movies WHERE release_date <= CURDATE() ORDER BY release_date DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

// Fetch upcoming movies
$upcoming_movies = $pdo->query("SELECT * FROM movies WHERE release_date > CURDATE() ORDER BY release_date ASC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CineMaxPro - Your Ultimate Movie Experience</title>
    <!-- Tailwind CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <!-- Optional: Google Fonts for a nicer look -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
        .hero-bg {
            background-image: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('assets/images/cinema-bg.jpg');
            background-size: cover;
            background-position: center;
        }
        /* Custom styles for dark mode if needed, though Tailwind handles much */
        .dark-mode {
            background-color: #1a1a1a;
            color: #f8f9fa;
        }
        .dark-mode .bg-white {
            background-color: #2c2c2c;
        }
        .dark-mode .text-gray-800 {
            color: #f8f9fa;
        }
        .dark-mode .text-gray-600 {
            color: #ccc;
        }
        .dark-mode .border {
            border-color: #444;
        }
        .dark-mode .shadow-md {
            box-shadow: 0 4px 6px -1px rgba(255, 255, 255, 0.1), 0 2px 4px -1px rgba(255, 255, 255, 0.06);
        }
    </style>
</head>
<body class="bg-gray-100 text-gray-800">

    <!-- Navigation (Simple, can be expanded) -->
    <nav class="bg-gray-800 p-4 text-white shadow-md">
        <div class="container mx-auto flex justify-between items-center">
            <a href="index.php" class="text-2xl font-bold text-white">CineMaxPro</a>
            <div class="space-x-4">
                <a href="movies_user_view.php" class="hover:text-gray-300">Movies</a>
                <a href="login.php" class="hover:text-gray-300">Login</a>
                <a href="signup.php" class="hover:text-gray-300">Sign Up</a>
                <a href="admin_staff_login.php" class="hover:text-gray-300">Staff Login</a>
                <!-- Theme Toggle Button -->
                <button id="theme-toggle" class="theme-toggle-button text-white hover:text-gray-300">
                    Toggle Theme
                </button>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <header class="hero-bg py-20 text-white text-center">
        <div class="container mx-auto px-4">
            <h1 class="text-5xl font-bold mb-4">Welcome to CineMaxPro</h1>
            <p class="text-xl mb-8">Experience movies like never before with state-of-the-art screens and premium comfort.</p>
            <a href="movies_user_view.php" class="bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-8 rounded-full transition duration-300">Book Tickets Now</a>
        </div>
    </header>

    <!-- Movies Section -->
    <section class="container mx-auto px-4 py-12">
        <h2 class="text-4xl font-bold text-center mb-10">Now Showing</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            <?php foreach ($latest_movies as $movie): ?>
            <div class="bg-white rounded-lg shadow-md overflow-hidden transform hover:scale-105 transition duration-300">
                <?php if (!empty($movie['poster'])): ?>
                    <img src="<?= htmlspecialchars($movie['poster']) ?>" alt="<?= htmlspecialchars($movie['title']) ?>" class="w-full h-64 object-cover">
                <?php else: ?>
                    <div class="w-full h-64 bg-gray-200 flex items-center justify-center text-gray-500">No Poster</div>
                <?php endif; ?>
                <div class="p-6">
                    <h3 class="text-xl font-semibold mb-2"><?= htmlspecialchars($movie['title']) ?></h3>
                    <p class="text-gray-600 text-sm mb-4">
                        <?= htmlspecialchars($movie['genre']) ?> | <?= htmlspecialchars($movie['language']) ?>
                    </p>
                    <a href="movies_user_view.php?movie_id=<?= $movie['movie_id'] ?>" class="block w-full bg-blue-600 hover:bg-blue-700 text-white text-center font-bold py-2 px-4 rounded transition duration-300">Book Now</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="container mx-auto px-4 py-12 bg-gray-50">
        <h2 class="text-4xl font-bold text-center mb-10">Coming Soon</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            <?php foreach ($upcoming_movies as $movie): ?>
            <div class="bg-white rounded-lg shadow-md overflow-hidden transform hover:scale-105 transition duration-300">
                <?php if (!empty($movie['poster'])): ?>
                    <img src="<?= htmlspecialchars($movie['poster']) ?>" alt="<?= htmlspecialchars($movie['title']) ?>" class="w-full h-64 object-cover">
                <?php else: ?>
                    <div class="w-full h-64 bg-gray-200 flex items-center justify-center text-gray-500">No Poster</div>
                <?php endif; ?>
                <div class="p-6">
                    <h3 class="text-xl font-semibold mb-2"><?= htmlspecialchars($movie['title']) ?></h3>
                    <p class="text-gray-600 text-sm mb-4">
                        Release Date: <?= date('d M Y', strtotime($movie['release_date'])) ?>
                    </p>
                    <a href="movies_user_view.php?movie_id=<?= $movie['movie_id'] ?>" class="block w-full bg-gray-600 hover:bg-gray-700 text-white text-center font-bold py-2 px-4 rounded transition duration-300">View Details</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Features Section -->
    <section class="container mx-auto px-4 py-12">
        <h2 class="text-4xl font-bold text-center mb-10">Why Choose CineMaxPro?</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-center">
            <div class="bg-white p-8 rounded-lg shadow-md transform hover:scale-105 transition duration-300">
                <div class="text-red-600 text-5xl mb-4">🎬</div>
                <h3 class="text-2xl font-semibold mb-2">Premium Screens</h3>
                <p class="text-gray-600">Experience movies in stunning clarity with our state-of-the-art projection systems.</p>
            </div>
            <div class="bg-white p-8 rounded-lg shadow-md transform hover:scale-105 transition duration-300">
                <div class="text-red-600 text-5xl mb-4">🛋️</div>
                <h3 class="text-2xl font-semibold mb-2">Luxurious Seating</h3>
                <p class="text-gray-600">Relax in our comfortable premium seats designed for the ultimate movie experience.</p>
            </div>
            <div class="bg-white p-8 rounded-lg shadow-md transform hover:scale-105 transition duration-300">
                <div class="text-red-600 text-5xl mb-4">🎟️</div>
                <h3 class="text-2xl font-semibold mb-2">Easy Booking</h3>
                <p class="text-gray-600">Book your tickets online with our simple and secure booking system.</p>
            </div>
        </div>
    </section>

    <!-- Newsletter Section -->
    <section class="bg-gray-800 text-white py-16">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-4xl font-bold mb-4">Stay Updated</h2>
            <p class="text-xl mb-8">Subscribe to our newsletter for the latest movie updates and special offers!</p>
            <form class="max-w-xl mx-auto flex flex-col md:flex-row gap-4">
                <input type="email" placeholder="Enter your email" class="flex-grow p-3 rounded-lg border border-gray-600 bg-gray-700 text-white focus:outline-none focus:ring-2 focus:ring-red-600">
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-8 rounded-lg transition duration-300">Subscribe</button>
            </form>
        </div>
    </section>

    <!-- Footer -->
    <?php require_once __DIR__ . '/includes/footer.php'; ?>

    <!-- Theme Toggle Script (Duplicated from header, but needed here for standalone page) -->
    <script>
        const themeToggle = document.getElementById('theme-toggle');
        const body = document.body;
        const currentTheme = localStorage.getItem('theme');

        const setTheme = (theme) => {
            body.classList.remove('light-mode', 'dark-mode'); // Remove existing themes
            body.classList.add(theme);
            if (theme === 'dark-mode') {
                themeToggle.textContent = 'Light Mode';
            } else {
                themeToggle.textContent = 'Dark Mode';
            }
        };

        if (currentTheme) {
            setTheme(currentTheme);
        } else {
            setTheme('light-mode'); // Default to light mode
        }

        themeToggle.addEventListener('click', () => {
            body.classList.toggle('dark-mode');
            const newTheme = body.classList.contains('dark-mode') ? 'dark-mode' : 'light-mode';
            localStorage.setItem('theme', newTheme);
            setTheme(newTheme);
        });
    </script>
</body>
</html>

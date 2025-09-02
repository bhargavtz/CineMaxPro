<?php
require_once __DIR__ . '/includes/init.php'; // Include init for session_start(), config, and functions
require_once __DIR__ . '/includes/functions.php'; // Include functions for isLoggedIn()

// If user is already logged in, redirect to user dashboard
if (isLoggedIn()) {
    header("Location: user_dashboard.php");
    exit();
}

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
</head>
<body class="bg-gray-900 text-gray-100 font-sans antialiased">

    <!-- Navigation (Simple, can be expanded) -->
    <nav class="bg-gray-800 p-4 text-white shadow-lg">
        <div class="container mx-auto flex justify-between items-center">
            <a href="index.php" class="text-3xl font-extrabold text-red-500">CineMaxPro</a>
            <div class="space-x-6 hidden md:flex">
                <a href="movies_user_view.php" class="hover:text-red-400 transition duration-300 ease-in-out">Movies</a>
                <a href="login.php" class="hover:text-red-400 transition duration-300 ease-in-out">Login</a>
                <a href="signup.php" class="hover:text-red-400 transition duration-300 ease-in-out">Sign Up</a>
                <a href="admin_staff_login.php" class="hover:text-red-400 transition duration-300 ease-in-out">Staff Login</a>
                <!-- Theme Toggle Button -->
                <button id="theme-toggle" class="theme-toggle-button text-white hover:text-red-400 transition duration-300 ease-in-out">
                    Toggle Theme
                </button>
            </div>
            <div class="md:hidden">
                <button id="mobile-menu-button" class="text-white focus:outline-none">
                    <i class="fas fa-bars text-2xl"></i>
                </button>
            </div>
        </div>
    </nav>

    <!-- Mobile Menu (Hidden by default) -->
    <div id="mobile-menu" class="hidden md:hidden bg-gray-800 text-white p-4">
        <a href="movies_user_view.php" class="block py-2 hover:bg-gray-700">Movies</a>
        <a href="login.php" class="block py-2 hover:bg-gray-700">Login</a>
        <a href="signup.php" class="block py-2 hover:bg-gray-700">Sign Up</a>
        <a href="admin_staff_login.php" class="block py-2 hover:bg-gray-700">Staff Login</a>
        <button id="theme-toggle-mobile" class="block w-full text-left py-2 hover:bg-gray-700">
            Toggle Theme
        </button>
    </div>

    <!-- Hero Section -->
    <header class="relative bg-gray-800 h-96 flex items-center justify-center text-white">
        <div class="container mx-auto px-4 text-center">
            <h1 class="text-6xl font-extrabold mb-4 animate-fade-in-up">Welcome to CineMaxPro</h1>
            <p class="text-xl md:text-2xl mb-8 animate-fade-in-up animation-delay-200">Experience movies like never before with state-of-the-art screens and premium comfort.</p>
            <a href="movies_user_view.php" class="bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-8 rounded-full transition duration-300 transform hover:scale-105 inline-block animate-fade-in-up animation-delay-400">Book Tickets Now</a>
        </div>
    </header>

    <!-- Movies Section -->
    <section class="container mx-auto px-4 py-16">
        <h2 class="text-5xl font-extrabold text-center mb-12 text-red-500">Now Showing</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10">
            <?php foreach ($latest_movies as $movie): ?>
            <div class="bg-gray-800 rounded-xl shadow-lg overflow-hidden transform hover:scale-105 transition duration-300 border border-gray-700">
                <?php if (!empty($movie['poster'])): ?>
                    <?php
                        $posterPath = htmlspecialchars($movie['poster']);
                        if (strpos($posterPath, 'uploads/posters/') === false) {
                            $posterPath = 'uploads/posters/' . $posterPath;
                        }
                    ?>
                    <img src="<?= $posterPath ?>" alt="<?= htmlspecialchars($movie['title']) ?>" class="w-full h-72 object-cover">
                <?php else: ?>
                    <div class="w-full h-72 bg-gray-700 flex items-center justify-center text-gray-400 text-xl font-semibold">No Poster</div>
                <?php endif; ?>
                <div class="p-6">
                    <h3 class="text-2xl font-bold mb-2 text-white"><?= htmlspecialchars($movie['title']) ?></h3>
                    <p class="text-gray-400 text-sm mb-4">
                        <?= htmlspecialchars($movie['genre']) ?> | <?= htmlspecialchars($movie['language']) ?>
                    </p>
                    <a href="movies_user_view.php?movie_id=<?= $movie['movie_id'] ?>" class="block w-full bg-red-600 hover:bg-red-700 text-white text-center font-bold py-3 px-4 rounded-lg transition duration-300">Book Now</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="container mx-auto px-4 py-16 bg-gray-800">
        <h2 class="text-5xl font-extrabold text-center mb-12 text-red-500">Coming Soon</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10">
            <?php foreach ($upcoming_movies as $movie): ?>
            <div class="bg-gray-900 rounded-xl shadow-lg overflow-hidden transform hover:scale-105 transition duration-300 border border-gray-700">
                <?php if (!empty($movie['poster'])): ?>
                    <?php
                        $posterPath = htmlspecialchars($movie['poster']);
                        if (strpos($posterPath, 'uploads/posters/') === false) {
                            $posterPath = 'uploads/posters/' . $posterPath;
                        }
                    ?>
                    <img src="<?= $posterPath ?>" alt="<?= htmlspecialchars($movie['title']) ?>" class="w-full h-72 object-cover">
                <?php else: ?>
                    <div class="w-full h-72 bg-gray-700 flex items-center justify-center text-gray-400 text-xl font-semibold">No Poster</div>
                <?php endif; ?>
                <div class="p-6">
                    <h3 class="text-2xl font-bold mb-2 text-white"><?= htmlspecialchars($movie['title']) ?></h3>
                    <p class="text-gray-400 text-sm mb-4">
                        Release Date: <?= date('d M Y', strtotime($movie['release_date'])) ?>
                    </p>
                    <a href="movies_user_view.php?movie_id=<?= $movie['movie_id'] ?>" class="block w-full bg-blue-600 hover:bg-blue-700 text-white text-center font-bold py-3 px-4 rounded-lg transition duration-300">View Details</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Features Section -->
    <section class="container mx-auto px-4 py-16">
        <h2 class="text-5xl font-extrabold text-center mb-12 text-red-500">Why Choose CineMaxPro?</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-10 text-center">
            <div class="bg-gray-800 p-8 rounded-xl shadow-lg transform hover:scale-105 transition duration-300 border border-gray-700">
                <div class="text-red-500 text-6xl mb-4">🎬</div>
                <h3 class="text-3xl font-bold mb-2 text-white">Premium Screens</h3>
                <p class="text-gray-400">Experience movies in stunning clarity with our state-of-the-art projection systems.</p>
            </div>
            <div class="bg-gray-800 p-8 rounded-xl shadow-lg transform hover:scale-105 transition duration-300 border border-gray-700">
                <div class="text-red-500 text-6xl mb-4">🛋️</div>
                <h3 class="text-3xl font-bold mb-2 text-white">Luxurious Seating</h3>
                <p class="text-gray-400">Relax in our comfortable premium seats designed for the ultimate movie experience.</p>
            </div>
            <div class="bg-gray-800 p-8 rounded-xl shadow-lg transform hover:scale-105 transition duration-300 border border-gray-700">
                <div class="text-red-500 text-6xl mb-4">🎟️</div>
                <h3 class="text-3xl font-bold mb-2 text-white">Easy Booking</h3>
                <p class="text-gray-400">Book your tickets online with our simple and secure booking system.</p>
            </div>
        </div>
    </section>

    <!-- Newsletter Section -->
    <section class="bg-gray-800 text-white py-16">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-5xl font-extrabold mb-6 text-red-500">Stay Updated</h2>
            <p class="text-xl mb-10 text-gray-300">Subscribe to our newsletter for the latest movie updates and special offers!</p>
            <form class="max-w-2xl mx-auto flex flex-col md:flex-row gap-4">
                <input type="email" placeholder="Enter your email" class="flex-grow p-4 rounded-lg border border-gray-600 bg-gray-700 text-white focus:outline-none focus:ring-2 focus:ring-red-500 placeholder-gray-400">
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold py-4 px-10 rounded-lg transition duration-300 transform hover:scale-105">Subscribe</button>
            </form>
        </div>
    </section>

    <!-- Footer -->
    <?php require_once __DIR__ . '/includes/footer.php'; ?>

    <!-- Theme Toggle Script (Duplicated from header, but needed here for standalone page) -->
    <script>
        const themeToggle = document.getElementById('theme-toggle');
        const themeToggleMobile = document.getElementById('theme-toggle-mobile');
        const body = document.body;
        const currentTheme = localStorage.getItem('theme');
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const mobileMenu = document.getElementById('mobile-menu');

        const setTheme = (theme) => {
            body.classList.remove('light-mode', 'dark-mode'); // Remove existing themes
            body.classList.add(theme);
            if (theme === 'dark-mode') {
                themeToggle.textContent = 'Light Mode';
                if (themeToggleMobile) themeToggleMobile.textContent = 'Light Mode';
            } else {
                themeToggle.textContent = 'Dark Mode';
                if (themeToggleMobile) themeToggleMobile.textContent = 'Dark Mode';
            }
        };

        if (currentTheme) {
            setTheme(currentTheme);
        } else {
            setTheme('dark-mode'); // Default to dark mode for the new design
        }

        const toggleTheme = () => {
            body.classList.toggle('dark-mode');
            const newTheme = body.classList.contains('dark-mode') ? 'dark-mode' : 'light-mode';
            localStorage.setItem('theme', newTheme);
            setTheme(newTheme);
        };

        themeToggle.addEventListener('click', toggleTheme);
        if (themeToggleMobile) {
            themeToggleMobile.addEventListener('click', toggleTheme);
        }

        mobileMenuButton.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });
    </script>
</body>
</html>
</body>
</html>

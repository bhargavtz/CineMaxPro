<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CineMaxPro - Your Ultimate Movie Experience</title>
    <!-- Tailwind CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 font-sans">
    <!-- Navigation -->
    <nav class="bg-gray-800 p-4 text-white shadow-md">
        <div class="container mx-auto flex justify-between items-center">
            <a href="index.php" class="text-2xl font-bold text-white">CineMaxPro</a>
            <div class="hidden md:flex space-x-4">
                <a href="index.php" class="hover:text-gray-300">Home</a>
                <a href="movies_user_view.php" class="hover:text-gray-300">Movies</a>
                <a href="#aboutus" class="hover:text-gray-300">About Us</a>
                <a href="#contact" class="hover:text-gray-300">Contact</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php if (isset($_SESSION['user_role']) && ($_SESSION['user_role'] === 'admin' || strpos($_SESSION['user_role'], 'staff') !== false)): ?>
                        <a href="admin_dashboard.php" class="hover:text-gray-300">Dashboard</a>
                    <?php endif; ?>
                    <a href="user_dashboard.php?page=profile" class="hover:text-gray-300">My Profile</a>
                    <a href="logout.php" class="hover:text-gray-300">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="hover:text-gray-300">Login</a>
                    <a href="signup.php" class="hover:text-gray-300">Sign Up</a>
                <?php endif; ?>
            </div>
            <div class="md:hidden">
                <button id="mobile-menu-button" class="text-white focus:outline-none">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </div>
    </nav>

    <!-- Mobile Menu (Hidden by default) -->
    <div id="mobile-menu" class="hidden md:hidden bg-gray-800 text-white p-4">
        <a href="index.php" class="block py-2 hover:bg-gray-700">Home</a>
        <a href="movies_user_view.php" class="block py-2 hover:bg-gray-700">Movies</a>
        <a href="#aboutus" class="block py-2 hover:bg-gray-700">About Us</a>
        <a href="#contact" class="block py-2 hover:bg-gray-700">Contact</a>
        <?php if (isset($_SESSION['user_id'])): ?>
            <?php if (isset($_SESSION['user_role']) && ($_SESSION['user_role'] === 'admin' || strpos($_SESSION['user_role'], 'staff') !== false)): ?>
                <a href="admin_dashboard.php" class="block py-2 hover:bg-gray-700">Dashboard</a>
            <?php endif; ?>
            <a href="user_dashboard.php?page=profile" class="block py-2 hover:bg-gray-700">My Profile</a>
            <a href="logout.php" class="block py-2 hover:bg-gray-700">Logout</a>
        <?php else: ?>
            <a href="login.php" class="block py-2 hover:bg-gray-700">Login</a>
            <a href="signup.php" class="block py-2 hover:bg-gray-700">Sign Up</a>
        <?php endif; ?>
    </div>

    <script>
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const mobileMenu = document.getElementById('mobile-menu');

        mobileMenuButton.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });
    </script>
</body>
</html>

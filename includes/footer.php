    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-8">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <h5 class="text-xl font-bold mb-4">About CineMaxPro</h5>
                    <p class="text-gray-400">Your gateway to movie magic! Experience the latest blockbusters in ultimate comfort with state-of-the-art technology.</p>
                </div>
                <div>
                    <h5 class="text-xl font-bold mb-4">Quick Links</h5>
                    <ul class="space-y-2">
                        <li><a href="movies_user_view.php" class="text-gray-400 hover:text-white transition duration-300">Now Showing</a></li>
                        <li><a href="movies_user_view.php" class="text-gray-400 hover:text-white transition duration-300">Coming Soon</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition duration-300">Promotions</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition duration-300">Gift Cards</a></li>
                    </ul>
                </div>
                <div>
                    <h5 class="text-xl font-bold mb-4">Contact Us</h5>
                    <ul class="space-y-2">
                        <li><i class="fas fa-phone mr-2"></i> +1 234 567 8900</li>
                        <li><i class="fas fa-envelope mr-2"></i> info@cinemaxpro.com</li>
                        <li><i class="fas fa-map-marker-alt mr-2"></i> 123 Movie Street, Cinema City</li>
                    </ul>
                    <div class="social-icons mt-4 flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-white transition duration-300"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white transition duration-300"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white transition duration-300"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
            </div>
            <hr class="my-8 border-gray-700">
            <div class="text-center text-gray-500">
                <p>&copy; <?php echo date('Y'); ?> CineMaxPro. All rights reserved.</p>
            </div>
    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-8">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <h5 class="text-xl font-bold mb-4">About CineMaxPro</h5>
                    <p class="text-gray-400">Your gateway to movie magic! Experience the latest blockbusters in ultimate comfort with state-of-the-art technology.</p>
                </div>
                <div>
                    <h5 class="text-xl font-bold mb-4">Quick Links</h5>
                    <ul class="space-y-2">
                        <li><a href="movies_user_view.php" class="text-gray-400 hover:text-white transition duration-300">Now Showing</a></li>
                        <li><a href="movies_user_view.php" class="text-gray-400 hover:text-white transition duration-300">Coming Soon</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition duration-300">Promotions</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition duration-300">Gift Cards</a></li>
                    </ul>
                </div>
                <div>
                    <h5 class="text-xl font-bold mb-4">Contact Us</h5>
                    <ul class="space-y-2">
                        <li><i class="fas fa-phone mr-2"></i> +1 234 567 8900</li>
                        <li><i class="fas fa-envelope mr-2"></i> info@cinemaxpro.com</li>
                        <li><i class="fas fa-map-marker-alt mr-2"></i> 123 Movie Street, Cinema City</li>
                    </ul>
                    <div class="social-icons mt-4 flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-white transition duration-300"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white transition duration-300"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white transition duration-300"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
            </div>
            <hr class="my-8 border-gray-700">
            <div class="text-center text-gray-500">
                <p>&copy; <?php echo date('Y'); ?> CineMaxPro. All rights reserved.</p>
            </div>
        </div>
    </footer>
    <!-- Theme Toggle and Mobile Menu Script -->
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

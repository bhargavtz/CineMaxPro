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
    <script>
        // Add any custom JavaScript here
        document.addEventListener('DOMContentLoaded', function() {
            const passwordInput = document.getElementById('password');
            const confirmPasswordInput = document.getElementById('confirm_password');
            const togglePassword = document.querySelectorAll('.toggle-password');

            if (togglePassword.length > 0) {
                togglePassword.forEach(function(icon) {
                    icon.addEventListener('click', function() {
                        const input = this.previousElementSibling; // Get the input field
                        const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                        input.setAttribute('type', type);
                        this.classList.toggle('fa-eye-slash');
                    });
                });
            }
        });
    </script>

    </div> <!-- /.content-wrapper -->

    <!-- Footer -->
    <footer class="mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5>About CineMaxPro</h5>
                    <p>Your gateway to movie magic! Experience the latest blockbusters in ultimate comfort with state-of-the-art technology.</p>
                </div>
                <div class="col-md-4">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="movies_user_view.php" class="text-white">Now Showing</a></li>
                        <li><a href="movies_user_view.php" class="text-white">Coming Soon</a></li>
                        <li><a href="#" class="text-white">Promotions</a></li>
                        <li><a href="#" class="text-white">Gift Cards</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>Contact Us</h5>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-phone me-2"></i> +1 234 567 8900</li>
                        <li><i class="fas fa-envelope me-2"></i> info@cinemaxpro.com</li>
                        <li><i class="fas fa-map-marker-alt me-2"></i> 123 Movie Street, Cinema City</li>
                    </ul>
                    <div class="social-icons mt-3">
                        <a href="#" class="text-white me-3"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
            </div>
            <hr class="mt-4" style="background-color: #ffffff;">
            <div class="text-center">
                <p>&copy; <?php echo date('Y'); ?> CineMaxPro. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
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
</body>
</html>

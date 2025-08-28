</div> <!-- /.auth-container -->

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JS (optional) -->
    <script>
        // Add any custom JavaScript here
        // For example, to show/hide password
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

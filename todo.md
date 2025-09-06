# TODO List for User, Admin, and Staff Management

- [ ] Analyze existing authentication logic in `includes/auth_functions.php` and `includes/functions.php`.
- [ ] Refactor `signup.php` to handle role selection (user, staff, admin) during signup, or create separate signup files if necessary.
- [ ] Ensure `login.php` handles user login.
- [ ] Ensure `admin_login.php` handles administrator login.
- [ ] Ensure `admin_staff_login.php` handles staff login.
- [ ] Implement distinct logout functionality for each role, potentially by modifying `logout.php` or creating role-specific logout files.
- [ ] Verify that the system supports multiple users, staff, and administrators.
- [ ] Implement role-based access control to restrict access to dashboards and functionalities based on user role.
- [ ] Test signup, login, and logout for all three roles (user, staff, admin).
- [ ] Test dashboard access and functionality for each role.

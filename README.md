# CineMaxPro Staff Panel and Admin Reports

This project implements a PHP-based staff panel and admin reporting system for a cinema management application (CineMaxPro). It allows staff members to log in, manage bookings and refund requests, verify tickets, and access various administrative reports with data visualization.

## Features

### Staff Panel
*   **Secure Staff Login:** Staff members can log in using their username and password. Authentication is performed against the `users` and `staff` tables, and their `position` is used for role-based access control.
*   **Role-Based Dashboard:** After successful login, staff are redirected to a dashboard (`admin_dashboard.php`) that displays navigation options relevant to their assigned role (e.g., Manager, Usher, Admin).
*   **Ticket Verification:** (`verify_ticket.php`) Staff can enter a ticket ID to verify its validity, retrieving associated booking and movie details.
*   **Refund Request Management:** (`admin_refunds.php`) Authorized staff (Managers, Admins) can view pending refund requests and approve or deny them, updating the database accordingly.
*   **Booking Management:** (`admin_bookings.php`) Authorized staff (Managers, Admins) can view all bookings and approve pending ones or cancel confirmed bookings.
*   **Logout Functionality:** Secure session termination is provided via `logout.php`.

### Admin Reports
*   **Reporting Hub:** (`admin_reports.php`) A central page for accessing various administrative reports.
*   **Revenue Reports:** (`revenue_report.php`) Generates daily, weekly, and monthly revenue summaries from successful payments. Data is displayed in tables and visualized using [Chart.js](https://www.chartjs.org/).
*   **Movie Popularity Report:** (`movie_popularity_report.php`) Identifies the most booked movies based on confirmed bookings, presented in a table and visualized with Chart.js.
*   **Top Users Report:** (`top_users_report.php`) Lists users with the highest number of confirmed bookings, displayed in a table and visualized with Chart.js.
*   **CSV Export:** All reports can be exported to CSV format for further analysis.

### UI Enhancements
*   **Bootstrap Dark/Light Mode Toggle:** A theme toggle is integrated into the header (`includes/header.php`), allowing users to switch between light and dark modes. The preference is saved using `localStorage`.

## Technologies Used
*   **Backend:** PHP
*   **Database:** MySQL (via PDO)
*   **Frontend:** HTML5, CSS3, Bootstrap 5
*   **Charting:** Chart.js
*   **Database Schema:** `cinema_schema.sql`

## Setup Instructions

1.  **Web Server:** Ensure you have a web server environment (e.g., XAMPP, WAMP, MAMP, or a custom Apache/Nginx setup) with PHP installed.
2.  **Database Setup:**
    *   Create a MySQL database (e.g., `cinemaxpro`).
    *   Import the `cinema_schema.sql` file into your database to create the necessary tables and populate them with sample data.
3.  **Configuration:**
    *   Open `config.php` and update the database credentials (`DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`) to match your MySQL setup.
4.  **File Placement:** Place all PHP files (including `includes/` directory) into your web server's document root (e.g., `c:/xampp/htdocs/CineMaxPro`).

## Usage

1.  **Access the Application:** Open your web browser and navigate to `http://localhost/CineMaxPro/admin_staff_login.php` (adjust the URL based on your server configuration).
2.  **Login:** Use the sample staff credentials from `cinema_schema.sql` or create new ones.
    *   Example: `username: john_doe`, `password: password` (assuming 'hashed_password_1' in `cinema_schema.sql` corresponds to 'password' after hashing). You might need to manually insert a staff user with a known password hash for initial testing.
3.  **Navigate:** After logging in, you will be redirected to the `admin_dashboard.php`. Use the navigation links to access ticket verification, booking management, refund requests, and admin reports.
4.  **Theme Toggle:** Use the "Toggle Theme" button in the top right to switch between light and dark modes.

## Future Enhancements

*   **PDF Export Implementation:** Integrate a robust PHP PDF generation library (e.g., FPDF, TCPDF, Dompdf) into `export_report.php` to enable actual PDF file downloads.
*   **Comprehensive Testing:** Implement unit and integration tests for all functionalities.
*   **User Management for Staff:** Add a dedicated page (`manage_staff.php`) for admins to add, edit, or remove staff accounts.
*   **Advanced Filtering/Sorting:** Enhance reports with more advanced filtering and sorting options.
*   **Security Hardening:** Implement more advanced security measures (e.g., CSRF protection, input validation on all forms).

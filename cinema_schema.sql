-- Table for users
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(50),
    last_name VARCHAR(50),
    phone_number VARCHAR(20),
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table for staff
CREATE TABLE staff (
    staff_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    hire_date DATE NOT NULL,
    position VARCHAR(50) NOT NULL,
    salary DECIMAL(10, 2),
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Table for admins (inherits from staff or has a direct link to users, assuming direct link for simplicity here)
CREATE TABLE admins (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    admin_level VARCHAR(50) NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Table for movies
CREATE TABLE movies (
    movie_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    genre VARCHAR(100),
    director VARCHAR(100),
    release_date DATE,
    duration_minutes INT,
    description TEXT,
    rating DECIMAL(3, 1)
);

-- Table for theaters
CREATE TABLE theaters (
    theater_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    location VARCHAR(255),
    capacity INT
);

-- Table for screens
CREATE TABLE screens (
    screen_id INT AUTO_INCREMENT PRIMARY KEY,
    theater_id INT NOT NULL,
    screen_number INT NOT NULL,
    capacity INT NOT NULL,
    screen_type VARCHAR(50), -- e.g., '2D', '3D', 'IMAX'
    FOREIGN KEY (theater_id) REFERENCES theaters(theater_id) ON DELETE CASCADE,
    UNIQUE (theater_id, screen_number)
);

-- Table for shows
CREATE TABLE shows (
    show_id INT AUTO_INCREMENT PRIMARY KEY,
    screen_id INT NOT NULL,
    movie_id INT NOT NULL,
    start_time DATETIME NOT NULL,
    end_time DATETIME NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (screen_id) REFERENCES screens(screen_id) ON DELETE CASCADE,
    FOREIGN KEY (movie_id) REFERENCES movies(movie_id) ON DELETE CASCADE
);

-- Table for bookings
CREATE TABLE bookings (
    booking_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    show_id INT NOT NULL,
    booking_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    total_amount DECIMAL(10, 2) NOT NULL,
    status VARCHAR(50) DEFAULT 'Pending', -- e.g., 'Pending', 'Confirmed', 'Cancelled'
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (show_id) REFERENCES shows(show_id) ON DELETE CASCADE
);

-- Table for payments
CREATE TABLE payments (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL UNIQUE,
    payment_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    amount DECIMAL(10, 2) NOT NULL,
    payment_method VARCHAR(50), -- e.g., 'Credit Card', 'Debit Card', 'UPI'
    transaction_id VARCHAR(100) UNIQUE,
    status VARCHAR(50) DEFAULT 'Success', -- e.g., 'Success', 'Failed', 'Pending'
    FOREIGN KEY (booking_id) REFERENCES bookings(booking_id) ON DELETE CASCADE
);

-- Table for refunds
CREATE TABLE refunds (
    refund_id INT AUTO_INCREMENT PRIMARY KEY,
    payment_id INT NOT NULL UNIQUE,
    refund_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    amount DECIMAL(10, 2) NOT NULL,
    reason TEXT,
    status VARCHAR(50) DEFAULT 'Processed', -- e.g., 'Processed', 'Pending', 'Failed'
    FOREIGN KEY (payment_id) REFERENCES payments(payment_id) ON DELETE CASCADE
);

-- Table for tickets
CREATE TABLE tickets (
    ticket_id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    seat_number VARCHAR(10) NOT NULL,
    FOREIGN KEY (booking_id) REFERENCES bookings(booking_id) ON DELETE CASCADE,
    UNIQUE (booking_id, seat_number)
);

-- Table for coupons
CREATE TABLE coupons (
    coupon_id INT AUTO_INCREMENT PRIMARY KEY,
    coupon_code VARCHAR(50) NOT NULL UNIQUE,
    discount_type VARCHAR(50) NOT NULL, -- e.g., 'percentage', 'fixed'
    discount_value DECIMAL(10, 2) NOT NULL,
    expiry_date DATE,
    is_active BOOLEAN DEFAULT TRUE
);

-- Add coupon_id to bookings table for applying coupons
ALTER TABLE bookings
ADD COLUMN coupon_id INT NULL,
ADD FOREIGN KEY (coupon_id) REFERENCES coupons(coupon_id) ON DELETE SET NULL;

-- Sample Data --

-- Users
INSERT INTO users (username, email, password_hash, first_name, last_name, phone_number) VALUES
('john_doe', 'john.doe@example.com', 'hashed_password_1', 'John', 'Doe', '123-456-7890'),
('jane_smith', 'jane.smith@example.com', 'hashed_password_2', 'Jane', 'Smith', '987-654-3210'),
('admin_user', 'admin@example.com', 'hashed_admin_password', 'Admin', 'User', '111-222-3333');

-- Staff
INSERT INTO staff (user_id, hire_date, position, salary) VALUES
(1, '2022-01-15', 'Manager', 60000.00),
(2, '2023-03-10', 'Usher', 30000.00);

-- Admins
INSERT INTO admins (user_id, admin_level) VALUES
(3, 'Super Admin');

-- Movies
INSERT INTO movies (title, genre, director, release_date, duration_minutes, description, rating) VALUES
('The Great Adventure', 'Adventure', 'Alex Johnson', '2023-05-10', 120, 'A thrilling adventure across uncharted lands.', 8.5),
('City of Dreams', 'Drama', 'Maria Garcia', '2023-07-20', 150, 'A compelling story of ambition and resilience.', 7.8),
('Space Odyssey', 'Sci-Fi', 'Chris Lee', '2024-01-01', 180, 'An epic journey through the cosmos.', 9.0);

-- Theaters
INSERT INTO theaters (name, location, capacity) VALUES
('Grand Cinema', '123 Main St', 200),
('Starplex', '456 Oak Ave', 150);

-- Screens
INSERT INTO screens (theater_id, screen_number, capacity, screen_type) VALUES
(1, 1, 50, '2D'),
(1, 2, 75, '3D'),
(2, 1, 60, '2D'),
(2, 2, 40, 'IMAX');

-- Shows
INSERT INTO shows (screen_id, movie_id, start_time, end_time, price) VALUES
(1, 1, '2024-08-29 14:00:00', '2024-08-29 16:00:00', 12.50),
(1, 1, '2024-08-29 19:00:00', '2024-08-29 21:00:00', 14.00),
(2, 2, '2024-08-29 15:00:00', '2024-08-29 17:30:00', 13.00),
(3, 3, '2024-08-29 18:00:00', '2024-08-29 21:00:00', 15.00),
(4, 3, '2024-08-29 20:00:00', '2024-08-29 23:00:00', 18.00);

-- Coupons
INSERT INTO coupons (coupon_code, discount_type, discount_value, expiry_date, is_active) VALUES
('SAVE10', 'percentage', 10.00, '2024-12-31', TRUE),
('BIGDISCOUNT', 'fixed', 5.00, '2024-11-30', TRUE),
('FREESHOW', 'fixed', 0.00, '2024-10-31', FALSE); -- Example of an inactive coupon

-- Bookings
INSERT INTO bookings (user_id, show_id, total_amount, status, coupon_id) VALUES
(1, 1, 50.00, 'Confirmed', 1), -- John Doe booked show 1 with coupon SAVE10
(2, 3, 39.00, 'Confirmed', 2), -- Jane Smith booked show 3 with coupon BIGDISCOUNT
(1, 5, 54.00, 'Confirmed', NULL); -- John Doe booked show 5 without coupon

-- Tickets
INSERT INTO tickets (booking_id, seat_number) VALUES
(1, 'A1'),
(1, 'A2'),
(1, 'A3'),
(1, 'A4'),
(2, 'B5'),
(2, 'B6'),
(3, 'C1'),
(3, 'C2'),
(3, 'C3');

-- Payments
INSERT INTO payments (booking_id, amount, payment_method, transaction_id, status) VALUES
(1, 50.00, 'Credit Card', 'TXN123456789', 'Success'),
(2, 39.00, 'UPI', 'TXN987654321', 'Success'),
(3, 54.00, 'Debit Card', 'TXN112233445', 'Success');

-- Refunds (Example: No refunds for now)
-- INSERT INTO refunds (payment_id, amount, reason, status) VALUES
-- (1, 50.00, 'Canceled booking', 'Pending');

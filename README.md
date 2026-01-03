# ProjectTubes-KomputasiAwan

CREATE DATABASE IF NOT EXISTS food_ordering_db 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;
USE food_ordering_db;

-- ============================================
1. USERS TABLE (Akun User + Admin)
-- ============================================
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255),
    oauth_provider ENUM('google', 'email') DEFAULT 'email',
    oauth_id VARCHAR(100),
    phone VARCHAR(20),
    subscription_status ENUM('active', 'inactive', 'expired') DEFAULT 'inactive',
    subscription_end DATE,
    role ENUM('user', 'admin') DEFAULT 'user',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ============================================
2. CATEGORIES TABLE
-- ============================================
CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    description TEXT,
    image VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
3. MENU ITEMS TABLE
-- ============================================
CREATE TABLE menu (
    id INT PRIMARY KEY AUTO_INCREMENT,
    category_id INT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    image VARCHAR(255),
    is_available BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- ============================================
4. BOOKING/PESANAN TABLE
-- ============================================
CREATE TABLE booking (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NULL,
    nama_pemesan VARCHAR(100) NOT NULL,
    email_pemesan VARCHAR(150),
    phone_pemesan VARCHAR(20),
    jumlah_orang INT NOT NULL DEFAULT 1,
    tanggal_booking DATE NOT NULL,
    jam_booking TIME NOT NULL,
    status ENUM('pending', 'diterima', 'ditolak', 'completed') DEFAULT 'pending',
    catatan TEXT,
    total_harga DECIMAL(10,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- ============================================
5. BOOKING MENU ITEMS (Many-to-Many)
-- ============================================
CREATE TABLE booking_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    booking_id INT NOT NULL,
    menu_id INT NOT NULL,
    jumlah INT NOT NULL,
    harga_unit DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (booking_id) REFERENCES booking(id) ON DELETE CASCADE,
    FOREIGN KEY (menu_id) REFERENCES menu(id) ON DELETE CASCADE
);

-- ============================================
6. PAYMENTS TABLE
-- ============================================
CREATE TABLE payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    booking_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('cash', 'qris', 'transfer', 'midtrans') DEFAULT 'cash',
    status ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending',
    snap_token VARCHAR(255),
    order_id VARCHAR(100),
    paid_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES booking(id) ON DELETE CASCADE
);

-- ============================================
7. NOTIFICATIONS TABLE
-- ============================================
CREATE TABLE notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    booking_id INT NULL,
    title VARCHAR(200),
    message TEXT,
    type ENUM('info', 'success', 'warning', 'error') DEFAULT 'info',
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (booking_id) REFERENCES booking(id) ON DELETE CASCADE
);

-- ============================================
INDEXES & PERFORMANCE OPTIMIZATION
-- ============================================
CREATE INDEX idx_booking_user ON booking(user_id);
CREATE INDEX idx_booking_date ON booking(tanggal_booking, jam_booking);
CREATE INDEX idx_booking_status ON booking(status);
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_payments_status ON payments(status);

-- ============================================
SAMPLE DATA (Testing)
-- ============================================

-- Admin User
INSERT INTO users (nama, email, password, role) VALUES 
('Admin Utama', 'admin@foodordering.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Categories
INSERT INTO categories (name, description) VALUES 
('Makanan Utama', 'Menu makanan utama restoran'),
('Minuman', 'Berbagai jenis minuman'),
('Dessert', 'Penutup makanan manis'),
('Appetizer', 'Pemula makanan');

-- Menu Items
INSERT INTO menu (category_id, name, description, price, image) VALUES 
(1, 'Nasi Goreng Spesial', 'Nasi goreng dengan topping ayam dan udang', 35000.00, 'nasi_goreng.jpg'),
(1, 'Ayam Bakar Madu', 'Ayam bakar dengan bumbu madu asli', 45000.00, 'ayam_bakar.jpg'),
(2, 'Es Teh Manis', 'Es teh manis segar', 8000.00, 'es_tea.jpg'),
(2, 'Jus Jeruk', 'Jus jeruk segar original', 15000.00, 'jus_jeruk.jpg'),
(3, 'Es Cendol', 'Es cendol tradisional', 12000.00, 'cendol.jpg'),
(4, 'Sate Lilit', 'Sate lilit khas Bali', 25000.00, 'sate_lilit.jpg');

-- Sample Booking
INSERT INTO booking (user_id, nama_pemesan, email_pemesan, phone_pemesan, jumlah_orang, tanggal_booking, jam_booking, status, total_harga) VALUES 
(1, 'Budi Santoso', 'budi@email.com', '08123456789', 4, '2026-01-05', '19:00:00', 'pending', 150000.00),
(1, 'Siti Aminah', 'siti@email.com', '08987654321', 2, '2026-01-06', '12:30:00', 'diterima', 85000.00);

-- Sample Booking Items
INSERT INTO booking_items (booking_id, menu_id, jumlah, harga_unit, subtotal) VALUES 
(1, 1, 2, 35000.00, 70000.00),
(1, 2, 1, 45000.00, 45000.00),
(1, 3, 4, 8000.00, 32000.00),
(2, 4, 1, 15000.00, 15000.00),
(2, 5, 2, 12000.00, 24000.00),
(2, 6, 1, 25000.00, 25000.00);

-- Sample Payment
INSERT INTO payments (booking_id, amount, payment_method, status) VALUES 
(1, 150000.00, 'qris', 'pending'),
(2, 85000.00, 'cash', 'paid');

-- ============================================
TRIGGER: Auto calculate total_harga di booking
-- ============================================
DELIMITER $$
CREATE TRIGGER before_booking_insert 
BEFORE INSERT ON booking
FOR EACH ROW
BEGIN
    SET NEW.total_harga = 0;
END$$

CREATE TRIGGER after_booking_items_insert 
AFTER INSERT ON booking_items
FOR EACH ROW
BEGIN
    UPDATE booking 
    SET total_harga = (
        SELECT SUM(subtotal) 
        FROM booking_items 
        WHERE booking_id = NEW.booking_id
    )
    WHERE id = NEW.booking_id;
END$$
DELIMITER ;

-- ============================================
VIEW: Dashboard Stats
-- ============================================
CREATE VIEW dashboard_stats AS
SELECT 
    (SELECT COUNT(*) FROM booking) as total_orders,
    (SELECT COUNT(*) FROM booking WHERE status = 'pending') as pending_orders,
    (SELECT COUNT(*) FROM booking WHERE status IN ('diterima', 'completed')) as completed_orders,
    (SELECT COUNT(*) FROM users) as total_users,
    (SELECT COALESCE(SUM(total_harga), 0) FROM booking WHERE status IN ('diterima', 'completed')) as total_revenue,
    (SELECT COUNT(*) FROM booking WHERE DATE(created_at) = CURDATE()) as today_orders;

-- ============================================
VIEW: Recent Bookings
-- ============================================
CREATE VIEW recent_bookings AS
SELECT 
    b.id, b.nama_pemesan, b.jumlah_orang, b.tanggal_booking, 
    b.jam_booking, b.status, b.total_harga, b.created_at,
    u.nama as user_name
FROM booking b 
LEFT JOIN users u ON b.user_id = u.id 
ORDER BY b.created_at DESC 
LIMIT 10;

-- ============================================
PROCEDURE: Update Booking Status
-- ============================================
DELIMITER $$
CREATE PROCEDURE UpdateBookingStatus(
    IN p_booking_id INT,
    IN p_status ENUM('pending', 'diterima', 'ditolak', 'completed')
)
BEGIN
    UPDATE booking SET status = p_status, updated_at = NOW() WHERE id = p_booking_id;
    
--Insert notification
    INSERT INTO notifications (user_id, booking_id, title, message, type)
    SELECT user_id, p_booking_id, 
           CONCAT('Booking #', p_booking_id, ' ', p_status),
           CASE p_status
               WHEN 'diterima' THEN 'Pesanan Anda telah diterima!'
               WHEN 'ditolak' THEN 'Mohon maaf, pesanan Anda dibatalkan'
               WHEN 'completed' THEN 'Pesanan Anda telah selesai!'
               ELSE 'Status pesanan berubah'
           END,
           CASE p_status
               WHEN 'diterima' THEN 'success'
               WHEN 'ditolak' THEN 'warning'
               ELSE 'info'
           END
    FROM booking WHERE id = p_booking_id;
END$$
DELIMITER ;

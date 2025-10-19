-- PRODIGI Digital Marketplace Database Schema
-- Created: October 19, 2025
-- Database for digital products marketplace with OOP structure

CREATE DATABASE IF NOT EXISTS prodigi_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE prodigi_db;

-- =====================================================
-- TABLE: users
-- Stores all user accounts (buyers, sellers, admins)
-- =====================================================
CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    user_type ENUM('buyer', 'seller', 'admin') DEFAULT 'buyer',
    profile_image VARCHAR(255) DEFAULT NULL,
    is_verified TINYINT(1) DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    email_verified TINYINT(1) DEFAULT 0,
    verification_token VARCHAR(100),
    reset_token VARCHAR(100),
    reset_token_expiry DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    INDEX idx_email (email),
    INDEX idx_username (username),
    INDEX idx_user_type (user_type)
) ENGINE=InnoDB;

-- =====================================================
-- TABLE: stores
-- Seller store information
-- =====================================================
CREATE TABLE stores (
    store_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    store_name VARCHAR(100) UNIQUE NOT NULL,
    store_slug VARCHAR(100) UNIQUE NOT NULL,
    store_description TEXT,
    store_logo VARCHAR(255),
    store_banner VARCHAR(255),
    social_links JSON,
    is_approved TINYINT(1) DEFAULT 0,
    approval_status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    approval_date DATETIME,
    is_active TINYINT(1) DEFAULT 1,
    total_sales DECIMAL(10,2) DEFAULT 0.00,
    total_products INT DEFAULT 0,
    rating_average DECIMAL(3,2) DEFAULT 0.00,
    total_reviews INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_store_slug (store_slug),
    INDEX idx_user_id (user_id),
    INDEX idx_approval_status (approval_status)
) ENGINE=InnoDB;

-- =====================================================
-- TABLE: categories
-- Product categories
-- =====================================================
CREATE TABLE categories (
    category_id INT PRIMARY KEY AUTO_INCREMENT,
    category_name VARCHAR(50) NOT NULL,
    category_slug VARCHAR(50) UNIQUE NOT NULL,
    category_description TEXT,
    category_icon VARCHAR(100),
    parent_category_id INT DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_category_id) REFERENCES categories(category_id) ON DELETE SET NULL,
    INDEX idx_category_slug (category_slug)
) ENGINE=InnoDB;

-- =====================================================
-- TABLE: products
-- Digital products listing
-- =====================================================
CREATE TABLE products (
    product_id INT PRIMARY KEY AUTO_INCREMENT,
    store_id INT NOT NULL,
    category_id INT NOT NULL,
    product_name VARCHAR(200) NOT NULL,
    product_slug VARCHAR(200) UNIQUE NOT NULL,
    product_description TEXT,
    short_description VARCHAR(255) DEFAULT NULL,
    price DECIMAL(10,2) NOT NULL,
    discount_price DECIMAL(10,2) DEFAULT NULL,
    thumbnail_image VARCHAR(255),
    product_file_path VARCHAR(255) DEFAULT NULL,
    product_file_original_name VARCHAR(255) DEFAULT NULL,
    product_file_size_bytes BIGINT DEFAULT NULL,
    preview_images JSON,
    product_tags VARCHAR(500),
    file_type VARCHAR(50),
    file_size_mb DECIMAL(8,2),
    total_files INT DEFAULT 1,
    is_featured TINYINT(1) DEFAULT 0,
    is_approved TINYINT(1) DEFAULT 0,
    approval_status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    is_active TINYINT(1) DEFAULT 1,
    download_limit INT DEFAULT 3,
    download_expiry_hours INT DEFAULT 24,
    total_sales INT DEFAULT 0,
    total_downloads INT DEFAULT 0,
    views_count INT DEFAULT 0,
    rating_average DECIMAL(3,2) DEFAULT 0.00,
    total_reviews INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (store_id) REFERENCES stores(store_id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(category_id) ON DELETE RESTRICT,
    INDEX idx_product_slug (product_slug),
    INDEX idx_store_id (store_id),
    INDEX idx_category_id (category_id),
    INDEX idx_is_featured (is_featured),
    INDEX idx_approval_status (approval_status),
    INDEX idx_product_file (product_file_path),
    FULLTEXT idx_search (product_name, product_description, product_tags)
) ENGINE=InnoDB;

-- =====================================================
-- TABLE: product_files
-- Digital files associated with products
-- =====================================================
CREATE TABLE product_files (
    file_id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_original_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size_bytes BIGINT NOT NULL,
    file_type VARCHAR(50) NOT NULL,
    file_extension VARCHAR(10) NOT NULL,
    upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE,
    INDEX idx_product_id (product_id)
) ENGINE=InnoDB;

-- =====================================================
-- TABLE: transactions
-- Payment and order transactions
-- =====================================================
CREATE TABLE transactions (
    transaction_id INT PRIMARY KEY AUTO_INCREMENT,
    transaction_uuid VARCHAR(50) UNIQUE NOT NULL,
    buyer_id INT NOT NULL,
    seller_id INT NOT NULL,
    product_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    commission_percentage DECIMAL(5,2) DEFAULT 10.00,
    commission_amount DECIMAL(10,2) NOT NULL,
    seller_earnings DECIMAL(10,2) NOT NULL,
    payment_gateway VARCHAR(50) DEFAULT 'razorpay',
    payment_id VARCHAR(100),
    payment_status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
    order_id VARCHAR(100),
    transaction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    payment_date TIMESTAMP NULL,
    refund_date TIMESTAMP NULL,
    refund_reason TEXT,
    download_token VARCHAR(100) UNIQUE,
    download_count INT DEFAULT 0,
    download_expiry TIMESTAMP NULL,
    is_downloaded TINYINT(1) DEFAULT 0,
    FOREIGN KEY (buyer_id) REFERENCES users(user_id) ON DELETE RESTRICT,
    FOREIGN KEY (seller_id) REFERENCES users(user_id) ON DELETE RESTRICT,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE RESTRICT,
    INDEX idx_buyer_id (buyer_id),
    INDEX idx_seller_id (seller_id),
    INDEX idx_transaction_uuid (transaction_uuid),
    INDEX idx_payment_status (payment_status),
    INDEX idx_download_token (download_token)
) ENGINE=InnoDB;

-- =====================================================
-- TABLE: shopping_cart
-- User shopping cart items
-- =====================================================
CREATE TABLE shopping_cart (
    cart_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE,
    UNIQUE KEY unique_cart_item (user_id, product_id),
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB;

-- =====================================================
-- TABLE: reviews
-- Product reviews and ratings
-- =====================================================
CREATE TABLE reviews (
    review_id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    user_id INT NOT NULL,
    transaction_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    review_title VARCHAR(200),
    review_text TEXT,
    is_verified_purchase TINYINT(1) DEFAULT 1,
    is_approved TINYINT(1) DEFAULT 1,
    helpful_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (transaction_id) REFERENCES transactions(transaction_id) ON DELETE CASCADE,
    UNIQUE KEY unique_review (transaction_id),
    INDEX idx_product_id (product_id),
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB;

-- =====================================================
-- TABLE: payouts
-- Seller payout requests
-- =====================================================
CREATE TABLE payouts (
    payout_id INT PRIMARY KEY AUTO_INCREMENT,
    seller_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payout_method VARCHAR(50) DEFAULT 'bank_transfer',
    payout_details JSON,
    request_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending', 'processing', 'completed', 'rejected') DEFAULT 'pending',
    processed_date TIMESTAMP NULL,
    processed_by INT,
    admin_notes TEXT,
    transaction_reference VARCHAR(100),
    FOREIGN KEY (seller_id) REFERENCES users(user_id) ON DELETE RESTRICT,
    FOREIGN KEY (processed_by) REFERENCES users(user_id) ON DELETE SET NULL,
    INDEX idx_seller_id (seller_id),
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- =====================================================
-- TABLE: admin_settings
-- System configuration and settings
-- =====================================================
CREATE TABLE admin_settings (
    setting_id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    setting_type VARCHAR(50) DEFAULT 'string',
    setting_description TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    updated_by INT,
    FOREIGN KEY (updated_by) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- =====================================================
-- TABLE: notifications
-- User notifications system
-- =====================================================
CREATE TABLE notifications (
    notification_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    notification_type VARCHAR(50) NOT NULL,
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    link VARCHAR(255),
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_is_read (is_read)
) ENGINE=InnoDB;

-- =====================================================
-- TABLE: activity_logs
-- System activity logging
-- =====================================================
CREATE TABLE activity_logs (
    log_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    action_type VARCHAR(100) NOT NULL,
    action_description TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_action_type (action_type)
) ENGINE=InnoDB;

-- =====================================================
-- INSERT DEFAULT DATA
-- =====================================================

-- Insert default admin user (password: admin123)
INSERT INTO users (username, email, password_hash, full_name, user_type, is_verified, is_active, email_verified) 
VALUES ('admin', 'admin@prodigi.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'admin', 1, 1, 1);

-- Insert default categories
INSERT INTO categories (category_name, category_slug, category_description, category_icon, display_order) VALUES
('Graphics', 'graphics', 'Digital graphics, illustrations, and design assets', 'fas fa-palette', 1),
('Templates', 'templates', 'Website templates, presentation templates, and more', 'fas fa-file-code', 2),
('Audio', 'audio', 'Music, sound effects, and audio files', 'fas fa-music', 3),
('Video', 'video', 'Video clips, animations, and motion graphics', 'fas fa-video', 4),
('Ebooks', 'ebooks', 'Digital books, guides, and educational content', 'fas fa-book', 5),
('Software', 'software', 'Software tools, plugins, and applications', 'fas fa-laptop-code', 6);

-- Insert default admin settings
INSERT INTO admin_settings (setting_key, setting_value, setting_type, setting_description) VALUES
('site_name', 'PRODIGI', 'string', 'Website name'),
('site_email', 'support@prodigi.com', 'string', 'Support email address'),
('commission_percentage', '10.00', 'decimal', 'Platform commission percentage'),
('max_file_size_mb', '100', 'integer', 'Maximum file upload size in MB'),
('download_limit', '3', 'integer', 'Default download limit per purchase'),
('download_expiry_hours', '24', 'integer', 'Download link expiry time in hours'),
('razorpay_key_id', 'rzp_test_xxxxxxxx', 'string', 'Razorpay API Key ID'),
('razorpay_key_secret', 'xxxxxxxxxxxxxxxx', 'string', 'Razorpay API Secret Key'),
('currency', 'INR', 'string', 'Default currency'),
('timezone', 'Asia/Kolkata', 'string', 'System timezone'),
('maintenance_mode', '0', 'boolean', 'Enable maintenance mode'),
('allow_registration', '1', 'boolean', 'Allow new user registrations'),
('require_email_verification', '1', 'boolean', 'Require email verification'),
('seller_auto_approve', '0', 'boolean', 'Auto-approve seller accounts'),
('product_auto_approve', '0', 'boolean', 'Auto-approve product listings');

-- =====================================================
-- VIEWS FOR ANALYTICS
-- =====================================================

-- View: Store Analytics
CREATE VIEW view_store_analytics AS
SELECT 
    s.store_id,
    s.store_name,
    s.user_id,
    u.username,
    u.email,
    COUNT(DISTINCT p.product_id) as total_products,
    COUNT(DISTINCT t.transaction_id) as total_sales,
    COALESCE(SUM(t.seller_earnings), 0) as total_earnings,
    s.rating_average,
    s.created_at as store_created
FROM stores s
JOIN users u ON s.user_id = u.user_id
LEFT JOIN products p ON s.store_id = p.store_id AND p.is_active = 1
LEFT JOIN transactions t ON p.product_id = t.product_id AND t.payment_status = 'completed'
GROUP BY s.store_id;

-- View: Product Performance
CREATE VIEW view_product_performance AS
SELECT 
    p.product_id,
    p.product_name,
    p.price,
    s.store_name,
    c.category_name,
    p.total_sales,
    p.total_downloads,
    p.views_count,
    p.rating_average,
    p.total_reviews,
    COALESCE(SUM(t.amount), 0) as total_revenue,
    p.created_at
FROM products p
JOIN stores s ON p.store_id = s.store_id
JOIN categories c ON p.category_id = c.category_id
LEFT JOIN transactions t ON p.product_id = t.product_id AND t.payment_status = 'completed'
GROUP BY p.product_id;

-- View: Admin Dashboard Stats
CREATE VIEW view_admin_stats AS
SELECT 
    (SELECT COUNT(*) FROM users WHERE user_type != 'admin') as total_users,
    (SELECT COUNT(*) FROM users WHERE user_type = 'seller') as total_sellers,
    (SELECT COUNT(*) FROM stores WHERE is_approved = 1) as active_stores,
    (SELECT COUNT(*) FROM products WHERE is_active = 1) as total_products,
    (SELECT COUNT(*) FROM transactions WHERE payment_status = 'completed') as total_transactions,
    (SELECT COALESCE(SUM(amount), 0) FROM transactions WHERE payment_status = 'completed') as total_revenue,
    (SELECT COALESCE(SUM(commission_amount), 0) FROM transactions WHERE payment_status = 'completed') as total_commission,
    (SELECT COUNT(*) FROM stores WHERE approval_status = 'pending') as pending_store_approvals,
    (SELECT COUNT(*) FROM products WHERE approval_status = 'pending') as pending_product_approvals,
    (SELECT COUNT(*) FROM payouts WHERE status = 'pending') as pending_payouts;

-- =====================================================
-- TRIGGERS
-- =====================================================

-- Trigger: Update product statistics after new transaction
DELIMITER //
CREATE TRIGGER after_transaction_completed
AFTER UPDATE ON transactions
FOR EACH ROW
BEGIN
    IF NEW.payment_status = 'completed' AND OLD.payment_status != 'completed' THEN
        UPDATE products 
        SET total_sales = total_sales + 1
        WHERE product_id = NEW.product_id;
        
        UPDATE stores 
        SET total_sales = total_sales + NEW.amount
        WHERE store_id = (SELECT store_id FROM products WHERE product_id = NEW.product_id);
    END IF;
END//

-- Trigger: Update product rating after review
CREATE TRIGGER after_review_insert
AFTER INSERT ON reviews
FOR EACH ROW
BEGIN
    UPDATE products 
    SET 
        rating_average = (SELECT AVG(rating) FROM reviews WHERE product_id = NEW.product_id AND is_approved = 1),
        total_reviews = (SELECT COUNT(*) FROM reviews WHERE product_id = NEW.product_id AND is_approved = 1)
    WHERE product_id = NEW.product_id;
END//

CREATE TRIGGER after_review_update
AFTER UPDATE ON reviews
FOR EACH ROW
BEGIN
    UPDATE products 
    SET 
        rating_average = (SELECT AVG(rating) FROM reviews WHERE product_id = NEW.product_id AND is_approved = 1),
        total_reviews = (SELECT COUNT(*) FROM reviews WHERE product_id = NEW.product_id AND is_approved = 1)
    WHERE product_id = NEW.product_id;
END//

DELIMITER ;

-- =====================================================
-- END OF SCHEMA
-- =====================================================

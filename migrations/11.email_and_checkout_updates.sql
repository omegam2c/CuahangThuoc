-- =====================================================
-- 11. CẬP NHẬT HỆ THỐNG EMAIL, GIỎ HÀNG & THANH TOÁN
-- Dành cho người dùng đã có sẵn Database cũ
-- =====================================================

USE pharmacy_db;

-- 1. Cập nhật bảng orders
-- Thêm cột email cho khách vãng lai và bổ sung phương thức PayOS
ALTER TABLE orders 
MODIFY COLUMN user_id INT NULL,
ADD COLUMN IF NOT EXISTS customer_email VARCHAR(255) NULL AFTER user_id,
MODIFY COLUMN payment_method ENUM('cod', 'bank_transfer', 'e_wallet', 'payos') DEFAULT 'cod',
MODIFY COLUMN payment_status ENUM('unpaid', 'paid', 'refunded') DEFAULT 'unpaid';

-- 2. Cập nhật bảng carts (Hỗ trợ giỏ hàng khách vãng lai)
-- Tự động tìm và xóa khóa ngoại cũ để tránh lỗi #1553
SET @constraint_name = (SELECT CONSTRAINT_NAME 
                        FROM information_schema.KEY_COLUMN_USAGE 
                        WHERE TABLE_NAME = 'carts' 
                        AND COLUMN_NAME = 'user_id' 
                        AND CONSTRAINT_NAME <> 'PRIMARY' LIMIT 1);

SET @query = IF(@constraint_name IS NOT NULL, 
                CONCAT('ALTER TABLE carts DROP FOREIGN KEY ', @constraint_name), 
                'SELECT "No FK found"');
PREPARE stmt FROM @query; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Xóa index Unique cũ nếu tồn tại
ALTER TABLE carts DROP INDEX IF EXISTS unique_user_cart;

-- Thực hiện thay đổi cấu trúc bảng carts
ALTER TABLE carts 
MODIFY COLUMN user_id INT NULL, 
ADD COLUMN IF NOT EXISTS session_id VARCHAR(255) NULL AFTER user_id;

-- Thêm lại khóa ngoại và index mới
ALTER TABLE carts ADD CONSTRAINT fk_cart_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE;
ALTER TABLE carts ADD INDEX IF NOT EXISTS idx_session_cart (session_id);

-- 3. Tạo bảng nhật ký gửi Email (Email Logs)
CREATE TABLE IF NOT EXISTS email_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NULL,
    recipient_email VARCHAR(255) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    type VARCHAR(50) NOT NULL COMMENT 'order_confirmation, order_completed, payment_success, password_reset',
    status ENUM('sent', 'failed', 'opened') DEFAULT 'sent',
    sent_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    error_message TEXT NULL,
    tracking_id VARCHAR(100) UNIQUE,
    
    INDEX idx_order_id (order_id),
    INDEX idx_recipient (recipient_email),
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Phân quyền Admin
INSERT IGNORE INTO permissions (permission_name, description) 
VALUES ('manage_emails', 'Quản lý và theo dõi nhật ký gửi email hệ thống');

INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT 1, permission_id FROM permissions WHERE permission_name = 'manage_emails';

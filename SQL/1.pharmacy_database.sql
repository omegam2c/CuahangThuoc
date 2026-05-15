-- =====================================================
-- DATABASE QUẢN LÝ NHÀ THUỐC - CHUYÊN NGHIỆP
-- Phiên bản: 1.0
-- Hỗ trợ: MySQL 5.7+ / MariaDB 10.3+
-- Tính năng: Quản lý lô hàng (FEFO), Phân quyền, Đơn thuốc
-- =====================================================

DROP DATABASE IF EXISTS pharmacy_db;
CREATE DATABASE pharmacy_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE pharmacy_db;

-- Tắt kiểm tra khóa ngoại để chèn dữ liệu mẫu
SET FOREIGN_KEY_CHECKS = 0;

-- =====================================================
-- 1. BẢNG PHÂN QUYỀN & NGƯỜI DÙNG
-- =====================================================

-- Bảng vai trò (roles)
CREATE TABLE roles (
    role_id INT PRIMARY KEY AUTO_INCREMENT,
    role_name VARCHAR(50) NOT NULL UNIQUE COMMENT 'Admin, Dược sĩ, Chủ cửa hàng, Khách hàng',
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Bảng người dùng
CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    role_id INT NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL COMMENT 'Mã hóa bằng bcrypt/argon2',
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE,
    phone VARCHAR(20),
    address TEXT,
    avatar VARCHAR(255) DEFAULT 'default-avatar.png',
    is_active BOOLEAN DEFAULT TRUE,
    last_login DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(role_id) ON DELETE RESTRICT,
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_role (role_id)
) ENGINE=InnoDB;

-- Bảng quyền (permissions)
CREATE TABLE permissions (
    permission_id INT PRIMARY KEY AUTO_INCREMENT,
    permission_name VARCHAR(100) NOT NULL UNIQUE COMMENT 'view_products, manage_inventory, approve_orders',
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Bảng phân quyền cho từng vai trò
CREATE TABLE role_permissions (
    role_id INT,
    permission_id INT,
    PRIMARY KEY (role_id, permission_id),
    FOREIGN KEY (role_id) REFERENCES roles(role_id) ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES permissions(permission_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =====================================================
-- 2. BẢNG DANH MỤC & SẢN PHẨM
-- =====================================================

-- Bảng nhà sản xuất
CREATE TABLE manufacturers (
    manufacturer_id INT PRIMARY KEY AUTO_INCREMENT,
    manufacturer_name VARCHAR(200) NOT NULL,
    country VARCHAR(100),
    website VARCHAR(255),
    contact_info TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Bảng danh mục sản phẩm
CREATE TABLE categories (
    category_id INT PRIMARY KEY AUTO_INCREMENT,
    category_name VARCHAR(100) NOT NULL,
    description TEXT,
    parent_category_id INT DEFAULT NULL COMMENT 'Hỗ trợ danh mục con',
    image_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_category_id) REFERENCES categories(category_id) ON DELETE SET NULL,
    INDEX idx_parent (parent_category_id)
) ENGINE=InnoDB;

-- Bảng hoạt chất (Ingredients) - Chuẩn hóa (Ref point 4)
CREATE TABLE ingredients (
    ingredient_id INT PRIMARY KEY AUTO_INCREMENT,
    ingredient_name VARCHAR(255) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Bảng sản phẩm (thuốc)
CREATE TABLE products (
    product_id INT PRIMARY KEY AUTO_INCREMENT,
    category_id INT NOT NULL,
    manufacturer_id INT,
    product_name VARCHAR(255) NOT NULL,
    generic_name VARCHAR(255) COMMENT 'Tên gốc/nhóm thuốc',
    dosage_form VARCHAR(100) COMMENT 'Viên nén, Viên nang, Siro, Ống tiêm...',
    strength VARCHAR(100) COMMENT 'Nồng độ: 500mg, 10ml...',
    unit VARCHAR(50) DEFAULT 'Viên' COMMENT 'Đơn vị tính: Viên, Hộp, Chai, Tuýp',
    
    -- Thông tin y tế
    indications TEXT COMMENT 'Công dụng, chỉ định',
    contraindications TEXT COMMENT 'Chống chỉ định',
    side_effects TEXT COMMENT 'Tác dụng phụ',
    dosage_instructions TEXT COMMENT 'Liều dùng, cách dùng',
    storage_conditions TEXT COMMENT 'Điều kiện bảo quản',
    
    -- Phân loại bán hàng - Chuẩn hóa (Ref point 1)
    sale_type ENUM('otc', 'prescription') DEFAULT 'otc' COMMENT 'OTC: Không kê đơn, Prescription: Kê đơn',
    is_prescription_required BOOLEAN DEFAULT FALSE,
    is_otc BOOLEAN DEFAULT TRUE,
    
    -- Hoạt chất (Legacy support)
    active_ingredients TEXT,
    
    -- Giá & hình ảnh
    price DECIMAL(12, 2) NOT NULL DEFAULT 0,
    discount_percent DECIMAL(5, 2) DEFAULT 0,
    image_url VARCHAR(255),
    additional_images TEXT COMMENT 'Lưu nhiều ảnh, phân tách bằng dấu ;',
    
    -- Trạng thái
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (category_id) REFERENCES categories(category_id) ON DELETE RESTRICT,
    FOREIGN KEY (manufacturer_id) REFERENCES manufacturers(manufacturer_id) ON DELETE SET NULL,
    INDEX idx_category (category_id),
    INDEX idx_name (product_name),
    INDEX idx_sale_type (sale_type)
) ENGINE=InnoDB;

-- Bảng liên kết Sản phẩm - Hoạt chất (Ref point 4)
CREATE TABLE product_ingredients (
    product_id INT,
    ingredient_id INT,
    amount VARCHAR(100) COMMENT 'Hàm lượng: 500mg, 10mg...',
    PRIMARY KEY (product_id, ingredient_id),
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE,
    FOREIGN KEY (ingredient_id) REFERENCES ingredients(ingredient_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =====================================================
-- 3. QUẢN LÝ KHO & LÔ HÀNG (FEFO)
-- =====================================================

-- Bảng nhà cung cấp
CREATE TABLE suppliers (
    supplier_id INT PRIMARY KEY AUTO_INCREMENT,
    supplier_name VARCHAR(200) NOT NULL,
    contact_person VARCHAR(100),
    phone VARCHAR(20),
    email VARCHAR(100),
    address TEXT,
    tax_code VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Bảng phiếu nhập kho
CREATE TABLE stock_receipts (
    receipt_id INT PRIMARY KEY AUTO_INCREMENT,
    supplier_id INT,
    user_id INT COMMENT 'Nhân viên nhập kho',
    receipt_date DATE NOT NULL,
    invoice_number VARCHAR(100),
    total_amount DECIMAL(15, 2),
    notes TEXT,
    status ENUM('pending', 'completed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(supplier_id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL,
    INDEX idx_date (receipt_date),
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- Bảng lô hàng (QUAN TRỌNG - HỖ TRỢ FEFO)
CREATE TABLE batches (
    batch_id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    receipt_id INT,
    batch_number VARCHAR(100) NOT NULL COMMENT 'Số lô sản xuất',
    manufacture_date DATE NOT NULL,
    expiry_date DATE NOT NULL COMMENT 'Hạn sử dụng - Trọng tâm FEFO',
    
    quantity_received INT NOT NULL DEFAULT 0,
    quantity_remaining INT NOT NULL DEFAULT 0,
    
    purchase_price DECIMAL(12, 2),
    selling_price DECIMAL(12, 2),
    
    storage_location VARCHAR(100),
    
    -- Cảnh báo hạn sử dụng (Remove redundant fields - Ref point 2, 3)
    expiry_alert_days INT DEFAULT 90,
    
    status ENUM('active', 'near_expiry', 'expired', 'recalled') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE,
    FOREIGN KEY (receipt_id) REFERENCES stock_receipts(receipt_id) ON DELETE SET NULL,
    INDEX idx_product (product_id),
    INDEX idx_expiry (expiry_date),
    INDEX idx_status (status),
    UNIQUE KEY unique_batch (product_id, batch_number)
) ENGINE=InnoDB;

-- Nhật ký biến động kho (Inventory Transaction Ledger - Ref point 7)
CREATE TABLE inventory_transactions (
    transaction_id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    batch_id INT NOT NULL,
    user_id INT NULL,
    order_id INT NULL,
    receipt_id INT NULL,
    
    transaction_type ENUM('import', 'sale', 'return', 'expired', 'adjustment', 'cancel_order') NOT NULL,
    quantity INT NOT NULL COMMENT 'Số lượng thay đổi (+/-)',
    note TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE,
    FOREIGN KEY (batch_id) REFERENCES batches(batch_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL,
    INDEX idx_product_trans (product_id),
    INDEX idx_batch_trans (batch_id),
    INDEX idx_type (transaction_type)
) ENGINE=InnoDB;

-- Bảng chi tiết phiếu nhập
CREATE TABLE stock_receipt_details (
    detail_id INT PRIMARY KEY AUTO_INCREMENT,
    receipt_id INT NOT NULL,
    batch_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(12, 2) NOT NULL,
    subtotal DECIMAL(15, 2) GENERATED ALWAYS AS (quantity * unit_price) STORED,
    FOREIGN KEY (receipt_id) REFERENCES stock_receipts(receipt_id) ON DELETE CASCADE,
    FOREIGN KEY (batch_id) REFERENCES batches(batch_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Bảng tương tác thuốc (Ref point 5: Symmetry)
CREATE TABLE drug_interactions (
    interaction_id INT PRIMARY KEY AUTO_INCREMENT,
    drug_a_id INT NOT NULL,
    drug_b_id INT NOT NULL,
    severity ENUM('mild', 'moderate', 'severe', 'contraindicated') DEFAULT 'moderate',
    description TEXT,
    recommendation TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (drug_a_id) REFERENCES products(product_id) ON DELETE CASCADE,
    FOREIGN KEY (drug_b_id) REFERENCES products(product_id) ON DELETE CASCADE,
    UNIQUE KEY unique_interaction (drug_a_id, drug_b_id)
) ENGINE=InnoDB;

-- =====================================================
-- 5. GIỎ HÀNG & ĐơN HÀNG
-- =====================================================

-- Bảng giỏ hàng (Ref point 9: Uniqueness)
CREATE TABLE carts (
    cart_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NULL,
    session_id VARCHAR(255) NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_user_cart (user_id),
    INDEX idx_session_cart (session_id)
) ENGINE=InnoDB;

-- Bảng chi tiết giỏ hàng
CREATE TABLE cart_items (
    cart_item_id INT PRIMARY KEY AUTO_INCREMENT,
    cart_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cart_id) REFERENCES carts(cart_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE,
    UNIQUE KEY unique_cart_product (cart_id, product_id)
) ENGINE=InnoDB;

-- Bảng mã giảm giá (vouchers)
CREATE TABLE vouchers (
    voucher_id INT PRIMARY KEY AUTO_INCREMENT,
    voucher_code VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    discount_type ENUM('percent', 'fixed') DEFAULT 'percent',
    discount_value DECIMAL(12, 2) NOT NULL,
    min_order_amount DECIMAL(12, 2) DEFAULT 0,
    max_discount_amount DECIMAL(12, 2) NULL,
    usage_limit INT DEFAULT 1,
    used_count INT DEFAULT 0,
    valid_from DATE,
    valid_to DATE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_code (voucher_code)
) ENGINE=InnoDB;

-- Bảng đơn hàng
CREATE TABLE orders (
    order_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NULL,
    voucher_id INT NULL,
    
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    subtotal DECIMAL(15, 2) NOT NULL,
    discount_amount DECIMAL(15, 2) DEFAULT 0,
    shipping_fee DECIMAL(12, 2) DEFAULT 0,
    total_amount DECIMAL(15, 2) NOT NULL,
    
    payment_method ENUM('cod', 'bank_transfer', 'e_wallet', 'payos') DEFAULT 'cod',
    payment_status ENUM('unpaid', 'paid', 'refunded') DEFAULT 'unpaid',
    status ENUM('pending', 'confirmed', 'preparing', 'shipping', 'completed', 'cancelled', 'returned') DEFAULT 'pending',
    
    -- Thông tin giao hàng
    shipping_name VARCHAR(100) NOT NULL,
    shipping_phone VARCHAR(20) NOT NULL,
    customer_email VARCHAR(100) NULL,
    shipping_address TEXT NOT NULL,
    shipping_note TEXT,
    
    -- Đơn thuốc (Nếu có)
    has_prescription BOOLEAN DEFAULT FALSE,
    prescription_image VARCHAR(255) NULL,
    prescription_verified BOOLEAN DEFAULT FALSE,
    verified_by INT NULL COMMENT 'Dược sĩ xác nhận',
    verified_at DATETIME NULL,
    
    admin_note TEXT,
    cancel_reason TEXT,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL,
    FOREIGN KEY (voucher_id) REFERENCES vouchers(voucher_id) ON DELETE SET NULL,
    FOREIGN KEY (verified_by) REFERENCES users(user_id) ON DELETE SET NULL,
    INDEX idx_user_order (user_id),
    INDEX idx_order_date (order_date),
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- Bảng chi tiết đơn hàng
CREATE TABLE order_details (
    detail_id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    batch_id INT NOT NULL COMMENT 'Xuất từ lô nào (FEFO)',
    product_name VARCHAR(255) NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(12, 2) NOT NULL,
    discount_percent DECIMAL(5, 2) DEFAULT 0,
    subtotal DECIMAL(15, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE RESTRICT,
    FOREIGN KEY (batch_id) REFERENCES batches(batch_id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Bảng đánh giá sản phẩm (Ref point 6: Constraint)
CREATE TABLE reviews (
    review_id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    user_id INT NOT NULL,
    order_id INT NULL COMMENT 'Liên kết với đơn hàng đã mua',
    rating INT NOT NULL COMMENT '1-5 sao',
    comment TEXT,
    image_url VARCHAR(255),
    is_verified_purchase BOOLEAN DEFAULT FALSE,
    is_approved BOOLEAN DEFAULT FALSE,
    admin_reply TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE SET NULL,
    UNIQUE KEY unique_review (product_id, user_id)
) ENGINE=InnoDB;

-- Bảng log hoạt động
CREATE TABLE activity_logs (
    log_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    table_name VARCHAR(50),
    record_id INT,
    description TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL,
    INDEX idx_user_action (user_id, action)
) ENGINE=InnoDB;

-- Bảng lịch sử thay đổi giá (Ref point: Trigger Support)
CREATE TABLE price_history (
    history_id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    old_price DECIMAL(12, 2),
    new_price DECIMAL(12, 2),
    changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =====================================================
-- 8. TRIGGERS & STORED PROCEDURES
-- =====================================================

DELIMITER $$


-- Stored Procedure: Lấy lô hàng theo FEFO (Ref point 8: Concurrency)
CREATE PROCEDURE get_batch_fefo(
    IN p_product_id INT,
    IN p_quantity_needed INT
)
BEGIN
    -- Sử dụng FOR UPDATE để tránh oversell trong transaction
    SELECT 
        batch_id,
        batch_number,
        quantity_remaining,
        expiry_date,
        selling_price
    FROM batches
    WHERE product_id = p_product_id
        AND quantity_remaining > 0
        AND status = 'active'
        AND expiry_date > CURDATE()
    ORDER BY expiry_date ASC, manufacture_date ASC
    LIMIT 5
    FOR UPDATE;
END$$

-- Stored Procedure: Kiểm tra tương tác thuốc trong giỏ hàng (Ref point 5: Safety)
CREATE PROCEDURE check_cart_interactions(
    IN p_cart_id INT
)
BEGIN
    SELECT 
        di.severity,
        di.description,
        di.recommendation,
        p1.product_name AS drug_a_name,
        p2.product_name AS drug_b_name
    FROM cart_items ci1
    JOIN cart_items ci2 ON ci1.cart_id = ci2.cart_id AND ci1.product_id < ci2.product_id
    JOIN drug_interactions di ON 
        (ci1.product_id = di.drug_a_id AND ci2.product_id = di.drug_b_id)
    JOIN products p1 ON ci1.product_id = p1.product_id
    JOIN products p2 ON ci2.product_id = p2.product_id
    WHERE ci1.cart_id = p_cart_id;
END$$

-- Trigger: Ngăn chặn tương tác thuốc trùng lặp (Ref point 5: Symmetry)
CREATE TRIGGER trg_drug_interactions_symmetry
BEFORE INSERT ON drug_interactions
FOR EACH ROW
BEGIN
    DECLARE temp_id INT;
    IF NEW.drug_a_id > NEW.drug_b_id THEN
        SET temp_id = NEW.drug_a_id;
        SET NEW.drug_a_id = NEW.drug_b_id;
        SET NEW.drug_b_id = temp_id;
    END IF;
END$$

-- Trigger: Kiểm tra rating (Ref point 6: MySQL 5.7 support)
CREATE TRIGGER trg_reviews_rating_check
BEFORE INSERT ON reviews
FOR EACH ROW
BEGIN
    IF NEW.rating < 1 OR NEW.rating > 5 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Rating must be between 1 and 5';
    END IF;
END$$

-- Trigger: Tự động cập nhật số lượng lô hàng từ ledger (Ref point 7)
CREATE TRIGGER trg_inventory_transaction_after_insert
AFTER INSERT ON inventory_transactions
FOR EACH ROW
BEGIN
    UPDATE batches 
    SET quantity_remaining = quantity_remaining + NEW.quantity
    WHERE batch_id = NEW.batch_id;
    
    -- Nếu số lượng về 0, chuyển status sang active (hoặc giữ nguyên nếu cần)
    -- Nếu hết hạn, trigger khác sẽ xử lý hoặc qua View
END$$

DELIMITER ;

-- =====================================================
-- 9. VIEWS (Khung nhìn tiện ích)
-- =====================================================

-- View: Tồn kho chi tiết theo sản phẩm
CREATE VIEW v_inventory_summary AS
SELECT 
    p.product_id,
    p.product_name,
    p.generic_name,
    c.category_name,
    COUNT(DISTINCT b.batch_id) as total_batches,
    SUM(b.quantity_remaining) as total_quantity,
    MIN(b.expiry_date) as earliest_expiry,
    SUM(CASE WHEN b.status = 'near_expiry' THEN b.quantity_remaining ELSE 0 END) as near_expiry_quantity,
    SUM(CASE WHEN b.status = 'expired' THEN b.quantity_remaining ELSE 0 END) as expired_quantity,
    p.price,
    SUM(b.quantity_remaining) * p.price as inventory_value
FROM products p
LEFT JOIN batches b ON p.product_id = b.product_id
LEFT JOIN categories c ON p.category_id = c.category_id
WHERE p.is_active = TRUE
GROUP BY p.product_id, p.product_name, p.generic_name, c.category_name, p.price;

-- View: Đơn hàng cần duyệt đơn thuốc
CREATE VIEW v_pending_prescriptions AS
SELECT 
    o.order_id,
    o.order_date,
    u.full_name as customer_name,
    u.phone as customer_phone,
    o.prescription_image,
    o.shipping_address,
    o.total_amount,
    o.status
FROM orders o
JOIN users u ON o.user_id = u.user_id
WHERE o.has_prescription = TRUE 
    AND o.prescription_verified = FALSE
    AND o.status NOT IN ('cancelled', 'completed')
ORDER BY o.order_date DESC;

-- View: Sản phẩm bán chạy
CREATE VIEW v_bestsellers AS
SELECT 
    p.product_id,
    p.product_name,
    p.image_url,
    p.price,
    COUNT(od.detail_id) as times_ordered,
    SUM(od.quantity) as total_sold,
    SUM(od.subtotal) as total_revenue,
    AVG(r.rating) as avg_rating,
    COUNT(DISTINCT r.review_id) as review_count
FROM products p
LEFT JOIN order_details od ON p.product_id = od.product_id
LEFT JOIN orders o ON od.order_id = o.order_id AND o.status = 'completed'
LEFT JOIN reviews r ON p.product_id = r.product_id
WHERE p.is_active = TRUE
GROUP BY p.product_id, p.product_name, p.image_url, p.price
HAVING total_sold > 0
ORDER BY total_sold DESC;

-- View: Cảnh báo lô hàng sắp hết hạn
CREATE OR REPLACE VIEW v_expiry_alerts AS
SELECT 
    b.batch_id,
    p.product_name,
    b.batch_number,
    b.manufacture_date,
    b.expiry_date,
    DATEDIFF(b.expiry_date, CURDATE()) as days_to_expiry,
    b.quantity_remaining,
    b.storage_location,
    b.status,
    CASE 
        WHEN DATEDIFF(b.expiry_date, CURDATE()) <= 0 THEN 'danger'
        WHEN DATEDIFF(b.expiry_date, CURDATE()) <= 30 THEN 'warning'
        WHEN DATEDIFF(b.expiry_date, CURDATE()) <= 90 THEN 'info'
        ELSE 'normal'
    END as alert_level
FROM batches b
JOIN products p ON b.product_id = p.product_id
WHERE b.quantity_remaining > 0 
    AND DATEDIFF(b.expiry_date, CURDATE()) <= 90
ORDER BY b.expiry_date ASC;

-- =====================================================
-- 10. DỮ LIỆU MẪU (SAMPLE DATA)
-- =====================================================

-- 10.1 Vai trò
INSERT INTO roles (role_name, description) VALUES
('Admin', 'Quản trị viên kỹ thuật - cấu hình hệ thống'),
('Dược sĩ', 'Dược sĩ - duyệt đơn thuốc, tư vấn khách hàng'),
('Chủ cửa hàng', 'Chủ cửa hàng - quản lý doanh thu, nhân sự và nhập hàng'),
('Khách hàng', 'Khách hàng - mua hàng trực tuyến');

-- 10.2 Quyền (Permissions)
INSERT INTO permissions (permission_name, description) VALUES
('view_business_stats', 'Xem báo cáo doanh thu, lợi nhuận'),
('manage_inventory', 'Quản lý nhập kho, lô hàng, nhà cung cấp'),
('manage_pharmacists', 'Quản lý tài khoản dược sĩ'),
('manage_prices_vouchers', 'Quản lý giá bán và mã giảm giá'),
('approve_prescriptions', 'Duyệt chuyên môn đơn thuốc'),
('process_orders', 'Tiếp nhận và xử lý đơn hàng'),
('system_config', 'Cấu hình hệ thống (SMTP, API)'),
('view_audit_logs', 'Xem nhật ký hệ thống và bảo mật'),
('manage_categories', 'Quản lý danh mục và thông số thuốc'),
('view_inventory', 'Xem tồn kho và lô hàng'),
('manage_chatbot', 'Cấu hình Trợ lý AI Gemini');

-- Phân quyền cho Chủ cửa hàng (Role 3)
INSERT INTO role_permissions (role_id, permission_id) 
SELECT 3, permission_id FROM permissions 
WHERE permission_name IN ('view_business_stats', 'manage_inventory', 'manage_pharmacists', 'manage_prices_vouchers', 'process_orders', 'manage_chatbot', 'view_audit_logs');

-- Phân quyền cho Dược sĩ (Role 2)
INSERT INTO role_permissions (role_id, permission_id)
SELECT 2, permission_id FROM permissions 
WHERE permission_name IN ('approve_prescriptions', 'process_orders', 'view_inventory');

-- Phân quyền cho Admin (Role 1) - Toàn quyền hệ thống
INSERT INTO role_permissions (role_id, permission_id)
SELECT 1, permission_id FROM permissions;

-- 10.3 Người dùng (Tất cả mật khẩu: "Check@123")
REPLACE INTO users (user_id, role_id, username, password_hash, full_name, email, phone, address) VALUES
(1, 1, 'admin',    '$2y$10$.VMzF/ziiVGRvO0mF0FL5ecA53LXSBw2UvNeRA4br9D1BNpmXjXRG', 'Nguyễn Văn Admin',  'admin@pharmacy.com',  '0901234567', '123 Nguyễn Huệ, Q.1, TP.HCM'),
(2, 2, 'duocsi01', '$2y$10$.VMzF/ziiVGRvO0mF0FL5ecA53LXSBw2UvNeRA4br9D1BNpmXjXRG', 'Trần Thị Hoa',      'duocsi@pharmacy.com', '0907654321', '456 Lê Lợi, Q.1, TP.HCM'),
(3, 3, 'chu01',    '$2y$10$.VMzF/ziiVGRvO0mF0FL5ecA53LXSBw2UvNeRA4br9D1BNpmXjXRG', 'Nguyễn Thúy Nga',   'owner@pharmacy.com',  '0909876543', '789 Trần Hưng Đạo, Q.5, TP.HCM'),
(4, 4, 'khach01',  '$2y$10$.VMzF/ziiVGRvO0mF0FL5ecA53LXSBw2UvNeRA4br9D1BNpmXjXRG', 'Phạm Thị Lan',      'khach01@gmail.com',   '0912345678', '321 Võ Văn Tần, Q.3, TP.HCM'),
(5, 4, 'khach02',  '$2y$10$.VMzF/ziiVGRvO0mF0FL5ecA53LXSBw2UvNeRA4br9D1BNpmXjXRG', 'Hoàng Văn Nam',     'khach02@gmail.com',   '0923456789', '654 Cách Mạng Tháng 8, Q.10, TP.HCM');

-- 10.4 Nhà sản xuất
INSERT INTO manufacturers (manufacturer_name, country, website) VALUES
('Công ty Dược Hậu Giang', 'Việt Nam', 'https://www.dhhg.com.vn'),
('Công ty Dược Traphaco', 'Việt Nam', 'https://www.traphaco.com.vn'),
('Pfizer Inc.', 'Hoa Kỳ', 'https://www.pfizer.com'),
('Sanofi', 'Pháp', 'https://www.sanofi.com'),
('Abbott Laboratories', 'Hoa Kỳ', 'https://www.abbott.com'),
('Roche', 'Thụy Sĩ', 'https://www.roche.com');

-- 10.5 Danh mục
INSERT INTO categories (category_name, description, parent_category_id) VALUES
('Thuốc kê đơn', 'Thuốc chỉ bán theo đơn của bác sĩ', NULL),
('Thuốc không kê đơn', 'Thuốc OTC - bán tự do', NULL),
('Thực phẩm chức năng', 'Vitamin, khoáng chất, thảo dược', NULL),
('Chăm sóc cá nhân', 'Sản phẩm vệ sinh, làm đẹp', NULL),
('Thiết bị y tế', 'Nhiệt kế, huyết áp, đường huyết', NULL),
('Kháng sinh', 'Thuốc kháng sinh', 1),
('Giảm đau hạ sốt', 'Thuốc giảm đau, hạ sốt', 2),
('Vitamin', 'Vitamin tổng hợp, vitamin đơn', 3);

-- 10.6 Sản phẩm
INSERT INTO products (category_id, manufacturer_id, product_name, generic_name, dosage_form, strength, unit, 
    active_ingredients, indications, contraindications, side_effects, dosage_instructions, storage_conditions,
    is_prescription_required, is_otc, price, discount_percent, image_url) VALUES

(6, 1, 'Amoxicillin 500mg DHG', 'Amoxicillin', 'Viên nang', '500mg', 'Viên',
    'Amoxicillin trihydrate 500mg',
    'Nhiễm khuẩn đường hô hấp, tai mũi họng, da',
    'Dị ứng với Penicillin',
    'Buồn nôn, tiêu chảy, phát ban da',
    'Người lớn: 500mg x 3 lần/ngày. Uống sau ăn.',
    'Nơi khô mát, tránh ánh sáng. Nhiệt độ dưới 30°C',
    FALSE, TRUE, 45000, 0.00, 'amoxicillin.jpg'),

(7, 1, 'Paracetamol 500mg', 'Paracetamol', 'Viên nén', '500mg', 'Viên',
    'Paracetamol 500mg',
    'Giảm đau, hạ sốt',
    'Suy gan nặng',
    'Hiếm gặp: Phát ban, rối loạn tiêu hóa',
    'Người lớn: 1-2 viên x 3-4 lần/ngày. Không quá 4g/ngày.',
    'Bảo quản nơi khô, tránh ánh sáng',
    FALSE, TRUE, 15000, 0.00, 'paracetamol.jpg'),

(7, 3, 'Aspirin 100mg', 'Aspirin', 'Viên nén bao phim', '100mg', 'Viên',
    'Acid acetylsalicylic 100mg',
    'Dự phòng đột quỵ, nhồi máu cơ tim',
    'Loét dạ dày, xuất huyết tiêu hóa',
    'Đau dạ dày, buồn nôn',
    '1 viên/ngày, uống sau ăn',
    'Nơi khô mát, nhiệt độ dưới 25°C',
    TRUE, FALSE, 35000, 0.00, 'aspirin.jpg'),

(8, 5, 'Vitamin C 1000mg Abbott', 'Ascorbic Acid', 'Viên sủi', '1000mg', 'Viên',
    'Ascorbic Acid 1000mg',
    'Bổ sung vitamin C, tăng sức đề kháng',
    'Sỏi thận oxalat',
    'Hiếm gặp: Tiêu chảy khi dùng liều cao',
    '1 viên/ngày, hòa tan vào 200ml nước',
    'Nơi khô mát, tránh ẩm',
    FALSE, TRUE, 120000, 0.00, 'vitamin-c.jpg'),

(8, 5, 'Vitamin D3 1000IU', 'Cholecalciferol', 'Viên nang mềm', '1000IU', 'Viên',
    'Cholecalciferol 1000IU',
    'Phòng ngừa thiếu vitamin D, loãng xương',
    'Tăng canxi máu',
    'Hiếm gặp: Táo bón, buồn nôn',
    '1 viên/ngày, uống cùng bữa ăn có dầu mỡ',
    'Bảo quản nơi khô, tránh ánh sáng',
    FALSE, TRUE, 180000, 0.00, 'vitamin-d3.jpg'),

(2, 2, 'Oresol 245', 'Oresol', 'Gói bột pha', '245mg', 'Gói',
    'Glucose, Natri clorid, Kali clorid, Natri citrat',
    'Bù nước điện giải khi tiêu chảy, mất nước',
    'Suy thận nặng',
    'Không',
    'Pha 1 gói vào 200ml nước sôi để nguội. Uống ngay sau pha.',
    'Nơi khô mát',
    FALSE, TRUE, 5000, 0.00, 'oresol.jpg'),

(1, 4, 'Metformin 500mg', 'Metformin', 'Viên nén bao phim', '500mg', 'Viên',
    'Metformin HCl 500mg',
    'Điều trị đái tháo đường type 2',
    'Suy thận, suy gan nặng, nhiễm toan ceton',
    'Buồn nôn, tiêu chảy, đầy hơi',
    '500mg x 2-3 lần/ngày, uống cùng bữa ăn',
    'Nơi khô mát, tránh ánh sáng',
    TRUE, FALSE, 55000, 0.00, 'metformin.jpg'),

(1, 6, 'Lipitor 20mg', 'Atorvastatin', 'Viên nén bao phim', '20mg', 'Viên',
    'Atorvastatin 20mg',
    'Giảm cholesterol máu, phòng ngừa tim mạch',
    'Bệnh gan đang tiến triển, thai kỳ',
    'Đau cơ, tăng men gan',
    '1 viên/ngày, có thể uống bất kỳ lúc nào',
    'Nhiệt độ phòng (15-30°C)',
    TRUE, FALSE, 280000, 0.00, 'lipitor.jpg'),

(3, 5, 'Ensure Gold 850g', 'Ensure', 'Bột pha sữa', '850g', 'Hộp',
    'Protein, Vitamin, Khoáng chất, HMB',
    'Bổ sung dinh dưỡng cho người lớn tuổi',
    'Galactosemia',
    'Hiếm gặp: Đầy hơi, khó tiêu',
    'Pha 6 muỗng (51g) vào 190ml nước ấm. 2 lần/ngày.',
    'Nơi khô mát, sau khi mở nắp dùng trong 3 tuần',
    FALSE, TRUE, 680000, 0.00, 'ensure-gold.jpg'),

(5, 5, 'Nhiệt kế điện tử Omron', 'Thermometer', 'Thiết bị', 'N/A', 'Cái',
    'N/A',
    'Đo nhiệt độ cơ thể',
    'Không',
    'Không',
    'Đặt đầu đo dưới lưỡi/nách, đợi tín hiệu beep',
    'Nơi khô, tránh va đập mạnh',
    FALSE, TRUE, 150000, 0.00, 'thermometer.jpg'),

(7, 1, 'Siro Ho Prospan 100ml', 'Hedera Helix', 'Siro', '100ml', 'Chai',
    'Cao khô lá thường xuân 700mg/100ml',
    'Điều trị ho có đờm, viêm phế quản cấp và mãn tính',
    'Mẫn cảm với thành phần của thuốc',
    'Buồn nôn, tiêu chảy',
    'Trẻ em 2.5ml-5ml x 3 lần/ngày',
    'Nơi khô mát, dưới 30°C',
    FALSE, TRUE, 125000, 0.00, 'prospan.jpg'),

(2, 2, 'Salonpas Gel 30g', 'Salonpas', 'Gel bôi', '30g', 'Tuýp',
    'Methyl Salicylate, L-Menthol',
    'Giảm đau cơ, đau khớp, đau lưng',
    'Vết thương hở, mắt, niêm mạc',
    'Kích ứng da nhẹ',
    'Bôi 3-4 lần/ngày vào vùng bị đau',
    'Nơi khô ráo, tránh ánh sáng trực tiếp',
    FALSE, TRUE, 45000, 0.00, 'salonpas.jpg'),

(8, 5, 'Blackmores Omega Daily 60 viên', 'Omega-3', 'Viên nang mềm', '1000mg', 'Viên',
    'Dầu cá 1000mg (EPA 180mg, DHA 120mg)',
    'Bổ sung Omega-3 cho tim mạch, não bộ',
    'Người dị ứng hải sản',
    'Rối loạn tiêu hóa nhẹ',
    '1 viên/ngày sau bữa ăn',
    'Nơi khô mát, dưới 30°C',
    FALSE, TRUE, 420000, 0.00, 'blackmores-omega.jpg'),

(8, 5, 'Nature Made Vitamin E 400IU', 'Vitamin E', 'Viên nang mềm', '400IU', 'Viên',
    'dl-alpha-Tocopheryl Acetate 400IU',
    'Chống oxy hóa, làm đẹp da, tốt cho tim mạch',
    'Người chuẩn bị phẫu thuật',
    'Rối loạn tiêu hóa',
    '1 viên/ngày sau bữa ăn',
    'Nơi khô mát, tránh ánh sáng',
    FALSE, TRUE, 350000, 0.00, 'vitamin-e.jpg'),

(3, 2, 'Sữa Glucerna 850g', 'Glucerna', 'Bột pha sữa', '850g', 'Hộp',
    'Protein, Chất xơ, Vitamin, Khoáng chất',
    'Dinh dưỡng cho người đái tháo đường',
    'Trẻ em dưới 13 tuổi (trừ khi có chỉ định)',
    'Đầy hơi nếu uống quá nhanh',
    'Pha 5 muỗng vào 200ml nước ấm',
    'Nơi khô mát, dùng trong 3 tuần sau mở nắp',
    FALSE, TRUE, 580000, 0.00, 'glucerna.jpg'),

(4, 1, 'Kem chống nắng Sunplay SPF50+ 30g', 'Sunplay', 'Kem bôi', '30g', 'Tuýp',
    'Zinc Oxide, Titanium Dioxide',
    'Chống nắng phổ rộng UVA/UVB',
    'Người dị ứng với kẽm',
    'Kích ứng da nếu có vết thương hở',
    'Thoa trước khi ra nắng 20 phút',
    'Nơi khô mát, tránh ánh sáng',
    FALSE, TRUE, 180000, 0.00, 'sunplay.jpg'),

(2, 1, 'Dầu gió Trúc Lâm 5ml', 'Dầu gió', 'Dầu', '5ml', 'Lọ',
    'Bạc hà, khuynh diệp, long não',
    'Xoa bóp giảm đau đầu, say xe, muỗi đốt',
    'Trẻ em dưới 2 tuổi',
    'Nóng rát nếu bôi quá nhiều',
    'Xoa trực tiếp vào vùng bị đau',
    'Nơi khô ráo, đậy nắp kín',
    FALSE, TRUE, 12000, 15.00, 'dau-gio.jpg'), -- Giảm 15%

(7, 3, 'Hapacol 325mg (100 viên)', 'Paracetamol', 'Viên nén', '325mg', 'Viên',
    'Paracetamol 325mg',
    'Giảm đau, hạ sốt cho trẻ em',
    'Người thiếu hụt G6PD',
    'Phát ban, buồn nôn',
    '10-15mg/kg cân nặng mỗi 4-6 giờ',
    'Nơi khô mát, tránh ánh sáng',
    FALSE, TRUE, 28000, 20.00, 'hapacol.jpg'), -- Giảm 20%

(8, 5, 'Centrum Silver 100 viên', 'Multivitamin', 'Viên nén bao phim', 'N/A', 'Viên',
    'Vitamin A, C, D, E, B-complex, Khoáng chất',
    'Vitamin tổng hợp cho người trên 50 tuổi',
    'Người thừa Vitamin A',
    'Thay đổi màu nước tiểu (vô hại)',
    '1 viên/ngày sau bữa ăn',
    'Nơi khô mát, tránh ẩm',
    FALSE, TRUE, 680000, 0.00, 'centrum.jpg'),

(2, 1, 'Xịt họng Tantum Verde 30ml', 'Benzydamine', 'Dung dịch xịt', '30ml', 'Chai',
    'Benzydamine HCl 1.5mg/ml',
    'Giảm đau, kháng viêm họng, miệng',
    'Trẻ em dưới 6 tuổi',
    'Tê đầu lưỡi tạm thời',
    'Xịt trực tiếp vào họng 2-6 lần/ngày',
    'Nơi khô mát, dưới 30°C',
    FALSE, TRUE, 95000, 0.00, 'tantum.jpg');

-- 10.6 Hoạt chất & Thành phần sản phẩm
INSERT INTO ingredients (ingredient_name, description) VALUES
('Amoxicillin', 'Kháng sinh nhóm penicillin'),
('Paracetamol', 'Thuốc giảm đau, hạ sốt'),
('Aspirin', 'Thuốc chống viêm không steroid (NSAID)'),
('Vitamin C', 'Chất chống oxy hóa, tăng cường đề kháng'),
('Vitamin D3', 'Hỗ trợ hấp thụ Canxi'),
('Glucose', 'Đường cung cấp năng lượng'),
('Natri Clorid', 'Muối điện giải'),
('Kali Clorid', 'Muối điện giải'),
('Metformin HCl', 'Thuốc điều trị tiểu đường type 2'),
('Atorvastatin', 'Thuốc hạ mỡ máu nhóm statin'),
('Zinc Oxide', 'Thành phần chống nắng vật lý'),
('Titanium Dioxide', 'Thành phần chống nắng vật lý'),
('Menthol', 'Tinh dầu bạc hà làm mát'),
('Eucalyptol', 'Tinh dầu khuynh diệp'),
('Methyl Salicylate', 'Hoạt chất giảm đau kháng viêm');

INSERT INTO product_ingredients (product_id, ingredient_id, amount) VALUES
(1, 1, '500mg'),
(2, 2, '500mg'),
(3, 3, '100mg'),
(4, 4, '1000mg'),
(5, 5, '1000IU'),
(6, 6, '135mg'),
(6, 7, '520mg'),
(6, 8, '300mg'),
(7, 9, '500mg'),
(8, 10, '20mg'),
(16, 11, '10%'),
(16, 12, '5%'),
(17, 13, '1.5ml'),
(17, 14, '0.5ml'),
(18, 2, '325mg');

-- 10.7 Nhà cung cấp
INSERT INTO suppliers (supplier_name, contact_person, phone, email, address, tax_code) VALUES
('Công ty TNHH Dược phẩm Phúc Vinh', 'Nguyễn Văn Phúc', '0283.123.4567', 'phucvinh@pharma.vn', '12 Đinh Tiên Hoàng, Q.1, TP.HCM', '0123456789'),
('Công ty CP Dược liệu Trung ương 3', 'Trần Thị Mai', '0284.234.5678', 'trunguong3@dlt.vn', '456 Võ Thị Sáu, Q.3, TP.HCM', '0234567890');

-- 10.8 Phiếu nhập kho
INSERT INTO stock_receipts (supplier_id, user_id, receipt_date, invoice_number, total_amount, status) VALUES
(1, 3, '2026-03-15', 'NK-2026-001', 50000000, 'completed'),
(2, 3, '2026-03-20', 'NK-2026-002', 35000000, 'completed');

-- 10.9 Lô hàng (với FEFO)
INSERT INTO batches (product_id, receipt_id, batch_number, manufacture_date, expiry_date, 
    quantity_received, quantity_remaining, purchase_price, selling_price, storage_location) VALUES

-- Amoxicillin - 3 lô khác nhau
(1, 1, 'AMX-2025-12-001', '2025-12-01', '2027-12-01', 500, 450, 35000, 45000, 'Kệ A1-01'),
(1, 1, 'AMX-2026-01-015', '2026-01-15', '2028-01-15', 500, 500, 36000, 45000, 'Kệ A1-02'),
(1, 2, 'AMX-2026-02-010', '2026-02-10', '2028-02-10', 300, 300, 36500, 45000, 'Kệ A1-03'),

-- Paracetamol - sắp hết hạn
(2, 1, 'PARA-2025-06-020', '2025-06-20', '2026-06-20', 1000, 850, 12000, 15000, 'Kệ B2-05'),
(2, 2, 'PARA-2026-03-01', '2026-03-01', '2028-03-01', 2000, 2000, 12500, 15000, 'Kệ B2-06'),

-- Aspirin
(3, 1, 'ASP-2025-11-10', '2025-11-10', '2027-11-10', 300, 280, 28000, 35000, 'Kệ A2-03'),

-- Vitamin C
(4, 2, 'VITC-2026-02-15', '2026-02-15', '2028-02-15', 200, 180, 95000, 120000, 'Kệ C1-01'),

-- Vitamin D3
(5, 2, 'VITD3-2026-01-20', '2026-01-20', '2028-01-20', 150, 140, 145000, 180000, 'Kệ C1-02'),

-- Oresol
(6, 1, 'ORS-2026-03-10', '2026-03-10', '2028-03-10', 5000, 4800, 3500, 5000, 'Kệ D3-01'),

-- Metformin
(7, 1, 'MET-2025-12-25', '2025-12-25', '2027-12-25', 400, 350, 42000, 55000, 'Kệ A3-04'),

-- Lipitor
(8, 2, 'LIP-2026-02-05', '2026-02-05', '2028-02-05', 100, 95, 225000, 280000, 'Kệ A4-01'),

-- Ensure
(9, 2, 'ENS-2026-03-01', '2026-03-01', '2027-09-01', 50, 45, 550000, 680000, 'Kệ E1-01'),

-- Nhiệt kế
(10, 1, 'THER-2026-01-10', '2026-01-10', '2031-01-10', 30, 25, 120000, 150000, 'Kệ F2-03');

-- 10.10 Chi tiết phiếu nhập
INSERT INTO stock_receipt_details (receipt_id, batch_id, quantity, unit_price) VALUES
(1, 1, 500, 35000),
(1, 4, 1000, 12000),
(1, 6, 300, 28000),
(1, 9, 5000, 3500),
(1, 10, 400, 42000),
(1, 13, 30, 120000),
(2, 2, 500, 36000),
(2, 3, 300, 36500),
(2, 5, 2000, 12500),
(2, 7, 200, 95000),
(2, 8, 150, 145000),
(2, 11, 100, 225000),
(2, 12, 50, 550000);

-- 10.11 Tương tác thuốc
INSERT INTO drug_interactions (drug_a_id, drug_b_id, severity, description, recommendation) VALUES
(1, 3, 'moderate', 
    'Aspirin có thể làm giảm hiệu quả kháng sinh Amoxicillin trong một số trường hợp', 
    'Theo dõi đáp ứng điều trị. Tránh dùng đồng thời nếu không cần thiết'),
(3, 7, 'severe', 
    'Aspirin kết hợp Metformin tăng nguy cơ hạ đường huyết và xuất huyết tiêu hóa', 
    'Tránh dùng đồng thời. Nếu cần thiết phải theo dõi sát đường huyết và dấu hiệu xuất huyết'),
(7, 8, 'moderate', 
    'Metformin có thể tăng tác dụng của Atorvastatin, tăng nguy cơ tổn thương cơ', 
    'Theo dõi triệu chứng đau cơ, yếu cơ. Xét nghiệm CK nếu có triệu chứng');

-- 10.12 Voucher
INSERT INTO vouchers (voucher_code, description, discount_type, discount_value, min_order_amount, max_discount_amount, 
    usage_limit, valid_from, valid_to, is_active) VALUES
('WELCOME10', 'Giảm 10% cho khách hàng mới', 'percent', 10, 200000, 50000, 100, '2026-01-01', '2026-12-31', TRUE),
('HEALTH50K', 'Giảm 50.000đ cho đơn từ 500.000đ', 'fixed', 50000, 500000, NULL, 200, '2026-03-01', '2026-06-30', TRUE),
('FREESHIP', 'Miễn phí vận chuyển', 'fixed', 25000, 300000, NULL, 500, '2026-01-01', '2026-12-31', TRUE);

-- 10.13 Giỏ hàng
INSERT INTO carts (user_id) VALUES (4), (5);

INSERT INTO cart_items (cart_id, product_id, quantity) VALUES
(1, 2, 2),  -- Khách 1: 2 hộp Paracetamol
(1, 4, 1),  -- Khách 1: 1 hộp Vitamin C
(2, 1, 1),  -- Khách 2: 1 hộp Amoxicillin
(2, 6, 3);  -- Khách 2: 3 gói Oresol

-- 10.14 Đơn hàng
INSERT INTO orders (user_id, voucher_id, shipping_name, shipping_phone, shipping_address, shipping_note,
    subtotal, discount_amount, shipping_fee, total_amount, has_prescription, prescription_image,
    prescription_verified, verified_by, verified_at, status, payment_status, payment_method, order_date) VALUES

-- Đơn hàng 1: Không cần đơn thuốc
(4, 3, 'Phạm Thị Lan', '0912345678', '321 Võ Văn Tần, Q.3, TP.HCM', 'Giao giờ hành chính',
    150000, 25000, 0, 125000, FALSE, NULL, FALSE, NULL, NULL, 'completed', 'paid', 'bank_transfer', '2026-03-25 10:30:00'),

-- Đơn hàng 2: Có đơn thuốc - Đã duyệt
(5, NULL, 'Hoàng Văn Nam', '0923456789', '654 Cách Mạng Tháng 8, Q.10, TP.HCM', NULL,
    280000, 0, 25000, 305000, TRUE, 'prescription_20260328_001.jpg', TRUE, 2, '2026-03-28 14:20:00', 
    'shipping', 'unpaid', 'cod', '2026-03-28 09:15:00'),

-- Đơn hàng 3: Có đơn thuốc - Chưa duyệt
(4, 1, 'Phạm Thị Lan', '0912345678', '321 Võ Văn Tần, Q.3, TP.HCM', 'Nhớ gọi trước khi giao',
    335000, 33500, 25000, 326500, TRUE, 'prescription_20260401_002.jpg', FALSE, NULL, NULL,
    'pending', 'unpaid', 'cod', '2026-04-01 16:45:00');

-- 10.15 Chi tiết đơn hàng (áp dụng FEFO)
INSERT INTO order_details (order_id, product_id, batch_id, product_name, quantity, unit_price, discount_percent) VALUES
-- Đơn 1
(1, 2, 4, 'Paracetamol 500mg', 2, 15000, 0),
(1, 4, 7, 'Vitamin C 1000mg Abbott', 1, 120000, 0),

-- Đơn 2 (Xuất lô hết hạn sớm nhất - FEFO)
(2, 1, 1, 'Amoxicillin 500mg DHG', 2, 45000, 0),
(2, 3, 6, 'Aspirin 100mg', 3, 35000, 0),
(2, 8, 11, 'Lipitor 20mg', 1, 280000, 0),

-- Đơn 3
(3, 7, 10, 'Metformin 500mg', 2, 55000, 0),
(3, 5, 8, 'Vitamin D3 1000IU', 1, 180000, 0),
(3, 2, 4, 'Paracetamol 500mg', 1, 15000, 0);

-- 10.16 Đánh giá sản phẩm
INSERT INTO reviews (product_id, user_id, order_id, rating, comment, is_verified_purchase, is_approved) VALUES
(2, 4, 1, 5, 'Thuốc rất tốt, hạ sốt nhanh. Giá cả hợp lý.', TRUE, TRUE),
(4, 4, 1, 4, 'Vitamin C sủi bọt, dễ uống. Hơi ngọt.', TRUE, TRUE),
(1, 5, 2, 5, 'Kháng sinh hiệu quả, viêm họng hết sau 3 ngày.', TRUE, TRUE);

-- =====================================================
-- 11. HỆ THỐNG CHAT & TƯ VẤN (Tích hợp từ chat_schema.sql)
-- =====================================================

CREATE TABLE IF NOT EXISTS consultation_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    current_doctor_id INT NULL,
    status ENUM('pending', 'accepted', 'rejected', 'completed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (current_doctor_id) REFERENCES users(user_id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_doctor (current_doctor_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS conversations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    doctor_id INT NOT NULL,
    customer_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (doctor_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    conversation_id INT NOT NULL,
    sender_id INT NOT NULL,
    content TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 12. QUÊN MẬT KHẨU (Tích hợp từ 4.password_resets.sql)
-- =====================================================

CREATE TABLE IF NOT EXISTS password_resets (
    email VARCHAR(255) NOT NULL,
    otp_code VARCHAR(10) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- 13. NHẬT KÝ EMAIL (Tích hợp từ 11.updates.sql)
-- =====================================================

CREATE TABLE IF NOT EXISTS email_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NULL,
    recipient_email VARCHAR(255) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    type VARCHAR(50) NOT NULL,
    status ENUM('sent', 'failed', 'opened') DEFAULT 'sent',
    sent_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    error_message TEXT NULL,
    tracking_id VARCHAR(100) UNIQUE,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- 15. LÔ HÀNG BỔ SUNG CHO CÁC SẢN PHẨM MỚI (FEFO)
-- =====================================================

INSERT INTO batches (product_id, receipt_id, batch_number, manufacture_date, expiry_date, 
    quantity_received, quantity_remaining, purchase_price, selling_price, storage_location) VALUES
(11, 1, 'PRO-2026-01', '2026-01-15', '2028-01-15', 200, 200, 100000, 125000, 'Kệ B1-01'),
(12, 1, 'SAL-2026-02', '2026-02-01', '2028-02-01', 300, 300, 35000, 45000, 'Kệ C2-01'),
(13, 2, 'BLK-2026-01', '2026-01-20', '2027-12-20', 150, 150, 320000, 420000, 'Kệ D1-01'),
(14, 2, 'VIT-2026-01', '2026-01-10', '2027-12-10', 100, 100, 280000, 350000, 'Kệ D1-02'),
(15, 2, 'GLU-2026-02', '2026-02-15', '2027-08-15', 80, 80, 520000, 580000, 'Kệ E1-01'),
(16, 1, 'SUN-2026-03', '2026-03-01', '2028-03-01', 120, 120, 140000, 180000, 'Kệ F1-01'),
(17, 1, 'TRUC-2026-01', '2026-01-10', '2029-01-10', 500, 500, 8000, 12000, 'Kệ G1-01'),
(18, 2, 'HAPA-2026-03', '2026-03-10', '2028-03-10', 1000, 1000, 20000, 28000, 'Kệ B2-01'),
(19, 2, 'CEN-2026-02', '2026-02-01', '2028-02-01', 60, 60, 550000, 680000, 'Kệ D1-03'),
(20, 1, 'TAN-2026-03', '2026-03-15', '2028-03-15', 100, 100, 75000, 95000, 'Kệ C3-01');

-- =====================================================
-- 16. LOGIC NÂNG CAO (TRIGGERS, INDEXES, EVENTS)
-- =====================================================

-- Tăng tốc truy vấn
CREATE INDEX idx_products_active ON products(is_active);
CREATE INDEX idx_products_category_active ON products(category_id, is_active);
CREATE INDEX idx_batches_product_expiry ON batches(product_id, expiry_date);
CREATE INDEX idx_batches_status_expiry ON batches(status, expiry_date);
CREATE INDEX idx_orders_user_status ON orders(user_id, status);
CREATE INDEX idx_orders_prescription ON orders(has_prescription, prescription_verified, status);
CREATE INDEX idx_order_details_product ON order_details(product_id);

DELIMITER $$

-- Trigger: Hoàn kho khi đơn hàng bị hủy
DROP TRIGGER IF EXISTS after_order_status_cancel$$
CREATE TRIGGER after_order_status_cancel
AFTER UPDATE ON orders
FOR EACH ROW
BEGIN
    IF OLD.status != 'cancelled' AND NEW.status = 'cancelled' THEN
        UPDATE batches b
        JOIN order_details od ON b.batch_id = od.batch_id
        SET b.quantity_remaining = b.quantity_remaining + od.quantity
        WHERE od.order_id = NEW.order_id;
    END IF;
END$$

-- Trigger: Cập nhật trạng thái lô hàng trước khi chèn
DROP TRIGGER IF EXISTS before_batch_insert$$
CREATE TRIGGER before_batch_insert
BEFORE INSERT ON batches
FOR EACH ROW
BEGIN
    IF NEW.expiry_date < CURDATE() THEN
        SET NEW.status = 'expired';
    ELSEIF DATEDIFF(NEW.expiry_date, CURDATE()) <= NEW.expiry_alert_days THEN
        SET NEW.status = 'near_expiry';
    ELSE
        SET NEW.status = 'active';
    END IF;
END$$

-- Trigger: Nhật ký thay đổi giá sản phẩm
DROP TRIGGER IF EXISTS after_product_price_update$$
CREATE TRIGGER after_product_price_update
AFTER UPDATE ON products
FOR EACH ROW
BEGIN
    IF OLD.price != NEW.price THEN
        INSERT INTO price_history (product_id, old_price, new_price)
        VALUES (NEW.product_id, OLD.price, NEW.price);
    END IF;
END$$

DELIMITER ;

-- Event: Cập nhật trạng thái hạn dùng hàng ngày
SET GLOBAL event_scheduler = ON;
DROP EVENT IF EXISTS update_batch_status_daily;
DELIMITER $$
CREATE EVENT update_batch_status_daily
ON SCHEDULE EVERY 1 DAY
STARTS (CURRENT_DATE + INTERVAL 1 DAY)
DO
BEGIN
    UPDATE batches SET status = 'expired'
    WHERE expiry_date < CURDATE() AND status != 'expired';
    
    UPDATE batches SET status = 'near_expiry'
    WHERE expiry_date >= CURDATE() AND DATEDIFF(expiry_date, CURDATE()) <= expiry_alert_days AND status = 'active';
END$$
DELIMITER ;

-- Bật lại kiểm tra khóa ngoại
SET FOREIGN_KEY_CHECKS = 1;

UPDATE users SET password_hash = '$2y$10$AulLUbZUBZ1dHhPNgAUZQehkVF.5mHhB7aOH.z/iIZwQXs6CdeewG';

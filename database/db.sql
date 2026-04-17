-- =========================================
-- DATABASE
-- =========================================
CREATE DATABASE IF NOT EXISTS qurban_hub_db;
USE qurban_hub_db;

-- =========================================
-- 1. USERS
-- =========================================
CREATE TABLE users (
    id_user BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NULL,
    phone_number VARCHAR(50) NOT NULL,

    status ENUM('active','inactive','deleted') DEFAULT 'active',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    created_by VARCHAR(100) NULL,
    updated_by VARCHAR(100) NULL,

    INDEX idx_users_email (email),
    INDEX idx_users_phone (phone_number)
);

-- =========================================
-- 2. PACKAGES
-- =========================================
CREATE TABLE packages (
    id_package BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    animal_type ENUM('goat', 'sheep', 'cow') NOT NULL,
    country VARCHAR(100) NOT NULL,
    price DECIMAL(12,2) NOT NULL,
    max_share INT DEFAULT 1,
    description TEXT NULL,

    status ENUM('active','inactive','deleted') DEFAULT 'active',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    created_by VARCHAR(100) NULL,
    updated_by VARCHAR(100) NULL
);

-- =========================================
-- 3. ORDERS
-- =========================================
CREATE TABLE orders (
    id_order BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_code VARCHAR(100) NOT NULL UNIQUE,

    id_user BIGINT UNSIGNED NOT NULL,
    id_package BIGINT UNSIGNED NOT NULL,

    quantity INT NOT NULL,
    total_price DECIMAL(12,2) NOT NULL,

    payment_status ENUM('pending', 'paid', 'failed') DEFAULT 'pending',
    qurban_status ENUM('pending', 'scheduled', 'completed') DEFAULT 'pending',

    status ENUM('active','inactive','deleted') DEFAULT 'active',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    created_by VARCHAR(100) NULL,
    updated_by VARCHAR(100) NULL,

    CONSTRAINT fk_orders_user
        FOREIGN KEY (id_user) REFERENCES users(id_user)
        ON DELETE CASCADE,

    CONSTRAINT fk_orders_package
        FOREIGN KEY (id_package) REFERENCES packages(id_package)
);

-- =========================================
-- 4. ORDER PARTICIPANTS
-- =========================================
CREATE TABLE order_participants (
    id_participant BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    id_order BIGINT UNSIGNED NOT NULL,
    qurban_name VARCHAR(255) NOT NULL,
    remarks TEXT NULL,

    status ENUM('active','inactive','deleted') DEFAULT 'active',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    created_by VARCHAR(100) NULL,
    updated_by VARCHAR(100) NULL,

    CONSTRAINT fk_participants_order
        FOREIGN KEY (id_order) REFERENCES orders(id_order)
        ON DELETE CASCADE
);

-- =========================================
-- 5. PAYMENTS
-- =========================================
CREATE TABLE payments (
    id_payment BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    id_order BIGINT UNSIGNED NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    amount DECIMAL(12,2) NOT NULL,

    payment_status ENUM('pending', 'success', 'failed') DEFAULT 'pending',
    paid_at TIMESTAMP NULL,

    status ENUM('active','inactive','deleted') DEFAULT 'active',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    created_by VARCHAR(100) NULL,
    updated_by VARCHAR(100) NULL,

    CONSTRAINT fk_payments_order
        FOREIGN KEY (id_order) REFERENCES orders(id_order)
        ON DELETE CASCADE
);

-- =========================================
-- 6. QURBAN EXECUTIONS
-- =========================================
CREATE TABLE qurban_executions (
    id_execution BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    id_order BIGINT UNSIGNED NOT NULL,
    execution_date DATE NULL,
    notes TEXT NULL,

    execution_status ENUM('pending', 'completed') DEFAULT 'pending',

    status ENUM('active','inactive','deleted') DEFAULT 'active',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    created_by VARCHAR(100) NULL,
    updated_by VARCHAR(100) NULL,

    CONSTRAINT fk_execution_order
        FOREIGN KEY (id_order) REFERENCES orders(id_order)
        ON DELETE CASCADE
);

-- =========================================
-- 7. QURBAN MEDIA
-- =========================================
CREATE TABLE qurban_media (
    id_media BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    id_execution BIGINT UNSIGNED NOT NULL,
    file_url VARCHAR(255) NOT NULL,
    type ENUM('photo', 'video') DEFAULT 'photo',

    status ENUM('active','inactive','deleted') DEFAULT 'active',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    created_by VARCHAR(100) NULL,
    updated_by VARCHAR(100) NULL,

    CONSTRAINT fk_media_execution
        FOREIGN KEY (id_execution) REFERENCES qurban_executions(id_execution)
        ON DELETE CASCADE
);

-- =========================================
-- 8. CERTIFICATES
-- =========================================
CREATE TABLE certificates (
    id_certificate BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    id_participant BIGINT UNSIGNED NOT NULL,
    file_url VARCHAR(255) NOT NULL,

    generated_at TIMESTAMP NULL,
    is_sent BOOLEAN DEFAULT FALSE,
    sent_at TIMESTAMP NULL,

    status ENUM('active','inactive','deleted') DEFAULT 'active',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    created_by VARCHAR(100) NULL,
    updated_by VARCHAR(100) NULL,

    CONSTRAINT fk_certificate_participant
        FOREIGN KEY (id_participant) REFERENCES order_participants(id_participant)
        ON DELETE CASCADE
);

-- =========================================
-- 9. DELIVERIES
-- =========================================
CREATE TABLE deliveries (
    id_delivery BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    id_order BIGINT UNSIGNED NOT NULL,
    sent_via ENUM('whatsapp', 'email') NOT NULL,
    delivery_status ENUM('pending', 'sent') DEFAULT 'pending',

    sent_at TIMESTAMP NULL,

    status ENUM('active','inactive','deleted') DEFAULT 'active',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    created_by VARCHAR(100) NULL,
    updated_by VARCHAR(100) NULL,

    CONSTRAINT fk_delivery_order
        FOREIGN KEY (id_order) REFERENCES orders(id_order)
        ON DELETE CASCADE
);

-- =========================================
-- 10. ADMINS
-- =========================================
CREATE TABLE admins (
    id_admin BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'partner') NOT NULL,

    status ENUM('active','inactive','deleted') DEFAULT 'active',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    created_by VARCHAR(100) NULL,
    updated_by VARCHAR(100) NULL
);
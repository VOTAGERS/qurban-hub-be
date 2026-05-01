CREATE TABLE orders_woo (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    woo_id BIGINT UNIQUE,
    user_id BIGINT NULL,
    email VARCHAR(255),
    total DECIMAL(12,2),
    status VARCHAR(50),
    raw_payload JSON,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);


CREATE TABLE users_woo (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    woo_customer_id BIGINT UNIQUE,
    name VARCHAR(255),
    email VARCHAR(255) UNIQUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);


CREATE TABLE products_woo (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    woo_id BIGINT UNIQUE,
    name VARCHAR(255),
    price DECIMAL(12,2),
    status VARCHAR(50),
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);


CREATE TABLE order_items_woo (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    order_id BIGINT,
    product_id BIGINT,
    product_name VARCHAR(255),
    qty INT,
    price DECIMAL(12,2),
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);



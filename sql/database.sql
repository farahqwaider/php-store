CREATE DATABASE IF NOT EXISTS projectdb
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE projectdb;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description VARCHAR(500) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE products (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id INT UNSIGNED NULL,
    image VARCHAR(255) NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    price DECIMAL(10, 2) UNSIGNED NOT NULL,
    quantity INT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT products_category_fk FOREIGN KEY (category_id)
        REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE orders (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total_price DECIMAL(10, 2) UNSIGNED NOT NULL,
    status ENUM('pending', 'processing', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT orders_user_fk FOREIGN KEY (user_id)
        REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE order_details (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NULL,
    product_name VARCHAR(255) NOT NULL,
    quantity INT UNSIGNED NOT NULL,
    price DECIMAL(10, 2) UNSIGNED NOT NULL,
    CONSTRAINT order_details_order_fk FOREIGN KEY (order_id)
        REFERENCES orders(id) ON DELETE CASCADE,
    CONSTRAINT order_details_product_fk FOREIGN KEY (product_id)
        REFERENCES products(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE cart (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    quantity INT UNSIGNED NOT NULL DEFAULT 1,
    UNIQUE KEY cart_user_product_unique (user_id, product_id),
    CONSTRAINT cart_user_fk FOREIGN KEY (user_id)
        REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT cart_product_fk FOREIGN KEY (product_id)
        REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO categories (name, description) VALUES
('Electronics', 'Devices and electronic accessories'),
('Clothing', 'Clothes and fashion items'),
('Home & Garden', 'Home and garden essentials'),
('Books', 'Printed and digital books'),
('Other', 'Other products');

INSERT INTO users (name, email, password, role) VALUES
('Administrator', 'admin@gmail.com', '$2y$12$JzE3AzSBLirrUnDMibBQhucE..uWvTUrJjYhkWeR9aPl3WywOzxom', 'admin'),
('Demo User', 'user@gmail.com', '$2y$12$bgVcRGiqnDCzLrmLazHVrOwq66u6nn5g27.2jjT4nhiCgcepcercq', 'user');

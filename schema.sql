-- Schema for my_store_db
-- Run with: mysql -h HOST -P PORT -u USER -p < schema.sql

CREATE DATABASE IF NOT EXISTS `my_store_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `my_store_db`;

-- categories
CREATE TABLE IF NOT EXISTS `categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(100) NOT NULL UNIQUE
);

-- products
CREATE TABLE IF NOT EXISTS `products` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL UNIQUE,
  `price` DECIMAL(10,2) NOT NULL,
  `img` VARCHAR(255),
  `category_id` INT,
  `description` TEXT,
  `features` TEXT,
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL
);

-- orders
CREATE TABLE IF NOT EXISTS `orders` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `customer_name` VARCHAR(255) NOT NULL,
  `customer_phone` VARCHAR(20) NOT NULL,
  `customer_address` TEXT NOT NULL,
  `payment_method` VARCHAR(50) NOT NULL,
  `total` DECIMAL(10,2) NOT NULL,
  `status` VARCHAR(50) DEFAULT 'pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- order_items
CREATE TABLE IF NOT EXISTS `order_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT,
  `product_id` INT,
  `quantity` INT NOT NULL,
  `price` DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE SET NULL
);

-- users
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `role` ENUM('user','admin') DEFAULT 'user',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default categories
INSERT IGNORE INTO `categories` (`id`,`name`,`slug`) VALUES
(1, 'هواتف', 'phones'),
(2, 'لابتوبات', 'laptops'),
(3, 'أجهزة لوحية', 'tablets');

-- Sample products (you can run install.php to populate a larger seeded list)
INSERT IGNORE INTO `products` (category_id, name, price, img, description, features) VALUES
(1, 'أيفون 17 برو ماكس', 1199.00, 'images/iphone17promax.jpg', 'أقوى هاتف من أبل بهيكل من التيطانيوم وكاميرا تقريب مذهلة.', 'هيكل تيتانيوم صلب,معالج A17 Pro,كاميرا تقريب 5x'),
(2, 'أبل ماك بوك برو M3', 1599.00, 'images/img.ph/macbook_m3.jpg', 'أقوى لابتوب من أبل للمصممين والمبرمجين.', 'معالج Apple M3,ذاكرة 16GB'),
(3, 'أيباد برو M4', 999.00, 'images/tp/ipad_pro.jpg', 'أقوى جهاز لوحي من أبل.', 'معالج M4,شاشة OLED');

-- Note: it's recommended to run /install.php (via browser or CLI) after the DB user is configured on the host,
-- because the PHP script also inserts a hashed admin password and a larger products list.

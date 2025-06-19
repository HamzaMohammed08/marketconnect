-- Simplified E-commerce Database Schema
-- Designed for student-level projects with core marketplace functionality
-- Removes complexity while maintaining essential features

DROP DATABASE IF EXISTS ecomm_platform;
CREATE DATABASE IF NOT EXISTS ecomm_platform;
USE ecomm_platform;

-- =====================================================
-- CORE SIMPLIFIED TABLES
-- =====================================================

-- Users table - unified buyer/seller model (simplified)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    role TINYINT NOT NULL DEFAULT 1 COMMENT '1=Regular User, 3=Admin',
    status ENUM('active','inactive') DEFAULT 'active',
    profile_picture VARCHAR(255),
    -- Simple location - just one field instead of province/city/suburb breakdown
    location VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_status (status)
);

-- Categories table - simple hierarchical structure
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    parent_id INT NULL,
    display_order INT DEFAULT 0,
    FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE CASCADE,
    INDEX idx_parent (parent_id)
);

-- Products table - core business entity (simplified)
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    negotiable TINYINT(1) DEFAULT 0,
    condition_status ENUM('new','like_new','good','fair','poor') DEFAULT 'good',
    category_id INT NOT NULL,
    seller_id INT NOT NULL,
    location VARCHAR(255), -- Simple location field
    stock INT NOT NULL DEFAULT 1,
    status ENUM('pending','approved','rejected') DEFAULT 'pending',
    featured TINYINT(1) NOT NULL DEFAULT 0,
    views INT DEFAULT 0,
    image VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_seller (seller_id),
    INDEX idx_category (category_id),
    INDEX idx_status (status),
    INDEX idx_featured (featured),
    INDEX idx_price (price)
);

-- Product images table
CREATE TABLE product_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    display_order INT NOT NULL DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_product (product_id)
);

-- Messages table - for user-to-user communication
CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    product_id INT NULL, -- Optional reference to product being discussed
    subject VARCHAR(255),
    message TEXT NOT NULL,
    read_status TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL,
    INDEX idx_sender (sender_id),
    INDEX idx_receiver (receiver_id),
    INDEX idx_product (product_id),
    INDEX idx_read (read_status)
);

-- Reviews table - for product ratings and reviews
CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    user_id INT NOT NULL,
    rating TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_product_review (user_id, product_id),
    INDEX idx_product (product_id),
    INDEX idx_user (user_id),
    INDEX idx_rating (rating)
);

-- Favorites table - for users to save favorite products
CREATE TABLE favorites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_favorite (user_id, product_id),
    INDEX idx_user (user_id),
    INDEX idx_product (product_id)
);

-- Purchase requests table - simplified transaction handling
CREATE TABLE purchase_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    buyer_id INT NOT NULL,
    seller_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    total_amount DECIMAL(10,2) NOT NULL,
    buyer_message TEXT,
    seller_response TEXT,
    status ENUM('pending','accepted','rejected','completed','cancelled') DEFAULT 'pending',
    delivery_method ENUM('pickup','delivery') DEFAULT 'pickup',
    delivery_address TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (buyer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_buyer (buyer_id),
    INDEX idx_seller (seller_id),
    INDEX idx_product (product_id),
    INDEX idx_status (status)
);

-- Cart items table - for shopping cart functionality
CREATE TABLE cart_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_cart_item (user_id, product_id),
    INDEX idx_user (user_id),
    INDEX idx_product (product_id)
);

-- =====================================================
-- DATABASE STRUCTURE COMPLETE
-- =====================================================
-- 
-- To populate with sample data, run: SOURCE sampledata.sql;

SELECT 'Simplified e-commerce database structure created successfully!' as status;

/*
=== SIMPLIFIED E-COMMERCE DATABASE FEATURES ===

Core Tables Included:
1. users - Unified buyer/seller model
2. categories - Hierarchical product categorization  
3. products - Core product listings
4. product_images - Multiple images per product
5. messages - User-to-user communication
6. reviews - Product ratings and reviews
7. favorites - User favorite products
8. purchase_requests - Transaction handling
9. cart_items - Shopping cart functionality

Features Removed for Simplification:
- Complex location breakdown (provinces/cities/suburbs)
- Two-factor authentication
- Advanced verification systems
- Seller-specific reviews (kept only product reviews)
- Complex payment processing
- Advanced indexing and optimization

This schema maintains all core e-commerce functionality while being
appropriate for student-level projects and easy to understand.

To add sample data:
1. Run this file first to create the structure
2. Then run: SOURCE sampledata.sql;
*/

# Thrift Palor - Setup Guide

Simple steps to get this website running on your computer using XAMPP.

## What You Need

- XAMPP (download from https://www.apachefriends.org/)
- This project folder

## Quick Setup (5 minutes)

### 1. Move Project to XAMPP Folder

Copy the `Thrift-Palor` folder to:
- **Windows:** `C:\xampp\htdocs\`
- **Mac:** `/Applications/XAMPP/htdocs/`
- **Linux:** `/opt/lampp/htdocs/`

### 2. Start XAMPP

Open XAMPP Control Panel and start:
- **Apache** (click Start)
- **MySQL** (click Start)

### 3. Create Database

1. Open browser → go to `http://localhost/phpmyadmin`
2. Click **New** on left sidebar
3. Type `thrift_palor` as database name
4. Click **Create**

### 4. Run the SQL Script

Copy the SQL code below and paste it in phpMyAdmin:

```sql
-- Create tables
CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE products (
    product_id INT PRIMARY KEY AUTO_INCREMENT,
    seller_id INT NOT NULL,
    product_name VARCHAR(200) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    category VARCHAR(50),
    condition ENUM('new', 'like-new', 'good', 'fair', 'poor') DEFAULT 'good',
    image_url VARCHAR(500),
    status ENUM('available', 'sold', 'pending') DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (seller_id) REFERENCES users(user_id) ON DELETE CASCADE
);

CREATE TABLE orders (
    order_id INT PRIMARY KEY AUTO_INCREMENT,
    buyer_id INT NOT NULL,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    total_amount DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    shipping_address TEXT,
    FOREIGN KEY (buyer_id) REFERENCES users(user_id)
);

CREATE TABLE order_items (
    order_item_id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT DEFAULT 1,
    price DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(product_id)
);

CREATE TABLE cart (
    cart_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT DEFAULT 1,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE
);

-- Add admin account (password = admin123)
INSERT INTO users (full_name, email, password, phone, role) VALUES 
('Admin User', 'admin@thriftpalor.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '1234567890', 'admin');

-- Add sample product
INSERT INTO products (seller_id, product_name, description, price, category, condition) VALUES 
(1, 'Vintage Denim Jacket', 'Classic blue denim jacket, good condition', 25.99, 'Clothing', 'good');

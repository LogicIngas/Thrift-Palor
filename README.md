# 🛍️ Thrift Palor

A community-driven thrift marketplace that allows buyers and sellers to buy and sell second-hand products online. The platform provides user authentication, product management, shopping carts, secure checkout, payment processing, shipping management, returns, refunds, and customer support.

---

# 📖 Project Overview

Thrift Palor was developed as part of an **Applications Development** project at the **Cape Peninsula University of Technology (CPUT)**.

The goal of the system is to provide a simple and secure platform where members of the community can purchase and sell affordable second-hand products.

---

# 📋 Project Specifications

<img width="6080" height="2184" alt="Physical Data Model (3)" src="https://github.com/user-attachments/assets/e59fa6be-0839-4450-a26b-efc7e2189cba" />

---

# ✨ Features

## Buyer Features

* Register an account
* Login securely
* Browse available products
* View product details
* Add products to cart
* Manage cart
* Checkout
* Make payments
* Track orders
* Request product returns
* Manage delivery addresses

---

## Seller Features

* Register as a seller
* Upload products
* Edit products
* Delete products
* Manage stock
* View customer orders

---

## System Features

* User authentication
* Product categories
* Shopping cart
* Order management
* Payment records
* Shipping management
* Return requests
* Refund management
* Contact form
* SQL View for order and shipping summaries

---

# 💻 Technologies Used

* PHP
* HTML5
* CSS3
* JavaScript
* Bootstrap
* MySQL
* phpMyAdmin
* XAMPP

---

# 🗄️ Database

Database Name:

```
thriftdb
```

The project contains the following tables:

* users
* addresses
* product_categories
* products
* carts
* cart_items
* orders
* order_items
* payments
* shipping
* returns
* refund
* contact_messages

Additional database objects:

* order_shipping_summary (SQL View)

---

# ⚙️ Requirements

Before running the project, install:

* XAMPP
* Apache
* MySQL
* PHP 8+

Download XAMPP:

[https://www.apachefriends.org/](https://www.apachefriends.org/)

---

# 🚀 Installation Guide

## Step 1

Clone the repository

```bash
git clone https://github.com/YOUR_USERNAME/Thrift-Palor.git
```

or download it as a ZIP file.

---

## Step 2

Move the project folder into your XAMPP htdocs directory.

### Windows

```
C:\xampp\htdocs\
```

### Linux

```
/opt/lampp/htdocs/
```

### macOS

```
/Applications/XAMPP/htdocs/
```

---

## Step 3

Open the XAMPP Control Panel.

Start:

* Apache
* MySQL

---

## Step 4

Open phpMyAdmin.

```
http://localhost/phpmyadmin
```

Create a database named

```
thriftdb
```

---

## Step 5

Open the **SQL** tab in phpMyAdmin.

Paste the following SQL script and click **Go**.

```sql
-- ======================================================
-- Database: thriftdb
-- Complete schema
-- ======================================================

CREATE DATABASE IF NOT EXISTS `thriftdb`;
USE `thriftdb`;

-- TABLE: users
CREATE TABLE `users` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `first_name` VARCHAR(255) NOT NULL,
    `last_name` VARCHAR(255) NOT NULL,
    `username` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `phone` VARCHAR(50) NULL,
    `role` ENUM('buyer', 'seller') NOT NULL DEFAULT 'buyer',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `profile_picture` VARCHAR(255) NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `username` (`username`),
    UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- TABLE: addresses
CREATE TABLE `addresses` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `user_id` INT NOT NULL,
    `street_address` VARCHAR(255) NOT NULL,
    `city` VARCHAR(100) NOT NULL,
    `province` VARCHAR(100) NOT NULL,
    `postal_code` VARCHAR(20) NOT NULL,
    `address_type` ENUM('home', 'work', 'shipping', 'billing') NOT NULL DEFAULT 'shipping',
    `is_default` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_addresses_user_id` (`user_id`),
    CONSTRAINT `fk_addresses_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- TABLE: product_categories
CREATE TABLE `product_categories` (
    `category_id` INT NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `description` TEXT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- TABLE: products
CREATE TABLE `products` (
    `product_id` INT NOT NULL AUTO_INCREMENT,
    `seller_id` INT NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `price` DECIMAL(10,2) NOT NULL,
    `image_url` VARCHAR(500) NULL,
    `category` VARCHAR(100) NULL,
    `category_id` INT NULL,
    `stock_quantity` INT NOT NULL DEFAULT 0,
    `status` ENUM('active', 'pending', 'rejected') NOT NULL DEFAULT 'pending',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`product_id`),
    INDEX `fk_products_seller` (`seller_id`),
    INDEX `fk_products_category` (`category_id`),
    CONSTRAINT `fk_products_seller` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `product_categories` (`category_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- TABLE: carts
CREATE TABLE `carts` (
    `cart_id` INT NOT NULL AUTO_INCREMENT,
    `user_id` INT NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`cart_id`),
    INDEX `idx_carts_user_id` (`user_id`),
    CONSTRAINT `fk_carts_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- TABLE: cart_items
CREATE TABLE `cart_items` (
    `cart_item_id` INT NOT NULL AUTO_INCREMENT,
    `cart_id` INT NOT NULL,
    `product_id` INT NOT NULL,
    `quantity` INT NOT NULL DEFAULT 1,
    `added_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`cart_item_id`),
    INDEX `idx_cart_items_cart_id` (`cart_id`),
    INDEX `idx_cart_items_product_id` (`product_id`),
    CONSTRAINT `fk_cart_items_cart` FOREIGN KEY (`cart_id`) REFERENCES `carts` (`cart_id`) ON DELETE CASCADE,
    CONSTRAINT `fk_cart_items_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- TABLE: orders
CREATE TABLE `orders` (
    `order_id` INT NOT NULL AUTO_INCREMENT,
    `buyer_id` INT NOT NULL,
    `total_amount` DECIMAL(10,2) NOT NULL,
    `status` ENUM('Pending', 'Paid', 'Shipped', 'Delivered', 'Returned', 'Return Requested') NOT NULL DEFAULT 'Pending',
    `shipping_cost` DECIMAL(10,2) DEFAULT 0.00,
    `order_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `address_id` INT NOT NULL,
    `payment_method` VARCHAR(100) NULL,
    `transaction_id` VARCHAR(255) NULL,
    `tracking_number` VARCHAR(100) NULL,
    `shipping_date` DATE NULL,
    PRIMARY KEY (`order_id`),
    INDEX `idx_orders_buyer_id` (`buyer_id`),
    INDEX `idx_orders_address_id` (`address_id`),
    CONSTRAINT `fk_orders_buyer` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_orders_address` FOREIGN KEY (`address_id`) REFERENCES `addresses` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- TABLE: order_items
CREATE TABLE `order_items` (
    `item_id` INT NOT NULL AUTO_INCREMENT,
    `order_id` INT NOT NULL,
    `product_name` VARCHAR(255) NOT NULL,
    `quantity` INT NOT NULL,
    `price` DECIMAL(10,2) NOT NULL,
    PRIMARY KEY (`item_id`),
    INDEX `order_items_ibfk_1` (`order_id`),
    CONSTRAINT `fk_order_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- TABLE: payments
CREATE TABLE `payments` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `order_id` INT NOT NULL,
    `card_holder_name` VARCHAR(255) NOT NULL,
    `card_number` VARCHAR(50) NOT NULL,
    `expiry_date` VARCHAR(10) NOT NULL,
    `cvv` VARCHAR(10) NOT NULL,
    `payment_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_payments_order_id` (`order_id`),
    CONSTRAINT `fk_payments_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- TABLE: shipping
CREATE TABLE `shipping` (
    `shipping_id` INT NOT NULL AUTO_INCREMENT,
    `order_id` INT NOT NULL,
    `address_id` INT NOT NULL,
    `tracking_number` VARCHAR(100) NOT NULL,
    `shipping_method` ENUM('standard', 'express', 'priority') NOT NULL DEFAULT 'standard',
    `shipping_cost` DECIMAL(10,2) NOT NULL,
    `status` ENUM('processing', 'delivered', 'returned', 'in_transit') NOT NULL DEFAULT 'processing',
    `shipping_date` DATE NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`shipping_id`),
    UNIQUE KEY `tracking_number` (`tracking_number`),
    INDEX `idx_shipping_order_id` (`order_id`),
    INDEX `idx_shipping_address_id` (`address_id`),
    CONSTRAINT `fk_shipping_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
    CONSTRAINT `fk_shipping_address` FOREIGN KEY (`address_id`) REFERENCES `addresses` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- TABLE: returns
CREATE TABLE `returns` (
    `return_id` INT NOT NULL AUTO_INCREMENT,
    `order_id` INT NOT NULL,
    `product_name` VARCHAR(255) NOT NULL,
    `quantity` INT NOT NULL,
    `status` ENUM('Pending', 'Completed', 'Failed') NOT NULL DEFAULT 'Pending',
    `request_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `reason` ENUM('defective', 'wrong_item', 'changed_mind', 'other') NOT NULL,
    `additional_notes` TEXT NULL,
    `processed_date` DATE NULL,
    PRIMARY KEY (`return_id`),
    INDEX `idx_returns_order_id` (`order_id`),
    CONSTRAINT `fk_returns_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- TABLE: refund
CREATE TABLE `refund` (
    `refund_id` INT NOT NULL AUTO_INCREMENT,
    `return_id` INT NOT NULL,
    `payment_id` INT NOT NULL,
    `amount` DECIMAL(10,2) NOT NULL,
    `processed_date` DATE NULL,
    `refund_status` ENUM('Pending', 'Processed', 'Failed') NOT NULL DEFAULT 'Pending',
    `notes` TEXT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`refund_id`),
    INDEX `idx_return_id` (`return_id`),
    INDEX `idx_payment_id` (`payment_id`),
    CONSTRAINT `fk_refund_return` FOREIGN KEY (`return_id`) REFERENCES `returns` (`return_id`) ON DELETE CASCADE,
    CONSTRAINT `fk_refund_payment` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- TABLE: contact_messages
CREATE TABLE `contact_messages` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `message` TEXT NOT NULL,
    `submitted_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- VIEW: order_shipping_summary
CREATE VIEW `order_shipping_summary` AS
SELECT 
    `o`.`order_id` AS `order_id`,
    `o`.`order_date` AS `order_date`,
    `o`.`total_amount` AS `total_amount`,
    `o`.`status` AS `order_status`,
    `s`.`shipping_method` AS `shipping_method`,
    `s`.`status` AS `shipping_status`,
    `s`.`tracking_number` AS `tracking_number`,
    `s`.`shipping_date` AS `shipping_date`,
    CONCAT(`a`.`street_address`, ', ', `a`.`city`, ', ', `a`.`province`, ' ', `a`.`postal_code`) AS `shipping_address`
FROM `orders` `o`
LEFT JOIN `shipping` `s` ON `o`.`order_id` = `s`.`order_id`
LEFT JOIN `addresses` `a` ON `s`.`address_id` = `a`.`id`;

-- Insert sample admin
INSERT INTO `users` (`first_name`, `last_name`, `username`, `email`, `password`, `phone`, `role`) VALUES
('Admin', 'User', 'admin', 'admin@thriftpalor.com', '2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '1234567890', 'seller');

-- Insert sample categories
INSERT INTO `product_categories` (`name`, `description`) VALUES
('Electronics', 'Electronic devices and accessories'),
('Clothing', 'Apparel and fashion items'),
('Books', 'Books and literature'),
('Home & Garden', 'Home decor and gardening supplies');
```

---

## Step 6

Open your browser and navigate to:

```
http://localhost/Thrift-Palor
```

---

# 🔑 Sample Administrator Account

The SQL script creates a sample administrator account.

| Username | Email                                                 |
| -------- | ----------------------------------------------------- |
| admin    | [admin@thriftpalor.com](mailto:admin@thriftpalor.com) |

> Password is stored as a BCrypt hash inside the database.

---

# 📦 Database Features

The SQL script automatically creates:

* Database
* 13 Tables
* Primary Keys
* Foreign Keys
* Indexes
* SQL View
* Sample Administrator Account
* Product Categories

---

# 📁 Project Structure

```
Thrift-Palor/

│
├── css/
├── images/
├── uploads/
├── includes/
├── js/
├── database/
├── index.php
├── login.php
├── register.php
├── products.php
├── cart.php
├── checkout.php
├── orders.php
├── contact.php
└── README.md
```

---

# 🔗 Database Relationships

The database uses a relational design with foreign key constraints.

Examples include:

* One User → Many Products
* One User → Many Addresses
* One User → One Cart
* One Cart → Many Cart Items
* One Order → Many Order Items
* One Order → One Payment
* One Order → One Shipping Record
* One Return → One Refund

---

# 🛡️ Notes

* The database uses **InnoDB** storage engine.
* Character set: **utf8mb4**
* Foreign key constraints enforce data integrity.
* Cascading deletes are implemented where appropriate.
* The project includes sample administrator data and product categories for testing.

---

# 👨‍💻 Authors

Developed as an **Applications Development Project** at **Cape Peninsula University of Technology (CPUT)**.

---

# 📄 License

This project is intended for educational purposes only.



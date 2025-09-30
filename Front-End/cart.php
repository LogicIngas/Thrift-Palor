<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: htmlogin.php");
    exit();
}

// Handle quantity updates and removals
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_quantity'])) {
        $product_id = $_POST['product_id'];
        $quantity = intval($_POST['quantity']);
        
        foreach ($_SESSION['cart'] as &$item) {
            if ($item['product_id'] == $product_id) {
                if ($quantity <= 0) {
                    // Remove item if quantity is 0
                    $_SESSION['cart'] = array_filter($_SESSION['cart'], function($item) use ($product_id) {
                        return $item['product_id'] != $product_id;
                    });
                } else {
                    $item['quantity'] = $quantity;
                }
                break;
            }
        }
    }
    
    if (isset($_POST['remove_item'])) {
        $product_id = $_POST['product_id'];
        $_SESSION['cart'] = array_filter($_SESSION['cart'], function($item) use ($product_id) {
            return $item['product_id'] != $product_id;
        });
    }
    
    // If proceeding to checkout from this page
    if (isset($_POST['proceed_to_checkout'])) {
        
        if (empty($_SESSION['cart'])) {
            // Redirect back to cart with an error if it's empty
            $_SESSION['error_message'] = "Your cart is empty. Please add items before checking out.";
            header("Location: cart.php");
            exit();
        }

        $database = new Database();
        $conn = $database->getConnection();
        $user_id = $_SESSION['user_id'];
        
        try {
            // Start a transaction to ensure both tables are updated successfully
            $conn->begin_transaction();
            
            // 1. Insert a new record into the carts table
            $cart_sql = "INSERT INTO carts (user_id) VALUES (?)";
            $cart_stmt = $conn->prepare($cart_sql);
            $cart_stmt->bind_param("i", $user_id);
            
            if (!$cart_stmt->execute()) {
                throw new Exception("Error creating cart: " . $cart_stmt->error);
            }
            
            $cart_id = $conn->insert_id;
            
            // 2. Insert each item from the session cart into the cart_items table
            $cart_item_sql = "INSERT INTO cart_items (cart_id, product_id, quantity) VALUES (?, ?, ?)";
            $cart_item_stmt = $conn->prepare($cart_item_sql);
            
            foreach ($_SESSION['cart'] as $item) {
                $product_id = $item['product_id'];
                $quantity = $item['quantity'];
                
                $cart_item_stmt->bind_param("iii", $cart_id, $product_id, $quantity);
                
                if (!$cart_item_stmt->execute()) {
                    throw new Exception("Error saving cart item for product ID " . $product_id . ": " . $cart_item_stmt->error);
                }
            }
            
            // Commit the transaction if all queries were successful
            $conn->commit();
            
            // Set success message and redirect
            $_SESSION['success_message'] = "Your cart has been saved to the database. Proceeding to checkout.";
            
            // Store the new cart_id in the session for the next page to use
            $_SESSION['active_cart_id'] = $cart_id;
            
            // Redirect to checkout page (DO NOT CLEAR SESSION CART - we need it for orders table later)
            header("Location: checkout.php");
            exit();
            
        } catch (Exception $e) {
            // Rollback the transaction on any error
            $conn->rollback();
            $_SESSION['error_message'] = "Checkout failed: " . $e->getMessage();
            header("Location: cart.php");
            exit();
        }
    }
}

// Re-calculate cart totals for display
$sub_total = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $sub_total += $item['price'] * $item['quantity'];
    }
}

$shipping = 50.00;
$total = $sub_total + $shipping;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart - Thrift Palor</title>
    <link rel="stylesheet" href="./css/style.css" type="text/css">
    <link rel="stylesheet" href="./css/cart.css" type="text/css">
    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" />
    <style>
        /* Mobile menu styles */
        #mobile-menu {
            display: none;
            cursor: pointer;
            padding: 10px;
        }
        
        @media (max-width: 799px) {
            #mobile-menu {
                display: block;
            }
            
            #navbar {
                display: flex;
                flex-direction: column;
                align-items: flex-start;
                justify-content: flex-start;
                position: fixed;
                top: 0;
                right: -300px;
                height: 100vh;
                width: 300px;
                background-color: #E8E6E6;
                box-shadow: 0 40px 60px rgba(0,0,0,0.1);
                padding: 80px 0 0 10px;
                transition: 0.3s;
                z-index: 999;
            }
            
            #navbar.active {
                right: 0;
            }
            
            #navbar li {
                margin-bottom: 25px;
            }
            
            #close-cart {
                position: absolute;
                top: 20px;
                left: 20px;
                font-size: 24px;
                cursor: pointer;
            }
        }

        /* Cart container styles */
        .cart-container {
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
            margin-top: 30px;
        }

        .cart-items {
            flex: 1;
            min-width: 300px;
        }

        .cart-summary {
            flex: 0 0 300px;
            background: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
        }

        .cart-item {
            display: flex;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
            gap: 15px;
        }

        .cart-item img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 4px;
        }

        .item-details {
            flex: 1;
        }

        .item-details h4 {
            margin: 0 0 5px 0;
            font-size: 16px;
        }

        .item-details .price {
            color: #088178;
            font-weight: bold;
            margin: 0;
        }

        .quantity-form {
            display: flex;
            align-items: center;
        }

        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .quantity-controls button {
            background: #088178;
            color: white;
            border: none;
            width: 30px;
            height: 30px;
            border-radius: 4px;
            cursor: pointer;
        }

        .quantity-controls input {
            width: 50px;
            text-align: center;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 5px;
        }

        .remove-btn {
            background: #ff6b6b;
            color: white;
            border: none;
            width: 30px;
            height: 30px;
            border-radius: 4px;
            cursor: pointer;
        }

        .item-total {
            font-weight: bold;
            color: #088178;
            min-width: 80px;
            text-align: right;
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }

        .summary-item.total {
            font-weight: bold;
            font-size: 18px;
            border-bottom: none;
        }

        .checkout-btn {
            width: 100%;
            background: #088178;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            margin-bottom: 15px;
        }

        .continue-shopping {
            display: block;
            text-align: center;
            color: #088178;
            text-decoration: none;
        }

        .empty-cart {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-cart i {
            font-size: 48px;
            color: #ccc;
            margin-bottom: 20px;
        }

        .empty-cart h3 {
            margin-bottom: 10px;
            color: #333;
        }

        .empty-cart p {
            color: #666;
            margin-bottom: 20px;
        }

        .normal {
            background: #088178;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 4px;
        }

        .success-message, .error-message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }

        .success-message {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .error-message {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <section id="header">
        <div id="mobile-menu">
            <i class="fas fa-bars"></i>
        </div>
        <div>
            <ul id="navbar">
                <li><a href="home.php">Home</a></li>
                <li><a href="Shop.php">Shop</a></li>
                <li><a href="AboutUs.html">About Us</a></li>
                <li><a href="Contact.html">Contact</a></li>
                <li><a class="active" href="cart.php"><i class="fas fa-shopping-cart"></i> Cart</a></li>
                <li><a href="orders.php">My Orders</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
    </section>

    <section id="page-header" class="cart-header">
        <h2>Shopping Cart</h2>
        <p>Review your items and proceed to checkout</p>
    </section>

    <section class="section-p1">
        <?php 
        if (isset($_SESSION['success_message'])) {
            echo '<div class="success-message">' . htmlspecialchars($_SESSION['success_message']) . '</div>';
            unset($_SESSION['success_message']);
        }
        if (isset($_SESSION['error_message'])) {
            echo '<div class="error-message">' . htmlspecialchars($_SESSION['error_message']) . '</div>';
            unset($_SESSION['error_message']);
        }
        ?>
        
        <?php if (empty($_SESSION['cart'])): ?>
            <div class="empty-cart">
                <i class="fas fa-shopping-cart" style="font-size: 48px; color: #ccc; margin-bottom: 20px;"></i>
                <h3>Your cart is empty</h3>
                <p>Continue shopping to add items to your cart</p>
                <a href="Shop.php" class="normal">Continue Shopping</a>
            </div>
        <?php else: ?>
            <div class="cart-container">
                <div class="cart-items">
                    <?php foreach ($_SESSION['cart'] as $item): ?>
                    <div class="cart-item">
                        <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                        <div class="item-details">
                            <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                            <p class="price">R<?php echo number_format($item['price'], 2); ?></p>
                        </div>
                        <form method="POST" class="quantity-form">
                            <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                            <div class="quantity-controls">
                                <button type="submit" name="update_quantity" 
                                        onclick="this.form.quantity.value=<?php echo $item['quantity'] - 1; ?>">-</button>
                                <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" 
                                       min="1" max="99" onchange="this.form.submit()">
                                <button type="submit" name="update_quantity" 
                                        onclick="this.form.quantity.value=<?php echo $item['quantity'] + 1; ?>">+</button>
                            </div>
                        </form>
                        <form method="POST">
                            <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                            <button type="submit" name="remove_item" class="remove-btn">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                        <div class="item-total">
                            R<?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="cart-summary">
                    <h3>Order Summary</h3>
                    <div class="summary-item">
                        <span>Subtotal:</span>
                        <span>R<?php echo number_format($sub_total, 2); ?></span>
                    </div>
                    <div class="summary-item">
                        <span>Shipping:</span>
                        <span>R<?php echo number_format($shipping, 2); ?></span>
                    </div>
                    <div class="summary-item total">
                        <span>Total:</span>
                        <span>R<?php echo number_format($total, 2); ?></span>
                    </div>
                    
                    <form method="POST">
                        <input type="hidden" name="proceed_to_checkout" value="1">
                        <button type="submit" class="checkout-btn">Proceed to Checkout</button>
                    </form>
                    
                    <a href="Shop.php" class="continue-shopping">Continue Shopping</a>
                </div>
            </div>
        <?php endif; ?>
    </section>

    <footer class="section-p1">
        <div class="follow">
            <h2>Follow on</h2>
            <div class="icon">
                <i class="fab fa-facebook-f"></i>
                <i class="fab fa-twitter"></i>
                <i class="fab fa-instagram"></i>
                <i class="fab fa-youtube"></i>
            </div>
        </div>
        <div class="copyright">
            <p>@ThriftPalor. All rights reserved.</p>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenu = document.getElementById('mobile-menu');
            const navbar = document.getElementById('navbar');
            
            if (mobileMenu && navbar) {
                mobileMenu.addEventListener('click', function() {
                    navbar.classList.toggle('active');
                });
            }
        });
    </script>
</body>
</html>
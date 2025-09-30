<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Success - Thrift Palor</title>
    <link rel="stylesheet" href="./css/style.css" type="text/css">
    <link rel="stylesheet" href="./css/order.css" type="text/css">
    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" />
    <style>
        .success-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 30px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .success-icon {
            font-size: 64px;
            color: #4CAF50;
            margin-bottom: 20px;
        }
        
        .success-message {
            margin-bottom: 30px;
        }
        
        .order-details {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: left;
        }
        
        .btn-container {
            display: flex;
            justify-content: center;
            gap: 10px;
        }
        
        .btn-primary {
            background-color: #471c3c;
            color: white;
        }
        
        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }

        @media (max-width: 768px) {
            .btn-container {
                flex-direction: column;
            }
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
                <li><a href="orders.php">My Orders</a></li>
                <li><a href="cart.php"><i class="fas fa-shopping-cart"></i> Cart</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
    </section>

    <div class="success-container">
        <div class="success-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        <h1 class="success-message">Order Placed Successfully!</h1>
        <p>Thank you for your purchase. Your order has been received and is being processed.</p>
        
        <div class="order-details">
            <?php
            session_start();
            require_once 'config/database.php';

            if (!isset($_SESSION['user_id']) || !isset($_GET['order_id'])) {
                header("Location: htmlogin.php");
                exit();
            }

            $order_id = $_GET['order_id'];
            $database = new Database();
            $conn = $database->getConnection();
            $buyer_id = $_SESSION['user_id'];
            
            // Check if this order belongs to the logged-in user
            $sql_verify_order = "SELECT * FROM orders WHERE order_id = ? AND buyer_id = ?";
            $stmt_verify_order = $conn->prepare($sql_verify_order);
            if (!$stmt_verify_order) {
                die("Prepare failed: " . $conn->error);
            }
            $stmt_verify_order->bind_param("ii", $order_id, $buyer_id);
            $stmt_verify_order->execute();
            $result_verify_order = $stmt_verify_order->get_result();

            if ($result_verify_order->num_rows === 0) {
                echo "<h3>Invalid Order ID or access denied.</h3>";
            } else {
                $order = $result_verify_order->fetch_assoc();
                echo '<h4>Order Details</h4>';
                echo '<p><strong>Order ID:</strong> ' . htmlspecialchars($order['order_id']) . '</p>';
                echo '<p><strong>Total Amount:</strong> R' . number_format($order['total_amount'], 2) . '</p>';
                echo '<p><strong>Order Status:</strong> ' . htmlspecialchars($order['status']) . '</p>';
                echo '<p><strong>Order Date:</strong> ' . htmlspecialchars($order['order_date']) . '</p>';
                
                // Fetch and display order items
                $sql_items = "SELECT quantity, product_name, price FROM order_items WHERE order_id = ?";
                $stmt_items = $conn->prepare($sql_items);
                if (!$stmt_items) {
                    die("Prepare failed: " . $conn->error);
                }
                $stmt_items->bind_param("i", $order_id);
                $stmt_items->execute();
                $result_items = $stmt_items->get_result();
                
                if ($result_items->num_rows > 0) {
                    echo '<h4>Order Items</h4>';
                    while ($item = $result_items->fetch_assoc()) {
                        echo '<p>' . htmlspecialchars($item['product_name']) . ' x ' . htmlspecialchars($item['quantity']) . ' - R' . number_format($item['price'] * $item['quantity'], 2) . '</p>';
                    }
                }

                // Fetch and display shipping address
                $sql_shipping = "SELECT a.street_address, a.city, a.province, a.postal_code 
                                 FROM shipping s
                                 JOIN addresses a ON s.address_id = a.id
                                 WHERE s.order_id = ?";

                $stmt_shipping = $conn->prepare($sql_shipping);
                
                // Add error checking for prepare()
                if ($stmt_shipping === false) {
                    echo '<p>Error fetching shipping address: ' . $conn->error . '</p>';
                } else {
                    $stmt_shipping->bind_param("i", $order_id);
                    $stmt_shipping->execute();
                    $result = $stmt_shipping->get_result();
                    
                    if ($result->num_rows > 0) {
                        $address = $result->fetch_assoc();
                        echo '<div class="shipping-address">';
                        echo '<h4>Shipping Address</h4>';
                        echo '<p>' . htmlspecialchars($address['street_address']) . '<br>';
                        echo htmlspecialchars($address['city']) . ', ' . htmlspecialchars($address['province']) . '<br>';
                        echo htmlspecialchars($address['postal_code']) . '</p>';
                        echo '</div>';
                    } else {
                         echo '<p>No shipping address found for this order.</p>';
                    }
                }
            }
            ?>
        </div>
        
        <div class="btn-container">     
            <a href="shipping.php?order_id=<?php echo htmlspecialchars($_GET['order_id']); ?>" class="btn btn-primary">View Shipping Details</a>
            <a href="orders.php" class="btn btn-primary">View Orders</a>
            <a href="Shop.php" class="btn btn-secondary">Continue Shopping</a>
        </div>
    </div>

    <script>
        // Mobile menu functionality
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenu = document.getElementById('mobile-menu');
            const navbar = document.getElementById('navbar');
            
            mobileMenu.addEventListener('click', function() {
                navbar.classList.toggle('active');
            });
        });
    </script>
</body>
</html>
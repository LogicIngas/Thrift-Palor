<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['order_id'])) {
    header("Location: orders.php");
    exit();
}

$database = new Database();
$conn = $database->getConnection();
$buyer_id = $_SESSION['user_id'];
$order_id = $_GET['order_id'];

// Fetch order and shipping details
$sql = "SELECT o.order_id, o.total_amount, o.status, o.order_date, o.tracking_number, o.shipping_date,
               a.street_address, a.city, a.province, a.postal_code, a.address_type
        FROM orders o
        JOIN addresses a ON o.address_id = a.id
        WHERE o.order_id = ? AND o.buyer_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $order_id, $buyer_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();

if (!$order) {
    echo "Order not found.";
    exit();
}

// Fetch order items
$items_sql = "SELECT product_name, quantity, price FROM order_items WHERE order_id = ?";
$items_stmt = $conn->prepare($items_sql);
$items_stmt->bind_param("i", $order_id);
$items_stmt->execute();
$items_result = $items_stmt->get_result();
$order_items = $items_result->fetch_all(MYSQLI_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shipping Confirmation - Thrift Palor</title>
    <link rel="stylesheet" href="./css/style.css" type="text/css">
    <link rel="stylesheet" href="./css/order.css" type="text/css">
    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" />
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

    <div class="order-container">
       <div class="success-message">
    <i class="fas fa-check-circle"></i> <h2>Order Confirmed!</h2>
    <p>
        Thank you for your order! We've successfully received your payment and are now preparing your items for shipment. 
        You will receive another update when the order ships.
    </p>
</div>

        <div class="order-details-card">
            <h3>Shipping Details</h3>
            <div class="detail-item">
                <strong>Tracking Number:</strong>
                <span><?php echo htmlspecialchars($order['tracking_number']); ?></span>
            </div>
            <div class="detail-item">
                <strong>Estimated Delivery:</strong>
                <span><?php echo date('F j, Y', strtotime($order['shipping_date'])); ?></span>
            </div>
        </div>
        
        <div class="order-details-card">
            <h3>Order Details</h3>
            <div class="detail-item">
                <strong>Order ID:</strong>
                <span><?php echo htmlspecialchars($order['order_id']); ?></span>
            </div>
            <div class="detail-item">
                <strong>Order Status:</strong>
                <span><?php echo htmlspecialchars($order['status']); ?></span>
            </div>
            <div class="detail-item">
                <strong>Total Amount:</strong>
                <span>R<?php echo number_format($order['total_amount'] + 50, 2); ?></span>
            </div>
        </div>

        <div class="order-items-card">
            <h3>Order Items</h3>
            <?php foreach ($order_items as $item): ?>
                <div class="order-item">
                    <span><?php echo htmlspecialchars($item['product_name']); ?> x<?php echo $item['quantity']; ?></span>
                    <span>R<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="shipping-address-card">
            <h3>Shipping Address</h3>
            <p><?php echo htmlspecialchars($order['street_address']); ?></p>
            <p><?php echo htmlspecialchars($order['city']) . ', ' . htmlspecialchars($order['province']); ?></p>
            <p><?php echo htmlspecialchars($order['postal_code']); ?></p>
            <p>Address Type: <?php echo ucfirst(htmlspecialchars($order['address_type'])); ?></p>
        </div>

       <div class="shipping-actions">
    <a href="orders.php" class="action-btn secondary-btn">View All My Orders</a>
    <a href="Shop.php" class="action-btn primary-btn">Continue Shopping</a>
</div>
    </div>
</body>
</html>
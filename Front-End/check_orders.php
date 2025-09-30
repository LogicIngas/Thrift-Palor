<?php
// check_orders.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = "localhost";
$username = "root";
$password = "";
$dbname = "thriftdb";

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if orders table exists
$result = $conn->query("SHOW TABLES LIKE 'orders'");
if ($result->num_rows > 0) {
    echo "✅ Orders table exists<br>";
    
    // Show recent orders
    $orders = $conn->query("SELECT * FROM orders ORDER BY order_id DESC LIMIT 5");
    if ($orders->num_rows > 0) {
        echo "<h3>Recent Orders:</h3>";
        while($order = $orders->fetch_assoc()) {
            echo "Order #{$order['order_id']} - R{$order['total_amount']} - {$order['status']}<br>";
        }
    } else {
        echo "No orders found in database<br>";
    }
} else {
    echo "❌ Orders table doesn't exist<br>";
}

$conn->close();
?>
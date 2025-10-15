<?php
session_start();

// Ensure the user is logged in and there's an active order
if (!isset($_SESSION['user_id']) || !isset($_SESSION['order_id'])) {
    header("Location: checkout.php");
    exit();
}

$order_id = $_SESSION['order_id'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - Thrift Palor</title>
    <link rel="stylesheet" href="./css/style.css" type="text/css">
    <link rel="stylesheet" href="./css/checkout.css" type="text/css">
    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" />
    <style>
        .payment-container {
            max-width: 500px;
            margin: 40px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        /* Navigation Bar Styles */
        nav {
            background-color: #2c3e50;
            padding: 15px 0;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 20px;
        }
        
        .logo {
            color: white;
            font-size: 24px;
            font-weight: bold;
            text-decoration: none;
        }
        
        .nav-links {
            display: flex;
            list-style: none;
            margin: 0;
            padding: 0;
        }
        
        .nav-links li {
            margin-left: 20px;
        }
        
        .nav-links a {
            color: white;
            text-decoration: none;
            font-size: 16px;
            transition: color 0.3s ease;
        }
        
        .nav-links a:hover {
            color: #3498db;
        }
        
        .nav-links i {
            margin-right: 5px;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav>
        <div class="nav-container">
            <a href="index.php" class="logo">Thrift Palor</a>
            <ul class="nav-links">
                <li><a href="home.php"><i class="fas fa-home"></i>Home</a></li>
                <li><a href="shop.php"><i class="fas fa-shopping-bag"></i>Shop</a></li>
                <li><a href="cart.php"><i class="fas fa-shopping-cart"></i>Cart</a></li>
                <li><a href="orders.php"><i class="fas fa-clipboard-list"></i>Orders</a></li>
                <li><a href="profile.php"><i class="fas fa-user"></i>Profile</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i>Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="payment-container">
        <h2>Payment Information</h2>
        <form action="process_payment.php" method="POST">
            <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($order_id); ?>">
            <div class="form-group">
                <label for="card_name">Card Holder Name</label>
                <input type="text" id="card_name" name="card_name" required>
            </div>
            <div class="form-group">
                <label for="card_number">Card Number</label>
                <input type="text" id="card_number" name="card_number" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="expiry_date">Expiry Date</label>
                    <input type="text" id="expiry_date" name="expiry_date" placeholder="MM/YY" required>
                </div>
                <div class="form-group">
                    <label for="cvv">CVV</label>
                    <input type="text" id="cvv" name="cvv" required>
                </div>
            </div>
            <button type="submit" name="submit_payment" class="place-order-btn">Pay Now</button>
        </form>
    </div>
</body>
</html>
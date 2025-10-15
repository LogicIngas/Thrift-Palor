<?php
session_start();
require_once 'products.php';
$productHandler = new Product();
$products = $productHandler->getAllProducts();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop - Thrift Palor</title>
    <link rel="stylesheet" href="./css/style.css" type="text/css">
    <link rel="stylesheet" href="./css/enhanced-style.css" type="text/css">
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
                <li><a class="active" href="Shop.php">Shop</a></li>
                <li><a href="AboutUs.html">About Us</a></li>
                <li><a href="Contact.html">Contact</a></li>
                
                <!-- Seller-specific navigation -->
                <?php if(isset($_SESSION['user_id']) && $_SESSION['role'] === 'seller'): ?>
                    <li><a href="seller_dashboard.php">Seller Dashboard</a></li>
                    <li><a href="add_product.php">Add Product</a></li>
                <?php endif; ?>
                
                <li><a href="orders.php">My Orders</a></li>
                <li><a href="cart.php"><i class="fas fa-shopping-cart"></i> Cart</a></li>
                
                <?php if(isset($_SESSION['user_id'])): ?>
                    <li><a href="logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="htmlogin.php">Login</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </section>

    <section id="page-header">
        <h2>Welcome to Our Shop</h2>
        <p>Discover amazing thrift finds and save up to 70% off!</p>
    </section>

    <section id="product1" class="section-p1">
        <div class="pro-container">
            <?php foreach ($products as $product): ?>
            <div class="pro" onclick="window.location.href='sproduct.php?id=<?php echo $product['product_id']; ?>'">
                <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                <div class="des">
                    <span><?php echo htmlspecialchars($product['category']); ?></span>
                    <h5><?php echo htmlspecialchars($product['name']); ?></h5>
                    <div class="star">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <h4>R<?php echo number_format($product['price'], 2); ?></h4>
                </div>
                <form action="add_to_cart.php" method="POST" class="add-to-cart-form">
                    <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                    <input type="hidden" name="product_name" value="<?php echo htmlspecialchars($product['name']); ?>">
                    <input type="hidden" name="product_price" value="<?php echo $product['price']; ?>">
                    <input type="hidden" name="product_image" value="<?php echo htmlspecialchars($product['image_url']); ?>">
                    <button type="submit" name="add_to_cart" class="cart-btn">
                        <i class="fal fa-shopping-cart"></i>
                    </button>
                </form>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section id="pagination" class="section-p1">
        <a href="#" class="normal">1</a>
        <a href="#" class="normal">2</a>
        <a href="#" class="normal"><i class="fal fa-long-arrow-alt-right"></i></a>
    </section>

    <section id="newsletter" class="section-p1 section-m1">
        <div class="newstext">
            <h4>Sign Up for Newsletter</h4>
            <p>Get E-mail updates about latest products and <span>special offers.</span></p>
        </div>
        <div class="form">
            <input type="email" placeholder="Your Email Address">
            <button class="normal">Sign Up</button>
        </div>
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
    
    <script src="./js/enhanced-effects.js"></script>
</body>

<!-- Notification Messages -->
<?php if(isset($_SESSION['success'])): ?>
    <div id="notification" class="notification success">
        <i class="fas fa-check-circle"></i>
        <span><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></span>
        <button class="close-btn" onclick="this.parentElement.style.display='none';">&times;</button>
    </div>
<?php endif; ?>

<?php if(isset($_SESSION['error'])): ?>
    <div id="notification" class="notification error">
        <i class="fas fa-exclamation-circle"></i>
        <span><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></span>
        <button class="close-btn" onclick="this.parentElement.style.display='none';">&times;</button>
    </div>
<?php endif; ?>


</html>
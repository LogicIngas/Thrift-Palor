<?php
session_start();
require_once 'products.php';

// --- Main Product Retrieval ---
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 1;
$productHandler = new Product();
$product = $productHandler->getProductById($product_id);

if (!$product) {
    header("Location: Shop.php");
    exit();
}

// --- Similar Products Retrieval (New Section) ---
// Note: This function needs to be implemented in your products.php
$similar_products = $productHandler->getSimilarProducts($product_id, 4); 

// Determine active link for the navbar
$active_page = basename(__FILE__);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - Thrift Palor</title>
    <link rel="stylesheet" href="./css/style.css" type="text/css">
    <link rel="stylesheet" href="./css/cart.css" type="text/css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <section id="header">
        <div id="mobile-menu">
            <i class="fas fa-bars"></i>
        </div>
        <div>
            <ul id="navbar">
                <li><a href="home.php" class="<?php echo ($active_page == 'home.php' ? 'active' : ''); ?>">Home</a></li>
                <li><a href="Shop.php" class="<?php echo ($active_page == 'Shop.php' ? 'active' : ''); ?>">Shop</a></li>
                <li><a href="AboutUs.html" class="<?php echo ($active_page == 'AboutUs.html' ? 'active' : ''); ?>">About Us</a></li>
                <li><a href="Contact.html" class="<?php echo ($active_page == 'Contact.html' ? 'active' : ''); ?>">Contact</a></li>
                <li><a href="orders.php" class="<?php echo ($active_page == 'orders.php' ? 'active' : ''); ?>">My Orders</a></li>
                <li><a href="cart.php" class="<?php echo ($active_page == 'cart.php' ? 'active' : ''); ?>"><i class="fas fa-shopping-cart"></i> Cart</a></li>
                <li><a href="logout.php">Logout</a></li> 
            </ul>
        </div>
    </section>

    <section id="prodetails" class="section-p1">
        <div class="single-pro-image">
            <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" id="MainImg" class="main-product-img">
        </div>

        <div class="single-pro-details">
            <span class="product-category"><?php echo htmlspecialchars($product['category']); ?></span>
            <h1><?php echo htmlspecialchars($product['name']); ?></h1>
            <h2 class="product-price">R<?php echo number_format($product['price'], 2); ?></h2>
            
            <div class="product-rating">
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star-half-alt"></i>
                <i class="far fa-star"></i>
                <span>(12 Reviews)</span>
            </div>

            <form action="add_to_cart.php" method="POST" class="add-to-cart-form">
                <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                <input type="hidden" name="product_name" value="<?php echo htmlspecialchars($product['name']); ?>">
                <input type="hidden" name="product_price" value="<?php echo $product['price']; ?>">
                <input type="hidden" name="product_image" value="<?php echo htmlspecialchars($product['image_url']); ?>">
                
                <div class="quantity-control">
                    <label for="quantity">Quantity:</label>
                    <input type="number" name="quantity" id="quantity" value="1" min="1" max="99" class="quantity-input">
                </div>
                
                <button type="submit" name="add_to_cart" class="btn primary-btn-lg">
                    <i class="fas fa-shopping-cart"></i> Add to Cart
                </button>
            </form>

            <div class="product-description-box">
                <h4>Product Details</h4>
                <p><?php echo htmlspecialchars($product['description']); ?></p>
            </div>
            
            <div class="product-features">
                <span class="feature-badge"><i class="fas fa-truck"></i> Free Shipping over R500</span>
                <span class="feature-badge"><i class="fas fa-recycle"></i> Sustainably Sourced</span>
            </div>
        </div>
    </section>

    <section id="similar-products" class="section-p1">
        <h2>You Might Also Like</h2>
        <p>Check out these similar items or random finds!</p>
        <div class="related-pro-container">
            <?php foreach ($similar_products as $sim_pro): ?>
            <div class="related-pro" onclick="window.location.href='sproduct.php?id=<?php echo $sim_pro['product_id']; ?>';">
                <img src="<?php echo htmlspecialchars($sim_pro['image_url']); ?>" alt="<?php echo htmlspecialchars($sim_pro['name']); ?>">
                <div class="des">
                    <span><?php echo htmlspecialchars($sim_pro['category']); ?></span>
                    <h5><?php echo htmlspecialchars($sim_pro['name']); ?></h5>
                    <div class="star">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <h4>R<?php echo number_format($sim_pro['price'], 2); ?></h4>
                </div>
                <a href="#"><i class="fas fa-shopping-cart cart-btn"></i></a> 
            </div>
            <?php endforeach; ?>
        </div>
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
    
    <script src="./js/Home.js"></script>
</body>
</html>
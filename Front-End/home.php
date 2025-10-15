<?php
session_start();
require_once 'products.php';
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: htmlogin.php");
    exit();
}

$productHandler = new Product();
$featuredProducts = $productHandler->getAllProducts();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thrift Palor - Home</title>
    <link rel="stylesheet" href="./css/style.css" type="text/css">
    <link rel="stylesheet" href="./css/enhanced-style.css" type="text/css">
    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" />
    <style>
        /* Hero Section Enhancements */
        #hero {
            display: flex;
            justify-content: space-between;
            align-items: center;
            text-align: left;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 100px 80px;
            position: relative;
            min-height: 400px;
            overflow: hidden;
        }

        .hero-content {
            flex-grow: 1;
            max-width: 65%;
            z-index: 10;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: flex-start;
        }
        
        .hero-logo-container {
            flex-shrink: 0;
            display: flex;
            justify-content: flex-end;
            align-items: center;
            margin-left: 20px;
            z-index: 10;
        }

        .hero-logo {
            width: 100%;
            max-height: 350px;
            max-width: 300px;
            height: auto;
            border-radius: 50%;
            box-shadow: 0 0 30px rgba(255, 255, 255, 0.2);
            transition: transform 0.3s ease;
            object-fit: contain;
        }

        .hero-logo:hover {
            transform: scale(1.05);
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.02); }
            100% { transform: scale(1); }
        }
        
        #hero h4, #hero h2, #hero h1, #hero p, #hero a {
            text-align: left;
            margin-left: 0;
            margin-right: 0;
        }
        
        #hero h2 {
            font-size: 3.5em;
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            color: white !important;
        }

        /* Feature Section */
        #feature {
            padding: 80px 20px;
            background: var(--light-bg);
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 30px;
        }

        .fe-box {
            width: calc(16.66% - 20px);
            min-width: 150px;
            background: white;
            padding: 40px 30px;
            border-radius: 15px;
            text-align: center;
            box-shadow: var(--shadow);
            transition: var(--transition);
            border: 2px solid transparent;
        }
        
        .fe-box:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-hover);
            border-color: var(--accent-color);
        }
        
        .fe-box img {
            width: 80px;
            height: 80px;
            margin-bottom: 20px;
            filter: hue-rotate(45deg);
        }
        
        .fe-box h6 {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-size: 18px;
            font-weight: 600;
            margin: 15px 0;
        }
        
        /* Banner Section */
        #banner {
            background: linear-gradient(rgba(71, 28, 60, 0.8), rgba(106, 44, 94, 0.8)), url('https://images.unsplash.com/photo-1441986300917-64674bd600d8?ixlib=rb-4.0.3&auto=format&fit=crop&w=2000&q=80');
            background-size: cover;
            background-position: center;
            padding: 100px 20px;
            text-align: center;
            color: white;
        }
        
        #banner h4 {
            font-size: 24px;
            margin-bottom: 15px;
            color: white !important;
        }
        
        #banner h2 {
            font-size: 3em;
            margin-bottom: 20px;
            color: white !important;
        }
        
        #banner span {
            color: var(--accent-color);
        }
        
        /* New Arrivals Section */
        #product1 {
            padding: 80px 20px;
        }
        
        .section-p1 {
            padding: 80px 20px;
        }
        
        .section-m1 {
            margin: 40px 0;
        }
        
        /* Newsletter Enhancements */
        #newsletter {
            background: linear-gradient(135deg, var(--text-dark), #1a202c);
            color: white;
            padding: 80px 20px;
        }
        
        .newstext h4 {
            font-size: 24px;
            margin-bottom: 15px;
        }
        
        .newstext p {
            font-size: 16px;
            opacity: 0.9;
        }
        
        .newstext span {
            color: var(--accent-color);
            font-weight: 600;
        }
        
        .form input {
            padding: 15px 20px;
            border: none;
            border-radius: 8px;
            width: 300px;
            max-width: 100%;
            font-size: 16px;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            #hero h2 {
                font-size: 2.5em;
            }
            
            #hero h1 {
                font-size: 2em;
            }
            
            #banner h2 {
                font-size: 2em;
            }
            
            .fe-box {
                width: calc(33% - 20px);
            }
            
            .form input {
                width: 100%;
                margin-bottom: 15px;
            }
            
            #hero {
                flex-direction: column;
                text-align: center;
                padding: 80px 40px;
            }

            .hero-content {
                max-width: 100%;
                margin-bottom: 40px;
            }
            
            .hero-logo-container {
                max-width: 100%;
                order: -1;
            }

            .hero-logo {
                max-width: 180px;
            }
            
            #hero h4, #hero h2, #hero h1, #hero p, #hero a {
                 text-align: center;
            }
            
            #hero p {
                 margin-left: auto;
                 margin-right: auto;
            }
        }

        @media (max-width: 600px) {
            .fe-box {
                width: calc(50% - 20px);
            }
        }
        
        @media (max-width: 480px) {
            #hero {
                padding: 60px 20px;
            }
            
            #hero h2 {
                font-size: 2em;
            }
            
            .section-p1 {
                padding: 40px 20px;
            }
        }
        .notification {
    position: fixed;
    top: 30px;
    right: 30px;
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 15px 25px;
    border-radius: 12px;
    color: #fff;
    font-weight: 500;
    box-shadow: 0 8px 20px rgba(0,0,0,0.25);
    opacity: 0;
    pointer-events: none;
    transform: translateY(-20px);
    transition: all 0.5s ease;
    z-index: 9999;
    max-width: 320px;
}

.notification.show {
    opacity: 1;
    pointer-events: auto;
    transform: translateY(0);
}

.notification.success {
    background: linear-gradient(135deg, #28a745, #3ed75f);
}

.notification.error {
    background: linear-gradient(135deg, #dc3545, #e95f5f);
}

.notification i {
    font-size: 20px;
}

.notification span {
    flex-grow: 1;
}

.notification .close-btn {
    background: transparent;
    border: none;
    font-size: 18px;
    color: #fff;
    cursor: pointer;
    padding: 0;
    line-height: 1;
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
                <li><a href="home.php" class="active">Home</a></li>
                <li><a href="profile.php">My Profile</a></li>
                <li><a href="Shop.php">Shop Now</a></li>
                <li><a href="AboutUs.html">About Us</a></li>
                <li><a href="contact.php">Contact</a></li>
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

    <!-- Display success/error messages -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="success-message"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="error-message"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <!-- Your existing hero section -->
    <section id="hero">
        <div class="hero-content">
            <h4>Trade-in-offer</h4>
            <h2>Super value deals</h2>
            <h1>On all products</h1>
            <p>Save more with coupons & up to 70% off! </p>
            <a href="Shop.php" class="normal">Shop Now</a>
        </div>
        <div class="hero-logo-container">
            <img src="images/Logo.jpg" alt="Thrit Palor Business Logo" class="hero-logo">
        </div>
    </section>

    <section id="product1" class="section-p1">
        <h2>Featured Products</h2>
        <p>Summer Collection New Modern Design</p>
        <div class="pro-container">
            <?php foreach (array_slice($featuredProducts, 0, 8) as $product): ?>
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

    <section id="banner" class="section-m1">
        <h4>Repair Services </h4>
        <h2>Up to <span>70% Off</span> - All t-Shirts & Accessories</h2>
        <a href="Shop.php" class="normal">Explore More</a>
    </section>

    <section id="product1" class="section-p1">
        <h2>New Arrivals</h2>
        <p>Fresh styles just for you</p>
        <div class="pro-container">
            <?php foreach (array_slice($featuredProducts, 4, 8) as $product): ?>
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

    <section id="newsletter" class="section-p1 section-m1">
        <div class="newstext">
            <h4>Sign Up For Newsletters</h4>
            <p>Get E-mail updates about our latest shop and <span>special offers.</span></p>
        </div>
        <div class="form">
            <input type="email" placeholder="Your email address">
            <button class="normal">Sign Up</button>
        </div>
    </section>

    <footer class="section-p1">
        <div class="footer-grid-wrapper">
            <div class="col">
                <h4>Contact</h4>
                <p><strong>Address: </strong> 8001 Foreshore, Adderly St, Cape Town</p>
                <p><strong>Phone: </strong> (+27) 069 927 9438 /(+26) 01 2345 6789</p>
                <p><strong>Hours: </strong> Open 24/7, Mon - Sat</p>
                <div class="follow">
                    <h4>Follow us</h4>
                    <div class="icon">
                        <i class="fab fa-facebook-f"></i>
                        <i class="fab fa-twitter"></i>
                        <i class="fab fa-instagram"></i>
                        <i class="fab fa-pinterest-p"></i>
                        <i class="fab fa-youtube"></i>
                    </div>
                </div>
            </div>

            <div class="col">
                <h4>About</h4>
                <a href="#">About us</a>
                <a href="#">Delivery Information</a>
                <a href="#">Privacy Policy</a>
                <a href="#">Terms & Conditions</a>
                <a href="#">Contact Us</a>
            </div>

            <div class="col">
                <h4>My Account</h4>
                <a href="#">Sign In</a>
                <a href="#">View Cart</a>
                <a href="#">My Wishlist</a>
                <a href="#">Track My Order</a>
                <a href="#">Help</a>
            </div>

            <div class="col install">
                <h4>Install App</h4>
                <p>From App Store or Google Play</p>
                <div class="row">
                    <img src="img/pay/app.jpg" alt="">
                    <img src="img/pay/play.jpg" alt="">
                </div>
                <p>Secured Payment Gateways </p>
                <img src="img/pay/pay.png" alt="">
            </div>

            <div class="copyright">
                <p>© 2024, ThriftPalor</p>
            </div>
        </div>
    </footer>

    <script src="./js/enhanced-effects.js"></script>
    <script>
        // Additional home page specific animations
        document.addEventListener('DOMContentLoaded', function() {
            // Animate feature boxes on scroll
            const featureBoxes = document.querySelectorAll('.fe-box');
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, { threshold: 0.1 });
            
            featureBoxes.forEach(box => {
                box.style.opacity = '0';
                box.style.transform = 'translateY(30px)';
                box.style.transition = 'all 0.6s ease';
                observer.observe(box);
            });
            
            // Hero text animation
            const heroText = document.querySelector('#hero h2');
            if (heroText) {
                setTimeout(() => {
                    heroText.style.opacity = '1';
                    heroText.style.transform = 'translateY(0)';
                }, 300);
            }
            
            // Mobile menu functionality
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
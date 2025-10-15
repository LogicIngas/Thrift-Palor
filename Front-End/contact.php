<?php
session_start();
require_once 'config/database.php';

// Create database instance and get connection
$db = new Database();
$conn = $db->getConnection();

if (isset($_POST['submit_contact'])) {
    // Sanitize inputs
    $name = htmlspecialchars(trim($_POST['name']));
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $message = htmlspecialchars(trim($_POST['message']));

    // Validate inputs
    if (empty($name) || empty($email) || empty($message)) {
        $_SESSION['error'] = "Please fill in all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Please enter a valid email.";
    } else {
        $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, message) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $message);

        if ($stmt->execute()) {
            $_SESSION['success'] = "Your message has been submitted successfully!";
        } else {
            $_SESSION['error'] = "Failed to submit message. Please try again.";
        }

        $stmt->close();
    }

    // Close connection
    $db->closeConnection();

    header("Location: contact.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Thrift Palor</title>
    <link rel="stylesheet" href="./css/style.css" type="text/css">
    <link rel="stylesheet" href="./css/enhanced-style.css">
    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" />

    <style>
        /* Notification banner styles */
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 25px;
            border-radius: 8px;
            color: #fff;
            font-weight: 600;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.5s ease, transform 0.5s ease;
            z-index: 9999;
        }

        .notification.show {
            opacity: 1;
            pointer-events: auto;
            transform: translateY(0);
        }

        .notification.success {
            background: #28a745;
        }

        .notification.error {
            background: #dc3545;
        }
    </style>
</head>
<body>
    <!-- Header/Nav -->
    <section id="header">
        <div id="mobile-menu">
            <i class="fas fa-bars"></i>
        </div>
        <div>
            <ul id="navbar">
                <li><a href="home.php">Home</a></li>
                <li><a href="Shop.php">Shop</a></li>
                <li><a href="AboutUs.html">About Us</a></li>
                <li><a href="contact.php" class="active">Contact</a></li>
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

    <!-- Notification Messages -->
    <?php if(isset($_SESSION['success'])): ?>
        <div id="notification" class="notification success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php elseif(isset($_SESSION['error'])): ?>
        <div id="notification" class="notification error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <!-- Page Header -->
    <section id="page-header" class="contact-header">
        <h2>Looking For Help</h2>
        <p>Leave a message, we love to hear from you!</p>
    </section>

    <!-- Contact Form & Details -->
    <section id="contact-details" class="section-p1">
        <div class="details">
            <span>GET IN TOUCH</span>
            <h2>Visit one of our agency locations or contact us today</h2>
            <h3>Head Office</h3>
            <div>
                <li>
                    <i class="fal fa-map"></i>
                    <p>8001 Foreshore, Adderly St, Cape Town</p>
                </li>
                <li>
                    <i class="far fa-envelope"></i>
                    <p>thriftpalor@gmail.com</p>
                </li>
                <li>
                    <i class="fas fa-phone-alt"></i>
                    <p>(+27) 069 927 9438</p>
                </li>
                <li>
                    <i class="far fa-clock"></i>
                    <p>Monday to Saturday: 9:00am to 16:00pm</p>
                </li>
            </div>
        </div>

        <div class="map">
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d13239.389240366601!2d18.67389145!3d-33.9189688!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x1dcc53d6d67b0933%3A0x86134b2f150e26d8!2s892%20Riba%20St%2C%20Langa%2C%20Cape%20Town%2C%207455%2C%20South%20Africa!5e0!3m2!1sen!2sza!4v1709477064434!5m2!1sen!2sza" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>
    </section>

    <section id="form-details">
        <form action="" method="POST">
            <span>LEAVE A MESSAGE</span>
            <h2>We love to hear from you</h2>
            <input type="text" name="name" placeholder="Your Name" required>
            <input type="email" name="email" placeholder="E-mail" required>
            <textarea name="message" cols="30" rows="10" placeholder="Your Message" required></textarea>
            <button type="submit" name="submit_contact" class="normal">Submit</button>
        </form>

        <div class="people">
            <div>
                <img src="images/1.png" alt="">
                <p><span>Pam</span> Web Developer <br> Phone: +2772 112 3847 <br>Email: pam@thrifptar.com</p>
            </div>
            <div>
                <img src="images/2.png" alt="">
                <p><span>Inga Mbobo</span> Full Stack Developer <br> Phone: (+27)64 360 6628 <br>Email: 230711723@mycput.ac.za</p>
            </div>
            <div>
                <img src="images/3.png" alt="">
                <p><span>Olona Williams</span> Full Stack Developer <br> Phone: +000 123 000 77 88 <br>Email: contact@thrifptar.com</p>
            </div>
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

    <script>
        // Show notification and fade out
        const notification = document.getElementById('notification');
        if(notification) {
            notification.classList.add('show');
            setTimeout(() => {
                notification.classList.remove('show');
            }, 5000); // disappears after 5 seconds
        }

        // Mobile menu toggle
        const mobileMenu = document.getElementById('mobile-menu');
        const navbar = document.getElementById('navbar');
        if(mobileMenu && navbar){
            mobileMenu.addEventListener('click', function() {
                navbar.classList.toggle('active');
            });
        }
    </script>
</body>
</html>

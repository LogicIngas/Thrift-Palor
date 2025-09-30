<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Thrift Parlor</title>
    <link rel="stylesheet" href="./css/login.css" type="text/css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/3.5.0/remixicon.css" crossorigin="">
    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" />
</head>
<body>
    <section id="header">
        <!-- <div id="mobile-menu">
            <i class="fas fa-bars"></i>
        </div> -->
        <div>
            <ul id="navbar">
                <li><a href="home.php">Home</a></li>
                <li><a href="Shop.php">Shop</a></li>
                <li><a href="AboutUs.html">About Us</a></li>
                <li><a href="Contact.html">Contact</a></li>
                <li><a href="Payment.html">Payment</a></li>
            </ul>
        </div>
    </section>

    <div class="login">
        <form action="login.php" method="POST" class="login_form">
            <h1 class="login_title">Login</h1>
            <?php
            session_start();
            if (!empty($_SESSION['error'])) {
                echo '<div class="error-message" style="color: #d9534f; margin-bottom: 15px; padding: 10px; background: rgba(217,83,79,0.1); border-radius: 4px; border: 1px solid #d9534f;">' . $_SESSION['error'] . '</div>';
                unset($_SESSION['error']);
            }
            if (!empty($_SESSION['success'])) {
                echo '<div class="success-message" style="color: #4CAF50; margin-bottom: 15px; padding: 10px; background: rgba(76,175,80,0.1); border-radius: 4px; border: 1px solid #4CAF50;">' . $_SESSION['success'] . '</div>';
                unset($_SESSION['success']);
            }
            ?>

            <div class="login_inputs">
                <div class="login_box">
                    <input type="email" name="email" placeholder="Email ID" required>
                    <i class="ri-mail-fill"></i>
                </div>

                <div class="login_box">
                    <input type="password" name="password" placeholder="Password" required>
                    <i class="ri-lock-2-fill"></i>
                </div>
            </div>

            <div class="login_check">
                <div class="login_check-box">
                    <input type="checkbox" class="login_check-input" id="user-check" name="remember">
                    <label for="user-check" class="login_check-label">Remember me</label>
                </div>
            </div>

            <button type="submit" class="login_button">Login</button>

            <p class="login_register">DON'T HAVE AN ACCOUNT? <a href="Signup.html">Sign Up</a></p>
        </form>
    </div>

    <script src="./js/Home.js"></script>
</body>
</html>
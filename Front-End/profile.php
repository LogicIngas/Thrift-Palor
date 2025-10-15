<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: htmlogin.php");
    exit();
}

$database = new Database();
$conn = $database->getConnection();
$user_id = $_SESSION['user_id'];

// Get current user info
$user_stmt = $conn->prepare("SELECT first_name, last_name, email, username, profile_picture FROM users WHERE id = ?");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$current_user = $user_result->fetch_assoc();

// Handle profile picture upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_picture'])) {
    $uploadDir = 'uploads/profile_pictures/';
    
    // Create directory if it doesn't exist
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $fileName = time() . '_' . basename($_FILES['profile_picture']['name']);
    $targetFilePath = $uploadDir . $fileName;
    $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);
    
    // Allow certain file formats
    $allowTypes = array('jpg', 'png', 'jpeg', 'gif');
    
    if (in_array(strtolower($fileType), $allowTypes)) {
        // Upload file to server
        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $targetFilePath)) {
            // Update database with new profile picture path
            $update_stmt = $conn->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
            $update_stmt->bind_param("si", $targetFilePath, $user_id);
            
            if ($update_stmt->execute()) {
                $_SESSION['success'] = "Profile picture updated successfully!";
                // Delete old profile picture if it exists and is not the default
                if (!empty($current_user['profile_picture']) && file_exists($current_user['profile_picture']) && 
                    !str_contains($current_user['profile_picture'], 'default')) {
                    unlink($current_user['profile_picture']);
                }
                $current_user['profile_picture'] = $targetFilePath;
            } else {
                $_SESSION['error'] = "Failed to update profile picture in database.";
            }
        } else {
            $_SESSION['error'] = "Sorry, there was an error uploading your file.";
        }
    } else {
        $_SESSION['error'] = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
    }
    
    header("Location: profile.php");
    exit();
}

// Handle account deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_account'])) {
    try {
        // Start transaction
        $conn->begin_transaction();
        
        // Delete user's data from related tables first
        $stmt1 = $conn->prepare("DELETE FROM addresses WHERE user_id = ?");
        $stmt1->bind_param("i", $user_id);
        $stmt1->execute();
        
        $stmt2 = $conn->prepare("DELETE FROM orders WHERE buyer_id = ?");
        $stmt2->bind_param("i", $user_id);
        $stmt2->execute();
        
        $stmt3 = $conn->prepare("DELETE FROM carts WHERE user_id = ?");
        $stmt3->bind_param("i", $user_id);
        $stmt3->execute();
        
        // Delete profile picture if exists
        if (!empty($current_user['profile_picture']) && file_exists($current_user['profile_picture']) && 
            !str_contains($current_user['profile_picture'], 'default')) {
            unlink($current_user['profile_picture']);
        }
        
        // Finally delete the user
        $stmt4 = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt4->bind_param("i", $user_id);
        $stmt4->execute();
        
        // Commit transaction
        $conn->commit();
        
        // Destroy session and redirect
        session_destroy();
        $_SESSION['success'] = "Your account has been deleted successfully.";
        header("Location: htmlogin.php");
        exit();
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        $_SESSION['error'] = "Error deleting account: " . $e->getMessage();
        header("Location: profile.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Thrift Palor</title>
    <link rel="stylesheet" href="./css/style.css" type="text/css">
    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" />
    <style>
        /* Profile Page Styles */
        .profile-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .profile-header {
            background: linear-gradient(135deg, #471c3c, #554348);
            color: white;
            padding: 40px;
            border-radius: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .user-info h1 {
            margin: 0 0 10px 0;
            font-size: 2rem;
            color: #efa00b;
        }

        .user-info p {
            margin: 0;
            opacity: 0.9;
            font-size: 1rem;
        }

        .profile-content {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 30px;
        }

        .profile-sidebar {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            text-align: center;
        }

        .profile-picture-container {
            position: relative;
            margin-bottom: 20px;
        }

        .profile-picture {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid #471c3c;
            margin: 0 auto 20px;
            display: block;
        }

        .profile-picture-upload {
            background: #471c3c;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 25px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: background-color 0.3s ease;
            display: inline-block;
        }

        .profile-picture-upload:hover {
            background: #5a2449;
        }

        .profile-main {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .profile-section {
            margin-bottom: 30px;
        }

        .profile-section h3 {
            color: #471c3c;
            margin-bottom: 20px;
            font-size: 1.5rem;
            border-bottom: 2px solid #efa00b;
            padding-bottom: 10px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .info-item {
            margin-bottom: 15px;
        }

        .info-label {
            font-weight: bold;
            color: #471c3c;
            display: block;
            margin-bottom: 5px;
        }

        .info-value {
            color: #555;
        }

        .danger-zone {
            border: 2px solid #dc3545;
            border-radius: 10px;
            padding: 20px;
            background: #f8f9fa;
        }

        .danger-zone h4 {
            color: #dc3545;
            margin-bottom: 15px;
        }

        .delete-account-btn {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.3s ease;
        }

        .delete-account-btn:hover {
            background-color: #c82333;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }

        .modal-content {
            background-color: white;
            margin: 15% auto;
            padding: 30px;
            border-radius: 10px;
            width: 400px;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
        }

        .modal-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 20px;
        }

        .confirm-delete-btn {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
        }

        .cancel-delete-btn {
            background-color: #6c757d;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
        }

        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            text-align: center;
            border: 1px solid #c3e6cb;
        }

        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            text-align: center;
            border: 1px solid #f5c6cb;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .profile-content {
                grid-template-columns: 1fr;
            }
            
            .profile-header {
                flex-direction: column;
                text-align: center;
                gap: 20px;
            }
            
            .info-grid {
                grid-template-columns: 1fr;
            }
            
            .modal-content {
                width: 90%;
                margin: 20% auto;
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
                <li><a href="Shop.php">Shop Now</a></li>
                <li><a href="AboutUs.html">About Us</a></li>
                <li><a href="Contact.html">Contact</a></li>
                <li><a href="profile.php" class="active">My Profile</a></li>
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

    <div class="profile-container">
        <!-- Display success/error messages -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="success-message"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="error-message"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <div class="profile-header">
            <div class="user-info">
                <h1>Welcome, <?php echo htmlspecialchars($current_user['first_name'] . ' ' . $current_user['last_name']); ?>!</h1>
                <p><?php echo htmlspecialchars($current_user['email']); ?> | @<?php echo htmlspecialchars($current_user['username']); ?></p>
            </div>
            <div class="profile-actions">
                <a href="orders.php" class="normal" style="background: #efa00b; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none;">
                    <i class="fas fa-clipboard-list"></i> View Orders
                </a>
            </div>
        </div>

        <div class="profile-content">
            <div class="profile-sidebar">
                <div class="profile-picture-container">
                    <?php if (!empty($current_user['profile_picture']) && file_exists($current_user['profile_picture'])): ?>
                        <img src="<?php echo htmlspecialchars($current_user['profile_picture']); ?>" alt="Profile Picture" class="profile-picture">
                    <?php else: ?>
                        <div class="profile-picture" style="background: linear-gradient(135deg, #471c3c, #efa00b); display: flex; align-items: center; justify-content: center; color: white; font-size: 3rem; font-weight: bold;">
                            <?php echo strtoupper(substr($current_user['first_name'], 0, 1) . substr($current_user['last_name'], 0, 1)); ?>
                        </div>
                    <?php endif; ?>
                    
                    <form action="profile.php" method="POST" enctype="multipart/form-data" id="profilePictureForm">
                        <input type="file" name="profile_picture" id="profile_picture" accept="image/*" style="display: none;" onchange="document.getElementById('profilePictureForm').submit()">
                        <button type="button" class="profile-picture-upload" onclick="document.getElementById('profile_picture').click()">
                            <i class="fas fa-camera"></i> Change Photo
                        </button>
                    </form>
                </div>
                
                <div class="user-stats">
                    <p><strong>Member Since:</strong> 2024</p>
                    <p><strong>Total Orders:</strong> 15</p>
                    <p><strong>Loyalty Points:</strong> 1,250</p>
                </div>
            </div>

            <div class="profile-main">
                <div class="profile-section">
                    <h3>Personal Information</h3>
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="info-label">First Name</span>
                            <span class="info-value"><?php echo htmlspecialchars($current_user['first_name']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Last Name</span>
                            <span class="info-value"><?php echo htmlspecialchars($current_user['last_name']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Email Address</span>
                            <span class="info-value"><?php echo htmlspecialchars($current_user['email']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Username</span>
                            <span class="info-value">@<?php echo htmlspecialchars($current_user['username']); ?></span>
                        </div>
                    </div>
                </div>

                <div class="profile-section">
                    <h3>Account Settings</h3>
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="info-label">Password</span>
                            <span class="info-value">••••••••</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Email Notifications</span>
                            <span class="info-value">Enabled</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Two-Factor Auth</span>
                            <span class="info-value">Disabled</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Privacy Settings</span>
                            <span class="info-value">Standard</span>
                        </div>
                    </div>
                </div>

                <div class="profile-section">
                    <div class="danger-zone">
                        <h4><i class="fas fa-exclamation-triangle"></i> Danger Zone</h4>
                        <p>Once you delete your account, there is no going back. Please be certain.</p>
                        <button class="delete-account-btn" onclick="openDeleteModal()">
                            <i class="fas fa-trash-alt"></i> Delete My Account
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Account Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <h3>Delete Your Account</h3>
            <p>Are you sure you want to delete your account? This action cannot be undone and will permanently remove all your data, including order history and saved addresses.</p>
            <div class="modal-buttons">
                <form method="POST" style="display: inline;">
                    <button type="submit" name="delete_account" class="confirm-delete-btn">
                        Yes, Delete My Account
                    </button>
                </form>
                <button class="cancel-delete-btn" onclick="closeDeleteModal()">Cancel</button>
            </div>
        </div>
    </div>

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

    <script>
        // Modal functions
        function openDeleteModal() {
            document.getElementById('deleteModal').style.display = 'block';
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('deleteModal');
            if (event.target === modal) {
                closeDeleteModal();
            }
        }

        // Mobile menu functionality
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
<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in and is a seller
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header("Location: htmlogin.php");
    exit();
}

$database = new Database();
$conn = $database->getConnection();
$seller_id = $_SESSION['user_id'];

// Get categories for dropdown
$categories_sql = "SELECT * FROM product_categories ORDER BY name";
$categories_result = $conn->query($categories_sql);
$categories = $categories_result->fetch_all(MYSQLI_ASSOC);

// Handle product submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $category_id = intval($_POST['category_id']);
    $stock_quantity = intval($_POST['stock_quantity']);
    
    // Handle image upload
    $image_url = '';
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/products/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_extension = pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION);
        $file_name = 'product_' . time() . '_' . uniqid() . '.' . $file_extension;
        $file_path = $upload_dir . $file_name;
        
        // Validate file type
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (in_array(strtolower($file_extension), $allowed_types)) {
            if (move_uploaded_file($_FILES['product_image']['tmp_name'], $file_path)) {
                $image_url = $file_path;
            } else {
                $_SESSION['error'] = "Error uploading image.";
            }
        } else {
            $_SESSION['error'] = "Invalid file type. Please upload JPG, PNG, or GIF images.";
        }
    }
    
    if (empty($_SESSION['error'])) {
        // Insert product
        $insert_sql = "INSERT INTO products (seller_id, name, description, price, image_url, category_id, stock_quantity, status, created_at) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', NOW())";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("issdsii", $seller_id, $name, $description, $price, $image_url, $category_id, $stock_quantity);
        
        if ($insert_stmt->execute()) {
            $_SESSION['success'] = "Product added successfully! It will be visible after approval.";
            header("Location: seller_dashboard.php");
            exit();
        } else {
            $_SESSION['error'] = "Error adding product: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product - Thrift Palor</title>
    <link rel="stylesheet" href="./css/style.css" type="text/css">
    <link rel="stylesheet" href="./css/enhanced-style.css" type="text/css">
    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" />
    <style>
        .form-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .product-form {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--text-dark);
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e2e8f0;
            border-radius: 5px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: var(--primary-color);
            outline: none;
        }
        
        .form-group textarea {
            height: 120px;
            resize: vertical;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        .image-preview {
            margin-top: 10px;
            text-align: center;
        }
        
        .image-preview img {
            max-width: 200px;
            max-height: 200px;
            border-radius: 5px;
            border: 2px dashed #ddd;
        }
        
        .submit-btn {
            background: var(--primary-color);
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            width: 100%;
            transition: var(--transition);
        }
        
        .submit-btn:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
        }
        
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
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
                <li><a href="seller_dashboard.php">Seller Dashboard</a></li>
                <li><a href="add_product.php" class="active">Add Product</a></li>
                <li><a href="Shop.php">Browse Shop</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
    </section>

    <div class="form-container">
        <div class="profile-section" style="margin-bottom: 30px;">
            <div class="user-info">
                <h3>Add New Product</h3>
                <p>List your item for sale on Thrift Palor</p>
            </div>
            <div class="user-avatar">
                <i class="fas fa-plus"></i>
            </div>
        </div>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="error-message"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <div class="product-form">
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="name">Product Name *</label>
                    <input type="text" id="name" name="name" required 
                           placeholder="Enter product name (e.g., Vintage Denim Jacket)">
                </div>
                
                <div class="form-group">
                    <label for="description">Description *</label>
                    <textarea id="description" name="description" required 
                              placeholder="Describe your product in detail..."></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="price">Price (R) *</label>
                        <input type="number" id="price" name="price" step="0.01" min="0" required 
                               placeholder="0.00">
                    </div>
                    
                    <div class="form-group">
                        <label for="stock_quantity">Stock Quantity *</label>
                        <input type="number" id="stock_quantity" name="stock_quantity" min="1" required 
                               value="1">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="category_id">Category *</label>
                    <select id="category_id" name="category_id" required>
                        <option value="">Select a category</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['category_id']; ?>">
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="product_image">Product Image *</label>
                    <input type="file" id="product_image" name="product_image" accept="image/*" required>
                    <div class="image-preview" id="imagePreview"></div>
                </div>
                
                <button type="submit" name="add_product" class="submit-btn">
                    <i class="fas fa-upload"></i> Add Product
                </button>
            </form>
        </div>
    </div>

    <script>
        // Image preview functionality
        document.getElementById('product_image').addEventListener('change', function(e) {
            const preview = document.getElementById('imagePreview');
            const file = e.target.files[0];
            
            if (file) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    preview.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
                }
                
                reader.readAsDataURL(file);
            } else {
                preview.innerHTML = '';
            }
        });

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
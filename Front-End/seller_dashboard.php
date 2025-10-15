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

// Handle product deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_product'])) {
    $product_id = intval($_POST['product_id']);
    
    // Verify product belongs to this seller
    $verify_sql = "SELECT product_id FROM products WHERE product_id = ? AND seller_id = ?";
    $verify_stmt = $conn->prepare($verify_sql);
    $verify_stmt->bind_param("ii", $product_id, $seller_id);
    $verify_stmt->execute();
    
    if ($verify_stmt->get_result()->num_rows > 0) {
        $delete_sql = "DELETE FROM products WHERE product_id = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("i", $product_id);
        
        if ($delete_stmt->execute()) {
            $_SESSION['success'] = "Product deleted successfully!";
        } else {
            $_SESSION['error'] = "Error deleting product.";
        }
    } else {
        $_SESSION['error'] = "Product not found or access denied.";
    }
    
    header("Location: seller_dashboard.php");
    exit();
}

// Get seller's products
$products_sql = "SELECT p.*, pc.name as category_name 
                 FROM products p 
                 LEFT JOIN product_categories pc ON p.category_id = pc.category_id 
                 WHERE p.seller_id = ? 
                 ORDER BY p.created_at DESC";
$products_stmt = $conn->prepare($products_sql);
$products_stmt->bind_param("i", $seller_id);
$products_stmt->execute();
$products_result = $products_stmt->get_result();
$products = $products_result->fetch_all(MYSQLI_ASSOC);

// Get sales statistics
$stats_sql = "SELECT 
                COUNT(*) as total_products,
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_products,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_products
              FROM products 
              WHERE seller_id = ?";
$stats_stmt = $conn->prepare($stats_sql);
$stats_stmt->bind_param("i", $seller_id);
$stats_stmt->execute();
$stats_result = $stats_stmt->get_result();
$stats = $stats_result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seller Dashboard - Thrift Palor</title>
    <link rel="stylesheet" href="./css/style.css" type="text/css">
    <link rel="stylesheet" href="./css/enhanced-style.css" type="text/css">
    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" />
    <style>
        .seller-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .seller-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            text-align: center;
            border-left: 4px solid var(--primary-color);
        }
        
        .stat-card h3 {
            margin: 0 0 10px 0;
            font-size: 14px;
            color: var(--text-light);
            text-transform: uppercase;
        }
        
        .stat-card .number {
            font-size: 2.5em;
            font-weight: bold;
            color: var(--primary-color);
            margin: 0;
        }
        
        .seller-actions {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .btn-primary {
            background: var(--primary-color);
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            transition: var(--transition);
        }
        
        .btn-primary:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
        }
        
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .product-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: var(--transition);
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0,0,0,0.2);
        }
        
        .product-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        
        .product-info {
            padding: 15px;
        }
        
        .product-status {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 8px;
        }
        
        .status-active { background: #d4edda; color: #155724; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-rejected { background: #f8d7da; color: #721c24; }
        
        .product-actions {
            display: flex;
            gap: 8px;
            margin-top: 15px;
        }
        
        .btn-small {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        
        .btn-edit {
            background: #17a2b8;
            color: white;
        }
        
        .btn-delete {
            background: #dc3545;
            color: white;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .empty-state i {
            font-size: 64px;
            color: #ccc;
            margin-bottom: 20px;
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
                <li><a href="seller_dashboard.php" class="active">Seller Dashboard</a></li>
                <li><a href="add_product.php">Add Product</a></li>
                <li><a href="Shop.php">Browse Shop</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
    </section>

    <div class="seller-container">
        <div class="profile-section" style="margin-bottom: 30px;">
            <div class="user-info">
                <h3>Seller Dashboard</h3>
                <p>Manage your products and track your sales</p>
            </div>
            <div class="user-avatar">
                <i class="fas fa-store"></i>
            </div>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="success-message"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="error-message"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <div class="seller-stats">
            <div class="stat-card">
                <h3>Total Products</h3>
                <p class="number"><?php echo $stats['total_products'] ?? 0; ?></p>
            </div>
            <div class="stat-card">
                <h3>Active Products</h3>
                <p class="number"><?php echo $stats['active_products'] ?? 0; ?></p>
            </div>
            <div class="stat-card">
                <h3>Pending Approval</h3>
                <p class="number"><?php echo $stats['pending_products'] ?? 0; ?></p>
            </div>
        </div>

        <div class="seller-actions">
            <a href="add_product.php" class="btn-primary">
                <i class="fas fa-plus"></i> Add New Product
            </a>
            <a href="Shop.php" class="btn-primary">
                <i class="fas fa-shopping-bag"></i> Browse Shop
            </a>
        </div>

        <h2>Your Products</h2>
        
        <?php if (empty($products)): ?>
            <div class="empty-state">
                <i class="fas fa-box-open"></i>
                <h3>No Products Yet</h3>
                <p>Start by adding your first product to the marketplace</p>
                <a href="add_product.php" class="btn-primary" style="margin-top: 15px;">
                    <i class="fas fa-plus"></i> Add Your First Product
                </a>
            </div>
        <?php else: ?>
            <div class="products-grid">
                <?php foreach ($products as $product): ?>
                    <div class="product-card">
                        <img src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                             alt="<?php echo htmlspecialchars($product['name']); ?>" 
                             class="product-image"
                             onerror="this.src='https://via.placeholder.com/300x200?text=No+Image'">
                        <div class="product-info">
                            <span class="product-status status-<?php echo $product['status']; ?>">
                                <?php echo ucfirst($product['status']); ?>
                            </span>
                            <h4><?php echo htmlspecialchars($product['name']); ?></h4>
                            <p style="color: var(--text-light); margin: 5px 0; font-size: 14px;">
                                <?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?>
                            </p>
                            <p style="font-weight: bold; color: var(--primary-color); margin: 10px 0;">
                                R<?php echo number_format($product['price'], 2); ?>
                            </p>
                            <p style="font-size: 14px; color: var(--text-light);">
                                Stock: <?php echo $product['stock_quantity']; ?>
                            </p>
                            <div class="product-actions">
                                <a href="add_product.php?id=<?php echo $product['product_id']; ?>" class="btn-small btn-edit">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                                    <button type="submit" name="delete_product" class="btn-small btn-delete" 
                                            onclick="return confirm('Are you sure you want to delete this product?')">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
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
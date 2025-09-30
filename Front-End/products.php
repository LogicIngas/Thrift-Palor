<?php
require_once 'config/database.php';

class Product {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        // Assuming $database->getConnection() returns a mysqli object
        $this->conn = $database->getConnection();
    }
    
    public function getAllProducts() {
        $query = "SELECT * FROM products ORDER BY created_at DESC";
        $result = $this->conn->query($query);
        
        $products = [];
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
        
        return $products;
    }
    
    public function getProductById($product_id) {
        $stmt = $this->conn->prepare("SELECT * FROM products WHERE product_id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Fetches a specified number of random products, excluding the current one.
     * Used for the "Similar Items" or "You Might Also Like" section.
     */
    public function getSimilarProducts($current_product_id, $limit = 4) {
        
        // Sanitize the limit for safety, though it's internally controlled
        $safe_limit = (int)$limit;
        
        // Query to select products randomly, excluding the current product ID
        $query = "
            SELECT * FROM products 
            WHERE product_id != ? 
            ORDER BY RAND() 
            LIMIT $safe_limit
        ";
        
        $stmt = $this->conn->prepare($query);
        // Bind the current product ID parameter
        $stmt->bind_param("i", $current_product_id); 
        
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            
            $products = [];
            while ($row = $result->fetch_assoc()) {
                $products[] = $row;
            }
            
            return $products;
        } else {
            // Return an empty array on failure
            return [];
        }
    }
}

// Initialize products - RUN THIS ONLY ONCE
function initializeProducts() {
    $database = new Database();
    $conn = $database->getConnection();
    
    // First, check if products already exist to avoid duplicates
    $check = $conn->query("SELECT COUNT(*) as count FROM products");
    $count = $check->fetch_assoc()['count'];
    
    if ($count > 0) {
        echo "Products already exist in database. Skipping initialization.";
        return;
    }
    
    $products = [
        ['Cartoon Astronut T-Shirts', 'Summer collection cartoon t-shirt', 110.00, 'images/shirt.jpeg', 'Summer'],
        ['Swimming Short', 'Comfortable swimming shorts', 250.00, 'images/Summer_Short.jpg', 'Short'],
        ['Hoodie', 'Warm and comfortable hoodie', 220.00, 'images/BnW Hoodie.jpeg', 'Warm'],
        ['Polo T-Shirts', 'Classic polo shirt', 250.00, 'images/PoloShirt.jpeg', 'Polo'],
        ['Women\'s Sandals', 'Adidas women sandals', 180.00, 'images/WomanChypreSandals.jpeg', 'Adidas'],
        ['Cardigan', 'Warm grey cardigan', 300.00, 'images/grey_cardigan.jpeg', 'Warm'],
        ['Brown Jacket', 'Brown silk jersey jacket', 299.00, 'images/BrownSilk_Jersey.jpg', 'Jet'],
        ['Red vest', 'Small size code vest', 80.00, 'images/SmallSizeCodeVest.jpg', 'Code'],
        ['Orange Binnie', 'Relay Jeans orange binnie', 110.00, 'images/OrangeBinnie.jpg', 'Relay Jeans'],
        ['Baggy Fit Jeans', 'Zara baggy fit jeans', 170.00, 'images/Zara Baggy Fit Jeans.jpeg', 'Zara'],
        ['Shirt + Cap', 'Panda Wear shirt and cap set', 210.00, 'images/ShitAndCap.jpg', 'Panda Wear'],
        ['Bucket Hat', 'SPCC bucket hat', 300.00, 'images/SPCCHat.jpg', 'SPCC'],
        ['Barklay Canvas', 'Nunn Bush Men\'s Barklay Canvas shoes', 450.00, 'images/Brand_ Nunn Bush Nunn Bush Men\'s Barklay Canvas Plain Toe Oxford Lace Up.jpeg', 'Lace Up'],
        ['Running Shoes', 'Adidas running shoes', 650.00, 'images/n6.jpg', 'Adidas'],
        ['Sports Jacket', 'Nike sports jacket', 420.00, 'images/n7.jpg', 'Nike'],
        ['Track Pants', 'Puma track pants', 280.00, 'images/n8.jpg', 'Puma']
    ];
    
    $inserted = 0;
    foreach ($products as $product) {
        $stmt = $conn->prepare("INSERT INTO products (name, description, price, image_url, category, stock_quantity) VALUES (?, ?, ?, ?, ?, ?)");
        $stock = rand(5, 50); // Random stock between 5-50
        $stmt->bind_param("ssdssi", $product[0], $product[1], $product[2], $product[3], $product[4], $stock);
        
        if ($stmt->execute()) {
            $inserted++;
        }
    }
    
    echo "Successfully inserted $inserted products into the database.";
}

// UNCOMMENT THE LINE BELOW TO INITIALIZE PRODUCTS (RUN ONLY ONCE)
// initializeProducts();

// After running once, COMMENT OUT the line above to prevent re-insertion
?>
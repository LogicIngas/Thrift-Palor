<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: htmlogin.php");
    exit();
}

// Instantiate the database connection
$database = new Database();
$conn = $database->getConnection();
$buyer_id = $_SESSION['user_id'];

$sa_provinces = [
    'Eastern Cape',
    'Free State',
    'Gauteng',
    'KwaZulu-Natal',
    'Limpopo',
    'Mpumalanga',
    'North West',
    'Northern Cape',
    'Western Cape'
];

// --- FIX 1: Initialize $total at the beginning
$total = 0;
// Check if the cart is not empty before calculating the total
if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $total += $item['price'] * $item['quantity'];
    }
}

// Handle AJAX requests for address management
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');

    $action = $_POST['action'];
    $response = ['success' => false, 'message' => ''];

    try {
        switch ($action) {
            case 'add_address':
                $street_address = $_POST['street_address'];
                $city = $_POST['city'];
                $province = $_POST['province'];
                $postal_code = $_POST['postal_code'];
                $address_type = $_POST['address_type'];
                
                $sql = "INSERT INTO addresses (user_id, street_address, city, province, postal_code, address_type) VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("isssss", $buyer_id, $street_address, $city, $province, $postal_code, $address_type);
                $stmt->execute();
                
                $response['success'] = true;
                $response['message'] = "Address added successfully.";
                $response['address_id'] = $stmt->insert_id;
                $response['address_html'] = '<p>' . htmlspecialchars($street_address) . ', ' . htmlspecialchars($city) . ', ' . htmlspecialchars($province) . ', ' . htmlspecialchars($postal_code) . '</p>';
                break;

            case 'update_address':
                $address_id = $_POST['address_id'];
                $street_address = $_POST['street_address'];
                $city = $_POST['city'];
                $province = $_POST['province'];
                $postal_code = $_POST['postal_code'];
                $address_type = $_POST['address_type'];

                $sql = "UPDATE addresses SET street_address = ?, city = ?, province = ?, postal_code = ?, address_type = ? WHERE id = ? AND user_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssssii", $street_address, $city, $province, $postal_code, $address_type, $address_id, $buyer_id);
                $stmt->execute();

                if ($stmt->affected_rows > 0) {
                    $response['success'] = true;
                    $response['message'] = "Address updated successfully.";
                    $response['address_html'] = '<p>' . htmlspecialchars($street_address) . ', ' . htmlspecialchars($city) . ', ' . htmlspecialchars($province) . ', ' . htmlspecialchars($postal_code) . '</p>';
                } else {
                    $response['message'] = "No changes made or address not found.";
                }
                break;

            case 'delete_address':
                $address_id = $_POST['address_id'];
                $sql = "DELETE FROM addresses WHERE id = ? AND user_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ii", $address_id, $buyer_id);
                $stmt->execute();

                if ($stmt->affected_rows > 0) {
                    $response['success'] = true;
                    $response['message'] = "Address deleted successfully.";
                } else {
                    $response['message'] = "Address not found or already deleted.";
                }
                break;
        }
    } catch (Exception $e) {
        $response['message'] = "Error: " . $e->getMessage();
    }
    
    echo json_encode($response);
    exit;
}

// Fetch user's saved addresses for initial page load
$addresses = [];
$address_sql = "SELECT id, street_address, city, province, postal_code, address_type FROM addresses WHERE user_id = ?";
$stmt = $conn->prepare($address_sql);
$stmt->bind_param("i", $buyer_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $addresses[] = $row;
}
$has_address = !empty($addresses);

// Handle placing an order
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    if (empty($_SESSION['cart'])) {
        header("Location: cart.php");
        exit();
    }

    $address_id = $_POST['address_id'];

    if (!$address_id) {
        $_SESSION['error'] = "Please select a shipping address.";
        header("Location: checkout.php");
        exit();
    }
    
    // --- FIX 2: Re-calculate total inside the order submission block
    $total = 0;
    foreach ($_SESSION['cart'] as $item) {
        $total += $item['price'] * $item['quantity'];
    }

    $shipping_cost = 50.00;
    $status = 'Pending';
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Create order
        $tracking_number = 'TRK' . time() . rand(100, 999); // Generate a unique mock tracking number
        $shipping_date = date('Y-m-d', strtotime('+3 days')); // Set a mock shipping date 3 days from now

        $order_sql = "INSERT INTO orders (buyer_id, total_amount, status, shipping_cost, order_date, address_id, tracking_number, shipping_date)
                     VALUES (?, ?, ?, ?, NOW(), ?, ?, ?)";
        $order_stmt = $conn->prepare($order_sql);
        $order_stmt->bind_param("idssiss", $buyer_id, $total, $status, $shipping_cost, $address_id, $tracking_number, $shipping_date);

        if (!$order_stmt->execute()) {
            throw new Exception("Error creating order: " . $order_stmt->error);
        }
        $order_id = $order_stmt->insert_id;
        
        // Add order items
        $item_sql = "INSERT INTO order_items (order_id, product_name, quantity, price) VALUES (?, ?, ?, ?)";
        $item_stmt = $conn->prepare($item_sql);
        
        foreach ($_SESSION['cart'] as $item) {
            $product_name = $item['name'];
            $quantity = $item['quantity'];
            $price = $item['price'];
            $item_stmt->bind_param("isid", $order_id, $product_name, $quantity, $price);
            
            if (!$item_stmt->execute()) {
                throw new Exception("Error adding order item: " . $item_stmt->error);
            }
        }
        
        // INSERT INTO SHIPPING TABLE - NEW CODE
        $shipping_method = 'standard'; // Default shipping method
        $shipping_status = 'processing'; // Initial shipping status
        
        $shipping_sql = "INSERT INTO shipping (order_id, address_id, tracking_number, shipping_method, shipping_cost, status, shipping_date) 
                        VALUES (?, ?, ?, ?, ?, ?, ?)";
        $shipping_stmt = $conn->prepare($shipping_sql);
        $shipping_stmt->bind_param("iissdss", $order_id, $address_id, $tracking_number, $shipping_method, $shipping_cost, $shipping_status, $shipping_date);
        
        if (!$shipping_stmt->execute()) {
            throw new Exception("Error creating shipping record: " . $shipping_stmt->error);
        }
        
        // Commit transaction
        $conn->commit();

        // Pass order ID to the payment processing page
        $_SESSION['order_id'] = $order_id;
        // Redirect to process payment
        header("Location: payment.php");
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = "Order processing failed: " . $e->getMessage();
        header("Location: checkout.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Thrift Palor</title>
    <link rel="stylesheet" href="./css/style.css" type="text/css">
    <link rel="stylesheet" href="./css/checkout.css" type="text/css">
    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" />
    <style>
        .address-container {
            border: 1px solid #ccc;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            background-color: #f9f9f9;
        }
        .address-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        .address-item:last-child {
            border-bottom: none;
        }
        .address-details p {
            margin: 0;
            font-size: 16px;
        }
        .address-buttons button {
            margin-left: 10px;
            cursor: pointer;
            border: none;
            padding: 8px 12px;
            border-radius: 5px;
            font-size: 14px;
        }
        .address-buttons .update-btn {
            background-color: #007bff;
            color: white;
        }
        .address-buttons .delete-btn {
            background-color: #dc3545;
            color: white;
        }
        .address-form-section {
            display: none;
        }
        .address-form-section h3 {
            margin-top: 0;
        }
        .address-form-section .form-group {
            margin-bottom: 15px;
        }
        .address-form-section .form-row {
            display: flex;
            gap: 15px;
        }
        .address-form-section .form-row .form-group {
            flex: 1;
        }
        #add-address-btn {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            margin-bottom: 15px;
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
                <li><a href="Shop.php">Shop</a></li>
                <li><a href="AboutUs.html">About Us</a></li>
                <li><a href="Contact.html">Contact</a></li>
                <li><a href="orders.php">My Orders</a></li>
                <li><a href="cart.php"><i class="fas fa-shopping-cart"></i> Cart</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
    </section>

    <div class="checkout-container">
        <h2>Checkout</h2>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="error-message"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <div class="shipping-info">
            <h3>Shipping Information</h3>
            
            <div id="address-view-section" style="display: <?php echo $has_address ? 'block' : 'none'; ?>;">
                <h4>Your Saved Address</h4>
                <div id="saved-address-container" class="address-container">
                    <?php if ($has_address): ?>
                        <div class="address-item" data-address-id="<?php echo htmlspecialchars($addresses[0]['id']); ?>">
                            <div class="address-details">
                                <p id="current-street_address"><?php echo htmlspecialchars($addresses[0]['street_address']); ?></p>
                                <p id="current-city"><?php echo htmlspecialchars($addresses[0]['city']); ?></p>
                                <p id="current-province"><?php echo htmlspecialchars($addresses[0]['province']); ?></p>
                                <p id="current-postal_code"><?php echo htmlspecialchars($addresses[0]['postal_code']); ?></p>
                                <p id="current-address_type">Type: <?php echo ucfirst(htmlspecialchars($addresses[0]['address_type'])); ?></p>
                            </div>
                            <div class="address-buttons">
                                <button class="update-btn">Update Address</button>
                                <button class="delete-btn">Delete Address</button>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <button id="add-address-btn" style="display: <?php echo $has_address ? 'none' : 'block'; ?>">Add Address</button>

            <div id="address-form-section" class="address-form-section">
                <h3><span id="form-title">Add</span> Address</h3>
                <form id="address-form">
                    <input type="hidden" id="address_id" name="address_id">
                    <div class="form-group">
                        <label for="street_address">Street Address</label>
                        <input type="text" id="street_address" name="street_address" required>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="city">City</label>
                            <input type="text" id="city" name="city" required>
                        </div>
                        <div class="form-group">
                            <label for="province">Province</label>
                             <select id="province" name="province" required>
                <option value="">-- Select Province --</option>
                <?php
                // Loop through the provinces array defined in PHP
                foreach ($sa_provinces as $province_name) {
                    echo '<option value="' . htmlspecialchars($province_name) . '">' . htmlspecialchars($province_name) . '</option>';
                }
                ?>
            </select>
                        </div>
                        <div class="form-group">
                            <label for="postal_code">Postal Code</label>
                            <input type="text" id="postal_code" name="postal_code" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="address_type">Address Type</label>
                        <select id="address_type" name="address_type" required>
                            <option value="home">Home Address</option>
                            <option value="work">Work Address</option>
                        </select>
                    </div>

                    <button type="submit" id="save-address-btn">Save Address</button>
                    <button type="button" id="cancel-address-btn" style="display: none;">Cancel</button>
                </form>
            </div>
        </div>

        <form action="checkout.php" method="POST">
            <div class="order-summary">
                <h3>Order Summary</h3>
                <?php if (!empty($_SESSION['cart'])): ?>
                    <?php foreach ($_SESSION['cart'] as $item): ?>
                    <div class="order-item">
                        <span><?php echo htmlspecialchars($item['name']); ?> x<?php echo $item['quantity']; ?></span>
                        <span>R<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                
                <div class="order-total">
                    <span>Total: R<?php echo number_format($total + 50, 2); ?></span>
                </div>
            </div>
            <input type="hidden" id="selected_address_id" name="address_id" value="<?php echo $has_address ? htmlspecialchars($addresses[0]['id']) : ''; ?>">
            <button type="submit" name="place_order" class="place-order-btn">Place Order</button>
        </form>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const addAddressBtn = document.getElementById('add-address-btn');
        const addressViewSection = document.getElementById('address-view-section');
        const addressFormSection = document.getElementById('address-form-section');
        const addressForm = document.getElementById('address-form');
        const saveAddressBtn = document.getElementById('save-address-btn');
        const cancelAddressBtn = document.getElementById('cancel-address-btn');
        const savedAddressContainer = document.getElementById('saved-address-container');
        const selectedAddressInput = document.getElementById('selected_address_id');
        const formTitle = document.getElementById('form-title');

        // Initial state
        if (savedAddressContainer.querySelector('.address-item')) {
            addAddressBtn.style.display = 'none';
            addressViewSection.style.display = 'block';
            addressFormSection.style.display = 'none';
        } else {
            addAddressBtn.style.display = 'block';
            addressViewSection.style.display = 'none';
            addressFormSection.style.display = 'none';
        }
        
        // Show the 'Add Address' form
        addAddressBtn.addEventListener('click', () => {
            addressViewSection.style.display = 'none';
            addressFormSection.style.display = 'block';
            addAddressBtn.style.display = 'none';
            addressForm.reset();
            document.getElementById('address_id').value = '';
            formTitle.textContent = 'Add';
            saveAddressBtn.textContent = 'Save Address';
            cancelAddressBtn.style.display = 'none';
        });

        // Handle form submission for Add/Update
        addressForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const addressId = document.getElementById('address_id').value;
            const action = addressId ? 'update_address' : 'add_address';

            const formData = new FormData(addressForm);
            formData.append('action', action);

            try {
                const response = await fetch('checkout.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    alert(result.message);
                    if (action === 'add_address') {
                        // Create and add the new address to the view
                        const newAddressItem = document.createElement('div');
                        newAddressItem.className = 'address-item';
                        newAddressItem.dataset.addressId = result.address_id;
                        newAddressItem.innerHTML = `
                            <div class="address-details">
                                <p id="current-street_address">${formData.get('street_address')}</p>
                                <p id="current-city">${formData.get('city')}</p>
                                <p id="current-province">${formData.get('province')}</p>
                                <p id="current-postal_code">${formData.get('postal_code')}</p>
                                <p id="current-address_type">Type: ${formData.get('address_type').charAt(0).toUpperCase() + formData.get('address_type').slice(1)}</p>
                            </div>
                            <div class="address-buttons">
                                <button class="update-btn">Update Address</button>
                                <button class="delete-btn">Delete Address</button>
                            </div>
                        `;
                        savedAddressContainer.innerHTML = '';
                        savedAddressContainer.appendChild(newAddressItem);
                        selectedAddressInput.value = result.address_id;
                    } else if (action === 'update_address') {
                        // Update existing address details in the view
                        document.getElementById('current-street_address').textContent = formData.get('street_address');
                        document.getElementById('current-city').textContent = formData.get('city');
                        document.getElementById('current-province').textContent = formData.get('province');
                        document.getElementById('current-postal_code').textContent = formData.get('postal_code');
                        document.getElementById('current-address_type').textContent = `Type: ${formData.get('address_type').charAt(0).toUpperCase() + formData.get('address_type').slice(1)}`;
                    }

                    // Switch back to view mode
                    addressFormSection.style.display = 'none';
                    addressViewSection.style.display = 'block';
                    addAddressBtn.style.display = 'none';
                } else {
                    alert(result.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            }
        });

        // Handle Update and Delete buttons via event delegation
        savedAddressContainer.addEventListener('click', async (e) => {
            const addressItem = e.target.closest('.address-item');
            if (!addressItem) return;

            const addressId = addressItem.dataset.addressId;

            if (e.target.classList.contains('update-btn')) {
                // Populate the form for updating
                document.getElementById('address_id').value = addressId;
                document.getElementById('street_address').value = document.getElementById('current-street_address').textContent;
                document.getElementById('city').value = document.getElementById('current-city').textContent;
                document.getElementById('province').value = document.getElementById('current-province').textContent;
                document.getElementById('postal_code').value = document.getElementById('current-postal_code').textContent;
                const addressType = document.getElementById('current-address_type').textContent.split(': ')[1].toLowerCase();
                document.getElementById('address_type').value = addressType;

                formTitle.textContent = 'Update';
                saveAddressBtn.textContent = 'Update Address';
                cancelAddressBtn.style.display = 'inline-block';

                // Switch to form view
                addressViewSection.style.display = 'none';
                addressFormSection.style.display = 'block';
            }

            if (e.target.classList.contains('delete-btn')) {
                if (confirm('Are you sure you want to delete this address?')) {
                    try {
                        const formData = new FormData();
                        formData.append('action', 'delete_address');
                        formData.append('address_id', addressId);

                        const response = await fetch('checkout.php', {
                            method: 'POST',
                            body: formData
                        });

                        const result = await response.json();

                        if (result.success) {
                            alert(result.message);
                            addressItem.remove(); // Remove the element from the DOM
                            selectedAddressInput.value = '';
                            addAddressBtn.style.display = 'block';
                            addressViewSection.style.display = 'none';
                        } else {
                            alert(result.message);
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        alert('An error occurred. Please try again.');
                    }
                }
            }
        });

        // Cancel button for Update form
        cancelAddressBtn.addEventListener('click', () => {
            addressFormSection.style.display = 'none';
            addressViewSection.style.display = 'block';
            addressForm.reset();
        });

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
</html>
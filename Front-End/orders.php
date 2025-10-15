<?php
session_start();
require_once 'config/database.php';
require_once 'refund_functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: htmlogin.php");
    exit();
}

$database = new Database();
$conn = $database->getConnection();
$refundHandler = new RefundHandler();

$buyer_id = $_SESSION['user_id'];

// Handle return request submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['return_order'])) {
    $order_id = intval($_POST['order_id']);
    $reason = $_POST['reason'];
    $additional_notes = trim($_POST['additional_notes']);
    
    // Debug: Log the submitted data
    error_log("Return request submitted - Order ID: $order_id, Reason: $reason, User ID: $buyer_id");
    
    // Verify that the order belongs to the current user and is eligible for return
    $verify_sql = "SELECT status FROM orders WHERE order_id = ? AND buyer_id = ? AND status IN ('Delivered', 'Shipped')";
    $verify_stmt = $conn->prepare($verify_sql);
    $verify_stmt->bind_param("ii", $order_id, $buyer_id);
    $verify_stmt->execute();
    $verify_result = $verify_stmt->get_result();
    
    if ($verify_result->num_rows > 0) {
        // Check if return request already exists
        $check_return_sql = "SELECT return_id FROM returns WHERE order_id = ?";
        $check_return_stmt = $conn->prepare($check_return_sql);
        $check_return_stmt->bind_param("i", $order_id);
        $check_return_stmt->execute();
        $check_return_result = $check_return_stmt->get_result();
        
        if ($check_return_result->num_rows == 0) {
            // Start transaction to ensure both updates happen together
            $conn->begin_transaction();
            
            try {
                // Insert return request
                $insert_sql = "INSERT INTO returns (order_id, status, reason, additional_notes) VALUES (?, 'Pending', ?, ?)";
                $insert_stmt = $conn->prepare($insert_sql);
                $insert_stmt->bind_param("iss", $order_id, $reason, $additional_notes);
                
                if (!$insert_stmt->execute()) {
                    throw new Exception("Failed to insert return request: " . $conn->error);
                }
                
                // Update order status to indicate return is requested
                $update_order_sql = "UPDATE orders SET status = 'Return Requested' WHERE order_id = ?";
                $update_order_stmt = $conn->prepare($update_order_sql);
                $update_order_stmt->bind_param("i", $order_id);
                
                if (!$update_order_stmt->execute()) {
                    throw new Exception("Failed to update order status: " . $conn->error);
                }
                
                // Commit transaction
                $conn->commit();
                $success_message = "Return request submitted successfully. We will process your request within 3-5 business days.";
                
            } catch (Exception $e) {
                // Rollback transaction on error
                $conn->rollback();
                $error_message = "Failed to submit return request. Please try again. Error: " . $e->getMessage();
            }
        } else {
            $error_message = "A return request for this order already exists.";
        }
    } else {
        // ENHANCED ERROR MESSAGE: Show what we found for debugging
        $debug_sql = "SELECT order_id, status, buyer_id FROM orders WHERE order_id = ?";
        $debug_stmt = $conn->prepare($debug_sql);
        $debug_stmt->bind_param("i", $order_id);
        $debug_stmt->execute();
        $debug_result = $debug_stmt->get_result();
        
        if ($debug_result->num_rows > 0) {
            $debug_order = $debug_result->fetch_assoc();
            if ($debug_order['buyer_id'] != $buyer_id) {
                $error_message = "This order does not belong to your account.";
            } else {
                $error_message = "Order is not eligible for return. Current status: " . $debug_order['status'] . ". Only 'Delivered' or 'Shipped' orders can be returned.";
            }
        } else {
            $error_message = "Order not found.";
        }
    }
}

// FIXED QUERY: Get basic order information first (no JOINs that cause duplicates)
$sql = "SELECT o.order_id, o.order_date, o.total_amount, o.status, o.shipping_cost,
               COUNT(oi.item_id) as item_count
        FROM orders o
        LEFT JOIN order_items oi ON o.order_id = oi.order_id
        WHERE o.buyer_id = ?
        GROUP BY o.order_id, o.order_date, o.total_amount, o.status, o.shipping_cost
        ORDER BY o.order_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $buyer_id);
$stmt->execute();
$result = $stmt->get_result();

$orders = [];
while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
}

// Now get additional information for each order separately to avoid duplicates
foreach ($orders as &$order) {
    $order_id = $order['order_id'];
    
    // Get shipping information
    $shipping_sql = "SELECT status as shipping_status, tracking_number FROM Shipping WHERE order_id = ? LIMIT 1";
    $shipping_stmt = $conn->prepare($shipping_sql);
    $shipping_stmt->bind_param("i", $order_id);
    $shipping_stmt->execute();
    $shipping_result = $shipping_stmt->get_result();
    
    if ($shipping_row = $shipping_result->fetch_assoc()) {
        $order['shipping_status'] = $shipping_row['shipping_status'];
        $order['tracking_number'] = $shipping_row['tracking_number'];
    } else {
        $order['shipping_status'] = null;
        $order['tracking_number'] = null;
    }
    
    // Get return information
    $return_sql = "SELECT return_id, status as return_status, request_date as return_date, reason as return_reason 
                   FROM returns WHERE order_id = ? LIMIT 1";
    $return_stmt = $conn->prepare($return_sql);
    $return_stmt->bind_param("i", $order_id);
    $return_stmt->execute();
    $return_result = $return_stmt->get_result();
    
    if ($return_row = $return_result->fetch_assoc()) {
        $order['return_id'] = $return_row['return_id'];
        $order['return_status'] = $return_row['return_status'];
        $order['return_date'] = $return_row['return_date'];
        $order['return_reason'] = $return_row['return_reason'];
    } else {
        $order['return_id'] = null;
        $order['return_status'] = null;
        $order['return_date'] = null;
        $order['return_reason'] = null;
    }
    
    // Get order items
    $items_sql = "SELECT product_name, quantity, price FROM order_items WHERE order_id = ?";
    $items_stmt = $conn->prepare($items_sql);
    $items_stmt->bind_param("i", $order_id);
    $items_stmt->execute();
    $items_result = $items_stmt->get_result();
    
    $order['items'] = [];
    while ($item = $items_result->fetch_assoc()) {
        $order['items'][] = $item;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Thrift Palor</title>
    <link rel="stylesheet" href="./css/style.css" type="text/css">
    <link rel="stylesheet" href="./css/enhanced-style.css" type="text/css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- <link rel="stylesheet" href="./css/order.css" type="text/css">
    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" /> -->
   
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
                <li><a href="contact.php">Contact</a></li>
                <li><a href="orders.php" class="active">My Orders</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
    </section>

    <section id="page-header" class="about-header" style="background: var(--primary-color); color: white;">
        <h2>My Orders</h2>
        <p>View your order history and track your purchases</p>
    </section>

    <div class="orders-container section-p1">
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($orders)): ?>
            <?php foreach ($orders as $order): ?>
                <div class="order-card">
                    <div class="order-header">
                        <div>
                            <div class="order-id">Order #<?php echo $order['order_id']; ?></div>
                            <div class="order-date"><?php echo date('F j, Y', strtotime($order['order_date'])); ?></div>
                        </div>
                        <div class="order-status status-<?php echo strtolower(str_replace(' ', '-', $order['status'])); ?>">
                            <?php echo $order['status']; ?>
                        </div>
                    </div>
                    
                    <div class="order-details">
                        <div><strong>Items:</strong> <?php echo $order['item_count']; ?></div>
                        <div><strong>Order Total:</strong> R<?php echo number_format($order['total_amount'], 2); ?></div>
                        <div><strong>Shipping:</strong> R<?php echo number_format($order['shipping_cost'], 2); ?></div>
                        
                        <!-- Shipping Status Information -->
                        <?php if (!empty($order['shipping_status'])): ?>
                            <div><strong>Shipping Status:</strong> 
                                <span class="status-<?php echo strtolower($order['shipping_status']); ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', $order['shipping_status'])); ?>
                                </span>
                            </div>
                        <?php else: ?>
                            <div><strong>Shipping Status:</strong> Not yet shipped</div>
                        <?php endif; ?>
                        
                        <?php if (!empty($order['tracking_number'])): ?>
                            <div><strong>Tracking #:</strong> 
                                <span class="tracking-number"><?php echo $order['tracking_number']; ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="order-items">
                        <h4>Order Items</h4>
                        <?php foreach ($order['items'] as $item): ?>
                            <div class="order-item">
                                <div class="order-item-info">
                                    <div><strong><?php echo $item['product_name']; ?></strong></div>
                                    <div>Quantity: <?php echo $item['quantity']; ?></div>
                                    <div>R<?php echo number_format($item['price'], 2); ?> each</div>
                                </div>
                                <div class="order-item-price">
                                    <strong>R<?php echo number_format($item['price'] * $item['quantity'], 2); ?></strong>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="order-total">
                        <strong>Total Amount: R<?php echo number_format($order['total_amount'] + $order['shipping_cost'], 2); ?></strong>
                    </div>
                    
                    <!-- Return and Refund Section -->
                    <div style="margin-top: 25px; padding-top: 20px; border-top: 2px dashed #e2e8f0;">
                        
                        <!-- Refund Information -->
                        <?php if ($order['return_id'] && $refundHandler->refundExistsForReturn($order['return_id'])): ?>
                            <?php $refund = $refundHandler->getRefundByReturnId($order['return_id']); ?>
                            <div class="refund-info">
                                <h5><i class="fas fa-money-bill-wave"></i> Refund Information</h5>
                                <div class="refund-details">
                                    <div><strong>Refund ID:</strong> #<?php echo $refund['refund_id']; ?></div>
                                    <div><strong>Amount:</strong> R<?php echo number_format($refund['amount'], 2); ?></div>
                                    <div><strong>Status:</strong> 
                                        <span class="refund-status refund-<?php echo strtolower($refund['refund_status']); ?>">
                                            <?php echo $refund['refund_status']; ?>
                                        </span>
                                    </div>
                                    <div><strong>Processed Date:</strong> <?php echo $refund['processed_date']; ?></div>
                                    <?php if (!empty($refund['notes'])): ?>
                                        <div style="grid-column: 1 / -1;"><strong>Notes:</strong> <?php echo htmlspecialchars($refund['notes']); ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Return Status -->
                        <?php if ($order['return_id']): ?>
                            <div class="return-status return-<?php echo strtolower($order['return_status']); ?>">
                                <i class="fas fa-undo"></i>
                                <strong>Return Status:</strong> <?php echo ucfirst($order['return_status']); ?>
                                <?php if ($order['return_reason']): ?>
                                    <br><strong>Reason:</strong> <?php echo ucfirst(str_replace('_', ' ', $order['return_reason'])); ?>
                                <?php endif; ?>
                                <br><small>Requested on: <?php echo date('F j, Y', strtotime($order['return_date'])); ?></small>
                            </div>
                        <?php elseif (in_array($order['status'], ['Delivered', 'Shipped'])): ?>
                            <button class="return-btn" onclick="openReturnModal(<?php echo $order['order_id']; ?>)">
                                <i class="fas fa-undo"></i> Request Return
                            </button>
                        <?php else: ?>
                            <p style="color: #666; font-size: 14px; margin-top: 10px;">
                                <i class="fas fa-info-circle"></i> 
                                Return not available (Order status: <?php echo $order['status']; ?>)
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-orders" style="text-align: center; padding: 60px; background: white; border-radius: 15px; box-shadow: var(--shadow);">
                <i class="fas fa-box-open" style="font-size: 64px; color: #ccc; margin-bottom: 20px;"></i>
                <p style="color: var(--text-light); margin-bottom: 25px; font-size: 18px;">You haven't placed any orders yet.</p>
                <a href="Shop.php" class="normal">Start Shopping</a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Return Modal -->
    <div id="returnModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeReturnModal()">&times;</span>
            <h3 style="color: var(--primary-color); margin-bottom: 20px;">Request Order Return</h3>
            <form method="POST" action="">
                <input type="hidden" id="returnOrderId" name="order_id" value="">
                <input type="hidden" name="return_order" value="1">
                
                <div class="form-group">
                    <label for="reason">Reason for Return:</label>
                    <select id="reason" name="reason" required>
                        <option value="">Select a reason</option>
                        <option value="defective">Item is defective</option>
                        <option value="wrong_item">Wrong item received</option>
                        <option value="changed_mind">Changed my mind</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="additional_notes">Additional Notes (Optional):</label>
                    <textarea id="additional_notes" name="additional_notes" placeholder="Please provide any additional details about your return request..."></textarea>
                </div>
                
                <button type="submit" class="submit-btn" style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white; border: none; padding: 15px; border-radius: 8px; width: 100%; font-weight: 600; cursor: pointer; transition: var(--transition);">
                    <i class="fas fa-paper-plane"></i> Submit Return Request
                </button>
            </form>
        </div>
    </div>

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
</html>
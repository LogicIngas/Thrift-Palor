<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in and form data is present
if (!isset($_SESSION['user_id']) || !isset($_POST['order_id']) || !isset($_POST['submit_payment'])) {
    header("Location: checkout.php");
    exit();
}

$database = new Database();
$conn = $database->getConnection();

$order_id = $_POST['order_id'];
$card_holder_name = $_POST['card_name'];
$card_number = $_POST['card_number'];
$expiry_date = $_POST['expiry_date'];
$cvv = $_POST['cvv'];

// Start a transaction for a two-step process
$conn->begin_transaction();

try {
    // Step 1: Insert payment details into the payments table
    $payment_sql = "INSERT INTO payments (order_id, card_holder_name, card_number, expiry_date, cvv, payment_date)
                    VALUES (?, ?, ?, ?, ?, NOW())";
    $payment_stmt = $conn->prepare($payment_sql);
    $payment_stmt->bind_param("issss", $order_id, $card_holder_name, $card_number, $expiry_date, $cvv);

    if (!$payment_stmt->execute()) {
        throw new Exception("Error saving payment details: " . $payment_stmt->error);
    }

    // Step 2: Update the order status in the orders table
    $transaction_id = 'TRX' . time() . rand(100, 999);
    $status = 'Processed';
    $payment_method = 'Credit Card';

    $order_sql = "UPDATE orders SET status = ?, transaction_id = ?, payment_method = ? WHERE order_id = ? AND buyer_id = ?";
    $order_stmt = $conn->prepare($order_sql);
    $order_stmt->bind_param("sssii", $status, $transaction_id, $payment_method, $order_id, $_SESSION['user_id']);
    
    if (!$order_stmt->execute()) {
        throw new Exception("Failed to update order status: " . $order_stmt->error);
    }
    
    // Commit the transaction and clear the cart
    $conn->commit();
    unset($_SESSION['cart']);

    // Redirect to the success page
    header("Location: order_success.php?order_id=" . $order_id);
    exit();

} catch (Exception $e) {
    // Rollback the transaction on any error
    $conn->rollback();
    $_SESSION['error'] = "Order processing failed: " . $e->getMessage();
    header("Location: checkout.php");
    exit();
}
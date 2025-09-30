<?php
// update_return_status.php
session_start();
require_once 'config/database.php';
require_once 'refund_functions.php';

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_return_status'])) {
    $return_id = intval($_POST['return_id']);
    $new_status = $_POST['status'];
    $admin_notes = trim($_POST['admin_notes']);
    
    $database = new Database();
    $conn = $database->getConnection();
    $refundHandler = new RefundHandler();
    
    try {
        // Start transaction
        $conn->begin_transaction();
        
        // Update return status
        $update_sql = "UPDATE returns SET status = ?, processed_date = NOW() WHERE return_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("si", $new_status, $return_id);
        
        if (!$update_stmt->execute()) {
            throw new Exception("Failed to update return status: " . $update_stmt->error);
        }
        
        // If status is being set to Completed, the trigger will automatically create a refund
        // But we can also add admin notes if provided
        if (!empty($admin_notes)) {
            $notes_sql = "UPDATE returns SET additional_notes = CONCAT(additional_notes, ' Admin: ', ?) WHERE return_id = ?";
            $notes_stmt = $conn->prepare($notes_sql);
            $notes_stmt->bind_param("si", $admin_notes, $return_id);
            $notes_stmt->execute();
        }
        
        // Commit transaction
        $conn->commit();
        
        $_SESSION['success_message'] = "Return status updated successfully.";
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        $_SESSION['error_message'] = "Error updating return status: " . $e->getMessage();
    }
    
    header("Location: admin_returns.php");
    exit();
}
?>
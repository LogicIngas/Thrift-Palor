<?php
// refund_functions.php
require_once 'config/database.php';

class RefundHandler {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    // Create refund manually (if needed)
    public function createRefund($return_id, $payment_id, $amount, $notes = '') {
        $sql = "INSERT INTO Refund (return_id, payment_id, amount, processed_date, refund_status, notes) 
                VALUES (?, ?, ?, CURDATE(), 'Pending', ?)";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iids", $return_id, $payment_id, $amount, $notes);
        
        if ($stmt->execute()) {
            return $this->conn->insert_id;
        } else {
            throw new Exception("Failed to create refund: " . $stmt->error);
        }
    }
    
    // Process a pending refund
    public function processRefund($refund_id) {
        $sql = "UPDATE Refund SET refund_status = 'Processed' WHERE refund_id = ? AND refund_status = 'Pending'";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $refund_id);
        
        return $stmt->execute();
    }
    
    // Mark refund as failed
    public function failRefund($refund_id, $reason = '') {
        $sql = "UPDATE Refund SET refund_status = 'Failed', notes = CONCAT(notes, ' - Failed: ', ?) WHERE refund_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $reason, $refund_id);
        
        return $stmt->execute();
    }
    
    // Get refund by return ID
    public function getRefundByReturnId($return_id) {
        $sql = "SELECT r.*, ret.order_id, p.card_holder_name 
                FROM Refund r 
                JOIN returns ret ON r.return_id = ret.return_id 
                JOIN payments p ON r.payment_id = p.id 
                WHERE r.return_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $return_id);
        $stmt->execute();
        
        return $stmt->get_result()->fetch_assoc();
    }
    
    // Get all refunds with pagination
    public function getAllRefunds($limit = 10, $offset = 0) {
        $sql = "SELECT r.*, ret.order_id, ret.product_name, ret.quantity, p.card_holder_name 
                FROM Refund r 
                JOIN returns ret ON r.return_id = ret.return_id 
                JOIN payments p ON r.payment_id = p.id 
                ORDER BY r.created_at DESC 
                LIMIT ? OFFSET ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $limit, $offset);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $refunds = [];
        while ($row = $result->fetch_assoc()) {
            $refunds[] = $row;
        }
        return $refunds;
    }
    
    // Get refunds by status
    public function getRefundsByStatus($status) {
        $sql = "SELECT r.*, ret.order_id, ret.product_name, p.card_holder_name 
                FROM Refund r 
                JOIN returns ret ON r.return_id = ret.return_id 
                JOIN payments p ON r.payment_id = p.id 
                WHERE r.refund_status = ? 
                ORDER BY r.created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $status);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $refunds = [];
        while ($row = $result->fetch_assoc()) {
            $refunds[] = $row;
        }
        return $refunds;
    }
    
    // Update refund amount
    public function updateRefundAmount($refund_id, $new_amount) {
        $sql = "UPDATE Refund SET amount = ?, notes = CONCAT(notes, ' - Amount updated to: ', ?) WHERE refund_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ddi", $new_amount, $new_amount, $refund_id);
        
        return $stmt->execute();
    }
    
    // Check if refund exists for return
    public function refundExistsForReturn($return_id) {
        $sql = "SELECT COUNT(*) as count FROM Refund WHERE return_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $return_id);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['count'] > 0;
    }
}
?>
<?php
// admin_refunds.php
session_start();
require_once 'config/database.php';
require_once 'refund_functions.php';

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header("Location: login.php");
    exit();
}

$refundHandler = new RefundHandler();

// Handle refund processing
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['process_refund'])) {
        $refund_id = intval($_POST['refund_id']);
        if ($refundHandler->processRefund($refund_id)) {
            $_SESSION['success_message'] = "Refund processed successfully.";
        } else {
            $_SESSION['error_message'] = "Failed to process refund.";
        }
    } elseif (isset($_POST['fail_refund'])) {
        $refund_id = intval($_POST['refund_id']);
        $reason = trim($_POST['fail_reason']);
        if ($refundHandler->failRefund($refund_id, $reason)) {
            $_SESSION['success_message'] = "Refund marked as failed.";
        } else {
            $_SESSION['error_message'] = "Failed to update refund status.";
        }
    }
}

// Get all refunds
$refunds = $refundHandler->getAllRefunds(50, 0);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Refunds - Thrift Palor</title>
    <link rel="stylesheet" href="./css/style.css">
    <style>
        .admin-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 20px;
        }
        .refund-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .status-pending { background-color: #fff3cd; color: #856404; padding: 5px 10px; border-radius: 4px; }
        .status-processed { background-color: #d1e7dd; color: #0f5132; padding: 5px 10px; border-radius: 4px; }
        .status-failed { background-color: #f8d7da; color: #721c24; padding: 5px 10px; border-radius: 4px; }
        .action-buttons { margin-top: 15px; }
        .btn { padding: 8px 15px; border: none; border-radius: 4px; cursor: pointer; margin-right: 10px; }
        .btn-process { background: #28a745; color: white; }
        .btn-fail { background: #dc3545; color: white; }
    </style>
</head>
<body>
    <section id="header">
        <div>
            <ul id="navbar">
                <li><a href="admin_dashboard.php">Dashboard</a></li>
                <li><a href="admin_returns.php">Returns</a></li>
                <li><a href="admin_refunds.php" class="active">Refunds</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
    </section>

    <div class="admin-container">
        <h2>Manage Refunds</h2>
        
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-error"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
        <?php endif; ?>

        <div class="refunds-list">
            <?php foreach ($refunds as $refund): ?>
                <div class="refund-card">
                    <h4>Refund #<?php echo $refund['refund_id']; ?></h4>
                    <p><strong>Return ID:</strong> <?php echo $refund['return_id']; ?></p>
                    <p><strong>Order ID:</strong> <?php echo $refund['order_id']; ?></p>
                    <p><strong>Amount:</strong> R<?php echo number_format($refund['amount'], 2); ?></p>
                    <p><strong>Customer:</strong> <?php echo htmlspecialchars($refund['card_holder_name']); ?></p>
                    <p><strong>Processed Date:</strong> <?php echo $refund['processed_date']; ?></p>
                    <p><strong>Status:</strong> <span class="status-<?php echo strtolower($refund['refund_status']); ?>">
                        <?php echo $refund['refund_status']; ?>
                    </span></p>
                    
                    <?php if ($refund['refund_status'] == 'Pending'): ?>
                        <div class="action-buttons">
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="refund_id" value="<?php echo $refund['refund_id']; ?>">
                                <button type="submit" name="process_refund" class="btn btn-process">Process Refund</button>
                            </form>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="refund_id" value="<?php echo $refund['refund_id']; ?>">
                                <input type="text" name="fail_reason" placeholder="Reason for failure" required>
                                <button type="submit" name="fail_refund" class="btn btn-fail">Mark as Failed</button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
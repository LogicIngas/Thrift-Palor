<?php
header('Content-Type: application/json');
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Authentication required.']);
    exit();
}

$database = new Database();
$conn = $database->getConnection();

$action = $_REQUEST['action'] ?? '';
$user_id = $_SESSION['user_id'];
$response = ['success' => false, 'message' => 'Invalid action.'];

switch ($action) {
    case 'read':
        $sql = "SELECT id, street_address, city, province, postal_code, is_default, address_type, created_at 
                FROM addresses WHERE user_id = ? ORDER BY is_default DESC, created_at DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $addresses = [];
        while ($row = $result->fetch_assoc()) {
            $addresses[] = $row;
        }

        $response = ['success' => true, 'addresses' => $addresses];
        break;

    case 'create':
        $street_address = $_POST['street_address'] ?? '';
        $city = $_POST['city'] ?? '';
        $province = $_POST['province'] ?? '';
        $postal_code = $_POST['postal_code'] ?? '';
        $is_default = isset($_POST['is_default']) ? 1 : 0;
        $address_type = $_POST['address_type'] ?? 'Home';

        if (empty($street_address) || empty($city) || empty($province) || empty($postal_code)) {
            $response = ['success' => false, 'message' => 'All address fields are required.'];
            break;
        }

        // If setting as default, remove default status from other addresses
        if ($is_default) {
            $updateSql = "UPDATE addresses SET is_default = 0 WHERE user_id = ?";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bind_param("i", $user_id);
            $updateStmt->execute();
        }

        $sql = "INSERT INTO addresses (user_id, street_address, city, province, postal_code, is_default, address_type, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issssis", $user_id, $street_address, $city, $province, $postal_code, $is_default, $address_type);

        if ($stmt->execute()) {
            $response = ['success' => true, 'message' => 'Address saved successfully!'];
        } else {
            $response = ['success' => false, 'message' => 'Error saving address.'];
        }
        break;

    case 'update':
        $address_id = $_POST['address_id'] ?? null;
        $street_address = $_POST['street_address'] ?? '';
        $city = $_POST['city'] ?? '';
        $province = $_POST['province'] ?? '';
        $postal_code = $_POST['postal_code'] ?? '';
        $is_default = isset($_POST['is_default']) ? 1 : 0;
        $address_type = $_POST['address_type'] ?? 'Home';

        if (!$address_id) {
            $response = ['success' => false, 'message' => 'Address ID is required.'];
            break;
        }

        // Verify address belongs to user
        $verify_stmt = $conn->prepare("SELECT user_id FROM addresses WHERE id = ?");
        $verify_stmt->bind_param("i", $address_id);
        $verify_stmt->execute();
        $verify_result = $verify_stmt->get_result();
        
        if ($verify_result->num_rows === 0 || $verify_result->fetch_assoc()['user_id'] != $user_id) {
            $response = ['success' => false, 'message' => 'Address not found or access denied.'];
            break;
        }

        // If setting as default, remove default status from other addresses
        if ($is_default) {
            $updateSql = "UPDATE addresses SET is_default = 0 WHERE user_id = ? AND id != ?";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bind_param("ii", $user_id, $address_id);
            $updateStmt->execute();
        }

        $sql = "UPDATE addresses SET street_address = ?, city = ?, province = ?, postal_code = ?, 
                is_default = ?, address_type = ? WHERE id = ? AND user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssisii", $street_address, $city, $province, $postal_code, $is_default, $address_type, $address_id, $user_id);
        
        if ($stmt->execute()) {
            $response = ['success' => true, 'message' => 'Address updated successfully!'];
        } else {
            $response = ['success' => false, 'message' => 'Error updating address.'];
        }
        break;

    case 'delete':
        $address_id = $_POST['address_id'] ?? null;

        if (!$address_id) {
            $response = ['success' => false, 'message' => 'Address ID is required.'];
            break;
        }

        // Verify address belongs to user
        $verify_stmt = $conn->prepare("SELECT user_id FROM addresses WHERE id = ?");
        $verify_stmt->bind_param("i", $address_id);
        $verify_stmt->execute();
        $verify_result = $verify_stmt->get_result();
        
        if ($verify_result->num_rows === 0 || $verify_result->fetch_assoc()['user_id'] != $user_id) {
            $response = ['success' => false, 'message' => 'Address not found or access denied.'];
            break;
        }

        $sql = "DELETE FROM addresses WHERE id = ? AND user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $address_id, $user_id);

        if ($stmt->execute()) {
            $response = ['success' => true, 'message' => 'Address deleted successfully!'];
        } else {
            $response = ['success' => false, 'message' => 'Error deleting address.'];
        }
        break;

    default:
        $response = ['success' => false, 'message' => 'Invalid action.'];
        break;
}

$conn->close();
echo json_encode($response);
?>
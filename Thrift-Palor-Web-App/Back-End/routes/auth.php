<?php
// Project-2/Thrift-Palor-Web-App/Back-End/routes/auth.php
header("Content-Type: application/json");
require_once('../models/UserModel.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (empty($input['first_name']) || empty($input['last_name']) || 
        empty($input['username']) || empty($input['email']) || 
        empty($input['password'])) {
        http_response_code(400);
        echo json_encode(["message" => "❌ All fields are required."]);
        exit;
    }

    $userModel = new UserModel();
    try {
        $userModel->createUser($input);
        http_response_code(201);
        echo json_encode(["message" => "✅ User registered successfully."]);
    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() === 1062) { // ER_DUP_ENTRY
            http_response_code(409);
            echo json_encode(["message" => "❌ Username or email already exists."]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "❌ Database error."]);
        }
    }
}
?>
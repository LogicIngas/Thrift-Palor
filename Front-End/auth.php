<?php
session_start();
require_once 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $database = new Database();
    $conn = $database->getConnection();
    
    $userData = [
        'first_name' => $_POST['first_name'] ?? '',
        'last_name' => $_POST['last_name'] ?? '',
        'username' => $_POST['username'] ?? '',
        'email' => $_POST['email'] ?? '',
        'password' => $_POST['password'] ?? '',
        'confirm_password' => $_POST['confirm_password'] ?? '',
        'phone' => $_POST['phone'] ?? '',
        'user_role' => $_POST['user_role'] ?? 'buyer',
        'terms' => $_POST['terms'] ?? ''
    ];

    try {
        // Validate required fields
        $required = ['first_name', 'last_name', 'username', 'email', 'password', 'user_role'];
        foreach ($required as $field) {
            if (empty($userData[$field])) {
                throw new Exception("The field '$field' is required.");
            }
        }

        // Validate email
        if (!filter_var($userData['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format.");
        }

        // Check password match
        if ($userData['password'] !== $userData['confirm_password']) {
            throw new Exception("Passwords don't match.");
        }

        // Check terms
        if (!isset($userData['terms']) || $userData['terms'] !== 'on') {
            throw new Exception("You must agree to the Terms & Conditions.");
        }

        // Check if user exists
        $check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
        $check_stmt->bind_param("ss", $userData['email'], $userData['username']);
        $check_stmt->execute();
        
        if ($check_stmt->get_result()->num_rows > 0) {
            throw new Exception("User with this email or username already exists.");
        }

        // Insert user
        $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, username, email, password, phone, role, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param(
            "sssssss",
            $userData['first_name'],
            $userData['last_name'],
            $userData['username'],
            $userData['email'],
            $userData['password'], // In production, you should hash this password
            $userData['phone'],
            $userData['user_role']
        );

        if ($stmt->execute()) {
            $_SESSION['success'] = "Registration successful! Please login.";
            header("Location: htmlogin.php");
            exit();
        } else {
            throw new Exception("Error creating user: " . $stmt->error);
        }
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        header("Location: Signup.html");
        exit();
    }
} else {
    header("Location: Signup.html");
    exit();
}
?>
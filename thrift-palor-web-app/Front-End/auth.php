<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

class Database {
    private $host = "localhost";
    private $username = "root";
    private $password = "";
    private $dbname = "thriftdb";
    private $conn;

    public function __construct() {
        try {
            $this->conn = new mysqli($this->host, $this->username, $this->password);
            
            if ($this->conn->connect_error) {
                throw new Exception("Database connection failed: " . $this->conn->connect_error);
            }
            
            // Check if database exists, create if not
            $this->createDatabase();

            // Select the database
            if (!$this->conn->select_db($this->dbname)) {
                throw new Exception("Failed to select database: " . $this->conn->error);
            }

            // Debug: confirm database selected
            $result = $this->conn->query("SELECT DATABASE() AS db");
            $row = $result->fetch_assoc();
            echo "Connected to database: " . $row['db'] . "<br>";

            $this->createUsersTable();
            
        } catch (Exception $e) {
            die("❌ " . $e->getMessage());
        }
    }

    private function createDatabase() {
        $sql = "CREATE DATABASE IF NOT EXISTS " . $this->dbname;
        if (!$this->conn->query($sql)) {
            throw new Exception("Database creation failed: " . $this->conn->error);
        }
    }

    public function getConnection() {
        return $this->conn;
    }

    private function createUsersTable() {
        $sql = "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            first_name VARCHAR(100) NOT NULL,
            last_name VARCHAR(100) NOT NULL,
            username VARCHAR(100) NOT NULL UNIQUE,
            email VARCHAR(100) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            phone VARCHAR(20),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";

        if (!$this->conn->query($sql)) {
            throw new Exception("Table creation failed: " . $this->conn->error);
        }
    }
}

class UserModel {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function createUser($userData) {
        // Check if required fields are present
        $required = ['first_name', 'last_name', 'username', 'email', 'password'];
        foreach ($required as $field) {
            if (empty($userData[$field])) {
                throw new Exception("The field '$field' is required.");
            }
        }

        // Validate email format
        if (!filter_var($userData['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format.");
        }

        // Check if passwords match
        if ($userData['password'] !== $userData['confirm_password']) {
            throw new Exception("Passwords don't match.");
        }

        // Check if terms are accepted
        if (!isset($userData['terms']) || $userData['terms'] !== 'on') {
            throw new Exception("You must agree to the Terms & Conditions.");
        }

        // Hash password
        $hashedPassword = $userData['password'];
        
        $stmt = $this->db->prepare("
            INSERT INTO users (first_name, last_name, username, email, password, phone)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->db->error);
        }
        
        $stmt->bind_param(
            "ssssss",
            $userData['first_name'],
            $userData['last_name'],
            $userData['username'],
            $userData['email'],
            $hashedPassword,
            $userData['phone']
        );

        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        // Check affected rows for confirmation
        if ($stmt->affected_rows === 0) {
            throw new Exception("No rows inserted. Insert may have failed.");
        }

        return true;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $userData = [
            'first_name' => $_POST['first_name'] ?? '',
            'last_name' => $_POST['last_name'] ?? '',
            'username' => $_POST['username'] ?? '',
            'email' => $_POST['email'] ?? '',
            'password' => $_POST['password'] ?? '',
            'confirm_password' => $_POST['confirm_password'] ?? '',
            'phone' => $_POST['phone'] ?? '',
            'terms' => $_POST['terms'] ?? ''
        ];

        $userModel = new UserModel();
        if ($userModel->createUser($userData)) {
    header("Location: home.html");
    exit();
}
        
    } catch (mysqli_sql_exception $e) {
        echo "❌ MySQL error: " . $e->getMessage();
        exit();
    } catch (Exception $e) {
        echo "❌ PHP error: " . $e->getMessage();
        exit();
    }
} else {
    echo "This page only handles POST requests.";
    exit();
}

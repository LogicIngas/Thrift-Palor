<?php
// Project-2/Thrift-Palor-Web-App/Back-End/models/UserModel.php
require_once('../config/config.php');

class UserModel {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function createUser($userData) {
        $stmt = $this->db->prepare("
            INSERT INTO users (first_name, last_name, username, email, password, phone)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->bind_param(
            "ssssss",
            $userData['first_name'],
            $userData['last_name'],
            $userData['username'],
            $userData['email'],
            $userData['password'],
            $userData['phone']
        );

        return $stmt->execute();
    }
}
?>
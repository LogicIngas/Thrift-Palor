<?php
// verify_db.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = "localhost";
$username = "root";
$password = "";
$dbname = "thriftdb";

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h3>Database Verification</h3>";

// Check required tables
$tables = ['users', 'orders'];
foreach ($tables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    echo $result->num_rows > 0 ? "✅ $table table exists<br>" : "❌ $table table missing<br>";
}

// Check orders table structure
$result = $conn->query("DESCRIBE orders");
if ($result) {
    echo "<h4>Orders Table Structure:</h4>";
    while ($row = $result->fetch_assoc()) {
        echo "{$row['Field']} - {$row['Type']}<br>";
    }
}

$conn->close();
?>
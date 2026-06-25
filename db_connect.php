<?php
// Start session globally for cart and state persistency
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Railway MySQL Configuration
$host = 'mysql.railway.internal';
$dbname = 'railway';
$username = 'root';
$password = 'kDYYOzVWVMjltQGhJfOBSpHClrAImNMS';
$port = '3306';

$db_connected = false;
$conn = null;

try {
    $conn = new PDO(
        "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4",
        $username,
        $password
    );

    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    $db_connected = true;

} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>

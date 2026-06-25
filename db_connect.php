<?php
// Start session globally for cart and state persistency
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database configuration
$host = 'localhost';
$dbname = 'modaren_db';
$username = 'root';
$password = ''; // Default for local web servers like XAMPP / MAMP / WampServer

$db_connected = false;
$conn = null;

try {
    // Establish connection with UTF-8 encoding configuration
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $db_connected = true;
} catch (PDOException $e) {
    // Silent catch so it degrades gracefully to mock data
    $db_connected = false;
    $conn = null;
}
?>

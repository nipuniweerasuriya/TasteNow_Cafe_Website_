<?php
$host = 'localhost';
$db   = 'cafe_website';
$user = 'root';
$pass = '1234';
$charset = 'utf8mb4';

// Create connection
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Connected successfully to the database.";
?>


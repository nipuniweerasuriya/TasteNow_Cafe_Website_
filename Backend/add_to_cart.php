<?php
// add_to_cart.php

include 'db_connect.php';
session_start();

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Get JSON input
$data = json_decode(file_get_contents("php://input"), true);

// Validate input
if (!isset($data['itemId'], $data['itemName'], $data['price'], $data['image'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid input']);
    exit;
}

$item_id = intval($data['itemId']);
$item_name = trim($data['itemName']);
$price = floatval($data['price']);
$image_url = trim($data['image']);
$quantity = 1;

try {
    // Check if the item already exists in the cart
    $check_stmt = $conn->prepare("SELECT id, quantity FROM cart_items WHERE user_id = ? AND item_id = ?");
    $check_stmt->bind_param("ii", $user_id, $item_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        // If already in cart, update quantity
        $new_quantity = $row['quantity'] + 1;
        $update_stmt = $conn->prepare("UPDATE cart_items SET quantity = ? WHERE id = ?");
        $update_stmt->bind_param("ii", $new_quantity, $row['id']);
        $update_stmt->execute();
    } else {
        // Insert new item into cart
        $insert_stmt = $conn->prepare("INSERT INTO cart_items (user_id, item_id, item_name, price, quantity, image_url) VALUES (?, ?, ?, ?, ?, ?)");
        $insert_stmt->bind_param("iisdis", $user_id, $item_id, $item_name, $price, $quantity, $image_url);
        $insert_stmt->execute();
    }

    echo json_encode(['status' => 'success']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}

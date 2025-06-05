<?php
// get_cart_qty.php

include 'db_connect.php';
session_start();

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // Sum all quantities of items in cart for this user
    $stmt = $conn->prepare("SELECT SUM(quantity) as total_quantity FROM cart_items WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $total_quantity = 0;
    if ($row = $result->fetch_assoc()) {
        $total_quantity = $row['total_quantity'] ?? 0;
    }

    echo json_encode(['status' => 'success', 'totalQuantity' => intval($total_quantity)]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}

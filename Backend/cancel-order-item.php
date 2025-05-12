<?php
global $conn;
require_once '../Backend/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_item_id'])) {
    $order_item_id = intval($_POST['order_item_id']);

    $stmt = $conn->prepare("UPDATE processed_order_items SET status = 'Canceled' WHERE id = ? AND status = 'Pending'");
    $stmt->bind_param('i', $order_item_id);

    if ($stmt->execute()) {
        header("Location: ../Frontend/profile.php");
        exit();
    } else {
        echo "Error canceling item: " . $stmt->error;
    }
} else {
    echo "Invalid request.";
}



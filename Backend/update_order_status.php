<?php
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderItemId = $_POST['item_id'] ?? null;
    $status = $_POST['status'] ?? null;

    $allowedStatuses = ['Pending', 'Preparing', 'Prepared', 'Served'];
    if (!$orderItemId || !in_array($status, $allowedStatuses)) {
        echo json_encode(['success' => false, 'message' => 'Invalid input']);
        exit;
    }

    $stmt = $conn->prepare("UPDATE processed_order_items SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $orderItemId);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Update failed']);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

<?php
require_once '../Backend/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderItemId = $_POST['item_id'];
    $status = $_POST['status'];

    $allowedStatuses = ['Pending', 'Prepared', 'Served'];
    if (!in_array($status, $allowedStatuses)) {
        echo json_encode(['success' => false, 'message' => 'Invalid status']);
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
}
?>

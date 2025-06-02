<?php
// remove_cancelled_item.php
header('Content-Type: application/json');

// Include your DB connection file here
include 'db_connect.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['item_id'])) {
    echo json_encode(['success' => false, 'message' => 'Item ID is missing']);
    exit;
}

$item_id = intval($data['item_id']);

// Example: Delete the item from processed_order_items or mark it removed

// Option 1: Delete from DB (be careful!)
$sql = "DELETE FROM processed_order_items WHERE id = ? AND status = 'Cancelled' LIMIT 1";

// Option 2: Mark as removed (better)
// $sql = "UPDATE processed_order_items SET status = 'Removed' WHERE id = ? AND status = 'Cancelled' LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $item_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}

$stmt->close();
$conn->close();

<?php
session_start();
header('Content-Type: application/json');
include('../Backend/db_connect.php');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Get item_ids from POST
$data = json_decode(file_get_contents('php://input'), true);
$item_ids = $data['item_ids'] ?? [];

if (empty($item_ids)) {
    echo json_encode(['success' => false, 'error' => 'No items selected']);
    exit();
}

$placeholders = implode(',', array_fill(0, count($item_ids), '?'));
$types = str_repeat('i', count($item_ids) + 1);
$params = array_merge([$user_id], $item_ids);

$stmt = $conn->prepare("DELETE FROM cart_items WHERE user_id = ? AND id IN ($placeholders)");
$stmt->bind_param($types, ...$params);
$success = $stmt->execute();

if ($success) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $stmt->error]);
}

$stmt->close();
$conn->close();
?>

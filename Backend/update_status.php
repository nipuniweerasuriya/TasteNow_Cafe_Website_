<?php
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $itemId = $_POST['item_id'] ?? null;
    $status = $_POST['status'] ?? null;

    if ($itemId && in_array($status, ['Pending', 'Prepared', 'Served'])) {
        $stmt = $conn->prepare("UPDATE processed_order_items SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $itemId);
        if ($stmt->execute()) {
            echo json_encode(["success" => true]);
        } else {
            echo json_encode(["success" => false, "error" => "DB update failed"]);
        }
        $stmt->close();
    } else {
        echo json_encode(["success" => false, "error" => "Invalid input"]);
    }
} else {
    echo json_encode(["success" => false, "error" => "Invalid request method"]);
}
?>

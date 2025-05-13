<?php
include '../Backend/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $order_item_id = $_POST['order_item_id'];
    $paid_amount = $_POST['paid_amount'];

    // Record the payment
    $insert = "INSERT INTO payments (order_item_id, paid_amount, paid_at) VALUES (?, ?, NOW())";
    $stmt = $conn->prepare($insert);
    $stmt->bind_param("id", $order_item_id, $paid_amount);

    if ($stmt->execute()) {
        // Mark the order item as Paid
        $update = "UPDATE processed_order_items SET status = 'Paid' WHERE id = ?";
        $updateStmt = $conn->prepare($update);
        $updateStmt->bind_param("i", $order_item_id);
        $updateStmt->execute();

        // Redirect back to cashier page
        header("Location: cashier.php?success=1");
        exit;
    } else {
        echo "Error: " . $conn->error;
    }
} else {
    echo "Invalid request method.";
}



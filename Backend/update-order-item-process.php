<?php
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_item_id = intval($_POST['order_item_id']);
    $order_id = intval($_POST['order_id']);
    $quantity = intval($_POST['quantity']);
    $table_number = trim($_POST['table_number']);

    if ($quantity < 1 || empty($table_number)) {
        echo "Invalid input.";
        exit;
    }

    // Update quantity in processed_order_items
    $update_item_sql = "UPDATE processed_order_items SET quantity = ? WHERE id = ?";
    $stmt1 = $conn->prepare($update_item_sql);
    $stmt1->bind_param("ii", $quantity, $order_item_id);
    $stmt1->execute();

    // Update table_number in processed_order
    $update_table_sql = "UPDATE processed_order SET table_number = ? WHERE id = ?";
    $stmt2 = $conn->prepare($update_table_sql);
    $stmt2->bind_param("si", $table_number, $order_id);
    $stmt2->execute();

    // Redirect back or show success message
    header("Location: profile.php?msg=Order updated successfully");
    exit;
} else {
    echo "Invalid request method.";
}

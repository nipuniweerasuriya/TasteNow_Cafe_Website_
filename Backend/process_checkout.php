<?php
include 'db_connect.php';

if (isset($_POST['user_id'], $_POST['table_number'], $_POST['cart_item_ids'])) {
    $userId = intval($_POST['user_id']);
    $tableNumber = $_POST['table_number'];
    $cartItemIds = $_POST['cart_item_ids']; // should be a comma-separated string

    // Get cart items
    $ids = implode(',', array_map('intval', explode(',', $cartItemIds)));
    $cartItemsQuery = $conn->query("SELECT * FROM cart_items WHERE id IN ($ids)");

    $totalPrice = 0;
    $cartItems = [];

    while ($row = $cartItemsQuery->fetch_assoc()) {
        $totalPrice += $row['price'] * $row['quantity'];
        $cartItems[] = $row;
    }

    // Insert into processed_order
    $stmt = $conn->prepare("INSERT INTO processed_order (user_id, table_number, total_price) VALUES (?, ?, ?)");
    $stmt->bind_param("isd", $userId, $tableNumber, $totalPrice);
    $stmt->execute();
    $orderId = $stmt->insert_id;
    $stmt->close();

    // Insert into processed_order_items
    $stmt = $conn->prepare("INSERT INTO processed_order_items (order_id, item_id, item_name, price, quantity, image_url) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($cartItems as $item) {
        $stmt->bind_param("iisdis", $orderId, $item['item_id'], $item['item_name'], $item['price'], $item['quantity'], $item['image_url']);
        $stmt->execute();
    }
    $stmt->close();


    echo json_encode(["success" => true, "order_id" => $orderId]);
} else {
    echo json_encode(["success" => false, "message" => "Missing data"]);
}

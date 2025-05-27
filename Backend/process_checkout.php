<?php
include 'db_connect.php';

$data = json_decode(file_get_contents("php://input"), true);

if (isset($data['user_id'], $data['table_number'], $data['cart_items']) && is_array($data['cart_items'])) {
    $userId = intval($data['user_id']);
    $tableNumber = $data['table_number'];
    $cartItems = $data['cart_items'];

    $totalPrice = 0;

    // Calculate total
    foreach ($cartItems as $item) {
        $totalPrice += floatval($item['price']) * intval($item['quantity']);
    }

    // Insert into processed_order
    $stmt = $conn->prepare("INSERT INTO processed_order (user_id, table_number, total_price) VALUES (?, ?, ?)");
    $stmt->bind_param("isd", $userId, $tableNumber, $totalPrice);
    $stmt->execute();
    $orderId = $stmt->insert_id;
    $stmt->close();

    // Insert into processed_order_items
    $stmt = $conn->prepare("SELECT * FROM cart_items WHERE id = ?");
    $insertStmt = $conn->prepare("INSERT INTO processed_order_items (order_id, item_id, item_name, price, quantity, image_url) VALUES (?, ?, ?, ?, ?, ?)");

    foreach ($cartItems as $ci) {
        $cartItemId = intval($ci['cart_item_id']);
        $qty = intval($ci['quantity']);
        $price = floatval($ci['price']);

        // Fetch full cart item data
        $stmt->bind_param("i", $cartItemId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $insertStmt->bind_param("iisdis", $orderId, $row['item_id'], $row['item_name'], $price, $qty, $row['image_url']);
            $insertStmt->execute();
        }
    }

    $stmt->close();
    $insertStmt->close();

    echo json_encode(["success" => true, "order_id" => $orderId]);
} else {
    echo json_encode(["success" => false, "message" => "Missing or invalid data"]);
}
?>

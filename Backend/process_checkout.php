<?php
session_start();
include 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "User not authenticated"]);
    exit;
}

// Get the input data
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['table_number'], $data['cart_items']) || !is_array($data['cart_items']) || empty($data['cart_items'])) {
    echo json_encode(["success" => false, "message" => "Missing or invalid data"]);
    exit;
}

$userId = intval($_SESSION['user_id']);
$tableNumber = trim($data['table_number']);
$cartItems = $data['cart_items'];

$totalPrice = 0;
foreach ($cartItems as $item) {
    $totalPrice += floatval($item['price']) * intval($item['quantity']);
}

// Start transaction
$conn->begin_transaction();

try {
    // Insert into processed_order
    $stmtOrder = $conn->prepare("INSERT INTO processed_order (user_id, table_number, total_price) VALUES (?, ?, ?)");
    $stmtOrder->bind_param("isd", $userId, $tableNumber, $totalPrice);
    $stmtOrder->execute();
    $orderId = $stmtOrder->insert_id;
    $stmtOrder->close();

    // Prepare statements
    $stmtSelect = $conn->prepare("SELECT * FROM cart_items WHERE id = ?");
    $stmtInsert = $conn->prepare("INSERT INTO processed_order_items (order_id, item_id, item_name, price, quantity, image_url) VALUES (?, ?, ?, ?, ?, ?)");
    $stmtDelete = $conn->prepare("DELETE FROM cart_items WHERE id = ?");

    foreach ($cartItems as $ci) {
        $cartItemId = intval($ci['cart_item_id']);
        $quantity = intval($ci['quantity']);
        $price = floatval($ci['price']);

        // Fetch cart item data
        $stmtSelect->bind_param("i", $cartItemId);
        $stmtSelect->execute();
        $result = $stmtSelect->get_result();

        if ($row = $result->fetch_assoc()) {
            $stmtInsert->bind_param("iisdis", $orderId, $row['item_id'], $row['item_name'], $price, $quantity, $row['image_url']);
            $stmtInsert->execute();

            // Remove the item from the cart
            $stmtDelete->bind_param("i", $cartItemId);
            $stmtDelete->execute();
        }
    }

    // Commit transaction
    $conn->commit();

    // Close prepared statements
    $stmtSelect->close();
    $stmtInsert->close();
    $stmtDelete->close();

    echo json_encode(["success" => true, "order_id" => $orderId]);

} catch (Exception $e) {
    $conn->rollback(); // Revert changes if error occurs
    echo json_encode(["success" => false, "message" => "Checkout failed. Please try again."]);
}

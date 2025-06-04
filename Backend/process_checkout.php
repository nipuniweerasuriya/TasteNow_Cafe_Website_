<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

include 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "User not authenticated"]);
    exit;
}

// Get the input data
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['tableNumber'], $data['items']) || !is_array($data['items']) || empty($data['items'])) {
    echo json_encode(["success" => false, "message" => "Missing or invalid data"]);
    exit;
}

$userId = intval($_SESSION['user_id']);
$tableNumber = trim($data['tableNumber']);
$cartItems = $data['items'];

// Calculate total price by fetching price from DB for each cart item
$totalPrice = 0;

try {
    foreach ($cartItems as $ci) {
        $cartItemId = intval($ci['cart_item_id']);
        $quantity = intval($ci['quantity']);

        // Fetch price from cart_items table
        $stmtPrice = $conn->prepare("SELECT price FROM cart_items WHERE id = ?");
        $stmtPrice->bind_param("i", $cartItemId);
        $stmtPrice->execute();
        $resultPrice = $stmtPrice->get_result();

        if ($rowPrice = $resultPrice->fetch_assoc()) {
            $price = floatval($rowPrice['price']);
            $totalPrice += $price * $quantity;
        } else {
            // Cart item id not found - throw error
            throw new Exception("Cart item ID $cartItemId not found.");
        }
        $stmtPrice->close();
    }

    // Start transaction
    $conn->begin_transaction();

    // Insert into processed_order
    $stmtOrder = $conn->prepare("INSERT INTO processed_order (user_id, table_number, total_price) VALUES (?, ?, ?)");
    $stmtOrder->bind_param("isd", $userId, $tableNumber, $totalPrice);
    $stmtOrder->execute();
    $orderId = $stmtOrder->insert_id;
    $stmtOrder->close();

    // Prepare statements for processing items
    $stmtSelect = $conn->prepare("SELECT * FROM cart_items WHERE id = ?");
    $stmtInsert = $conn->prepare("INSERT INTO processed_order_items (order_id, item_id, item_name, price, quantity, image_url) VALUES (?, ?, ?, ?, ?, ?)");
    $stmtDelete = $conn->prepare("DELETE FROM cart_items WHERE id = ?");

    foreach ($cartItems as $ci) {
        $cartItemId = intval($ci['cart_item_id']);
        $quantity = intval($ci['quantity']);

        // Fetch cart item data
        $stmtSelect->bind_param("i", $cartItemId);
        $stmtSelect->execute();
        $result = $stmtSelect->get_result();

        if ($row = $result->fetch_assoc()) {
            $stmtInsert->bind_param(
                "iisdis",
                $orderId,
                $row['item_id'],
                $row['item_name'],
                $row['price'],
                $quantity,
                $row['image_url']
            );
            $stmtInsert->execute();

            // Remove the item from the cart
            $stmtDelete->bind_param("i", $cartItemId);
            $stmtDelete->execute();
        } else {
            throw new Exception("Cart item ID $cartItemId not found during processing.");
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
    echo json_encode(["success" => false, "message" => "Checkout failed: " . $e->getMessage()]);
}

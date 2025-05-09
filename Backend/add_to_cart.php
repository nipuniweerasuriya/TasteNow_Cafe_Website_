<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

include 'db_connect.php';

$data = json_decode(file_get_contents("php://input"), true);

// Extract data with validation
$itemId = isset($data['itemId']) ? intval($data['itemId']) : 0;
$variantIds = $data['variantIds'] ?? [];
$addOnIds = $data['addOnIds'] ?? [];
$quantity = 1;

if ($itemId === 0 || empty($variantIds)) {
    echo json_encode(["status" => "error", "message" => "Invalid item or no variants selected."]);
    exit;
}

// Get item details
$itemStmt = $conn->prepare("SELECT name, price FROM menu_items WHERE id = ?");
$itemStmt->bind_param("i", $itemId);
$itemStmt->execute();
$itemResult = $itemStmt->get_result();
$item = $itemResult->fetch_assoc();
$itemStmt->close();

if (!$item) {
    echo json_encode(["status" => "error", "message" => "Item not found."]);
    exit;
}

$itemName = $item['name'];
$itemPrice = $item['price'];

// Insert into cart for each selected variant
$insertCartSql = "INSERT INTO cart_items (item_id, name, price, quantity, variant) VALUES (?, ?, ?, ?, ?)";
$cartStmt = $conn->prepare($insertCartSql);

foreach ($variantIds as $variantId) {
    // Get variant name
    $variantStmt = $conn->prepare("SELECT variant_name FROM menu_variants WHERE id = ?");
    $variantStmt->bind_param("i", $variantId);
    $variantStmt->execute();
    $variantResult = $variantStmt->get_result();
    $variant = $variantResult->fetch_assoc();
    $variantStmt->close();

    $variantName = $variant ? $variant['variant_name'] : 'No Variant';

    // Insert into cart_items
    $cartStmt->bind_param("isdis", $itemId, $itemName, $itemPrice, $quantity, $variantName);
    $cartStmt->execute();
    $cartItemId = $cartStmt->insert_id;

    // Insert selected add-ons
    if (!empty($addOnIds)) {
        $addonStmt = $conn->prepare("INSERT INTO cart_item_addons (cart_item_id, addon_id) VALUES (?, ?)");
        foreach ($addOnIds as $addonId) {
            $addonStmt->bind_param("ii", $cartItemId, $addonId);
            $addonStmt->execute();
        }
        $addonStmt->close();
    }
}

$cartStmt->close();
$conn->close();

echo json_encode(["status" => "success", "message" => "Item added to cart"]);
?>

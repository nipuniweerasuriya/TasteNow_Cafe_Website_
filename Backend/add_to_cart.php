<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

session_start(); // Start the session to access user_id

include 'db_connect.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "User not logged in."]);
    exit;
}

$userId = $_SESSION['user_id']; // Get logged-in user's ID

// Decode incoming JSON data
$data = json_decode(file_get_contents("php://input"), true);

// Extract and validate input
$itemId = isset($data['itemId']) ? intval($data['itemId']) : 0;
$variantIds = $data['variantIds'] ?? [];
$addOnIds = $data['addOnIds'] ?? [];
$quantity = 1; // default quantity

if ($itemId === 0 || empty($variantIds)) {
    echo json_encode(["status" => "error", "message" => "Invalid item or no variants selected."]);
    exit;
}

// Get item details from database
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

// Prepare insert query for cart items (with user_id)
$insertCartSql = "INSERT INTO cart_items (item_id, name, price, quantity, variant, user_id) VALUES (?, ?, ?, ?, ?, ?)";
$cartStmt = $conn->prepare($insertCartSql);

// Loop through each selected variant
foreach ($variantIds as $variantId) {
    // Get the name of the variant
    $variantStmt = $conn->prepare("SELECT variant_name FROM menu_variants WHERE id = ?");
    $variantStmt->bind_param("i", $variantId);
    $variantStmt->execute();
    $variantResult = $variantStmt->get_result();
    $variant = $variantResult->fetch_assoc();
    $variantStmt->close();

    $variantName = $variant ? $variant['variant_name'] : 'No Variant';

    // Insert into cart_items with user_id
    $cartStmt->bind_param("isdisi", $itemId, $itemName, $itemPrice, $quantity, $variantName, $userId);
    $cartStmt->execute();
    $cartItemId = $cartStmt->insert_id;

    // Insert selected add-ons for this cart item
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

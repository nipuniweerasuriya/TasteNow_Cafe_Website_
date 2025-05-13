<?php
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_item_id = intval($_POST['order_item_id']);
    $cart_item_id = intval($_POST['cart_item_id']);
    $quantity = intval($_POST['quantity']);
    $variant_id = intval($_POST['variant']);
    $addons = $_POST['addons'] ?? [];

    if ($quantity < 1) {
        echo "Quantity must be at least 1.";
        exit;
    }

    // ✅ Get base item price (menu item price)
    $sql = "SELECT mi.price AS item_price 
            FROM cart_items ci
            JOIN menu_items mi ON ci.item_id = mi.id
            WHERE ci.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $cart_item_id);
    $stmt->execute();
    $item_result = $stmt->get_result();
    $item = $item_result->fetch_assoc();
    $item_price = floatval($item['item_price']);

    // ✅ Get the total price for selected add-ons
    $addon_total = 0;
    if (!empty($addons)) {
        // Fetch add-on prices from the menu_add_ons table
        $addon_ids = implode(',', array_map('intval', $addons));
        $addon_sql = "SELECT SUM(ma.addon_price) AS addon_total 
                      FROM menu_add_ons ma 
                      WHERE ma.id IN ($addon_ids)";
        $addon_result = $conn->query($addon_sql);
        $addon_row = $addon_result->fetch_assoc();
        $addon_total = floatval($addon_row['addon_total']);
    }

    // ✅ Calculate the final price
    $final_item_price = ($item_price + $addon_total) * $quantity;

    // ✅ Update the quantity, variant, and final price for the processed order item
    $stmt = $conn->prepare("UPDATE processed_order_items SET quantity = ?, total_price = ? WHERE id = ?");
    $stmt->bind_param("idi", $quantity, $final_item_price, $order_item_id);
    if (!$stmt->execute()) {
        echo "Failed to update processed order item: " . $stmt->error;
        exit;
    }

    // ✅ Update variant in cart_items
    $stmt_variant = $conn->prepare("UPDATE cart_items SET variant = ? WHERE id = ?");
    $stmt_variant->bind_param("ii", $variant_id, $cart_item_id);
    $stmt_variant->execute();

    // ✅ Clear existing add-ons in cart_item_addons
    $conn->query("DELETE FROM cart_item_addons WHERE cart_item_id = $cart_item_id");

    // ✅ Insert new add-ons (if any)
    if (!empty($addons)) {
        $addon_stmt = $conn->prepare("INSERT INTO cart_item_addons (cart_item_id, addon_id) VALUES (?, ?)");
        foreach ($addons as $addon_id) {
            $addon_stmt->bind_param("ii", $cart_item_id, $addon_id);
            $addon_stmt->execute();
        }
    }

    // ✅ Redirect to profile page
    header("Location: ../Frontend/profile.php");
} else {
    echo "Invalid request.";
}



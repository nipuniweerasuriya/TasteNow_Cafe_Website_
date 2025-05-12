<?php
session_start();
require_once '../Backend/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_item_id = $_POST['order_item_id'] ?? null;
    $new_quantity = $_POST['quantity'] ?? 1;
    $new_variant_id = $_POST['variant'] ?? null;
    $addon_ids = $_POST['addons'] ?? []; // array of addon IDs

    if (!$order_item_id) {
        die("Missing order item ID.");
    }

    // Step 1: Get the cart_item_id from processed_order_items
    $stmt = $conn->prepare("SELECT cart_item_id FROM processed_order_items WHERE id = ?");
    $stmt->bind_param("i", $order_item_id);
    $stmt->execute();
    $stmt->bind_result($cart_item_id);
    $stmt->fetch();
    $stmt->close();

    if (!$cart_item_id) {
        die("Cart item not found.");
    }

    // Step 2: Update quantity in processed_order_items
    $stmt = $conn->prepare("UPDATE processed_order_items SET quantity = ? WHERE id = ?");
    $stmt->bind_param("ii", $new_quantity, $order_item_id);
    $stmt->execute();
    $stmt->close();

    // Step 3: Update variant in cart_items
    if ($new_variant_id) {
        $stmt = $conn->prepare("UPDATE cart_items SET variant = ? WHERE id = ?");
        $stmt->bind_param("ii", $new_variant_id, $cart_item_id);
        $stmt->execute();
        $stmt->close();
    }

    // Step 4: Update add-ons
    // Delete old add-ons
    $stmt = $conn->prepare("DELETE FROM cart_item_addons WHERE cart_item_id = ?");
    $stmt->bind_param("i", $cart_item_id);
    $stmt->execute();
    $stmt->close();

    // Insert new add-ons
    if (!empty($addon_ids)) {
        $stmt = $conn->prepare("INSERT INTO cart_item_addons (cart_item_id, addon_id) VALUES (?, ?)");
        foreach ($addon_ids as $addon_id) {
            $stmt->bind_param("ii", $cart_item_id, $addon_id);
            $stmt->execute();
        }
        $stmt->close();
    }

    // Optional Step 5: Recalculate and update total_price
    $total_price = 0;

    // Get base price from variant
    if ($new_variant_id) {
        $stmt = $conn->prepare("SELECT price FROM menu_variants WHERE id = ?");
        $stmt->bind_param("i", $new_variant_id);
        $stmt->execute();
        $stmt->bind_result($variant_price);
        $stmt->fetch();
        $stmt->close();

        $total_price += $variant_price * $new_quantity;
    }

    // Add add-on prices
    if (!empty($addon_ids)) {
        $placeholders = implode(',', array_fill(0, count($addon_ids), '?'));
        $types = str_repeat('i', count($addon_ids));
        $stmt = $conn->prepare("SELECT addon_price FROM menu_add_ons WHERE id IN ($placeholders)");
        $stmt->bind_param($types, ...$addon_ids);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $total_price += $row['addon_price'] * $new_quantity;
        }
        $stmt->close();
    }

    // Update total_price
    $stmt = $conn->prepare("UPDATE processed_order_items SET total_price = ? WHERE id = ?");
    $stmt->bind_param("di", $total_price, $order_item_id);
    $stmt->execute();
    $stmt->close();

    // Redirect back to profile or show success message
    header("Location: ../Frontend/profile.php?update=success");
    exit();
} else {
    echo "Invalid request.";
}

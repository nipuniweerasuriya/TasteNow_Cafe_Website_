<?php
require_once 'db_connect.php'; // adjust path as needed

if ($_SERVER['REQUEST_METHOD'] === 'DELETE' && isset($_GET['id'])) {
    $itemId = intval($_GET['id']);

    // Start transaction
    $conn->begin_transaction();

    try {
        // Delete related add-ons
        $conn->query("DELETE FROM menu_add_ons WHERE item_id = $itemId");

        // Delete related variants
        $conn->query("DELETE FROM menu_variants WHERE item_id = $itemId");

        // Delete the main menu item
        $conn->query("DELETE FROM menu_items WHERE id = $itemId");

        $conn->commit();
        echo "Menu item and related data deleted successfully.";
    } catch (Exception $e) {
        $conn->rollback();
        http_response_code(500);
        echo "Error deleting menu item: " . $e->getMessage();
    }
} else {
    http_response_code(400);
    echo "Invalid request.";
}


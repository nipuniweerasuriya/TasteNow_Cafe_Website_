<?php
// Include your database connection file
include('../Backend/db_connect.php');

// Check if the 'cart_item_ids' POST data exists
if (isset($_POST['cart_item_ids'])) {
    $cart_item_ids = json_decode($_POST['cart_item_ids']);  // Decode the cart item IDs

    if (!empty($cart_item_ids)) {
        // Debugging: Check if the item IDs are passed correctly
        error_log("Deleting items: " . implode(',', $cart_item_ids));

        // Prepare the SQL query to delete the selected items
        $ids = implode(',', array_map('intval', $cart_item_ids));  // Convert array to comma-separated values
        $query = "DELETE FROM cart_items WHERE id IN ($ids)";

        if (mysqli_query($conn, $query)) {
            // Return success response
            echo json_encode(['success' => true]);
        } else {
            // Return error response
            echo json_encode(['success' => false, 'message' => 'Error deleting items.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'No items to delete.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}

mysqli_close($conn);  // Close the database connection
?>


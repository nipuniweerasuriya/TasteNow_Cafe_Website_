<?php
// Include the database connection file
include('../Backend/db_connect.php');

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the table number and selected items from the request
    $table_number = mysqli_real_escape_string($conn, $_POST['table_number']);
    $selected_items = json_decode($_POST['selected_items'], true);

    // Insert the order into the 'processed_order' table
    $order_query = "
        INSERT INTO processed_order (table_number, order_date)
        VALUES ('$table_number', NOW())
    ";

    if (mysqli_query($conn, $order_query)) {
        // Get the inserted order ID
        $order_id = mysqli_insert_id($conn);

        // Insert the selected items into the 'processed_order_items' table
        foreach ($selected_items as $item) {
            $cart_item_id = $item['cart_item_id'];
            $quantity = $item['quantity'];
            $base_price = $item['base_price'];
            $addon_total = $item['addon_total'];
            $total_price = ($base_price + $addon_total) * $quantity;

            // Insert each item in the order
            $item_query = "
                INSERT INTO processed_order_items (order_id, cart_item_id, quantity, total_price)
                VALUES ('$order_id', '$cart_item_id', '$quantity', '$total_price')
            ";

            mysqli_query($conn, $item_query);
        }

        // Return a success response
        echo json_encode(['success' => true]);
    } else {
        // Return an error response if the order insertion fails
        echo json_encode(['success' => false, 'message' => 'Failed to create the order.']);
    }

    // Close the database connection
    mysqli_close($conn);
}
?>

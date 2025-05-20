<?php
// Include the DB connection
include 'db_connect.php';

// Enable error reporting for development (remove in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// SQL to fetch processed order items along with item name, variant, quantity, and add-ons
$sql = "SELECT 
            poi.id AS processed_item_id,
            po.id AS order_id,
            po.table_number,
            po.order_date,
            ci.name AS item_name,
            ci.variant,
            poi.quantity,
            poi.status,
            poi.total_price,
            GROUP_CONCAT(ma.addon_name SEPARATOR ', ') AS addons
        FROM processed_order_items poi
        JOIN processed_order po ON poi.order_id = po.id
        JOIN cart_items ci ON poi.cart_item_id = ci.id
        LEFT JOIN cart_item_addons cia ON cia.cart_item_id = ci.id
        LEFT JOIN menu_add_ons ma ON cia.addon_id = ma.id
        GROUP BY poi.id
        ORDER BY po.order_date DESC";

$result = mysqli_query($conn, $sql);

// Check for SQL errors
if (!$result) {
    header('Content-Type: application/json');
    echo json_encode(['error' => mysqli_error($conn)]);
    exit;
}

// Fetch all rows
$orders = [];
while ($row = mysqli_fetch_assoc($result)) {
    $orders[] = $row;
}

// Return as JSON
header('Content-Type: application/json');
echo json_encode($orders, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);



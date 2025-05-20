<?php
// Include the DB connection
include 'db_connect.php';

// Enable error reporting for development (remove in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Check if a filter is passed (e.g., ?filter=today)
$filter = $_GET['filter'] ?? 'today';

// Base SQL
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
        LEFT JOIN menu_add_ons ma ON cia.addon_id = ma.id";

// Add filtering for today’s orders if requested
if ($filter === 'today') {
    $sql .= " WHERE DATE(po.order_date) = CURDATE()";
}

// Grouping and sorting
$sql .= " GROUP BY poi.id ORDER BY po.order_date DESC";

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



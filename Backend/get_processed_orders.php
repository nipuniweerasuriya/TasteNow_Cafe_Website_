<?php
global$conn;
include 'db_connect.php';

$sql = "SELECT 
            poi.id AS processed_item_id,
            po.id AS order_id,
            po.table_number,
            po.order_date,
            ci.name AS item_name,
            ci.variant,
            ci.addons,
            ci.quantity,
            poi.status,
            poi.total_price
        FROM processed_order_items poi
        JOIN processed_order po ON poi.order_id = po.id
        JOIN cart_items ci ON poi.cart_item_id = ci.id
        ORDER BY po.order_date DESC";

$result = mysqli_query($conn, $sql);

$orders = [];
while ($row = mysqli_fetch_assoc($result)) {
    $orders[] = $row;
}

header('Content-Type: application/json');
echo json_encode($orders);


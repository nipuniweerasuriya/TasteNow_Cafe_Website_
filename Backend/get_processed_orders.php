<?php
require_once '../Backend/db_connect.php'; // your DB connection

$sql = "
SELECT 
    poi.id AS processed_item_id,
    mo.table_number,
    mo.order_date,
    mi.name AS item_name,
    mi.image_url,
    poi.quantity,
    poi.total_price,
    v.variant_name,
    v.price AS variant_price,
    a.addon_name,
    a.addon_price,
    'Paid' as payment_status -- update based on actual payment status
FROM processed_order mo
JOIN processed_order_items poi ON poi.order_id = mo.id
JOIN menu_items mi ON poi.cart_item_id = mi.id
LEFT JOIN processed_order_variants pov ON pov.processed_order_item_id = poi.id
LEFT JOIN menu_variants v ON pov.variant_id = v.id
LEFT JOIN processed_order_add_ons poa ON poa.processed_order_item_id = poi.id
LEFT JOIN menu_add_ons a ON poa.addon_id = a.id
ORDER BY mo.order_date DESC
";

$result = mysqli_query($conn, $sql);

$orders = [];
while ($row = mysqli_fetch_assoc($result)) {
    $id = $row['processed_item_id'];

    if (!isset($orders[$id])) {
        $orders[$id] = [
            'item_name' => $row['item_name'],
            'image_url' => $row['image_url'],
            'variant' => $row['variant_name'] ? $row['variant_name'] . " (+Rs." . $row['variant_price'] . ")" : null,
            'add_ons' => [],
            'quantity' => $row['quantity'],
            'total_price' => $row['total_price'],
            'table_number' => $row['table_number'],
            'payment_status' => $row['payment_status'],
        ];
    }

    if ($row['addon_name']) {
        $orders[$id]['add_ons'][] = $row['addon_name'] . " (+Rs." . $row['addon_price'] . ")";
    }
}

echo json_encode(array_values($orders));
?>


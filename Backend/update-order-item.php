<?php
require_once 'db_connect.php';

if (!isset($_GET['item_id'])) {
    echo "Invalid request.";
    exit;
}

$order_item_id = intval($_GET['item_id']);

// Fetch order item with associated order to get table_number
$sql = "SELECT 
            poi.id AS order_item_id,
            poi.quantity,
            poi.order_id,
            po.table_number,
            poi.item_name
        FROM processed_order_items poi
        JOIN processed_order po ON poi.order_id = po.id
        WHERE poi.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $order_item_id);
$stmt->execute();
$result = $stmt->get_result();
$item = $result->fetch_assoc();

if (!$item) {
    echo "Order item not found.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Update Order Item</title>
</head>
<body>
<h2>Update Order Item - <?php echo htmlspecialchars($item['item_name']); ?></h2>
<form method="POST" action="update-order-item-process.php">
    <input type="hidden" name="order_item_id" value="<?php echo $item['order_item_id']; ?>">
    <input type="hidden" name="order_id" value="<?php echo $item['order_id']; ?>">

    <label>Quantity:</label>
    <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" required><br><br>

    <label>Table Number:</label>
    <input type="text" name="table_number" value="<?php echo htmlspecialchars($item['table_number']); ?>" required><br><br>

    <button type="submit">Update</button>
</form>
</body>
</html>

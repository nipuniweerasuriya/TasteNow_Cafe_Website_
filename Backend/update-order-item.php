<?php
require_once 'db_connect.php';

if (!isset($_GET['item_id'])) {
    echo "Invalid request.";
    exit;
}

$order_item_id = intval($_GET['item_id']);

// Fetch item, variant, cart_item_id, and item_id
$sql = "SELECT 
            poi.id AS order_item_id, 
            poi.quantity, 
            ci.id AS cart_item_id, 
            ci.variant, 
            ci.item_id,
            mi.name AS item_name
        FROM processed_order_items poi
        JOIN cart_items ci ON poi.cart_item_id = ci.id
        JOIN menu_items mi ON ci.item_id = mi.id
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

$cart_item_id = $item['cart_item_id'];
$item_id = $item['item_id'];

// ✅ Fetch only relevant variants for this item
$variant_stmt = $conn->prepare("SELECT * FROM menu_variants WHERE item_id = ?");
$variant_stmt->bind_param("i", $item_id);
$variant_stmt->execute();
$variant_result = $variant_stmt->get_result();

// ✅ Fetch only relevant add-ons for this item
$addon_stmt = $conn->prepare("SELECT * FROM menu_add_ons WHERE item_id = ?");
$addon_stmt->bind_param("i", $item_id);
$addon_stmt->execute();
$addon_result = $addon_stmt->get_result();

// ✅ Fetch current add-ons of this cart item
$current_addons = [];
$addon_check = $conn->query("SELECT addon_id FROM cart_item_addons WHERE cart_item_id = $cart_item_id");
while ($row = $addon_check->fetch_assoc()) {
    $current_addons[] = $row['addon_id'];
}
?>

<!DOCTYPE html>
<html lang="">
<head>
    <title>Update Order Item</title>
</head>
<body>
<h2>Update Order Item - <?php echo htmlspecialchars($item['item_name']); ?></h2>
<form method="POST" action="update-order-item-process.php">
    <input type="hidden" name="order_item_id" value="<?php echo $item['order_item_id']; ?>">
    <input type="hidden" name="cart_item_id" value="<?php echo $cart_item_id; ?>">

    <label>Quantity:</label>
    <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" required><br><br>

    <label>Select Variant:</label>
    <select name="variant" required>
        <option value="">-- Select Variant --</option>
        <?php while ($variant = $variant_result->fetch_assoc()): ?>
            <option value="<?php echo $variant['id']; ?>" <?php if ($variant['id'] == $item['variant']) echo 'selected'; ?>>
                <?php echo htmlspecialchars($variant['variant_name']); ?>
            </option>
        <?php endwhile; ?>
    </select><br><br>

    <label>Select Add-ons:</label><br>
    <?php while ($addon = $addon_result->fetch_assoc()): ?>
        <input type="checkbox" name="addons[]" value="<?php echo $addon['id']; ?>" <?php echo in_array($addon['id'], $current_addons) ? 'checked' : ''; ?>>
        <?php echo htmlspecialchars($addon['addon_name']); ?> (+Rs.<?php echo $addon['addon_price']; ?>)<br>
    <?php endwhile; ?><br>

    <button type="submit">Update</button>
</form>
</body>
</html>


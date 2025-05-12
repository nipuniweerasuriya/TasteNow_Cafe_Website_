<?php
require_once '../Backend/db_connect.php';
session_start();

if (!isset($_GET['id'])) {
    echo "Missing order item ID.";
    exit();
}

$order_item_id = intval($_GET['id']);

// Fetch order item details
$sql = "
    SELECT 
        poi.id AS order_item_id,
        poi.quantity,
        ci.item_id,
        ci.variant AS selected_variant,
        mi.name AS item_name
    FROM processed_order_items poi
    JOIN cart_items ci ON poi.cart_item_id = ci.id
    JOIN menu_items mi ON ci.item_id = mi.id
    WHERE poi.id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $order_item_id);
$stmt->execute();
$result = $stmt->get_result();
$orderItem = $result->fetch_assoc();

if (!$orderItem) {
    echo "Order item not found.";
    exit();
}

// Fetch all variants for this item
$variantQuery = $conn->prepare("SELECT id, variant_name, price FROM menu_variants WHERE item_id = ?");
$variantQuery->bind_param("i", $orderItem['item_id']);
$variantQuery->execute();
$variants = $variantQuery->get_result();

// Fetch all add-ons for this item
$addonQuery = $conn->prepare("SELECT id, addon_name, addon_price FROM menu_add_ons WHERE item_id = ?");
$addonQuery->bind_param("i", $orderItem['item_id']);
$addonQuery->execute();
$addons = $addonQuery->get_result();

// Get selected add-ons
$addonSelectedQuery = $conn->prepare("SELECT addon_id FROM cart_item_addons WHERE cart_item_id = (SELECT cart_item_id FROM processed_order_items WHERE id = ?)");
$addonSelectedQuery->bind_param("i", $order_item_id);
$addonSelectedQuery->execute();
$addonSelectedResult = $addonSelectedQuery->get_result();
$selected_addons = [];
while ($row = $addonSelectedResult->fetch_assoc()) {
    $selected_addons[] = $row['addon_id'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Order Item</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
<div class="container">
    <h3>Update Order Item: <?php echo htmlspecialchars($orderItem['item_name']); ?></h3>
    <form method="POST" action="../Backend/update-order-item-handler.php">
        <input type="hidden" name="order_item_id" value="<?php echo $orderItem['order_item_id']; ?>">

        <div class="mb-3">
            <label for="quantity" class="form-label">Quantity:</label>
            <input type="number" class="form-control" name="quantity" min="1" value="<?php echo $orderItem['quantity']; ?>" required>
        </div>

        <div class="mb-3">
            <label for="variant" class="form-label">Variant:</label>
            <select class="form-select" name="variant">
                <option value="">-- No Variant --</option>
                <?php while ($variant = $variants->fetch_assoc()): ?>
                    <option value="<?php echo $variant['id']; ?>" <?php if ($orderItem['selected_variant'] == $variant['id']) echo "selected"; ?>>
                        <?php echo $variant['variant_name'] . " (+Rs." . $variant['price'] . ")"; ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Add-ons:</label><br>
            <?php while ($addon = $addons->fetch_assoc()): ?>
                <div class="form-check">
                    <form method="POST" action="../Backend/update-order-item-handler.php">
                        <input type="hidden" name="order_item_id" value="<?= $order_item_id ?>">
                        <input type="number" name="quantity" value="<?= $quantity ?>" required>

                        <!-- Variant dropdown -->
                        <select name="variant">
                            <?php foreach ($variants as $variant): ?>
                                <option value="<?= $variant['id'] ?>" <?= $variant['id'] == $selected_variant ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($variant['variant_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <!-- Add-ons (checkboxes) -->
                        <?php foreach ($addons as $addon): ?>
                            <label>
                                <input type="checkbox" name="addons[]" value="<?= $addon['id'] ?>"
                                    <?= in_array($addon['id'], $selected_addons) ? 'checked' : '' ?>>
                                <?= htmlspecialchars($addon['addon_name']) ?>
                            </label>
                        <?php endforeach; ?>

                        <button type="submit">Update Item</button>
                    </form>

                    <?php endwhile; ?>
        </div>

        <button type="submit" class="btn btn-primary">Update Order Item</button>
    </form>
</div>
</body>
</html>

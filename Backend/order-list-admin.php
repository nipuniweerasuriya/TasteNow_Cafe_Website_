<?php
global $conn;
require_once '../Backend/db_connect.php';

$sql = "
    SELECT
        po.id AS order_id,
        poi.quantity,
        poi.total_price,
        mi.name AS item_name,
        mi.image_url AS item_image,
        ci.variant AS variant_name,
        ma.addon_name AS addon_name,
        ma.addon_price AS addon_price,
        po.table_number,
        po.order_date
    FROM processed_order po
    JOIN processed_order_items poi ON po.id = poi.order_id
    JOIN cart_items ci ON poi.cart_item_id = ci.id
    JOIN menu_items mi ON ci.item_id = mi.id
    LEFT JOIN cart_item_addons cia ON ci.id = cia.cart_item_id
    LEFT JOIN menu_add_ons ma ON cia.addon_id = ma.id
    ORDER BY po.order_date DESC";

$result = $conn->query($sql);
?>

<div class="order-items-container bg-white p-3 mb-3">
    <?php if ($result && $result->num_rows > 0): ?>
        <?php while ($order = $result->fetch_assoc()): ?>
            <div class="d-flex align-items-start gap-3 cart-item border-bottom pb-3 mb-3">
                <input type="checkbox" class="mt-2">
                <img src="../Backend/uploads/<?php echo ltrim(htmlspecialchars($order['item_image']), '/'); ?>" alt="Item">
                <div class="flex-grow-1">
                    <p class="item-title mb-1"><?php echo htmlspecialchars($order['item_name']); ?></p>
                    <div class="customizations">
                        <?php if (!empty($order['variant_name'])): ?>
                            <p><span>Variant:</span> <?php echo htmlspecialchars($order['variant_name']); ?></p>
                        <?php endif; ?>
                        <?php if (!empty($order['addon_name'])): ?>
                            <p><span>Add-on:</span> <?php echo htmlspecialchars($order['addon_name']); ?> (+Rs.<?php echo $order['addon_price']; ?>)</p>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="text-end">
                    <div class="order-id text-muted small mb-2">
                        <span>Order ID: #<?php echo $order['order_id']; ?></span>
                    </div>
                    <div class="item-price mb-2">
                        <span class="price">Rs. <?php echo $order['total_price']; ?></span>
                    </div>
                    <div class="table-number">
                        <span>Table <?php echo $order['table_number']; ?></span>
                    </div>
                    <div class="qty mb-2">
                        <span>Qty: <?php echo $order['quantity']; ?></span>
                    </div>
                    <div class="order-date text-muted small mb-2">
                        <span><?php echo date('Y-m-d H:i', strtotime($order['order_date'])); ?></span>
                    </div>
                    <div class="btn-group" role="group">
                        <button class="btn btn-outline-warning btn-sm">Pending</button>
                        <button class="btn btn-outline-primary btn-sm">Prepared</button>
                        <button class="btn btn-outline-success btn-sm">Served</button>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No orders found.</p>
    <?php endif; ?>
</div>
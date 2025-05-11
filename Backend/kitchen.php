<?php
require_once '../Backend/db_connect.php';  // Include your database connection

// SQL Query to fetch all processed orders and related data
$sql = "
    SELECT
        po.id AS order_id,
        poi.quantity,
        poi.total_price,
        mi.name AS item_name,
        mi.image_url AS item_image,
        ci.variant AS variant_name,  -- Get variant name directly from cart_items
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

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kitchen Orders</title>

    <!-- Fonts and Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700&family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined&display=swap" rel="stylesheet"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css"/>
</head>
<body class="common-page" id="kitchen-page">

<!-- Navbar -->
<div class="navbar">
    <div class="navbar-container">
        <div class="navbar-brand">
            <a class="navbar-brand logo-wiggle" href="../Frontend/index.php">TASTENOW</a>
        </div>
        <div class="d-flex align-items-center ms-3">
            <a href="../Backend/logout.php" class="text-decoration-none text-dark d-flex align-items-center">
                <span class="material-symbols-outlined icon-logout me-2">logout</span>
            </a>
        </div>
    </div>
</div>

<!-- Kitchen Orders -->
<div class="container">
    <div class="profile-layout">
        <div class="order-container w-100">
            <h4 class="mb-3">Kitchen Orders</h4>
            <div class="order-items-container bg-white p-3 mb-3">
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($order = $result->fetch_assoc()): ?>
                        <div class="d-flex align-items-start gap-3 cart-item border-bottom pb-3 mb-3">
                            <input type="checkbox" class="mt-2">
                            <img src="../Backend/uploads<?php echo htmlspecialchars($order['item_image']); ?>" alt="Item" style="width: 100px; height: auto;">
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
                                <!-- Status Buttons -->
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
        </div>
    </div>
</div>

<!-- JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/script.js"></script>

</body>
</html>

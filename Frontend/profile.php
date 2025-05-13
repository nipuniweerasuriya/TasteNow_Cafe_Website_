<?php
global $conn;
session_start();
require_once '../Backend/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$sql = "
    SELECT
        po.id AS order_id,
        poi.id AS order_item_id, -- ✅ Needed for cancel
        poi.status AS item_status,
        poi.quantity,
        poi.total_price,
        mi.name AS item_name,
        mi.image_url AS item_image,
        mv.variant_name AS variant_name,
        ma.addon_name AS addon_name,
        ma.addon_price AS addon_price,
        po.table_number,
        po.order_date
    FROM processed_order po
    JOIN processed_order_items poi ON po.id = poi.order_id
    JOIN cart_items ci ON poi.cart_item_id = ci.id
    JOIN menu_items mi ON ci.item_id = mi.id
    LEFT JOIN menu_variants mv ON ci.variant = mv.id
    LEFT JOIN cart_item_addons cia ON ci.id = cia.cart_item_id
    LEFT JOIN menu_add_ons ma ON cia.addon_id = ma.id
    WHERE po.user_id = ? ORDER BY po.order_date DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($stmt->error) {
    echo "SQL Error: " . $stmt->error;
}
?>


<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Profile</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700&family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined&display=swap" rel="stylesheet"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css"/>
    <style>
        .status-badge {
            padding: 4px 8px;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: bold;
            display: inline-block;
        }
        .status-pending { background-color: #ffc107; color: black; }
        .status-prepared { background-color: #0d6efd; color: white; }
        .status-served { background-color: #198754; color: white; }
        .status-canceled { background-color: #dc3545; color: white; }
    </style>
</head>
<body class="common-page" id="profile-page">

<div class="navbar">
    <div class="navbar-container">
        <div class="navbar-brand">
            <a class="navbar-brand logo-wiggle" href="index.php">TASTENOW</a>
        </div>
        <div class="d-flex align-items-center ms-3">
            <a href="../Backend/logout.php" class="text-decoration-none text-dark d-flex align-items-center">
                <span class="material-symbols-outlined icon-logout me-2">logout</span>
            </a>
        </div>
    </div>
</div>

<div class="container">
    <div class="profile-layout">
        <div class="profile-sidebar">
            <div class="d-flex align-items-center mb-4">
                <div class="text-white d-flex justify-content-center align-items-center profile-avatar me-3">
                    NW
                </div>
                <div>
                    <h6 class="mb-0">Nipuni Weerasuriya</h6>
                    <small class="text-muted">nipuni@example.com</small>
                </div>
            </div>

            <div class="profile-actions">
                <div class="profile-action-item"><small>Orders</small></div>
                <div class="profile-action-item"><small>Paid</small></div>
                <div class="profile-action-item"><small>Unpaid</small></div>
                <div class="profile-action-item"><small>Canceled</small></div>
            </div>
        </div>

        <div class="order-container">
            <div class="order-items-container bg-white p-3 mb-3">
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($order = $result->fetch_assoc()): ?>
                        <div class="d-flex align-items-start gap-3 cart-item border-bottom pb-3 mb-3">
                            <input type="checkbox" class="mt-2">
                            <img src="../Backend/uploads<?php echo $order['item_image']; ?>" alt="Product" style="width: 100px; height: auto;">
                            <div class="flex-grow-1">
                                <p class="item-title"><?php echo $order['item_name']; ?></p>
                                <p class="custom-text-danger">Only <?php echo $order['quantity']; ?> item(s) we have now</p>
                                <div class="customizations">
                                    <?php if (!empty($order['variant_name'])): ?>
                                        <p><span>Variant:</span> <?php echo $order['variant_name']; ?></p>
                                    <?php else: ?>
                                        <p><span>Variant:</span> No variant selected</p>
                                    <?php endif; ?>
                                    <?php if ($order['addon_name']): ?>
                                        <p><span>Add-ons:</span> <?php echo $order['addon_name']; ?> (+Rs.<?php echo $order['addon_price']; ?>)</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="text-end">
                                <div class="item-price mb-2">
                                    <span class="price">Rs. <?php echo $order['total_price']; ?></span>
                                </div>
                                <div class="payment-status">
                                    <span>Not Paid</span>
                                </div>
                                <div class="table-number">
                                    <span>Table <?php echo $order['table_number']; ?></span>
                                </div>
                                <div class="qty mb-2">
                                    <span>Qty: <?php echo $order['quantity']; ?></span>
                                </div>
                                <div class="mb-2">
                                    <?php
                                    // Use 'item_status' to access the status from the query result
                                    $status = $order['item_status'] ?? 'Unknown';
                                    $statusClass = strtolower($status);

                                    // Display different status badge based on the status
                                    if ($status == 'Pending') {
                                        $statusClass = 'pending';
                                    } elseif ($status == 'Prepared') {
                                        $statusClass = 'prepared';
                                    } elseif ($status == 'Served') {
                                        $statusClass = 'served';
                                    } elseif ($status == 'Canceled') {
                                        $statusClass = 'canceled';
                                    }
                                    echo '<span class="status-badge status-' . $statusClass . '">' . $status . '</span>';
                                    ?>
                                </div>

                                <!-- ✅ Conditional buttons for 'Pending' status -->
                                <?php if (strtolower($order['item_status']) === 'pending'): ?>
                                    <div class="mb-2 d-flex gap-2">
                                        <!-- Update Button -->
                                        <a href="update-order-item.php?id=<?php echo $order['order_id']; ?>" class="btn btn-sm btn-outline-primary">
                                            Update
                                        </a>

                                        <!-- Cancel Button -->
                                        <form method="POST" action="../Backend/cancel-order-item.php" style="display:inline;">
                                            <input type="hidden" name="order_item_id" value="<?php echo $order['order_item_id']; ?>"> <!-- ✅ FIXED -->
                                            <button type="submit" class="btn btn-sm btn-outline-danger"
                                                    onclick="return confirm('Are you sure you want to cancel this item?');">
                                                Cancel
                                            </button>
                                        </form>

                                    </div>
                                <?php else: ?>
                                    <div class="text-muted small">
                                        <em>Cannot update or cancel this item (status: <?php echo $order['item_status']; ?>)</em>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No orders found for this user.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/script.js"></script>

</body>
</html>

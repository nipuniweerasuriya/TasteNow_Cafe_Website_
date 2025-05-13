<?php
global $conn;
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $itemId = $_POST['item_id'] ?? null;
    $status = $_POST['status'] ?? null;

    if ($itemId && in_array($status, ['Pending', 'Prepared', 'Served'])) {
        $stmt = $conn->prepare("UPDATE processed_order_items SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $itemId);
        if ($stmt->execute()) {
            echo json_encode(["success" => true]);
        } else {
            echo json_encode(["success" => false, "error" => "DB update failed"]);
        }
        $stmt->close();
    } else {
        echo json_encode(["success" => false, "error" => "Invalid input"]);
    }
} else {
    echo json_encode(["success" => false, "error" => "Invalid request method"]);
}
?><?php
require_once '../Backend/db_connect.php';  // Include your database connection

// SQL Query to fetch all processed orders and related data
$sql = "
    SELECT
        poi.id AS item_id,
        po.id AS order_id,
        poi.quantity,
        poi.total_price,
        poi.status,
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
    <link rel="stylesheet" href="../Frontend/css/styles.css"/>
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




<!-- Display Menu Button -->
<div class="mb-3 text-end">
    <button class="btn btn-dark" onclick="displayMenu()">Display Menu</button>
</div>

<!-- Menu Section (Populated by JS) -->
<div id="menu-section" style="display: none;"></div>




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
                                <div class="btn-group status-btn-group" data-item-id="<?php echo $order['item_id']; ?>">
                                    <button class="btn btn-sm status-btn <?php echo ($order['status'] == 'Pending') ? 'btn-warning active' : 'btn-outline-warning'; ?>" data-status="Pending">Pending</button>
                                    <button class="btn btn-sm status-btn <?php echo ($order['status'] == 'Prepared') ? 'btn-primary active' : 'btn-outline-primary'; ?>" data-status="Prepared">Prepared</button>
                                    <button class="btn btn-sm status-btn <?php echo ($order['status'] == 'Served') ? 'btn-success active' : 'btn-outline-success'; ?>" data-status="Served">Served</button>
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


<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.status-btn-group').forEach(group => {
            group.addEventListener('click', function (e) {
                if (e.target.classList.contains('status-btn')) {
                    const button = e.target;
                    const newStatus = button.getAttribute('data-status');
                    const itemId = group.getAttribute('data-item-id');

                    fetch('../Backend/update_order_status.php', {  // Changed path
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `item_id=${itemId}&status=${newStatus}`
                    })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                group.querySelectorAll('.status-btn').forEach(btn => {
                                    btn.classList.remove('btn-warning', 'btn-outline-warning', 'btn-primary', 'btn-outline-primary', 'btn-success', 'btn-outline-success', 'active');
                                    if (btn.getAttribute('data-status') === 'Pending') {
                                        btn.classList.add('btn-outline-warning');
                                    } else if (btn.getAttribute('data-status') === 'Prepared') {
                                        btn.classList.add('btn-outline-primary');
                                    } else if (btn.getAttribute('data-status') === 'Served') {
                                        btn.classList.add('btn-outline-success');
                                    }
                                });

                                button.classList.remove('btn-outline-warning', 'btn-outline-primary', 'btn-outline-success');
                                button.classList.add('active');
                                if (newStatus === 'Pending') button.classList.add('btn-warning');
                                if (newStatus === 'Prepared') button.classList.add('btn-primary');
                                if (newStatus === 'Served') button.classList.add('btn-success');
                            } else {
                                alert('Failed to update status: ' + data.error);
                            }
                        })
                        .catch(err => {
                            console.error(err);
                            alert('An error occurred.');
                        });
                }
            });
        });
    });
</script>


<!-- JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../Frontend/js/script.js"></script>
 </body>
</html>

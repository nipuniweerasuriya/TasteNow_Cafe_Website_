<?php
session_start();  // Start the session to access session variables
require_once '../Backend/db_connect.php';  // Include your database connection file

// Retrieve user_id from session (make sure the user is logged in and session is set)
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if the user is not logged in
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];  // Dynamically set user ID from session

// SQL Query to fetch the orders and related data
$sql = "
    SELECT
        po.id AS order_id,
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
    WHERE po.user_id = ? ORDER BY po.order_date DESC";

// Prepare and execute the query
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id); // Bind the user_id to the query
$stmt->execute();
$result = $stmt->get_result();

// Check for any errors
if ($stmt->error) {
    echo "SQL Error: " . $stmt->error;
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>

    <!-- Preconnects -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!-- Load Poppins & Roboto -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700&family=Roboto:wght@300;400;500&display=swap"
          rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&display=swap"
          rel="stylesheet">

    <!-- Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined&display=swap" rel="stylesheet"/>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/styles.css"/>
</head>
<body class="common-page" id="profile-page">

<!-- Navbar -->
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

<!-- Profile Content -->
<div class="container">
    <div class="profile-layout">

        <!-- Profile Sidebar -->
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

        <!-- Present Order Section -->
        <div class="order-container">
            <div class="order-actions">
                <button type="button" class="btn-pending">PENDING</button>
                <button type="button" class="btn-prepared">PREPARED</button>
                <button type="button" class="btn-served">SERVED</button>
                <button type="button" class="btn-update">UPDATE</button>
                <button type="button" class="btn-cancel">CANCEL</button>
            </div>

            <!-- Orders -->
            <div class="order-items-container bg-white p-3 mb-3">
                <?php if ($result->num_rows > 0): ?>
                    <!-- Loop through the orders and display them -->
                    <?php while ($order = $result->fetch_assoc()): ?>
                        <div class="d-flex align-items-start gap-3 cart-item">
                            <input type="checkbox" class="mt-2">
                            <img src="../Backend/uploads<?php echo $order['item_image']; ?>" alt="Product" style="width: 100px; height: auto;">
                            <div class="flex-grow-1">
                                <p class="item-title"><?php echo $order['item_name']; ?></p>
                                <p class="custom-text-danger">Only <?php echo $order['quantity']; ?> item(s) we have now</p>
                                <div class="customizations">
                                    <?php if (!empty($order['variant_name'])): ?>
                                        <p><span>Variant:</span> <?php echo $order['variant_name']; ?> (+Rs.<?php echo $order['addon_price']; ?>)</p>
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
                                    <span id="table-number" class="table-number"><?php echo $order['table_number']; ?></span>
                                </div>
                                <div class="qty">
                                    <span id="qty" class="table-number"><?php echo $order['quantity']; ?></span>
                                </div>
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

<!-- JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/script.js"></script>

</body>
</html>


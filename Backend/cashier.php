<?php
// Connect to DB
include '../Backend/db_connect.php';

// Get search query if any
$search_order_id = $_GET['search_order_id'] ?? '';

// SQL query to get only 'Served' items that are not yet paid
$query = "
SELECT 
    poi.id AS order_item_id,
    po.id AS order_id,
    po.table_number,
    po.order_date,
    ci.name AS item_name,
    ci.variant,
    ci.quantity,
    poi.total_price,
    mv.variant_name,
    mv.price AS variant_price,
    ci.addons,
    po.user_id
FROM processed_order_items poi
JOIN processed_order po ON poi.order_id = po.id
JOIN cart_items ci ON poi.cart_item_id = ci.id
LEFT JOIN menu_variants mv ON ci.variant = mv.id
WHERE poi.status = 'Served'
";

// Add a condition to search by order ID if a search term is provided
if ($search_order_id) {
    $query .= " AND po.id LIKE '%" . mysqli_real_escape_string($conn, $search_order_id) . "%'";
}

$query .= " ORDER BY po.order_date DESC";

$result = mysqli_query($conn, $query);

// Error check
if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cashier</title>

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
            <a class="navbar-brand logo-wiggle" href="index.php">TASTENOW</a>
        </div>
        <div class="d-flex align-items-center ms-3">
            <a href="../Backend/logout.php" class="text-decoration-none text-dark d-flex align-items-center">
                <span class="material-symbols-outlined icon-logout me-2">logout</span>
            </a>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="container mt-4">
    <h2 class="mb-4">Cashier Orders (Served)</h2>

    <!-- Search Form -->
    <form method="GET" action="cashier.php" class="mb-3">
        <div class="input-group">
            <input type="text" class="form-control" name="search_order_id" placeholder="Search by Order ID"
                   value="<?php echo isset($_GET['search_order_id']) ? htmlspecialchars($_GET['search_order_id']) : ''; ?>">
            <button class="btn btn-outline-secondary" type="submit">Search</button>
        </div>
    </form>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">Payment processed successfully!</div>
    <?php endif; ?>

    <?php
    $found = false;
    while ($row = mysqli_fetch_assoc($result)) {
        $found = true;
        ?>
        <div class="card mb-3 p-3 shadow-sm">
            <div class="row">
                <div class="col-md-8">
                    <h5><?php echo htmlspecialchars($row['item_name']); ?></h5>
                    <p>
                        Variant: <?php echo htmlspecialchars($row['variant_name']); ?><br>
                        Add-ons: <?php echo htmlspecialchars($row['addons']); ?><br>
                        Qty: <?php echo htmlspecialchars($row['quantity']); ?><br>
                        Table: <?php echo htmlspecialchars($row['table_number']); ?><br>
                        Order ID: <?php echo htmlspecialchars($row['order_id']); ?><br>
                        Date: <?php echo htmlspecialchars($row['order_date']); ?><br>
                        <strong>Total: Rs. <?php echo number_format($row['total_price'], 2); ?></strong>
                    </p>
                </div>
                <div class="col-md-4">
                    <form method="POST" action="cashier_payment_process.php">
                        <input type="hidden" name="order_item_id" value="<?php echo $row['order_item_id']; ?>">
                        <div class="mb-2">
                            <label>Customer Paid (Rs.):</label>
                            <input type="number" class="form-control" step="0.01" name="paid_amount" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Submit Payment</button>
                    </form>
                </div>
            </div>
        </div>
    <?php } ?>

    <?php if (!$found): ?>
        <div class="alert alert-warning">No served orders found.</div>
    <?php endif; ?>
</div>

<!-- JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

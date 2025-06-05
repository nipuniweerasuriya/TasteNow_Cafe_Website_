<?php
session_start();
include '../Backend/db_connect.php';

// Store and clear session messages
$success_message = $_SESSION['success'] ?? '';
$error_message = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);
$query = "
SELECT 
    poi.id AS order_item_id,
    po.id AS order_id,
    po.table_number,
    po.created_at AS order_date,
    poi.item_name,
    poi.price,
    poi.quantity,
    poi.status,
    poi.cancellation_penalty,
    po.user_id,
    po.payment_status
FROM processed_order_items poi
JOIN processed_order po ON poi.order_id = po.id
WHERE 
    po.payment_status = 'Not Paid' AND (
        poi.status = 'Served' 
        OR (poi.status = 'Canceled' AND poi.cancellation_penalty > 0)
    )
ORDER BY po.created_at DESC
";


$result = mysqli_query($conn, $query);

// Error check
if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}

// Group results by order_id
$orders = [];
while ($row = mysqli_fetch_assoc($result)) {
    $orderId = $row['order_id'];

    // Initialize order group if not already set
    if (!isset($orders[$orderId])) {
        $orders[$orderId] = [
            'order_id' => $orderId,
            'table_number' => $row['table_number'],
            'order_date' => $row['order_date'],
            'payment_status' => $row['payment_status'],
            'total_price' => 0,
            'items' => []
        ];
    }

    // Add the item to the order
    $orders[$orderId]['items'][] = [
        'order_item_id' => $row['order_item_id'],
        'item_name' => $row['item_name'],
        'price' => $row['price'],
        'quantity' => $row['quantity'],
        'status' => $row['status']
    ];

    // Update total price
    $orders[$orderId]['total_price'] += $row['price'] * $row['quantity'];
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Cashier</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700&family=Roboto:wght@300;400;500&display=swap"
          rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&display=swap"
          rel="stylesheet">

    <!-- Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">


    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="../Frontend/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

</head>
<body class="common-page" id="cashier-page">

<div class="navbar">
    <div class="navbar-container">
        <div class="navbar-brand">
            <a class="navbar-brand logo-wiggle" href="index.php">TASTENOW</a>
        </div>

        <div class="search-container">
            <i class="fas fa-search"></i>
            <input type="text" id="searchInput" placeholder="Search by Order Id">
        </div>

        <div class="d-flex align-items-center ms-3">
            <a href="../Backend/logout.php" class="text-decoration-none text-dark d-flex align-items-center">
                <span class="material-symbols-outlined icon-logout me-2">logout</span>
            </a>
        </div>
    </div>
</div>

<div class="container mt-4">
    <h3 class="heading mb-4">Cashier Orders</h3>

    <!-- SEARCH FORM REMOVED -->

    <?php if (!empty($success_message)): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
    <?php endif; ?>

    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
    <?php endif; ?>

    <?php if (!empty($orders)): ?>
        <?php foreach ($orders as $order): ?>
            <div class="card mb-3 p-3 shadow-sm">
                <h5>Order ID: <?php echo htmlspecialchars($order['order_id']); ?></h5>
                <p>Table: <?php echo htmlspecialchars($order['table_number']); ?></p>
                <p>Date: <?php echo htmlspecialchars($order['order_date']); ?></p>

                <div class="table-responsive cashier-table-responsive">
                    <table class="table">
                        <thead>
                        <tr>
                            <th>Item Name</th>
                            <th>Qty</th>
                            <th>Price (Rs.)</th>
                            <th>Total (Rs.)</th>
                            <th>Payment</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        $total_amount = 0;
                        foreach ($order['items'] as $item):
                            $item_total = $item['price'] * $item['quantity'];

                            if ($item['status'] === 'Canceled') {
                                $item_total *= 0.10; // Only 10% charged
                            }

                            $total_amount += $item_total;
                            ?>
                            <tr<?php if ($item['status'] === 'Canceled') echo ' class="table-warning"'; ?>>
                                <td>
                                    <?php echo htmlspecialchars($item['item_name']); ?>
                                    <?php if ($item['status'] === 'Canceled'): ?>
                                        <span class="badge bg-black ms-2" style="border-radius: 3px">Canceled</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                                <td><?php echo number_format($item['price'], 2); ?></td>
                                <td>
                                    <?php
                                    echo number_format($item_total, 2);
                                    if ($item['status'] === 'Canceled') echo " (10%)";
                                    ?>
                                </td>
                                <td></td>
                            </tr>
                        <?php endforeach; ?>

                        <tr>
                            <td colspan="3" class="text-end fw-bold">Total:</td>
                            <td class="fw-bold"><?php echo number_format($total_amount, 2); ?> Rs.</td>
                            <td>
                                <!-- Place this inside each order card -->
                                <form method="POST" action="cashier_payment_process.php" class="mt-3">
                                    <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                    <input type="hidden" name="total_amount" value="<?php echo $total_amount; ?>">
                                    <div class="input-group">
                                        <span class="input-group-text">Given Rs.</span>
                                        <input type="number" name="given_money" class="form-control" required>
                                        <button type="submit" class="btn btn-success">Pay</button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="alert alert-warning">No served orders found.</div>
    <?php endif; ?>



    <a href="#" class="back-to-top" id="backToTopBtn">
        <span class="material-icons">arrow_upward</span>
    </a>

</div>



<script>
    document.getElementById('searchInput').addEventListener('input', function () {
        const filter = this.value.toLowerCase();
        const orderCards = document.querySelectorAll('.card');

        orderCards.forEach(card => {
            const orderId = card.querySelector('h5')?.textContent.toLowerCase() || '';
            const tableNumber = card.querySelector('p')?.textContent.toLowerCase() || '';

            // Show card if filter matches Order ID or Table Number
            if (orderId.includes(filter) || tableNumber.includes(filter)) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });
    });

    // Back to top btn
    const backToTopBtn = document.getElementById("backToTopBtn");

    window.addEventListener("scroll", () => {
        if (document.documentElement.scrollTop > 500) {
            backToTopBtn.style.display = "block";
        } else {
            backToTopBtn.style.display = "none";
        }
    });

    backToTopBtn.addEventListener("click", function (e) {
        e.preventDefault();
        window.scrollTo({
            top: 0,
            behavior: "smooth"
        });
    });
</script>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script src="../Frontend/js/script.js"></script>
</body>
</html>

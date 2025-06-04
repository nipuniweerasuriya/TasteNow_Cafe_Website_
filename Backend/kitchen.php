<?php
require_once '../Backend/db_connect.php';  // Include your database connection

// SQL Query to fetch all processed orders and related data (excluding canceled items)
$sql = "
    SELECT
        poi.id AS item_id,
        poi.order_id,
        poi.item_name,
        poi.status,
        poi.price,
        poi.quantity,
        poi.image_url,
        po.table_number,
        po.total_price,
        po.created_at
    FROM processed_order_items poi
    JOIN processed_order po ON poi.order_id = po.id
    WHERE DATE(po.created_at) = CURDATE()
      AND poi.status != 'Canceled'
      AND EXISTS (
          SELECT 1
          FROM processed_order_items p
          WHERE p.order_id = po.id
            AND p.status != 'Canceled'
      )
    ORDER BY 
        CASE poi.status
            WHEN 'Pending' THEN 1
            WHEN 'Preparing' THEN 2
            WHEN 'Prepared' THEN 3
            WHEN 'Served' THEN 4
            ELSE 5
        END,
        po.created_at DESC
";

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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700&family=Roboto:wght@300;400;500&display=swap"
          rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&display=swap"
          rel="stylesheet">

    <!-- Bootstrap and Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="../Frontend/css/styles.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">


</head>
<body class="common-page" id="kitchen-page">

<!-- Navbar -->
<div class="navbar">
    <div class="navbar-container">
        <div class="navbar-brand">
            <a class="navbar-brand logo-wiggle" href="index.php">TASTENOW</a>
        </div>

        <div class="search-container">
            <i class="fas fa-search"></i>
            <input type="text" id="searchInput" placeholder="Search by Order ID, Table,Order Status or Item Name">
        </div>


        <div class="d-flex align-items-center ms-3">
            <a href="../Backend/logout.php" class="text-decoration-none text-dark d-flex align-items-center">
                <span class="material-symbols-outlined icon-logout me-2">logout</span>
            </a>
        </div>
    </div>
</div>


<?php
$orders = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $orderId = $row['order_id'];
        if (!isset($orders[$orderId])) {
            $orders[$orderId] = [
                'order_id' => $orderId,
                'table_number' => $row['table_number'],
                'created_at' => $row['created_at'],
                'total_price' => $row['total_price'],
                'items' => []
            ];
        }
        $orders[$orderId]['items'][] = $row;
    }
}
?>

<div class="container">
    <div class="profile-layout">
        <div class="order-container w-100">
            <h3 class="heading mb-4">Today Orders</h3>

            <div class="order-items-container p-3 mb-3">
                <div class="menu-table-container" style="background-color: white">
                    <!-- Display Menu Button -->
                    <div class="mb-3 text-end">
                        <button class="btn menu-btn" onclick="displayMenu()">Menu</button>
                    </div>

                    <!-- Menu Section (Initially Hidden) - Will show below the button -->
                    <div id="menu-section" style="display: none;"></div>

                </div>
                <?php if (!empty($orders)): ?>
                    <?php foreach ($orders as $order): ?>
                        <div class="order-card p-3 mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <strong class="order-id">Order ID:</strong> #<?php echo $order['order_id']; ?><br>
                                    <strong class="table-number">Table:</strong> <?php echo $order['table_number']; ?><br>

                                    <strong>Date:</strong> <?php echo date('Y-m-d H:i', strtotime($order['created_at'])); ?>
                                </div>
                                <div class="text-end">
                                    <strong>Total Price:</strong><br>
                                    <span class="fs-5">Rs. <?php echo number_format($order['total_price'], 2); ?></span>
                                </div>
                            </div>
                            <hr>
                            <?php foreach ($order['items'] as $item): ?>
                                <div class="d-flex align-items-start gap-3 cart-item border-bottom pb-3 mb-3 <?= ($item['status'] === 'Cancelled') ? 'cancelled-highlight' : '' ?>">
                                    <div class="flex-grow-1">
                                        <div class="qty-price-container">
                                            <img src="uploads/<?php echo htmlspecialchars($item['image_url']); ?>"
                                                 style="width: 100px;" alt="">
                                            <p class="item-title mb-1">
                                                <?php echo htmlspecialchars($item['item_name']); ?>
                                                <?php if ($item['status'] === 'Cancelled'): ?>
                                                    <span class="cancelled-badge">Cancelled</span>
                                                <?php endif; ?>
                                            </p>
                                            <div>Qty: <?php echo $item['quantity']; ?></div>
                                            <div>Rs. <?php echo number_format($item['price'], 2); ?></div>
                                        </div>
                                    </div>

                                    <?php if ($item['status'] === 'Canceled'): ?>
                                        <button
                                                class="btn btn-danger cancelled-remove-btn"
                                                data-item-id="<?php echo $item['item_id']; ?>"
                                                title="Remove this cancelled item"
                                        >
                                            Cancelled
                                        </button>
                                    <?php else: ?>
                                        <div class="btn-group status-btn-group"
                                             data-item-id="<?php echo $item['item_id']; ?>">
                                            <button class="btn btn-sm status-btn <?= ($item['status'] == 'Pending') ? 'btn-warning active' : 'btn-outline-warning'; ?>"
                                                    data-status="Pending">Pending
                                            </button>
                                            <button class="btn btn-sm status-btn <?= ($item['status'] == 'Preparing') ? 'btn-info active' : 'btn-outline-info'; ?>"
                                                    data-status="Preparing">Preparing
                                            </button>
                                            <button class="btn btn-sm status-btn <?= ($item['status'] == 'Prepared') ? 'btn-primary active' : 'btn-outline-primary'; ?>"
                                                    data-status="Prepared">Prepared
                                            </button>
                                            <button class="btn btn-sm status-btn <?= ($item['status'] == 'Served') ? 'btn-success active' : 'btn-outline-success'; ?>"
                                                    data-status="Served">Served
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>

                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No processed orders found.</p>
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

                    fetch('../Backend/update_order_status.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: `item_id=${itemId}&status=${newStatus}`
                    })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                // Remove all status-related classes and 'active' from all buttons in the group
                                group.querySelectorAll('.status-btn').forEach(btn => {
                                    btn.classList.remove(
                                        'btn-warning', 'btn-outline-warning',
                                        'btn-info', 'btn-outline-info',
                                        'btn-primary', 'btn-outline-primary',
                                        'btn-success', 'btn-outline-success',
                                        'active'
                                    );

                                    // Add outline class according to status
                                    const status = btn.getAttribute('data-status');
                                    if (status === 'Pending') btn.classList.add('btn-outline-warning');
                                    if (status === 'Preparing') btn.classList.add('btn-outline-info');
                                    if (status === 'Prepared') btn.classList.add('btn-outline-primary');
                                    if (status === 'Served') btn.classList.add('btn-outline-success');
                                });

                                // Remove outline class and add active + filled class to clicked button
                                button.classList.remove('btn-outline-warning', 'btn-outline-info', 'btn-outline-primary', 'btn-outline-success');
                                button.classList.add('active');
                                if (newStatus === 'Pending') button.classList.add('btn-warning');
                                if (newStatus === 'Preparing') button.classList.add('btn-info');
                                if (newStatus === 'Prepared') button.classList.add('btn-primary');
                                if (newStatus === 'Served') button.classList.add('btn-success');
                            } else {
                                alert('Failed to update status: ' + data.message);
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


    function displayMenu() {
        fetch('display_menu_items.php')
            .then(response => response.json())
            .then(data => {
                const menuSection = document.getElementById('menu-section');

                menuSection.innerHTML = `
                <span class="close-icon" onclick="closeMenuSection()">&times;</span>
                <h3 class="heading mt-4 mb-3">Menu Items</h3>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="menuTable">
                        <thead class="table-dark">
                            <tr>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Price</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="menuItemsTableBody">
                            <!-- Items will be added here dynamically -->
                        </tbody>
                    </table>
                </div>
            `;

                menuSection.style.display = 'block';

                const tableBody = document.getElementById('menuItemsTableBody');
                tableBody.innerHTML = '';

                if (data.length === 0) {
                    tableBody.innerHTML = '<tr><td colspan="4">No menu items found.</td></tr>';
                } else {
                    data.forEach(item => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                        <td><img src="uploads/${item.image_url}" alt="${item.name}" style="max-width: 100px;"></td>
                        <td>${item.name}</td>
                        <td>Rs. ${item.price}</td>
                        <td><button class="btn btn-sm btn-danger" onclick="deleteMenuItem(${item.id})">Delete</button></td>
                    `;
                        tableBody.appendChild(row);
                    });
                }
            })
            .catch(error => {
                console.error('Error fetching menu items:', error);
                const menuSection = document.getElementById('menu-section');
                menuSection.innerHTML = `<div class="alert alert-danger">Failed to load menu items.</div>`;
                menuSection.style.display = 'block';
            });
    }


    function closeMenuSection() {
        document.getElementById('menu-section').style.display = 'none';
    }


    function deleteMenuItem(id) {
        if (confirm('Are you sure you want to delete this menu item?')) {
            fetch('delete_menu_item.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'id=' + encodeURIComponent(id)
            })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        alert('Menu item deleted successfully.');
                        displayMenu(); // Refresh the table
                    } else {
                        alert('Failed to delete: ' + (result.message || 'Unknown error.'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while deleting the menu item.');
                });
        }
    }


    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('searchInput');

        if (searchInput) {
            searchInput.addEventListener('input', function () {
                const filter = this.value.toLowerCase();

                // === FILTER ORDER CARDS ===
                const orderCards = document.querySelectorAll('.order-card');
                orderCards.forEach(card => {
                    const orderId = card.querySelector('.order-id')?.nextSibling?.textContent.trim().toLowerCase() || '';
                    const tableNumber = card.querySelector('.table-number')?.nextSibling?.textContent.trim().toLowerCase() || '';

                    let statusMatch = false;
                    card.querySelectorAll('.status-btn-group .status-btn.active').forEach(btn => {
                        if (btn.textContent.toLowerCase().includes(filter)) {
                            statusMatch = true;
                        }
                    });

                    const matches = orderId.includes(filter) || tableNumber.includes(filter) || statusMatch;
                    card.style.display = matches ? '' : 'none';
                });

                // === FILTER MENU TABLE ROWS ===
                const menuRows = document.querySelectorAll('#menuItemsTableBody tr');
                menuRows.forEach(row => {
                    const itemName = row.querySelector('td:nth-child(2)')?.textContent.trim().toLowerCase() || '';
                    row.style.display = itemName.includes(filter) ? '' : 'none';
                });
            });
        }
    });


</script>


<!-- JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../Frontend/js/script.js"></script>
</body>
</html>

<?php
session_start();
require_once '../Backend/db_connect.php';  // Ensure your database connection file is included

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");  // Redirect to login if the user is not logged in
    exit();
}

// Fetch user details from the session
$user_id = $_SESSION['user_id'];
$filter = $_GET['filter'] ?? '';  // Get the filter from URL if set

// Query to fetch user name and email from the database
$sql = "SELECT name, email FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();

// Fetch user data
$stmt->bind_result($name, $email);
$stmt->fetch();
$stmt->close();

// Handle if no user found
if (!$name || !$email) {
    echo "User not found.";
    exit();
}

// Get initials from the user's name
$initials = strtoupper(substr($name, 0, 1) . substr(strrchr($name, ' '), 1, 1));

// Determine filter
$filter = $_GET['filter'] ?? 'today';

$sql = "
    SELECT
        po.id AS order_id,
        poi.id AS order_item_id,
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
    WHERE po.user_id = ?
";

// Apply filter logic
if ($filter === 'history') {
    // Order History = past orders
    $sql .= " AND DATE(po.order_date) < CURDATE()";
} elseif ($filter === 'canceled') {
    // Canceled = only canceled orders
    $sql .= " AND poi.status = 'Canceled'";
} else {
    // Default or reset = today's orders
    $sql .= " AND DATE(po.order_date) = CURDATE()";
}

$sql .= " ORDER BY po.order_date DESC";



$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Handle SQL errors
if ($stmt->error) {
    echo "SQL Error: " . $stmt->error;
}




$tab = $_GET['tab'] ?? '';

$bookings = [];
if ($tab === 'bookings') {
    $booking_sql = "SELECT booking_id, name, phone, email, number_of_people, booking_date, booking_time, duration, special_request, created_at FROM table_bookings WHERE user_id = ? ORDER BY booking_date DESC, booking_time DESC";
    $booking_stmt = $conn->prepare($booking_sql);
    $booking_stmt->bind_param('i', $user_id);
    $booking_stmt->execute();
    $booking_result = $booking_stmt->get_result();
    while ($row = $booking_result->fetch_assoc()) {
        $bookings[] = $row;
    }
    $booking_stmt->close();
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
    <link rel="stylesheet" href="../Frontend/css/styles.css"/>
    <style>
        /* Search Containers */
        .search-container {
            position: relative;
            width: 200%;
            margin-top: 0.1rem;
            margin-bottom: 0.1rem;
            font-size: 12px;
        }

        .search-container i {
            position: absolute;
            top: 50%;
            left: 12px;
            transform: translateY(-50%);
            color: #fac003 !important;
        }

        #searchInput,
        #searchBar {
            width: 100%;
            padding: 8px 12px 8px 36px;
            font-size: 12px;
            border: 1px solid #fac003;
            border-radius: 3px;
            outline: none;
            transition: 0.3s ease;
        }

        #searchInput::placeholder,
        #searchBar::placeholder {
            color: #fac003;
        }


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

        <div class="search-container">
            <i class="fas fa-search"></i>
            <input type="text" id="searchInput" placeholder="Search by Table No, Order Id, Date, or Status">
        </div>


        <div class="d-flex align-items-center ms-3">
            <a href="logout.php" class="text-decoration-none text-dark d-flex align-items-center">
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
                    <?= $initials ?>  <!-- Display initials -->
                </div>
                <div>
                    <h6 class="mb-0"><?= htmlspecialchars($name) ?></h6>  <!-- Display name -->
                    <small class="text-muted"><?= htmlspecialchars($email) ?></small>  <!-- Display email -->
                </div>
            </div> <!-- End of Profile Info -->

            <div class="profile-actions">
                <?php
                $isHistory = ($filter === 'history');
                $historyLink = $isHistory ? 'profile.php' : 'profile.php?filter=history';
                $historyLabel = $isHistory ? 'Today Orders' : 'Order History';
                ?>
                <div class="profile-action-item <?php echo $isHistory ? 'active' : ''; ?>">
                    <a href="<?= $historyLink ?>" class="text-decoration-none">
                        <small><?= $historyLabel ?></small>
                    </a>
                </div>


                <?php
                $isCanceled = ($filter === 'canceled');
                $canceledLink = $isCanceled ? 'profile.php' : 'profile.php?filter=canceled';
                $canceledLabel = $isCanceled ? 'Today Orders' : 'Canceled';
                ?>
                <div class="profile-action-item <?php echo $isCanceled ? 'active' : ''; ?>">
                    <a href="<?= $canceledLink ?>" class="text-decoration-none">
                        <small><?= $canceledLabel ?></small>
                    </a>
                </div>



                <div class="profile-action-item <?php echo ($_GET['tab'] ?? '') === 'bookings' ? 'active' : ''; ?>">
                    <a href="profile.php?tab=bookings" class="text-decoration-none">
                        <small>Table Bookings</small>
                    </a>
                </div>


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
                                <div class="order-date">
                                    <span><?php echo $order['order_date']; ?></span>
                                </div>
                                <div class="item-price mb-2">
                                    <span class="price">Rs. <?php echo $order['total_price']; ?></span>
                                </div>
                                <div class="order-id">
                                    <span>Order Id <?php echo $order['order_id']; ?></span>
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
                                        <a href="update-order-item.php?item_id=<?php echo $order['order_item_id']; ?>" class="btn btn-sm btn-outline-primary">
                                            Update
                                        </a>


                                        <!-- Cancel Button -->
                                        <form method="POST" action="cancel-order-item.php" style="display:inline;">
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

    <div class="booking-table-container bg-white p-3 mb-3" id="booking-table-container">
        <?php if ($tab === 'bookings'): ?>
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="mb-0">Your Table Bookings</h4>
                <button class="btn btn-sm btn-outline-secondary" id="close-booking-table" title="Close">
                    <i class="bi bi-x-lg"></i> <!-- Bootstrap icon -->
                </button>
            </div>

            <?php if (count($bookings) > 0): ?>
                <table class="table table-bordered">
                    <thead>
                    <tr>
                        <th>Booking ID</th>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>People</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Duration (hrs)</th>
                        <th>Special Request</th>
                        <th>Booked At</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($bookings as $booking): ?>
                        <tr id="booking-row-<?= $booking['booking_id'] ?>">
                            <td><?= htmlspecialchars($booking['booking_id']) ?></td>
                            <td><?= htmlspecialchars($booking['name']) ?></td>
                            <td><?= htmlspecialchars($booking['phone']) ?></td>
                            <td><?= htmlspecialchars($booking['email']) ?></td>
                            <td><?= htmlspecialchars($booking['number_of_people']) ?></td>
                            <td><?= htmlspecialchars($booking['booking_date']) ?></td>
                            <td><?= htmlspecialchars(substr($booking['booking_time'], 0, 5)) ?></td>
                            <td><?= htmlspecialchars($booking['duration']) ?></td>
                            <td><?= htmlspecialchars($booking['special_request']) ?></td>
                            <td><?= htmlspecialchars($booking['created_at']) ?></td>
                            <td>
                                <button class="btn btn-sm btn-danger cancel-booking-btn" data-id="<?= $booking['booking_id'] ?>">
                                    Cancel
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No table bookings found.</p>
            <?php endif; ?>

        <?php else: ?>
        <?php endif; ?>
    </div>
</div>




<script>
    document.getElementById('searchInput').addEventListener('input', function () {
        const searchTerm = this.value.toLowerCase();

        // -------- Handle Order Items --------
        const orderContainer = document.querySelector('.order-items-container');
        if (orderContainer) {
            const orderItems = Array.from(orderContainer.querySelectorAll('.cart-item'));

            const matchedOrders = [];
            const unmatchedOrders = [];

            orderItems.forEach(item => {
                const table = item.querySelector('.table-number')?.innerText.toLowerCase() || '';
                const orderId = item.querySelector('.order-id')?.innerText.toLowerCase() || '';
                const status = item.querySelector('.status-badge')?.innerText.toLowerCase() || '';
                const content = item.innerText.toLowerCase(); // Fallback for date match

                const isMatch = table.includes(searchTerm) || orderId.includes(searchTerm) || status.includes(searchTerm) || content.includes(searchTerm);

                if (isMatch) {
                    item.style.display = '';
                    matchedOrders.push(item);
                } else {
                    item.style.display = searchTerm ? 'none' : '';
                    unmatchedOrders.push(item);
                }
            });

            orderContainer.innerHTML = '';
            matchedOrders.concat(unmatchedOrders).forEach(item => orderContainer.appendChild(item));
        }

        // -------- Handle Booking Rows --------
        const bookingTableBody = document.querySelector('#booking-table-container tbody');
        if (bookingTableBody) {
            const rows = Array.from(bookingTableBody.querySelectorAll('tr'));
            rows.forEach(row => {
                const bookingId = row.cells[0]?.innerText.toLowerCase() || '';
                const isMatch = bookingId.includes(searchTerm);
                row.style.display = isMatch || searchTerm === '' ? '' : 'none';
            });
        }
    });



    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.cancel-booking-btn').forEach(function(button) {
            button.addEventListener('click', function() {
                if (!confirm('Are you sure you want to cancel this booking?')) return;

                const bookingId = this.getAttribute('data-id');
                const row = document.getElementById('booking-row-' + bookingId);

                fetch('cancel_booking.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'booking_id=' + encodeURIComponent(bookingId)
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            // Remove row from table
                            if (row) row.remove();
                            alert('Booking cancelled successfully.');
                        } else {
                            alert('Error: ' + data.message);
                        }
                    })
                    .catch(() => alert('An error occurred while cancelling booking.'));
            });
        });
    });

    document.getElementById('close-booking-table').addEventListener('click', function () {
        const container = document.getElementById('booking-table-container');
        if (container) {
            container.remove(); // Completely removes the booking-table-container from DOM
        }
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../Frontend/js/script.js"></script>

</body>
</html>

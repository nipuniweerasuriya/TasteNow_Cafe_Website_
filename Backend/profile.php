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
$nameParts = explode(' ', $name);
$initials = strtoupper(substr($nameParts[0], 0, 1) . (isset($nameParts[1]) ? substr($nameParts[1], 0, 1) : ''));

// Determine filter
$filter = $_GET['filter'] ?? 'today';

// Build the SQL query to get order details
$sql = "
    SELECT
        po.id AS order_id,
        poi.id AS order_item_id,
        poi.status AS item_status,
        poi.quantity,
        poi.price AS total_price,
        poi.item_name,
        poi.image_url AS item_image,
        po.table_number,
        po.created_at AS order_date,
        po.payment_status
    FROM processed_order po
    JOIN processed_order_items poi ON po.id = poi.order_id
    WHERE po.user_id = ?
";

// Apply filter
if ($filter === 'history') {
    $sql .= " AND DATE(po.created_at) < CURDATE()";  // Past orders
} elseif ($filter === 'canceled') {
    $sql .= " AND poi.status = 'Canceled'";
} else {  // today or default
    $sql .= " AND DATE(po.created_at) = CURDATE()";
}

$sql .= " ORDER BY po.created_at DESC";

// Prepare and execute the statement
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

// ✅ GROUP ORDER ITEMS BY ORDER ID
$ordersGrouped = [];
while ($order = $result->fetch_assoc()) {
    $orderId = $order['order_id'];
    if (!isset($ordersGrouped[$orderId])) {
        $ordersGrouped[$orderId] = [
            'order_date' => $order['order_date'],
            'table_number' => $order['table_number'],
            'payment_status' => $order['payment_status'],
            'items' => []
        ];
    }
    $ordersGrouped[$orderId]['items'][] = $order;
}

// Check if tab is for bookings
$tab = $_GET['tab'] ?? '';
$bookings = [];

if ($tab === 'bookings') {
    $booking_sql = "SELECT booking_id, table_number, name, phone, email, number_of_people, booking_date, booking_time, duration, special_request, created_at FROM table_bookings WHERE user_id = ? ORDER BY booking_date DESC, booking_time DESC";
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
                    <?php if (!empty($ordersGrouped)): ?>
                        <?php foreach ($ordersGrouped as $orderId => $order): ?>
                            <div class="order-section" style="border: 1px solid #ccc; margin: 15px 0; padding: 10px;">
                                <h3>Order #<?= $orderId ?> (<?= $order['order_date'] ?>)</h3>
                                <p><strong>Table:</strong> <?= $order['table_number'] ?> | <strong>Payment:</strong> <?= $order['payment_status'] ?></p>
                                <ul style="list-style: none; padding-left: 0;">
                                    <?php foreach ($order['items'] as $item): ?>
                                        <li style="margin-bottom: 15px; border-bottom: 1px dashed #ccc; padding-bottom: 10px;">
                                            <div style="display: flex; align-items: center;">
                                                <img src="<?= $item['item_image'] ?>" alt="<?= $item['item_name'] ?>" style="width: 60px; height: 60px; object-fit: cover; margin-right: 10px;">
                                                <div>
                                                    <strong><?= $item['item_name'] ?></strong><br>
                                                    Quantity: <?= $item['quantity'] ?> | ₹<?= $item['total_price'] ?><br>
                                                    Status: <span style="font-weight: bold; color: <?= $item['item_status'] === 'Pending' ? 'orange' : ($item['item_status'] === 'Prepared' ? 'blue' : ($item['item_status'] === 'Served' ? 'green' : 'red')) ?>;">
                                <?= $item['item_status'] ?>
                            </span>
                                                    <br>

                                                    <?php if ($item['item_status'] == 'Pending'): ?>
                                                        <!-- Cancel Button -->
                                                        <form action="../Backend/cancel-order-item.php" method="post" style="display: inline-block; margin-top: 5px;">
                                                            <input type="hidden" name="order_item_id" value="<?= $item['order_item_id'] ?>">
                                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure to cancel this item?');">Cancel</button>
                                                        </form>

                                                        <!-- Update Button -->
                                                        <!-- Update Button -->
                                                        <form action="../Backend/update-order-item.php" method="get" style="display: inline-block; margin-left: 5px; margin-top: 5px;">
                                                            <input type="hidden" name="item_id" value="<?= $item['order_item_id'] ?>">
                                                            <button type="submit" class="btn btn-primary btn-sm">Update</button>
                                                        </form>

                                                    <?php else: ?>
                                                        <!-- Show disabled buttons or none -->
                                                        <span style="color: gray; font-size: 12px;">No actions available</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endforeach; ?>

                    <?php else: ?>
                        <p>No orders found for the selected filter.</p>
                    <?php endif; ?>

                <?php else: ?>
                    <p>No orders found for the selected filter.</p>
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
                        <th>Table Number</th>
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
                            <td><?= htmlspecialchars($booking['table_number']) ?></td>
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
        const searchQuery = this.value.toLowerCase();
        const orderSections = document.querySelectorAll('.order-section');

        orderSections.forEach(section => {
            const textContent = section.textContent.toLowerCase();
            if (textContent.includes(searchQuery)) {
                section.style.display = '';
            } else {
                section.style.display = 'none';
            }
        });
    });


    document.getElementById('bookingSearchInput').addEventListener('input', function () {
        const filter = this.value.toLowerCase();
        const rows = document.querySelectorAll('#bookingTable tbody tr');

        rows.forEach(row => {
            const bookingId = row.cells[0].textContent.toLowerCase();
            const tableNo = row.cells[1].textContent.toLowerCase();

            if (bookingId.includes(filter) || tableNo.includes(filter)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });



    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.cancel-booking-btn').forEach(function(button) {
            button.addEventListener('click', function() {
                if (!confirm('Are you sure you want to cancel this booking?')) return;

                const bookingId = this.getAttribute('data-id');
                const row = document.getElementById('booking-row-' + bookingId);
                const cancelButton = this;

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
                            // Option 1: Change button text and disable it
                            cancelButton.textContent = 'Cancelled';
                            cancelButton.disabled = true;
                            cancelButton.classList.remove('btn-danger'); // if using Bootstrap or similar
                            cancelButton.classList.add('btn-secondary'); // optional styling change

                            // Option 2: Or replace button completely with a non-clickable label
                            // const cancelledLabel = document.createElement('span');
                            // cancelledLabel.textContent = 'Cancelled';
                            // cancelledLabel.className = 'badge bg-secondary';
                            // cancelButton.replaceWith(cancelledLabel);

                            alert('Booking cancelled successfully.');
                        } else {
                            alert('Error: ' + data.message);
                        }
                    })
                    .catch(() => alert('An error occurred while cancelling the booking.'));
            });
        });

        document.getElementById('close-booking-table')?.addEventListener('click', function () {
            const container = document.getElementById('booking-table-container');
            if (container) container.remove();
        });
    });


</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../Frontend/js/script.js"></script>

</body>
</html>

<?php
session_start();
require_once '../Backend/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$filter = $_GET['filter'] ?? 'today';

// Fetch user details
$sql = "SELECT name, email FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$stmt->bind_result($name, $email);
$stmt->fetch();
$stmt->close();

if (!$name || !$email) {
    echo "User not found.";
    exit();
}

// Profile avatar
$nameParts = explode(' ', $name);
$initials = strtoupper(substr($nameParts[0], 0, 1) . (isset($nameParts[1]) ? substr($nameParts[1], 0, 1) : ''));

// Orders
$sql = "
    SELECT
        po.id AS order_id,
        poi.id AS order_item_id,
        poi.status AS item_status,
        poi.quantity,
        poi.price AS item_price,
        (poi.price * poi.quantity) AS item_total,
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
    $sql .= " AND DATE(po.created_at) < CURDATE()";
} elseif ($filter === 'canceled') {
    $sql .= " AND poi.status = 'Canceled'";
} else {
    $sql .= " AND DATE(po.created_at) = CURDATE()";
}

$sql .= " ORDER BY po.created_at DESC";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Group by order_id
$ordersGrouped = [];
while ($order = $result->fetch_assoc()) {
    $orderId = $order['order_id'];
    if (!isset($ordersGrouped[$orderId])) {
        $ordersGrouped[$orderId] = [
            'order_date' => $order['order_date'],
            'table_number' => $order['table_number'],
            'payment_status' => $order['payment_status'],
            'total_price' => 0,
            'items' => []
        ];
    }

    $ordersGrouped[$orderId]['items'][] = $order;

    //  Only add non-canceled items to total
    if (strtolower($order['item_status']) !== 'canceled') {
        $ordersGrouped[$orderId]['total_price'] += $order['item_total'];
    }
}


// Bookings tables
$tab = $_GET['tab'] ?? '';
$bookings = [];

if ($tab === 'bookings') {
    $booking_sql = "SELECT booking_id, user_id, table_number, name, phone, email, number_of_people, booking_date, booking_time, duration, special_request, status FROM table_bookings WHERE user_id = ? ORDER BY booking_date DESC, booking_time DESC";
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
                    <?= $initials ?> <!-- Display initials -->
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

        <!-- Orders Section -->
        <div class="order-items-container bg-white p-3 mb-3">
            <h3 class="order-section-heading">Today Orders</h3>
            <?php if ($result->num_rows > 0): ?>
                <?php if (!empty($ordersGrouped)): ?>
                    <?php foreach ($ordersGrouped as $orderId => $order): ?>
                        <div class="order-section">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px; font-size: 14px">
                                <div>
                                    <strong>Order ID:</strong> #<?= $orderId ?><br>
                                    <strong>Table:</strong> <?= $order['table_number'] ?><br>
                                    <strong>Date:</strong> <?= $order['order_date'] ?>
                                </div>

                                <div class="text-end" style="text-align: right;">
                                    <strong>Total Price:</strong> Rs. <?= number_format($order['total_price'], 2) ?><br>
                                    <?= $order['payment_status'] ?><br>
                                </div>
                            </div>
                            <strong style="margin-bottom: 15px; border-bottom: 1px solid #cccccc; padding-bottom: 10px;">
                                <hr>
                            </strong>

                            <ul style="list-style: none; padding-left: 0;">
                                <?php foreach ($order['items'] as $item): ?>

                                    <li style="margin-bottom: 15px; border-bottom: 1px solid #cccccc; padding-bottom: 10px;">
                                        <div style="display: flex; align-items: center;">
                                            <img src="<?= $item['item_image'] ?>" alt="<?= $item['item_name'] ?>"
                                                 style="width: 80px; height: 80px; object-fit: cover; margin-right: 10px;">
                                            <div>
                                                <strong><?= $item['item_name'] ?></strong> |
                                                Qty: <?= $item['quantity'] ?> |
                                                Rs.<?= $item['item_price'] ?> |
                                                <span class="<?= $statusClass ?>"
                                                      style="font-size: 0.9rem; padding: 5px 10px; color: #fac003;">
                                            <?= $item['item_status'] ?>
                                        </span>
                                                <br>

                                                <?php if (in_array($item['item_status'], ['Pending', 'Preparing'])): ?>
                                                    <!-- Cancel and Update buttons -->
                                                    <form action="../Backend/cancel-order-item.php" method="post"
                                                          style="display: inline-block; margin-top: 5px;">
                                                        <input type="hidden" name="order_item_id"
                                                               value="<?= $item['order_item_id'] ?>">
                                                        <button type="submit" class="btn-cancel btn-sm"
                                                                onclick="return confirm('Are you sure to cancel this item?');">
                                                            Cancel
                                                        </button>
                                                    </form>
                                                <?php else: ?>
                                                    <span class="no-actions">No actions available</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No Today Orders.</p>
                <?php endif; ?>
            <?php else: ?>
                <p>No Today Orders.</p>
            <?php endif; ?>

        </div>
    </div>

    <!-- Bookings Section -->
    <div class="container my-4" id="bookingContainer"
         style="<?= $tab === 'bookings' ? 'display:block;' : 'display:none;' ?>">
        <span class="close-icon" onclick=window.location='profile.php'>&times;</span>

        <h3 class="booking-table-heading mb-0">Your Table Bookings</h3>


        <div class="card-body p-3">
            <?php if (count($bookings) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle text-center">
                        <thead class="table-dark">
                        <tr>
                            <th>Booking ID</th>
                            <th>User ID</th>
                            <th>Table No</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Duration</th>
                            <th>No. of People</th>
                            <th>Special Request</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($bookings as $booking): ?>
                            <tr>
                                <td><?= $booking['booking_id'] ?></td>
                                <td><?= $booking['user_id'] ?></td>
                                <td><?= $booking['table_number'] ?></td>
                                <td><?= $booking['name'] ?></td>
                                <td><?= $booking['email'] ?></td>
                                <td><?= $booking['phone'] ?></td>
                                <td><?= $booking['booking_date'] ?></td>
                                <td><?= $booking['booking_time'] ?></td>
                                <td><?= $booking['duration'] ?></td>
                                <td><?= $booking['number_of_people'] ?></td>
                                <td><?= $booking['special_request'] ?: 'None' ?></td>
                                <td><?= $booking['status'] ?></td>


                                <td>
                                    <?php
                                    $bookingDateTime = strtotime($booking['booking_date'] . ' ' . $booking['booking_time']);
                                    $now = time();
                                    if ($bookingDateTime > $now):
                                        ?>
                                        <form action="../Backend/cancel_booking.php" method="post" class="d-inline"
                                              onsubmit="return confirm('Are you sure you want to cancel this booking?');">
                                            <input type="hidden" name="booking_id"
                                                   value="<?= $booking['booking_id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-danger" data-bs-toggle="tooltip"
                                                    title="Cancel Booking">
                                                <i class="bi bi-x-circle"></i> Cancel
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-muted small">N/A</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info mb-0" role="alert">
                    No table bookings found.
                </div>
            <?php endif; ?>

            <a href="#" class="back-to-top" id="backToTopBtn">
                <span class="material-icons">arrow_upward</span>
            </a>
        </div>


    </div>


</div>


<script>
    // Search function
    document.getElementById("unifiedSearchInput").addEventListener("keyup", function () {
        const searchValue = this.value.toLowerCase();

        // --- Reorder Orders Section ---
        const orderContainer = document.querySelector(".order-items-container");
        const orderSections = Array.from(document.querySelectorAll(".order-section"));

        const matchedOrders = [];
        const unmatchedOrders = [];

        orderSections.forEach(section => {
            const sectionText = section.innerText.toLowerCase();
            if (sectionText.includes(searchValue)) {
                section.style.display = "";
                matchedOrders.push(section);
            } else {
                section.style.display = "";
                unmatchedOrders.push(section);
            }
        });

        // Append matched first, then unmatched
        [...matchedOrders, ...unmatchedOrders].forEach(section => orderContainer.appendChild(section));

        // --- Reorder Bookings Table ---
        const bookingTableBody = document.querySelector("#bookingContainer table tbody");
        const bookingRows = Array.from(bookingTableBody.querySelectorAll("tr"));

        const matchedRows = [];
        const unmatchedRows = [];

        bookingRows.forEach(row => {
            const rowText = row.innerText.toLowerCase();
            if (rowText.includes(searchValue)) {
                row.style.display = "";
                matchedRows.push(row);
            } else {
                row.style.display = "";
                unmatchedRows.push(row);
            }
        });

        // Append matched first, then unmatched
        [...matchedRows, ...unmatchedRows].forEach(row => bookingTableBody.appendChild(row));
    });


    // Cancel booking
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.cancel-booking-btn').forEach(function (button) {
            button.addEventListener('click', function () {
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
                            // Change button text and disable it
                            cancelButton.textContent = 'Cancelled';
                            cancelButton.disabled = true;
                            cancelButton.classList.remove('btn-danger');
                            cancelButton.classList.add('btn-secondary');

                            alert('Booking cancelled successfully.');
                        } else {
                            alert('Error: ' + data.message);
                        }
                    })
                    .catch(() => alert('An error occurred while cancelling the booking.'));
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

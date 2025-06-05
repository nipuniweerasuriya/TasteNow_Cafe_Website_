<?php
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

include '../Backend/db_connect.php';

$user_id = $_SESSION['user_id'];

// Fetch user details
$query = "SELECT name, email FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) > 0) {
    $user = mysqli_fetch_assoc($result);
} else {
    echo "User not found!";
    exit();
}

// Extract initials
$names = explode(' ', $user['name']);
$initials = strtoupper(substr($names[0], 0, 1) . substr(end($names), 0, 1));

// ===================== HANDLE AJAX REQUESTS ===================== //

if (isset($_GET['load_orders'])) {
    header('Content-Type: application/json');
    $status = $_GET['status'] ?? 'all';

    $query = "SELECT 
                poi.id AS order_item_id,
                poi.order_id,
                poi.item_name,
                poi.price,
                poi.quantity,
                poi.status,
                poi.image_url,
                po.table_number,
                po.created_at,
                po.payment_status
              FROM processed_order_items poi
              JOIN processed_order po ON poi.order_id = po.id";

    if ($status == 'pending') {
        $query .= " WHERE poi.status = 'Pending'";
    } elseif ($status == 'prepared') {
        $query .= " WHERE poi.status = 'Prepared'";
    } elseif ($status == 'canceled') {
        $query .= " WHERE poi.status = 'Canceled'";
    }

    $result = mysqli_query($conn, $query);

    $orders = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $orders[] = $row;
    }

    echo json_encode($orders);
    exit();
}

if (isset($_GET['load_bookings'])) {
    $query = "SELECT * FROM table_bookings ORDER BY booking_date DESC, booking_time DESC";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        echo "<div class='p-4 mb-5' id='booking-table-container' style='max-width: 100%; overflow-x: auto;'>";
        echo "<div class='d-flex justify-content-between align-items-center mb-3'>";
        echo "<h3 class='text-center flex-grow-1 mb-0 booking-heading'>Table Booking Details</h3>";
        echo "<span class='close-icon' onclick='closeBookingContainer()'>&times;</span>";
        echo "</div>";

        echo "<div class='table-responsive'>";
        echo "<table id='booking-table' class='table table-bordered table-hover table-striped align-middle text-center' style='min-width: 900px; table-layout: fixed;'>";
        echo "<thead><tr>
                <th>Booking ID</th>
                <th>User ID</th>
                <th>Table Number</th>
                <th>Name</th>
                <th>Phone</th>
                <th>Email</th>
                <th>People</th>
                <th>Date</th>
                <th>Time</th>
                <th>Duration (hrs)</th>
                <th>Special Request</th>
                <th>Created At</th>
                <th>Status</th>
                <th>Action</th>
            </tr></thead><tbody>";

        while ($row = mysqli_fetch_assoc($result)) {
            $booking_id = htmlspecialchars($row['booking_id']);
            $status = htmlspecialchars($row['status']);

            // Determine badge color
            $badgeClass = 'secondary';
            if ($status === 'Confirmed') {
                $badgeClass = 'success';
            } elseif ($status === 'Canceled') {
                $badgeClass = 'danger';
            } elseif ($status === 'Pending') {
                $badgeClass = 'warning';
            }

            // Disable buttons if already processed
            $disabled = ($status === 'Confirmed' || $status === 'Canceled') ? 'disabled' : '';

            echo "<tr>";
            echo "<td>" . $booking_id . "</td>";
            echo "<td>" . htmlspecialchars($row['user_id']) . "</td>";
            echo "<td>" . htmlspecialchars($row['table_number']) . "</td>";
            echo "<td>" . htmlspecialchars($row['name']) . "</td>";
            echo "<td>" . htmlspecialchars($row['phone']) . "</td>";
            echo "<td>" . htmlspecialchars($row['email']) . "</td>";
            echo "<td>" . htmlspecialchars($row['number_of_people']) . "</td>";
            echo "<td>" . htmlspecialchars($row['booking_date']) . "</td>";
            echo "<td>" . htmlspecialchars($row['booking_time']) . "</td>";
            echo "<td>" . htmlspecialchars($row['duration']) . "</td>";
            echo "<td>" . nl2br(htmlspecialchars($row['special_request'])) . "</td>";
            echo "<td>" . htmlspecialchars($row['created_at']) . "</td>";
            echo "<td>$status</td>";

            // Action buttons
            echo "<td>
                    <button class='btn btn-success btn-sm me-1' onclick='confirmBooking($booking_id)' $disabled>Confirm</button>
                    <button class='btn btn-danger btn-sm' onclick='cancelBooking($booking_id)' $disabled>Cancel</button>
                  </td>";
            echo "</tr>";
        }

        echo "</tbody></table>";
        echo "</div>"; // close table-responsive
        echo "</div>"; // close booking-table-container
    } else {
        echo "<div>No bookings found.</div>";
    }

    exit();
}


$today = date('Y-m-d');

// Total Orders Today
$sqlOrders = "SELECT COUNT(*) AS total_orders FROM processed_order WHERE DATE(created_at) = ?";
$stmt1 = $conn->prepare($sqlOrders);
$stmt1->bind_param("s", $today);
$stmt1->execute();
$result1 = $stmt1->get_result();
$totalOrders = $result1->fetch_assoc()['total_orders'] ?? 0;

// Total Revenue Today
$sqlRevenue = "SELECT COALESCE(SUM(amount_paid), 0) AS total_revenue FROM payments WHERE DATE(paid_at) = ?";
$stmt2 = $conn->prepare($sqlRevenue);
$stmt2->bind_param("s", $today);
$stmt2->execute();
$result2 = $stmt2->get_result();
$totalRevenue = $result2->fetch_assoc()['total_revenue'] ?? 0.00;


// Total Confirmed Bookings Today
$sqlBookings = "SELECT COUNT(*) AS total_bookings FROM table_bookings WHERE booking_date = ? AND status = 'Confirmed'";
$stmt3 = $conn->prepare($sqlBookings);
$stmt3->bind_param("s", $today);
$stmt3->execute();
$result3 = $stmt3->get_result();
$totalBookings = $result3->fetch_assoc()['total_bookings'] ?? 0;


// Save into daily_summary table (INSERT or UPDATE)
$checkSql = "SELECT id FROM daily_summary WHERE summary_date = ?";
$checkStmt = $conn->prepare($checkSql);
$checkStmt->bind_param("s", $today);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($checkResult->num_rows > 0) {
    // Update existing entry
    $updateSql = "UPDATE daily_summary SET total_orders = ?, total_revenue = ?, total_bookings = ? WHERE summary_date = ?";
    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->bind_param("iids", $totalOrders, $totalRevenue, $totalBookings, $today);
    $updateStmt->execute();
} else {
    // Insert new entry
    $insertSql = "INSERT INTO daily_summary (summary_date, total_orders, total_revenue, total_bookings) VALUES (?, ?, ?, ?)";
    $insertStmt = $conn->prepare($insertSql);
    $insertStmt->bind_param("sidi", $today, $totalOrders, $totalRevenue, $totalBookings);
    $insertStmt->execute();
}


?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>

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

<body class="common-page" id="admin-page">

<!-- Navbar -->
<div class="navbar">
    <div class="navbar-container">
        <div class="navbar-brand">
            <a class="navbar-brand logo-wiggle" href="index.php">TASTENOW</a>
        </div>

        <div class="search-container">
            <i class="fas fa-search"></i>
            <input type="text" id="searchInput" placeholder="Search by Table No, Order Id, Status, Booking ID, User ID, Item Name">
        </div>


        <div class="d-flex align-items-center ms-3">
            <a href="logout.php" class="text-decoration-none text-dark d-flex align-items-center">
                <span class="material-symbols-outlined icon-logout me-2">logout</span>
            </a>
        </div>
    </div>
</div>

<!-- Dashboard content -->
<div class="container">

    <div class="dashboard-layout d-flex">
        <!-- Sidebar -->
        <div id="dashboardSidebar" class="dashboard-sidebar me-4">
            <!-- Header Section: Profile + Toggler -->
            <div class="d-flex align-items-center justify-content-between mb-4">
                <!-- Left: Avatar + Name + Email -->
                <div class="d-flex align-items-center">
                    <div class="text-white d-flex justify-content-center align-items-center dashboard-avatar me-3">
                        <?php echo $initials; ?>
                    </div>
                    <div>
                        <h6 class="mb-0"><?php echo $user['name']; ?></h6>
                        <small class="text-muted"><?php echo $user['email']; ?></small>
                    </div>
                </div>
            </div>

            <!-- Sidebar Menu -->
            <div class="dashboard-actions">
                <a href="index.php" class="dashboard-action-item" style="text-decoration: none"><small>Home</small></a>
                <a href="kitchen.php" class="dashboard-action-item" style="text-decoration: none"><small>Kitchen</small></a>
                <a href="cashier.php" class="dashboard-action-item" style="text-decoration: none"><small>Cashier</small></a>
                <a href="#bookingContainer" class="dashboard-action-item" style="text-decoration: none;"
                   onclick="showTableBooking()">Table Booking</a>
                <a href="#" id="orderHistoryBtn" class="dashboard-action-item" style="text-decoration: none"><small>Order
                        History</small></a>

                <div class="dropdown-wrapper">
                    <div class="dashboard-action-item" onclick="toggleDropdown('paidDropdown')">
                        <small>Manage</small>
                    </div>
                    <div class="dropdown-menu" id="paidDropdown">
                        <a href="#form-container" class="dropdown-item" onclick="showAddMenuForm()">Menu</a>
                        <a href="#userDetailsContainer" class="dropdown-item" onclick="showUserDetails()">User</a>
                    </div>
                </div>
            </div>
        </div>


        <div class="details-container flex-grow-1">
            <div class="orders-wrapper" id="ordersContainer">

                <!-- Your Summary Cards HTML -->
                <div class="summary-container my-4" id="summary-container">
                    <h2 class="summary-heading">Today Summary</h2>
                    <div class="summary-grid" id="summary-grid">
                        <div class="summary-card">
                            <h3>Total Orders Today</h3>
                            <p id="ordersCount"><?= $totalOrders ?></p>
                        </div>

                        <div class="summary-card">
                            <h3>Total Revenue</h3>
                            <p>Rs. <span id="totalRevenue"><?= number_format($totalRevenue, 2) ?></span></p>
                        </div>

                        <div class="summary-card">
                            <h3>Total Bookings Today</h3>
                            <p id="bookingsCount"><?= $totalBookings ?></p>
                        </div>
                    </div>
                </div>

                <!-- Processed Orders Display -->
                <div class="container my-4" id="ordersTableContainer">
                    <h3 class="order-table-heading mb-4">Today's Orders</h3>
                    <div class="table-responsive">
                        <table id="orders-table"
                               class="table table-bordered table-striped table-hover align-middle text-center">
                            <thead class="table-dark">
                            <tr>
                                <th>Order ID</th>
                                <th>Table No</th>
                                <th>Order Date</th>
                                <th>Item</th>
                                <th>Quantity</th>
                                <th>Status</th>
                                <th>Payment</th>
                                <th>Total Price</th>
                            </tr>
                            </thead>
                            <tbody>
                            <!-- Processed orders will be inserted here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div id="bookingContainer"></div>
            <div id="userDetailsContainer" style="display: none;"></div>
            <div id="form-container" class="form-container" style="display: none; margin-top: 30px;"></div>
            <div id="menu-section" style="display: none; margin-top: 20px;"></div>



            <a href="#" class="back-to-top" id="backToTopBtn">
                <span class="material-icons">arrow_upward</span>
            </a>

        </div>


        <!-- JS Scripts -->
        <script>

            document.addEventListener("DOMContentLoaded", function () {
                const tbody = document.querySelector("#orders-table tbody");
                const historyBtn = document.getElementById('orderHistoryBtn');

                let showingAll = false;

                // Load today's orders initially
                loadOrders();

                // Auto-refresh every 10 seconds
                setInterval(() => {
                    loadOrders(showingAll ? 'all' : 'today');
                }, 10000);

                // Toggle today's vs all orders
                if (historyBtn) {
                    historyBtn.addEventListener('click', function (e) {
                        e.preventDefault();
                        if (showingAll) {
                            loadOrders('today');
                            historyBtn.innerHTML = '<small>Order History</small>';
                            showingAll = false;
                        } else {
                            loadOrders('all');
                            historyBtn.innerHTML = '<small>Today\'s Orders</small>';
                            showingAll = true;
                        }
                    });
                }

                function loadOrders(filter = 'today') {
                    let url = window.location.pathname + '?load_orders=1';
                    if (filter === 'all') url += '&status=all';

                    fetch(url)
                        .then(response => response.json())
                        .then(data => {
                            tbody.innerHTML = '';

                            if (!data || data.length === 0) {
                                tbody.innerHTML = '<tr><td colspan="8">No processed orders found.</td></tr>';
                                return;
                            }

                            const todayDate = new Date().toISOString().slice(0, 10);

                            data.forEach(order => {
                                if (filter === 'today' && order.created_at.slice(0, 10) !== todayDate) return;

                                const totalPrice = (parseFloat(order.price) * parseInt(order.quantity)).toFixed(2);
                                const orderDateFormatted = new Date(order.created_at).toLocaleString();
                                const paymentStatus = order.payment_status || 'N/A';

                                const row = document.createElement('tr');
                                row.innerHTML = `
                        <td>${order.order_id}</td>
                        <td>${order.table_number || 'N/A'}</td>
                        <td>${orderDateFormatted}</td>
                        <td>${order.item_name}</td>
                        <td>${order.quantity}</td>
                        <td>${order.status}</td>
                        <td>${paymentStatus}</td>
                        <td>Rs. ${totalPrice}</td>
                    `;
                                tbody.appendChild(row);
                            });
                        })
                        .catch(error => {
                            console.error('Error loading processed orders:', error);
                            tbody.innerHTML = '<tr><td colspan="8">Failed to load data.</td></tr>';
                        });
                }
            });


            // Search Prosecced orders
            document.getElementById('searchInput').addEventListener('keyup', function () {
                const searchTerm = this.value.toLowerCase();
                const table = document.getElementById('orders-table');
                const rows = table.tBodies[0].rows;

                for (let row of rows) {
                    const orderId = row.cells[0].textContent.toLowerCase();
                    const tableNumber = row.cells[1].textContent.toLowerCase();
                    const orderDate = row.cells[2].textContent.toLowerCase();
                    const status = row.cells[7].textContent.toLowerCase();

                    if (
                        orderId.includes(searchTerm) ||
                        tableNumber.includes(searchTerm) ||
                        orderDate.includes(searchTerm) ||
                        status.includes(searchTerm)
                    ) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                }
            });


            function toggleDropdown(id) {
                document.querySelectorAll('.dropdown-menu').forEach(menu => {
                    if (menu.id !== id) menu.style.display = 'none';
                });
                const dropdown = document.getElementById(id);
                dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
            }

            function loadOrders(status) {
                const xhr = new XMLHttpRequest();
                xhr.open("GET", "../Frontend/admin_dashboard.php?load_orders=1&status=" + status, true);
                xhr.onload = function () {
                    if (xhr.status === 200) {
                        document.getElementById("ordersContainer").innerHTML = xhr.responseText;
                    } else {
                        document.getElementById("ordersContainer").innerHTML = "Failed to load orders.";
                    }
                };
                xhr.send();
            }


            //Admin Page Drop Down Logic
            function toggleDropdown(dropdownId) {
                // Close all dropdowns first
                const allDropdowns = document.querySelectorAll('#admin-page .dropdown-menu');
                allDropdowns.forEach(dropdown => {
                    if (dropdown.id !== dropdownId) {
                        dropdown.style.display = 'none';
                    }
                });

                // Toggle the clicked dropdown
                const dropdown = document.getElementById(dropdownId);
                if (dropdown) {
                    const isVisible = dropdown.style.display === 'block';
                    dropdown.style.display = isVisible ? 'none' : 'block';
                }
            }

            // Optional: Close dropdowns if clicking outside
            document.addEventListener('click', function (event) {
                const isClickInside = event.target.closest('.dropdown-wrapper');
                if (!isClickInside) {
                    const allDropdowns = document.querySelectorAll('#admin-page .dropdown-menu');
                    allDropdowns.forEach(dropdown => {
                        dropdown.style.display = 'none';
                    });
                }
            });


            // Add Menu Items
            document.addEventListener("DOMContentLoaded", function () {
                let isFormVisible = false;
                let isMenuVisible = false;

                window.showAddMenuForm = function () {
                    const formContainer = document.getElementById('form-container');
                    const menuSection = document.getElementById('menu-section');
                    const userDetailsContainer = document.getElementById('userDetailsContainer');

                    // Hide other sections
                    menuSection.style.display = 'none';
                    menuSection.innerHTML = '';
                    isMenuVisible = false;

                    userDetailsContainer.style.display = 'none';
                    userDetailsContainer.innerHTML = '';

                    // Toggle form
                    if (isFormVisible) {
                        formContainer.style.display = 'none';
                        formContainer.innerHTML = '';
                        isFormVisible = false;
                        return;
                    }

                    formContainer.style.display = 'block';
                    isFormVisible = true;

                    if (formContainer.innerHTML.trim() !== '') return;

                    formContainer.innerHTML = `
            <span class="close-icon" onclick="closeFormContainer()">&times;</span>
            <h3 class="form-heading">Add New Menu Item</h3>
            <form class="menu-form" id="add-menu-form" action="add_menu_item.php" method="POST" enctype="multipart/form-data" onsubmit="return handleFormSubmit(event)">
                <div class="form-row horizontal-group">
                  <div class="form-group">
                    <label>Item Name</label>
                    <input type="text" name="item_name" placeholder="Enter Item Name" required>
                  </div>
                  <div class="form-group">
                    <label>Price</label>
                    <input type="number" name="item_price" placeholder="Enter Price" step="0.01" min="0" required>
                  </div>
                </div>
                <div class="form-row horizontal-group">
                  <div class="form-group">
                    <label>Choose Image</label>
                    <input type="file" name="item_image" accept="image/*">
                  </div>
                  <div class="form-group">
                    <label>Category</label>
                    <select name="category_id" required>
                        <option value="">Select Category</option>
                        <option value="1">Coffee</option>
                        <option value="2">Tea</option>
                        <option value="3">Smoothies</option>
                        <option value="4">Snacks & Pastries</option>
                        <option value="5">Desserts</option>
                        <option value="6">Drinks</option>
                    </select>
                  </div>
                </div>
                <div class="form-row">
                    <button type="submit">Add Item</button>
                </div>
                <div class="form-row">
                    <button type="submit" onclick="displayMenu()">Display Menu</button>
                </div>
            </form>
        `;
                };

                window.handleFormSubmit = function (event) {
                    event.preventDefault();

                    const form = event.target;
                    const formData = new FormData(form);

                    fetch(form.action, {
                        method: "POST",
                        body: formData
                    })
                        .then(response => response.text())
                        .then(result => {
                            alert(result);
                            form.reset();
                            document.getElementById('form-container').style.display = 'none';
                            document.getElementById('form-container').innerHTML = '';
                            isFormVisible = false;
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert("There was an error adding the menu item.");
                        });

                    return false;
                };

                window.displayMenu = function () {
                    const formContainer = document.getElementById('form-container');
                    const menuSection = document.getElementById('menu-section');
                    const userDetailsContainer = document.getElementById('userDetailsContainer');

                    // Hide other sections
                    formContainer.style.display = 'none';
                    formContainer.innerHTML = '';
                    userDetailsContainer.style.display = 'none';
                    userDetailsContainer.innerHTML = '';

                    // Toggle
                    if (menuSection.style.display === 'block') {
                        menuSection.style.display = 'none';
                        menuSection.innerHTML = '';
                        return;
                    }

                    fetch('../Backend/display_menu_items.php')
                        .then(response => response.json())
                        .then(data => {
                            menuSection.innerHTML += `<span class="close-icon" onclick="closeMenuSection()">&times;</span>`;
                            menuSection.style.display = 'block';

                            if (data.length === 0) {
                                menuSection.innerHTML = '<p>No menu items found.</p>';
                                return;
                            }


                            let tableHTML = `
                    <h3 class="menu-heading">Menu Items</h3>
                    <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped align-middle text-center" id="menuTable">
                        <thead class="table-dark">
                            <tr>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Price</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="menuTableBody">`;


                            data.forEach(item => {
                                tableHTML += `
                    <tr data-item-name="${item.name.toLowerCase()}">
                        <td><img src="uploads/${item.image_url}" alt="${item.name}" style="max-width: 100px;"></td>
                        <td>${item.name}</td>
                        <td>Rs.${item.price}</td>
                        <td><button onclick="deleteMenuItem(${item.id})">Delete</button></td>
                    </tr>`;
                            });

                            tableHTML += '</tbody></table></div>';
                            menuSection.innerHTML += tableHTML;
                        })
                        .catch(error => {
                            console.error('Error fetching menu:', error);
                            menuSection.innerHTML = '<p>Error loading menu.</p>';
                        });
                };

                window.filterTable = function () {
                    const searchValue = document.getElementById('searchBar').value.toLowerCase();
                    const tableBody = document.getElementById('menuTableBody');
                    const rows = Array.from(tableBody.getElementsByTagName('tr'));

                    const matchingRows = [];
                    const nonMatchingRows = [];

                    rows.forEach(row => {
                        const itemName = row.getAttribute('data-item-name');
                        if (itemName.includes(searchValue)) {
                            matchingRows.push(row);
                        } else {
                            nonMatchingRows.push(row);
                        }
                    });

                    const allRows = [...matchingRows, ...nonMatchingRows];

                    tableBody.innerHTML = '';
                    allRows.forEach(row => {
                        tableBody.appendChild(row);
                    });
                };

                window.deleteMenuItem = function (itemId) {
                    if (!confirm("Delete this menu item and related variants/add-ons?")) return;

                    fetch('../Backend/delete_menu_item.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: `id=${encodeURIComponent(itemId)}`
                    })
                        .then(res => res.json())
                        .then(result => {
                            if (result.success) {
                                alert("Menu item deleted successfully.");
                                displayMenu(); // refresh
                            } else {
                                alert("Delete failed: " + (result.message || 'Unknown error'));
                            }
                        })
                        .catch(err => console.error('Delete failed:', err));
                };


                window.showUserDetails = function () {
                    const formContainer = document.getElementById('form-container');
                    const menuSection = document.getElementById('menu-section');
                    const userDetailsContainer = document.getElementById('userDetailsContainer');

                    // Hide other sections
                    formContainer.style.display = 'none';
                    formContainer.innerHTML = '';
                    menuSection.style.display = 'none';
                    menuSection.innerHTML = '';

                    fetch('../Backend/display-users.php')
                        .then(response => response.json())
                        .then(data => {
                            userDetailsContainer.innerHTML = '';
                            userDetailsContainer.style.display = 'block';

                            // Add close icon
                            userDetailsContainer.innerHTML += `<span class="close-icon" onclick="closeUserDetails()">&times;</span>`;

                            if (data.length === 0) {
                                userDetailsContainer.innerHTML += '<p>No users found.</p>';
                            } else {
                                let table = `
                        <h3 class="user-table-heading">User's Details</h3>
                        <div class="table-responsive">
                        <table class="table table-bordered table-hover table-striped align-middle text-center">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>`;

                                data.forEach(user => {
                                    table += `
                        <tr>
                            <td>${user.id}</td>
                            <td>${user.name}</td>
                            <td>${user.email}</td>
                            <td>${user.role}</td>
                            <td><button class="delete-btn" onclick="deleteUser(${user.id})">Delete</button></td>
                        </tr>`;
                                });
                                table += `</tbody></table></div>`;


                                userDetailsContainer.innerHTML += table;
                            }
                        })
                        .catch(error => {
                            console.error('Error fetching users:', error);
                            userDetailsContainer.innerHTML += '<p>Error loading users.</p>';
                        });
                };


                window.deleteUser = function (userId) {
                    if (!confirm("Are you sure you want to delete this user?")) return;

                    fetch('../Backend/delete-user.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `user_id=${encodeURIComponent(userId)}`
                    })
                        .then(res => res.text())
                        .then(result => {
                            alert(result);
                            showUserDetails(); // Refresh list
                        })
                        .catch(err => console.error('Delete failed:', err));
                };



            });





            function showTableBooking() {
                fetch('admin_dashboard.php?load_bookings=true')
                    .then(response => response.text())
                    .then(data => {
                        document.getElementById('bookingContainer').innerHTML = data;
                    })
                    .catch(error => {
                        console.error('Error loading bookings:', error);
                    });
            }

            function confirmBooking(id) {
                if (confirm("Are you sure you want to confirm this booking?")) {
                    window.location.href = 'update_booking_status.php?action=confirm&id=' + id;
                }
            }

            function cancelBooking(id) {
                if (confirm("Are you sure you want to cancel this booking?")) {
                    window.location.href = 'update_booking_status.php?action=cancel&id=' + id;
                }
            }


            // Close Form Container, Menu Section, User Details Container, Booking Container
            function closeBookingContainer() {
                const container = document.getElementById('booking-table-container');
                if (container) {
                    container.remove(); // or use container.style.display = 'none';
                }
            }

            window.closeFormContainer = function () {
                const formContainer = document.getElementById('form-container');
                formContainer.style.display = 'none';
                formContainer.innerHTML = '';
            };

            window.closeMenuSection = function () {
                const menuSection = document.getElementById('menu-section');
                menuSection.style.display = 'none';
                menuSection.innerHTML = '';
            };

            window.closeUserDetails = function () {
                const userDetailsContainer = document.getElementById('userDetailsContainer');
                userDetailsContainer.style.display = 'none';
                userDetailsContainer.innerHTML = '';
            };

            // Search Bookings, Users, Orders, Menu Items
            document.getElementById('searchInput').addEventListener('keyup', function () {
                const searchTerm = this.value.toLowerCase();

                function filterTable(table, columnsToCheck) {
                    if (!table) return;
                    Array.from(table.tBodies[0].rows).forEach(row => {
                        const matches = columnsToCheck.some(colIndex => {
                            const cellText = row.cells[colIndex]?.textContent.toLowerCase() || '';
                            return cellText.includes(searchTerm);
                        });
                        row.style.display = matches ? '' : 'none';
                    });
                }

                // Orders Table: search order ID, table number, order status
                // Assuming order ID is column 0, table number column 2, order status column 7 (adjust if needed)
                const ordersTable = document.querySelector('#ordersContainer table');
                filterTable(ordersTable, [0, 1, 5,6]);

                // Booking Table: search booking ID
                // Assuming booking ID is in column 0
                const bookingTable = document.querySelector('#bookingContainer table');
                filterTable(bookingTable, [0]);

                // Menu Table: search item name
                // Assuming item name is in column 1
                const menuTable = document.querySelector('#menu-section table');
                filterTable(menuTable, [1]);

                // User Table: search user ID
                // Assuming user ID is in column 0
                const userTable = document.querySelector('#userDetailsContainer table');
                filterTable(userTable, [0]);
            });

            // Display Daily Summary
            async function fetchDailySummary() {
                try {
                    const response = await fetch('get_daily_summary.php');
                    if (!response.ok) throw new Error('Network error');

                    const data = await response.json();

                    document.getElementById('ordersCount').textContent = data.total_orders;
                    document.getElementById('totalRevenue').textContent = data.total_revenue.toFixed(2);
                    document.getElementById('bookingsCount').textContent = data.total_bookings;

                    const topItemsList = document.getElementById('topItemsList');
                    topItemsList.innerHTML = '';
                    if (data.popular_items.length === 0) {
                        topItemsList.innerHTML = '<li>No orders yet</li>';
                    } else {
                        data.popular_items.forEach(item => {
                            const li = document.createElement('li');
                            li.textContent = `${item.name} — ${item.quantity} sold`;
                            topItemsList.appendChild(li);
                        });
                    }
                } catch (err) {
                    console.error('Error fetching daily summary:', err);
                }
            }

            fetchDailySummary();


            function toggleSidebar() {
                const sidebar = document.getElementById('dashboardSidebar');
                sidebar.classList.toggle('show-sidebar');
            }

            function toggleDropdown(id) {
                const dropdown = document.getElementById(id);
                dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
            }



                window.addEventListener('scroll', function () {
                const navbar = document.querySelector('.navbar');
                if (window.scrollY > 50) {
                navbar.classList.add('navbar-scrolled');
            } else {
                navbar.classList.remove('navbar-scrolled');
            }
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

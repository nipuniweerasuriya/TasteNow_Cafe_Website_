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
    $status = $_GET['status'] ?? 'all';
    $query = "SELECT 
                poi.id AS order_item_id, 
                poi.order_id, 
                ci.name AS item_name, 
                ci.price AS item_price, 
                poi.quantity, 
                poi.total_price, 
                poi.status,
                mv.variant_name, 
                mv.price AS variant_price,
                mi.image_url AS item_image
              FROM processed_order_items poi
              JOIN cart_items ci ON poi.cart_item_id = ci.id
              LEFT JOIN menu_variants mv ON ci.item_id = mv.item_id
              LEFT JOIN menu_items mi ON ci.item_id = mi.id";

    if ($status == 'pending') {
        $query .= " WHERE poi.status = 'Pending'";
    } elseif ($status == 'prepared') {
        $query .= " WHERE poi.status = 'Prepared'";
    } elseif ($status == 'canceled') {
        $query .= " WHERE poi.status = 'Canceled'";
    }

    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        echo "<div class='d-flex align-items-center mb-4'>";
        echo "<div class='text-white d-flex justify-content-center align-items-center dashboard-avatar me-3' style='width:50px;height:50px;background:#6c757d;border-radius:50%;'>";
        echo $initials;
        echo "</div>";
        echo "<div>";
        echo "<h6 class='mb-0'>" . htmlspecialchars($user['name']) . "</h6>";
        echo "<small class='text-muted'>" . htmlspecialchars($user['email']) . "</small>";
        echo "</div></div>";

        while ($row = mysqli_fetch_assoc($result)) {
            echo "<div class='card p-3 mb-2'>";
            echo "<strong>Order Item ID:</strong> " . htmlspecialchars($row['order_item_id']) . "<br>";
            echo "<strong>Order ID:</strong> " . htmlspecialchars($row['order_id']) . "<br>";
            echo "<strong>Item:</strong> " . htmlspecialchars($row['item_name']) . "<br>";
            echo "<strong>Price:</strong> $" . number_format($row['item_price'], 2) . "<br>";
            echo "<strong>Quantity:</strong> " . htmlspecialchars($row['quantity']) . "<br>";
            echo "<strong>Total Price:</strong> $" . number_format($row['total_price'], 2) . "<br>";
            echo "<strong>Status:</strong> " . htmlspecialchars($row['status']) . "<br>";

            if ($row['item_image']) {
                echo "<img src='../Backend/uploads/" . htmlspecialchars($row['item_image']) . "' alt='" . htmlspecialchars($row['item_name']) . "' style='width:100px;height:100px;'><br>";
            }

            if ($row['variant_name']) {
                echo "<strong>Variant:</strong> " . htmlspecialchars($row['variant_name']) . "<br>";
                echo "<strong>Variant Price:</strong> $" . number_format($row['variant_price'], 2) . "<br>";
            }

            echo "</div>";
        }
    } else {
        echo "<div class='text-muted'>No orders found for <strong>" . htmlspecialchars($status) . "</strong>.</div>";
    }
    exit();
}

if (isset($_GET['load_bookings'])) {
    $query = "SELECT * FROM table_bookings ORDER BY booking_date DESC, booking_time DESC";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        echo "<div class='booking-table-container' id='booking-table-container'>";
        echo "<span class='close-icon' onclick='closeBookingContainer()'>&times;</span>";
        echo "<h3 class='mb-4 heading-center'>Table Booking Details</h3>";
        echo "<table id='booking-table' cellspacing='0' cellpadding='10'>"; // ✅ Added ID
        echo "<thead><tr>
        <th>Booking ID</th>
        <th>Table Num</th>
        <th>Name</th>
        <th>Phone</th>
        <th>Email</th>
        <th>People</th>
        <th>Date</th>
        <th>Time</th>
        <th>Duration</th>
        <th>Special Request</th>
        <th>Booked At</th>
        <th>Status</th>
    </tr></thead><tbody>";

        while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['booking_id']) . "</td>";
            echo "<td>" . htmlspecialchars($row['table_number']) . "</td>";
            echo "<td>" . htmlspecialchars($row['name']) . "</td>";
            echo "<td>" . htmlspecialchars($row['phone']) . "</td>";
            echo "<td>" . htmlspecialchars($row['email']) . "</td>";
            echo "<td>" . htmlspecialchars($row['number_of_people']) . "</td>";
            echo "<td>" . htmlspecialchars($row['booking_date']) . "</td>";
            echo "<td>" . htmlspecialchars($row['booking_time']) . "</td>";
            echo "<td>" . htmlspecialchars($row['duration']) . " hrs</td>";
            echo "<td>" . nl2br(htmlspecialchars($row['special_request'])) . "</td>";
            echo "<td>" . htmlspecialchars($row['created_at']) . "</td>";
            echo "<td>" . htmlspecialchars($row['status']) . "</td>";
            echo "</tr>";
        }

        echo "</tbody></table>";
        echo "</div>";
    } else {
        echo "<div class='text-muted'>No bookings found.</div>";
    }

    exit(); // ✅ Moved outside the if-else

}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>

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

        .dashboard-sidebar {
            width: 280px;
            height: auto;
            background-color: #f8f9fa;
            border-right: 1px solid #e4e4e4;
            border-left: none;
            border-top: none;
            border-bottom: none;
            padding: 1.5rem;
            margin-bottom: 1rem;
            display: flex;
            margin-top: 1rem;
            flex-direction: column;
        }






        .summary-container {
            background-color: #ffffff;
            padding: 20px 30px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin: 20px auto;
            max-width: 600px;
        }

        .summary-container h2 {
            margin-bottom: 20px;
            color: #333;
            text-align: center;
        }

        .summary-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            max-width: 1000px;
            margin: 20px auto;
            font-family: Arial, sans-serif;
        }

        .summary-card {
            flex: 1 1 calc(33.333% - 20px);
            background-color: #f9f9f9;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            min-width: 200px;
        }

        .summary-card h3 {
            margin-bottom: 10px;
            font-size: 18px;
            color: #333;
        }

        .summary-card p {
            font-size: 22px;
            font-weight: bold;
            color: #2c3e50;
        }

        .summary-card.full-width {
            flex: 1 1 100%;
        }

        .popular-items-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .popular-items-list li {
            padding: 6px 0;
            font-size: 16px;
            color: #555;
        }


        /* Heading */
        .heading-center {
            text-align: center;
            color: #f1cc52;
            font-size: x-large;
            margin-top: 2rem;
            font-style: italic;
            font-family: 'Playfair Display', ui-sans-serif;
        }

        /* Base Table Styling */
        /* Base Table Styling */
        table {
            width: 100%;
            border: 1px solid white;
            font-family: Arial, sans-serif;
        }

        /* Shared Table Styling for Sections */
        #menu-section table,
        #userDetailsContainer table,
        #booking-table-container table,
        #orders-table {
            border-collapse: collapse;
            margin: 25px 0;
            font-size: 0.9em;
            font-family: 'Segoe UI', sans-serif;
            min-width: 400px;
            width: 100%;
            border: 1px solid white;
            background-color: white;
        }

        /* Table Headers (all <th> in <thead> + optional .table-header class) */
        #menu-section table thead th,
        #userDetailsContainer table thead th,
        #orders-table thead th,
        #booking-table-container table thead th,
        .table-header {
            background-color: #fac003; /* Yellow/Orange */
            color: white;
            border: 1px solid white;
            font-size: small;
            text-align: left;
            padding: 10px;
            font-weight: bold;
        }

        /* Table Cells */
        #menu-section table th,
        #menu-section table td,
        #userDetailsContainer table th,
        #userDetailsContainer table td,
        #booking-table-container table th,
        #booking-table-container table td,
        #orders-table th,
        #orders-table td {
            padding: 10px;
            text-align: left;
            font-size: smaller;
            vertical-align: top;
            border: 1px solid #fac003;
        }

        /* Hover Row Effect */
        #menu-section table tbody tr:hover,
        #userDetailsContainer table tbody tr:hover,
        #booking-table-container table tbody tr:hover,
        #orders-table tbody tr:hover {
            background-color: #f6dc88;
        }

        /* Section Containers */
        #menu-section,
        #form-container,
        #ordersContainer,
        #userDetailsContainer,
        #booking-table-container {
            margin-top: 20px;
            margin-bottom: 20px;
            max-width: 900px;
            background: white;
            padding: 20px;
            border-radius: 3px;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.05);
        }

        /* Menu Images */
        #menu-section table img {
            max-width: 80px;
            max-height: 80px;
            border-radius: 3px;
        }

        /* Buttons (Menu + User Table) */
        #menu-section button,
        #userDetailsContainer button {
            padding: 5px 8px;
            font-size: 13px;
            background-color: #fac003;
            color: white;
            width: 100%;
            border: 1px solid white;
            border-radius: 3px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s ease;
            margin-top: 5px;
        }

        /* Hover Effect for Buttons */
        #menu-section button:hover,
        #userDetailsContainer button:hover {
            background-color: white;
            color: #fac003;
            border: 1px solid #fac003;
        }

        /* Delete Button Specific Styling */
        #menu-section button[onclick^="deleteMenuItem"],
        #userDetailsContainer button.delete-btn {
            background-color: #fac003 !important;
            color: white !important;
        }

        /* Hover Effect for Delete Button */
        #menu-section button[onclick^="deleteMenuItem"]:hover,
        #userDetailsContainer button.delete-btn:hover {
            background-color: white !important;
            color: #fac003 !important;
            border: 1px solid #fac003;
        }


        /* Hover Effect for Delete Button */
        #menu-section button[onclick^="deleteMenuItem"]:hover,
        #userDetailsContainer button.delete-btn:hover {
            background-color: white !important;
            color: #fac003 !important;
            border: 1px solid #fac003;
        }


        /* Search Containers */
        #admin-page .search-container {
            position: relative;
            width: 200%;
            margin-top: 0.1rem;
            margin-bottom: 0.1rem;
            font-size: 12px;
        }

        #admin-page .search-container i {
            position: absolute;
            top: 50%;
            left: 12px;
            transform: translateY(-50%);
            color: #fac003;
        }

        #admin-page #searchInput,
        #admin-page #searchBar {
            width: 100%;
            padding: 8px 12px 8px 36px;
            font-size: 12px;
            border: 1px solid #fac003;
            border-radius: 3px;
            outline: none;
            transition: 0.3s ease;
        }

        #admin-page #searchInput::placeholder,
        #admin-page #searchBar::placeholder {
            color: #fac003;
        }






        /* Container for Add Menu Form */
        #form-container,
        #userDetailsContainer {
            border: none;
            margin-top: 20px;
            background: white;
            padding: 20px;
            border-radius: 3px;
        }

        /* Form Title */
        .form-heading {
            text-align: center;
            color: #f1cc52;
            font-size: x-large;
            margin-top: 2rem;
            margin-bottom: 2rem;
            font-style: italic;
            font-family: 'Playfair Display', ui-sans-serif;
        }

        /* Form labels */
        .form-row label {
            display: block;
            color: black;
            font-weight: bold;
            margin-bottom: 5px;
        }

        /* Inputs, selects, and textareas */
        .form-row input,
        .form-row select,
        .form-row textarea,
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #fac003;
            border-radius: 3px;
            margin-bottom: 10px;
            background-color: white;
            color: black;
            font-size: 1rem;
        }

        /* Placeholders */
        #admin-page .form-row input::placeholder,
        #admin-page .form-row textarea::placeholder,
        #admin-page.form-group input::placeholder,
        .form-group textarea::placeholder {
            color: #f1d476;
            font-size: 0.9rem;
        }

        /* Submit and Add buttons */
        #admin-page .form-row button,
        #admin-page .form-group button {
            padding: 10px;
            background-color: #fac003;
            color: white;
            width: 100%;
            border: 1px solid #fac003;
            border-radius: 3px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s ease;
            margin-top: 5px;
        }

        /* Hover effect for submit buttons */
        #admin-page .form-row button[type="submit"]:hover,
        #admin-page .form-group button[type="submit"]:hover {
            background-color: white;
            color: #fac003;
            border: 3px solid #fac003;
        }

        /* Add buttons (e.g., + Add Variant, + Add Add-on) */
        #admin-page .form-group button[type="button"],
        #admin-page .form-row button[type="button"] {
            background-color: white;
            height: 2rem;
            color: #fac003;
            font-size: 14px;
            border: 1px solid #fac003;
            border-radius: 3px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s ease;
            margin-top: 5px;
            padding: 2px 10px;
        }

        /* Section title */
        #admin-page #add-menu-form h3 {
            font-size: 24px;
            color: #fac003;
            text-align: center;
            margin-bottom: 20px;
            text-transform: uppercase;
        }

        /* NEW: Horizontal layout for grouped fields */
        #admin-page .horizontal-group {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: flex-start;
        }

        /* NEW: Form group blocks inside horizontal rows */
        #admin-page .form-group {
            flex: 1;
            min-width: 280px;
        }

        /* NEW: Variant inputs in one row */
        #admin-page .form-subrow {
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
        }

        #admin-page .form-subrow input {
            flex: 1;
            padding: 10px;
            border: 1px solid #fac003;
            border-radius: 3px;
            background-color: white;
            color: black;
            font-size: 1rem;
        }

        #admin-page .form-subrow input::placeholder {
            color: #f1d476;
            font-size: 0.9rem;
        }


        .close-icon {
            float: right;
            font-size: 16px;
            cursor: pointer;
            color: #fac003;
            margin: 10px;
        }







    </style>


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
            <input type="text" id="searchInput" placeholder="Search by Table No, Order Id, Date, or Status">
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
        <div class="dashboard-sidebar me-4">
            <div class="d-flex align-items-center mb-4">
                <div class="text-white d-flex justify-content-center align-items-center dashboard-avatar me-3">
                    <?php echo $initials; ?>
                </div>
                <div>
                    <h6 class="mb-0"><?php echo $user['name']; ?></h6>
                    <small class="text-muted"><?php echo $user['email']; ?></small>
                </div>
            </div>


            <!-- Sidebar Menu -->
            <div class="dashboard-actions">
                <a href="index.php" class="dashboard-action-item" style="text-decoration: none"><small>Home</small></a>
                <a href="kitchen.php" class="dashboard-action-item" style="text-decoration: none"><small>Kitchen</small></a>
                <a href="cashier.php" class="dashboard-action-item" style="text-decoration: none"><small>Cashier</small></a>
                <a href="summary_history.php" class="dashboard-action-item" style="text-decoration: none;">Summary</a>
                <a href="#" class="dashboard-action-item" style="text-decoration: none;" onclick="showTableBooking()">Table Booking</a>
                <a href="#" id="orderHistoryBtn" class="dashboard-action-item" style="text-decoration: none"><small>Order History</small></a>

                <div class="dropdown-wrapper">
                    <div class="dashboard-action-item" onclick="toggleDropdown('paidDropdown')">
                        <small>Manage</small>
                    </div>
                    <div class="dropdown-menu" id="paidDropdown">
                        <div class="dropdown-item" onclick="showAddMenuForm()">Menu</div>
                        <div class="dropdown-item" onclick="showUserDetails()">User</div>
                    </div>

                </div>
            </div>
        </div>







        <!-- Orders Display Section -->
        <div class="flex-grow-1">
           <div class="orders-wrapper" id="ordersContainer">

               <!-- ✅ Summary Container at the Top -->
               <div class="summary-grid">
                   <div class="summary-card">
                       <h3>Total Orders Today</h3>
                       <p id="ordersCount">0</p>
                   </div>

                   <div class="summary-card">
                       <h3>Total Revenue</h3>
                       <p>Rs. <span id="totalRevenue">0.00</span></p>
                   </div>

                   <div class="summary-card">
                       <h3>Total Bookings Today</h3>
                       <p id="bookingsCount">0</p>
                   </div>

               </div>



               <!-- Processed Orders Display -->
               <h3 class="mb-4 heading-center">Today's Orders</h3>
               <table id="orders-table" border="3" cellspacing="0" cellpadding="10">
                   <thead>
                   <tr>
                       <th>Order ID</th>
                       <th>Table No</th>
                       <th>Order Date</th>
                       <th>Item</th>
                       <th>Variant</th>
                       <th>Add-ons</th>
                       <th>Quantity</th>
                       <th>Status</th>
                       <th>Total Price</th>
                   </tr>
                   </thead>
                   <tbody>
                   <!-- Processed orders will be inserted here -->
                   </tbody>
               </table>
           </div>



            <div id="bookingContainer" style="margin-top: 30px;">

            </div>



            <div id="userDetailsContainer" style="display: none;">
                <!-- User details will be inserted here -->
            </div>


            <!-- Add Menu Form Container -->
            <div class="form-container" id="form-container" style="display: none; margin-top: 30px;"></div>

            <div id="menu-section" style="display: none; margin-top: 20px;"></div>


        </div>
    </div>
</div>


<!-- JS Scripts -->
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const tbody = document.querySelector("#orders-table tbody");
        const historyBtn = document.getElementById('orderHistoryBtn');

        // Keep track of current view
        let showingAll = false;

        // Load today's orders on page load
        loadOrders();

        // Auto-refresh orders every 10 seconds
        setInterval(() => {
            loadOrders(showingAll ? 'all' : 'today');
        }, 500);

        // Toggle view on button click
        if (historyBtn) {
            historyBtn.addEventListener('click', function (e) {
                e.preventDefault(); // prevent link behavior

                if (showingAll) {
                    loadOrders(); // load today's orders
                    historyBtn.innerHTML = '<small>Order History</small>';
                    showingAll = false;
                } else {
                    loadOrders('all'); // load all orders
                    historyBtn.innerHTML = '<small>Today\'s Orders</small>';
                    showingAll = true;
                }
            });
        }

        // Load orders function
        function loadOrders(filter = 'today') {
            const url = filter === 'all'
                ? '../Backend/get_processed_orders.php?filter=all'
                : '../Backend/get_processed_orders.php';

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    tbody.innerHTML = ''; // clear table

                    if (!data || data.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="9">No processed orders found.</td></tr>';
                        return;
                    }

                    data.forEach(order => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                        <td>${order.order_id}</td>
                        <td>${order.table_number}</td>
                        <td>${order.order_date}</td>
                        <td>${order.item_name}</td>
                        <td>${order.variant}</td>
                        <td>${order.addons || 'None'}</td>
                        <td>${order.quantity}</td>
                        <td>${order.status}</td>
                        <td>Rs. ${order.total_price}</td>
                    `;
                        tbody.appendChild(row);
                    });
                })
                .catch(error => {
                    console.error('Error loading processed orders:', error);
                    tbody.innerHTML = '<tr><td colspan="9">Failed to load data.</td></tr>';
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
    document.addEventListener('click', function(event) {
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
            <form class="menu-form" action="add_menu_item.php" method="POST" enctype="multipart/form-data" onsubmit="return handleFormSubmit(event)">
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
                <div class="form-row horizontal-group">
                  <div class="form-group">
                    <label>Variants:</label>
                    <div id="variants-container">
                      <div class="form-subrow">
                        <input type="text" name="variants[]" placeholder="Variant Name" />
                        <input type="number" name="variant_prices[]" placeholder="Extra Price" step="0.01" min="0" />
                      </div>
                    </div>
                    <button type="button" onclick="addVariant()">+ Add Variant</button>
                  </div>
                </div>
                <div class="form-row horizontal-group">
                  <div class="form-group">
                    <label>Add-ons:</label>
                    <div id="addons-container">
                      <div class="form-subrow">
                        <input type="text" name="addons[]" placeholder="Add-on Name" />
                        <input type="number" name="addon_prices[]" placeholder="Add-on Price" step="0.01" min="0" />
                      </div>
                    </div>
                    <button type="button" onclick="addAddon()">+ Add Add-on</button>
                  </div>
                </div>
                <div class="form-row">
                    <button type="submit">Add Item</button>
                </div>
                <div class="form-row">
                    <button type="button" onclick="displayMenu()">Display Menu</button>
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

        window.addVariant = function () {
            const container = document.getElementById('variants-container');
            const div = document.createElement('div');
            div.className = "form-subrow";
            div.innerHTML = `
            <input type="text" name="variants[]" placeholder="Variant Name" />
            <input type="number" name="variant_prices[]" placeholder="Extra Price" step="0.01" min="0" />
        `;
            container.appendChild(div);
        };

        window.addAddon = function () {
            const container = document.getElementById('addons-container');
            const div = document.createElement('div');
            div.className = "form-subrow";
            div.innerHTML = `
            <input type="text" name="addons[]" placeholder="Add-on Name" />
            <input type="number" name="addon_prices[]" placeholder="Add-on Price" step="0.01" min="0" />
        `;
            container.appendChild(div);
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
                    menuSection.innerHTML += `<span class="close-icon" onclick="closeMenuSection()" style="float:right;cursor:pointer;font-size:24px;">&times;</span>`;
                    menuSection.style.display = 'block';

                    if (data.length === 0) {
                        menuSection.innerHTML = '<p>No menu items found.</p>';
                        return;
                    }


                    let tableHTML = `
                <h3 class="heading-center">Menu Items</h3>
                <table border="1" cellspacing="0" cellpadding="10" style="width: 100%; border-collapse: collapse;" id="menuTable">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Price</th>
                            <th>Variants</th>
                            <th>Add-ons</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="menuTableBody">`;

                    data.forEach(item => {
                        const variants = item.variants.length
                            ? item.variants.map(v => `${v.variant_name} (+Rs.${v.price})`).join('<br>')
                            : 'None';
                        const addons = item.addons.length
                            ? item.addons.map(a => `${a.addon_name} (+Rs.${a.addon_price})`).join('<br>')
                            : 'None';

                        tableHTML += `
                    <tr data-item-name="${item.name.toLowerCase()}">
                        <td><img src="${item.image_url}" alt="${item.name}" style="max-width: 100px;"></td>
                        <td>${item.name}</td>
                        <td>Rs.${item.price}</td>
                        <td>${variants}</td>
                        <td>${addons}</td>
                        <td><button onclick="deleteMenuItem(${item.id})" style="background-color:red;color:white;">Delete</button></td>
                    </tr>`;
                    });

                    tableHTML += '</tbody></table>';
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

            fetch(`../Backend/delete_menu_item.php?id=${itemId}`, { method: 'DELETE' })
                .then(res => res.text())
                .then(result => {
                    alert(result);
                    displayMenu(); // refresh
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

            fetch('../Backend/get-users.php')
                .then(response => response.json())
                .then(data => {
                    userDetailsContainer.innerHTML = '';
                    userDetailsContainer.style.display = 'block';

                    // Add close icon
                    userDetailsContainer.innerHTML += `<span class="close-icon" onclick="closeUserDetails()" style="float:right;cursor:pointer;font-size:24px;">&times;</span>`;

                    if (data.length === 0) {
                        userDetailsContainer.innerHTML += '<p>No users found.</p>';
                    } else {
                        let table = `
                    <h3 class="heading-center">User's Details</h3>
                    <table>
                        <thead>
                            <tr>
                                <th class="table-header">ID</th>
                                <th class="table-header">Name</th>
                                <th class="table-header">Email</th>
                                <th class="table-header">Role</th>
                                <th class="table-header">Action</th>
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
                        table += `
                        </tbody>
                    </table>`;

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

            fetch(`../Backend/delete_user.php?id=${userId}`, { method: 'DELETE' })
                .then(res => res.text())
                .then(result => {
                    alert(result);
                    showUserDetails(); // Refresh
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

        // Filter Orders table: Order ID(0), Table No(1), Order Date(2), Status(7)
        const ordersTable = document.querySelector('#ordersContainer table');
        filterTable(ordersTable, [0, 1, 2, 7]);

        // Filter Booking table: Booking ID(0), Status(11)
        const bookingTable = document.querySelector('#bookingContainer table');
        filterTable(bookingTable, [0, 11]);

        // Filter Menu table: Item Name(0)
        const menuTable = document.querySelector('#menu-section table');
        filterTable(menuTable, [1]);

        // Filter User Details table: User ID(0), User Role(3)
        const userTable = document.querySelector('#userDetailsContainer table');
        filterTable(userTable, [0, 3]);
    });













        async function fetchDailySummary() {
        try {
        const response = await fetch('get_daily_summary.php');
        if (!response.ok) throw new Error('Network error');

        const data = await response.json();

        document.getElementById('ordersCount').textContent = data.total_orders;
        document.getElementById('totalRevenue').textContent = data.total_revenue.toFixed(2);
        document.getElementById('bookingsCount').textContent = data.total_bookings;
        document.getElementById('servedCount').textContent = data.served_items;
        document.getElementById('canceledCount').textContent = data.canceled_items;

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

        // Load data on page load
        fetchDailySummary();
</script>





<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../Frontend/js/script.js"></script>

</body>
</html>

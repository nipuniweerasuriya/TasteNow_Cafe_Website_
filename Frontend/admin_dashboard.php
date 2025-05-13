<?php
session_start(); // Start the session to access session data

// Check if the user is logged in and has a valid user ID in the session
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if the user is not logged in
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id']; // Get user ID from the session

// Connect to the database
include '../Backend/db_connect.php';

// Fetch the user details from the database using the user ID
$query = "SELECT name, email FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) > 0) {
    $user = mysqli_fetch_assoc($result); // Fetch the user details
} else {
    // Handle the case where the user is not found
    echo "User not found!";
    exit();
}

// Extract initials from the user's name (first letters of the first and last name)
$names = explode(' ', $user['name']); // Split name into parts (e.g., 'Nipuni Weerasuriya')
$initials = strtoupper(substr($names[0], 0, 1) . substr(end($names), 0, 1)); // "NW"

// Check if we need to load orders
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
              LEFT JOIN menu_items mi ON ci.item_id = mi.id"; // Join with menu_items to get the image_url

    // Filter by order status
    if ($status == 'pending') {
        $query .= " WHERE poi.status = 'Pending'";
    } elseif ($status == 'prepared') {
        $query .= " WHERE poi.status = 'Prepared'";
    } elseif ($status == 'canceled') {
        $query .= " WHERE poi.status = 'Canceled'";
    }

    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        // Display User Information (Avatar, Name, and Email)
        echo "<div class='d-flex align-items-center mb-4'>";
        echo "<div class='text-white d-flex justify-content-center align-items-center dashboard-avatar me-3'>";
        echo $initials;
        echo "</div>";
        echo "<div>";
        echo "<h6 class='mb-0'>" . htmlspecialchars($user['name']) . "</h6>";
        echo "<small class='text-muted'>" . htmlspecialchars($user['email']) . "</small>";
        echo "</div>";
        echo "</div>";

        // Display Orders
        while ($row = mysqli_fetch_assoc($result)) {
            // Output the order details with an image
            echo "<div class='card p-3 mb-2'>";
            echo "<strong>Order Item ID:</strong> " . htmlspecialchars($row['order_item_id']) . "<br>";
            echo "<strong>Order ID:</strong> " . htmlspecialchars($row['order_id']) . "<br>";
            echo "<strong>Item:</strong> " . htmlspecialchars($row['item_name']) . "<br>";
            echo "<strong>Price:</strong> $" . number_format($row['item_price'], 2) . "<br>";
            echo "<strong>Quantity:</strong> " . htmlspecialchars($row['quantity']) . "<br>";
            echo "<strong>Total Price:</strong> $" . number_format($row['total_price'], 2) . "<br>";
            echo "<strong>Status:</strong> " . htmlspecialchars($row['status']) . "<br>";

            // Display image if available
            if ($row['item_image']) {
                echo "<img src='../Backend/uploads/" . htmlspecialchars($row['item_image']) . "' alt='" . htmlspecialchars($row['item_name']) . "' class='item-image' style='width: 100px; height: 100px;'><br>";
            }

            // Display variant details if available
            if ($row['variant_name']) {
                echo "<strong>Variant:</strong> " . htmlspecialchars($row['variant_name']) . "<br>";
                echo "<strong>Variant Price:</strong> $" . number_format($row['variant_price'], 2) . "<br>";
            }
            echo "</div>";
        }
    } else {
        echo "<div class='text-muted'>No orders found for <strong>" . htmlspecialchars($status) . "</strong>.</div>";
    }

    exit; // Prevent the rest of the page from rendering
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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700&family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&display=swap" rel="stylesheet">

    <!-- Bootstrap and Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/styles.css"/>


    <style>
        table {
            width: 100%;
            border: 1px solid #ddd;
            font-family: Arial, sans-serif;
        }

        th, td {
            border: 1px solid #ccc;
            padding: 8px;
            vertical-align: top;
            text-align: left;
        }

        th {
            background-color: #f4f4f4;
        }

        img {
            border-radius: 8px;
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
        <div class="d-flex align-items-center ms-3">
            <a href="../Backend/logout.php" class="text-decoration-none text-dark d-flex align-items-center">
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
                <a href="../Frontend/index.php" class="dashboard-action-item"><small>Home</small></a>
                <a href="../Backend/kitchen.php" class="dashboard-action-item"><small>Kitchen</small></a>
                <a href="#" class="dashboard-action-item"><small>Cashier</small></a>
                <a href="#" class="dashboard-action-item" onclick="showTableBooking()">Table Booking</a>


                <div class="dropdown-wrapper">
                    <div class="dashboard-action-item" onclick="toggleDropdown('ordersDropdown')">
                        <small>Orders</small>
                    </div>
                    <div class="dropdown-menu" id="ordersDropdown">
                        <div class="dropdown-item" onclick="loadOrders('all')">All Orders</div>
                        <div class="dropdown-item" onclick="loadOrders('pending')">Pending Orders</div>
                        <div class="dropdown-item" onclick="loadOrders('prepared')">Prepared Orders</div>
                        <div class="dropdown-item" onclick="loadOrders('canceled')">Canceled Orders</div>
                    </div>
                </div>

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
            <!-- Processed Orders Display -->
            <h4 class="mt-4 mb-3">Processed Orders</h4>
            <div id="current-orders" class="row gy-3"></div>

            <div id="ordersContainer">
                <!-- Orders will be displayed here -->
            </div>


            <div id="tableBookingContainer" style="display:none;">
                <!-- Tabele Booking Details -->
            </div>



            <div id="userDetailsContainer" style="display: none;">
                <!-- User details will be inserted here -->
            </div>


            <!-- Add Menu Form Container -->
            <div id="form-container" style="display: none; margin-top: 30px;"></div>

            <div id="menu-section" style="display: none; margin-top: 20px;"></div>


        </div>
    </div>
    </div>



<!-- JS Scripts -->
<script>
    //Processed order display
    document.addEventListener("DOMContentLoaded", function () {
        fetchProcessedOrders();
    });

    function fetchProcessedOrders() {
        fetch("../Backend/get_processed_orders.php") // Adjust this to your API endpoint for processed orders
            .then(res => res.json())
            .then(data => {
                const container = document.getElementById("current-orders");
                container.innerHTML = "";

                if (data.length === 0) {
                    container.innerHTML = "<p>No processed orders found.</p>";
                    return;
                }

                data.forEach(order => {
                    const card = document.createElement("div");
                    card.className = "col-md-6";

                    card.innerHTML = `
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <h5 class="card-title">Table: ${order.table_number}</h5>
                            <p class="card-text">
                                <strong>Item:</strong> ${order.item_name} (${order.variant || "No Variant"})<br>
                                <strong>Quantity:</strong> ${order.quantity}<br>
                                <strong>Add-ons:</strong> ${order.addons || "None"}<br>
                                <strong>Total:</strong> Rs. ${order.total_price}<br>
                                <strong>Status:</strong>
                                <span class="badge bg-${getStatusColor(order.status)}">${order.status}</span><br>
                                <small class="text-muted">Ordered on: ${new Date(order.order_date).toLocaleString()}</small><br>

                                <!-- Display Item Image -->
                                <img src="${order.image_path || 'path/to/default/image.jpg'}" alt="${order.item_name}" class="img-fluid" style="max-height: 150px; object-fit: cover;">
                            </p>
                        </div>
                    </div>
                `;

                    container.appendChild(card);
                });
            })
            .catch(err => {
                console.error("Failed to load processed orders", err);
                document.getElementById("current-orders").innerHTML = "<p>Error loading orders.</p>";
            });
    }

    function getStatusColor(status) {
        switch (status) {
            case 'Pending': return 'warning';
            case 'Prepared': return 'info';
            case 'Served': return 'success';
            default: return 'secondary';
        }
    }





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
        xhr.onload = function() {
            if (xhr.status === 200) {
                document.getElementById("ordersContainer").innerHTML = xhr.responseText;
            } else {
                document.getElementById("ordersContainer").innerHTML = "Failed to load orders.";
            }
        };
        xhr.send();
    }




    function showUserDetails() {
        fetch('../Backend/get-users.php')
            .then(response => response.json())
            .then(data => {
                const container = document.getElementById('userDetailsContainer');
                container.innerHTML = '';

                if (data.length === 0) {
                    container.innerHTML = '<p>No users found.</p>';
                } else {
                    let table = `
                    <table border="1" cellpadding="8" cellspacing="0">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Action</th>
                        </tr>`;
                    data.forEach(user => {
                        table += `
                        <tr>
                            <td>${user.id}</td>
                            <td>${user.name}</td>
                            <td>${user.email}</td>
                            <td>${user.role}</td>
                            <td>
                                <button onclick="deleteUser(${user.id})">Delete</button>
                            </td>
                        </tr>`;
                    });
                    table += '</table>';
                    container.innerHTML = table;
                }

                container.style.display = 'block';
            })
            .catch(error => {
                console.error('Error fetching users:', error);
            });
    }



    function deleteUser(userId) {
        if (!confirm('Are you sure you want to delete this user?')) return;

        fetch('../Backend/delete-user.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `user_id=${userId}`
        })
            .then(response => response.text())
            .then(result => {
                alert(result);
                showUserDetails(); // Refresh the list
            })
            .catch(error => {
                console.error('Error deleting user:', error);
            });
    }









</script>


<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/script.js"></script>

</body>
</html>

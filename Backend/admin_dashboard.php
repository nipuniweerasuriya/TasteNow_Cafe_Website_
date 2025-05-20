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



        #orders-table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            border: 1px solid #fac003;
            font-family: Arial, sans-serif;
            margin-top: 20px;
            margin-bottom: 20px;
        }

        #orders-table th,
        #orders-table td {
            border: 1px solid #fac003;
            padding: 10px 15px;
            text-align: left;
            color: black;
            font-size: smaller;
        }

        #orders-table th {
            background-color: #fbdb71; /* lighter orange-ish */
            font-weight: bold;
        }

        #orders-table tbody tr:hover {
            background-color: #ffe5b4; /* light orange highlight on hover */
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

        <input type="text" id="searchInput" placeholder="Search by Table No, Date, or Status" style="margin-bottom: 10px; padding: 5px; width: 300px;">


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
                <a href="index.php" class="dashboard-action-item"><small>Home</small></a>
                <a href="kitchen.php" class="dashboard-action-item"><small>Kitchen</small></a>
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
            <table id="orders-table" border="1" cellspacing="0" cellpadding="10">
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
        document.addEventListener("DOMContentLoaded", function () {
        fetch('../Backend/get_processed_orders.php')
            .then(response => response.json())
            .then(data => {
                const tbody = document.querySelector("#orders-table tbody");
                tbody.innerHTML = ''; // Clear existing rows

                if (data.length === 0) {
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
            });
    });



        // Search Prosecced orders
        document.getElementById('searchInput').addEventListener('keyup', function () {
            const searchTerm = this.value.toLowerCase();
            const table = document.getElementById('orders-table');
            const rows = table.tBodies[0].rows;

            for (let row of rows) {
                const tableNumber = row.cells[1].textContent.toLowerCase();
                const orderDate = row.cells[2].textContent.toLowerCase();
                const status = row.cells[7].textContent.toLowerCase();

                if (
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
        window.showAddMenuForm = function () {
            const formContainer = document.getElementById('form-container');
            formContainer.style.display = 'block';
            formContainer.scrollIntoView({ behavior: "smooth" });

            if (formContainer.innerHTML.trim() !== '') return;

            formContainer.innerHTML = `
            <div class="add-menu-container">
                <h2 class="form-heading">-----Add New Menu Item-----</h2>
                <form class="menu-form" action="add_menu_item.php" method="POST" enctype="multipart/form-data" onsubmit="return handleFormSubmit(event)">
                    <div class="form-row">
                        <label><input type="text" name="name" placeholder="Item Name" required /></label>
                        <label><input type="number" name="price" placeholder="Price (Rs.)" required /></label>
                        <label>Select Image:</label><input type="file" name="image_file" accept="image/*" required><br/><br/>
                    </div>
                    <div class="form-row">
                        <label>
                            <select name="category_id" required>
                                <option value="">Select Category</option>
                                <option value="1">Coffee</option>
                                <option value="2">Tea</option>
                                <option value="3">Smoothies</option>
                                <option value="4">Snacks & Pastries</option>
                                <option value="5">Desserts</option>
                                <option value="6">Drinks</option>
                            </select>
                        </label>
                    </div>
                    <div class="form-row">
                        <label>Variants:</label>
                        <div id="variants-container">
                            <div class="form-subrow">
                                <input type="text" name="variants[]" placeholder="Variant Name" />
                                <input type="number" name="variant_prices[]" placeholder="Extra Price" />
                            </div>
                        </div>
                        <button type="button" onclick="addVariant()">+ Add Variant</button>
                    </div>
                    <div class="form-row">
                        <label>Add-ons:</label>
                        <div id="addons-container">
                            <div class="form-subrow">
                                <input type="text" name="addons[]" placeholder="Add-on Name" />
                                <input type="number" name="addon_prices[]" placeholder="Add-on Price" />
                            </div>
                        </div>
                        <button type="button" onclick="addAddon()">+ Add Add-on</button>
                    </div>
                    <div class="form-row">
                        <button type="submit">Add Item</button>
                    </div>

                     <div class="form-row">
                        <button type="button" onclick="displayMenu()">Display Menu</button>
                     </div>
                </form>
            </div>
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
                    console.log("Server response:", result);
                    alert(result);
                    form.reset();
                    document.getElementById('form-container').style.display = 'none';
                    document.getElementById('form-container').innerHTML = '';
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
            <input type="number" name="variant_prices[]" placeholder="Extra Price" />
        `;
            container.appendChild(div);
        };

        window.addAddon = function () {
            const container = document.getElementById('addons-container');
            const div = document.createElement('div');
            div.className = "form-subrow";
            div.innerHTML = `
            <input type="text" name="addons[]" placeholder="Add-on Name" />
            <input type="number" name="addon_prices[]" placeholder="Add-on Price" />
        `;
            container.appendChild(div);
        };
    });




    // Display Menu Items In Admin Pge
    function displayMenu() {
        fetch('../Backend/display_menu_items.php')
            .then(response => response.json())
            .then(data => {
                const menuSection = document.getElementById('menu-section');
                menuSection.innerHTML = '';
                menuSection.style.display = 'block';

                if (data.length === 0) {
                    menuSection.innerHTML = '<p>No menu items found.</p>';
                    return;
                }

                // Create search bar
                const searchHTML = `
                <input type="text" id="searchBar" placeholder="Search by item name..." onkeyup="filterTable()">
            `;
                menuSection.innerHTML = searchHTML;

                let tableHTML = `
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
                    <tbody id="menuTableBody">
            `;

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
                    </tr>
                `;
                });

                tableHTML += '</tbody></table>';
                menuSection.innerHTML += tableHTML;
            })
            .catch(error => {
                console.error('Error fetching menu:', error);
                document.getElementById('menu-section').innerHTML = '<p>Error loading menu.</p>';
            });
    }

    function filterTable() {
        const searchValue = document.getElementById('searchBar').value.toLowerCase();
        const tableBody = document.getElementById('menuTableBody');
        const rows = Array.from(tableBody.getElementsByTagName('tr'));

        const matchingRows = [];
        const nonMatchingRows = [];

        rows.forEach(row => {
            const itemName = row.getAttribute('data-item-name'); // Get the item name from the row's data attribute

            if (itemName.includes(searchValue)) {
                matchingRows.push(row); // Keep matching rows
            } else {
                nonMatchingRows.push(row); // Keep non-matching rows for later
            }
        });

        // Reorder the rows: matching rows at the top, then non-matching rows
        const allRows = [...matchingRows, ...nonMatchingRows];

        // Clear the table body and append the reordered rows
        tableBody.innerHTML = '';
        allRows.forEach(row => {
            tableBody.appendChild(row);
        });
    }




    // Delete Menu Items By Admin
    function deleteMenuItem(itemId) {
        if (!confirm("Delete this menu item and related variants/add-ons?")) return;

        fetch(`../Backend/delete_menu_item.php?id=${itemId}`, { method: 'DELETE' })
            .then(res => res.text())
            .then(result => {
                alert(result);
                displayMenu(); // refresh
            })
            .catch(err => console.error('Delete failed:', err));
    }



    // Table Bookings
    function showTableBooking() {
        // Use Fetch to get table booking details from the server
        fetch('../Backend/get_table_bookings.php')
            .then(response => response.json())
            .then(data => {
                let tableBookingContainer = document.getElementById('tableBookingContainer');
                tableBookingContainer.innerHTML = ''; // Clear any existing content

                // Create table to display booking details
                let table = document.createElement('table');
                table.innerHTML = `
                <tr>
                    <th>Booking ID</th>
                    <th>Customer Name</th>
                    <th>Table Number</th>
                    <th>Booking Time</th>
                    <th>Status</th>
                </tr>
            `;

                data.forEach(booking => {
                    let row = table.insertRow();
                    row.innerHTML = `
                    <td>${booking.booking_id}</td>
                    <td>${booking.customer_name}</td>
                    <td>${booking.table_number}</td>
                    <td>${booking.booking_time}</td>
                    <td>${booking.status}</td>
                `;
                });

                tableBookingContainer.appendChild(table);
                tableBookingContainer.style.display = 'block'; // Show the container
            })
            .catch(error => console.log('Error loading table bookings:', error));
    }

</script>


<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../Frontend/js/script.js"></script>

</body>
</html>

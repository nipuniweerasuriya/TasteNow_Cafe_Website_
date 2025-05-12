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
                <div class="text-white d-flex justify-content-center align-items-center dashboard-avatar me-3">NW</div>
                <div>
                    <h6 class="mb-0">Nipuni Weerasuriya</h6>
                    <small class="text-muted">nipuni@example.com</small>
                </div>
            </div>

            <!-- Sidebar Menu -->
            <div class="dashboard-actions">
                <a href="../Frontend/index.php" class="dashboard-action-item"><small>Home</small></a>
                <a href="../Backend/kitchen.php" class="dashboard-action-item"><small>Kitchen</small></a>
                <a href="#" class="dashboard-action-item"><small>Cashier</small></a>

                <div class="dropdown-wrapper">
                    <div class="dashboard-action-item" onclick="toggleDropdown('ordersDropdown')">
                        <small>Orders</small>
                    </div>
                    <div class="dropdown-menu" id="ordersDropdown">
                        <div class="dropdown-item">All Orders</div>
                        <div class="dropdown-item">Pending Orders</div>
                        <div class="dropdown-item">Completed Orders</div>
                        <div class="dropdown-item">Canceled Orders</div>
                    </div>
                </div>

                <div class="dropdown-wrapper">
                    <div class="dashboard-action-item" onclick="toggleDropdown('paidDropdown')">
                        <small>Manage</small>
                    </div>
                    <div class="dropdown-menu" id="paidDropdown">
                        <div class="dropdown-item" onclick="showAddMenuForm()">Menu</div>
                        <div class="dropdown-item">Gallery</div>
                        <div class="dropdown-item">Footer</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Orders Display Section -->
        <div class="flex-grow-1">
            <!-- Processed Orders Display -->
            <h4 class="mt-4 mb-3">Processed Orders</h4>
            <div id="current-orders" class="row gy-3"></div>
            <!-- Add Menu Form Container with Yellow Background -->
            <div id="form-container" style="display: none; margin-top: 30px; background-color: yellow; padding: 20px; border-radius: 8px;">
                <h1>Menu</h1>
            </div>


            <!-- Add Menu Form Container -->
            <div id="form-container" style="display: none; margin-top: 30px;"></div>
        </div>
    </div>


</div>

<!-- JS Scripts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/script.js"></script>

</body>
</html>

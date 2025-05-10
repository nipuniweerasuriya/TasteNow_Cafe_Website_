<?php
// Include your database connection file
include('../Backend/db_connect.php');

// Query to fetch cart items and their details
$query = "
    SELECT 
        ci.id AS cart_item_id,
        ci.name AS item_name,
        ci.price AS item_price,
        ci.quantity AS item_quantity,
        ci.variant AS item_variant,
        ci.addons AS item_addons,
        GROUP_CONCAT(ma.addon_name) AS addon_names,
        GROUP_CONCAT(ma.addon_price) AS addon_prices
    FROM 
        cart_items ci
    LEFT JOIN 
        cart_item_addons cia ON ci.id = cia.cart_item_id
    LEFT JOIN 
        menu_add_ons ma ON cia.addon_id = ma.id
    GROUP BY 
        ci.id
";

$result = mysqli_query($conn, $query);

// Array to store cart items
$cart_items = [];

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $cart_item_id = $row['cart_item_id'];
        $item_name = $row['item_name'];
        $item_price = $row['item_price'];
        $item_quantity = $row['item_quantity'];
        $item_variant = $row['item_variant'];
        $addon_names = explode(',', $row['addon_names']);
        $addon_prices = explode(',', $row['addon_prices']);

        // Add the cart item to the array
        $cart_items[] = [
            'cart_item_id' => $cart_item_id,
            'item_name' => $item_name,
            'item_price' => $item_price,
            'item_quantity' => $item_quantity,
            'item_variant' => $item_variant,
            'addon_names' => $addon_names,
            'addon_prices' => $addon_prices,
        ];
    }
} else {
    // Handle error if the query fails
    echo "Error retrieving cart items.";
}

mysqli_close($conn);  // Close the database connection
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart</title>
    <!-- Preconnects -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!-- Load Poppins & Roboto -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700&family=Roboto:wght@300;400;500&display=swap"
          rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&display=swap"
          rel="stylesheet">

    <!--icons-->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined&display=swap" rel="stylesheet"/>

    <!--Bootstrap-->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <!--css link-->
    <link rel="stylesheet" href="css/styles.css"/>
</head>

<body class="common-page" id="cart-page">
<!-- navbar -->
<div>
    <div class="navbar">
        <div class="navbar-container">
            <div class="navbar-brand">
                <a class="navbar-brand logo-wiggle" href="index.php">TASTENOW</a>
            </div>
            <!-- Account -->
            <div class="d-flex align-items-center ms-3">
                <a class="nav-link" href="#">User's Account</a>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Cart Item Part -->
        <div>
            <div class="row">
                <!-- Cart Left -->
                <div class="col-lg-8">
                    <div class="mb-3 d-flex justify-content-between align-items-center">
                        <div>
                            <input type="checkbox" class="form-check-input me-2">
                            <label>SELECT ALL (39 ITEM(S))</label>
                        </div>
                        <button class="btn btn-sm btn-delete">DELETE</button>
                    </div>

                    <!-- Cart Items -->
                    <?php
                    // Cart Items Loop (inside your foreach loop)
                    foreach ($cart_items as $item) {
                        $cart_item_id = $item['cart_item_id'];
                        $item_name = $item['item_name'];
                        $item_price = $item['item_price'];
                        $item_quantity = $item['item_quantity'];
                        $item_variant = $item['item_variant'];
                        $addon_names = $item['addon_names'];
                        $addon_prices = $item['addon_prices'];

                        // Calculate total price (base price + addon prices)
                        $addon_total = array_sum($addon_prices);
                        $total_item_price = ($item_price + $addon_total) * $item_quantity;

                        echo "
        <div class='cart-items-container bg-white p-3 mb-3'>
            <div class='d-flex align-items-start gap-3 cart-item' data-item-id='$cart_item_id'>
                <input type='checkbox' class='mt-2 item-select' data-item-id='$cart_item_id'>
                <img src='./assets/images/Menu/$item_name.jpg' alt='Product'>
                <div class='flex-grow-1'>
                    <p class='item-title'>$item_name</p>
                    <p class='custom-text-danger'>Only 7 item(s) we have now</p>
                    <div class='customizations'>
                        <p><span>Variant:</span> $item_variant</p>";
                        if (!empty($addon_names)) {
                            echo "<p><span>Add-ons:</span> ";
                            foreach ($addon_names as $index => $addon_name) {
                                echo "$addon_name (+Rs.$addon_prices[$index])";
                                if ($index < count($addon_names) - 1) {
                                    echo ", ";
                                }
                            }
                            echo "</p>";
                        }

                        echo "
                    </div>
                </div>
                <div>
                    <div class='item-price'>
                        <span class='price' id='total-$cart_item_id'>Rs. $total_item_price</span>
                    </div>
                    <div class='quantity-control d-flex align-items-center gap-2 mt-2'>
                        <button class='btn btn-outline-secondary btn-sm btn-decrease' data-item-id='$cart_item_id'>−</button>
                        <span class='item-qty' id='qty-$cart_item_id'>$item_quantity</span>
                        <button class='btn btn-outline-secondary btn-sm btn-increase' data-item-id='$cart_item_id'>+</button>
                        <input type='hidden' id='price-$cart_item_id' value='$item_price'>
                        <input type='hidden' id='addons-total-$cart_item_id' value='$addon_total'>
                    </div>
                </div>
            </div>
        </div>";
                    }
                    ?>
                </div>
                <!-- Cart Order Summary -->
                <div class="col-lg-4">
                    <div class="order-summary-container">
                        <h5>Order Summary</h5>
                        <p class="mb-2">Total (0): <span class="price fw-bold" id="order-total">Rs. 0</span></p>
                        <input type="text" class="custom-form-control mb-3" placeholder="Enter Your Table Number">
                        <button class="payment-btn w-100" id="checkout-btn">
                            PROCEED TO CHECKOUT
                        </button>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="js/script.js"></script>

</body>
</html>
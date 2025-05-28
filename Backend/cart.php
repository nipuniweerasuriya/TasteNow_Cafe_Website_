<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location:../Backend/signin.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? 'Guest';

include('../Backend/db_connect.php');

// Fetch only cart items for the current user
$query = "
    SELECT 
        id AS cart_item_id,
        item_id,
        item_name,
        price,
        quantity,
        image_url
    FROM 
        cart_items
    WHERE 
        user_id = ?
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$cart_items = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $cart_items[] = [
            'cart_item_id' => $row['cart_item_id'],
            'item_id' => $row['item_id'],
            'item_name' => $row['item_name'],
            'price' => (float)$row['price'],
            'quantity' => (int)$row['quantity'],
            'image_url' => $row['image_url'] ?? './assets/images/Menu/default.jpg', // fallback image
        ];
    }
}

$stmt->close();
$conn->close();
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
    <link rel="stylesheet" href="../Frontend/css/styles.css"/>
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
                <a class="nav-link" href="#"><?php echo htmlspecialchars($user_name); ?></a>
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
                            <input type="checkbox" id="select-all" class="form-check-input me-2">
                            <label for="select-all">SELECT ALL (<?php echo count($cart_items); ?> ITEM(S))</label>
                        </div>
                        <button class="btn btn-sm btn-delete">DELETE</button>
                    </div>


                    <!-- Cart Items -->
                    <?php foreach ($cart_items as $item):
                        $id = $item['cart_item_id'];
                        $name = htmlspecialchars($item['item_name']);
                        $price = $item['price'];
                        $qty = $item['quantity'];
                        $img = htmlspecialchars($item['image_url']);
                        $total = $price * $qty;
                        ?>
                        <div class='cart-items-container bg-white p-3 mb-3'>
                            <div class='d-flex align-items-start gap-3 cart-item' data-item-id='<?= $id ?>'>
                                <input type='checkbox' class='mt-2 item-select' value="1" data-item-id='<?= $id ?>'>
                                <img src='<?= $img ?>' alt='Product' width='80' height='80'>
                                <div class='flex-grow-1'>
                                    <p class='item-title'><?= $name ?></p>
                                </div>
                                <div>
                                    <div class='item-price'>
                                        <span class='price' id='total-<?= $id ?>'>Rs. <?= number_format($total, 2) ?></span>
                                    </div>
                                    <div class='quantity-control d-flex align-items-center gap-2 mt-2'>
                                        <button class='btn btn-outline-secondary btn-sm btn-decrease' data-item-id='<?= $id ?>'>−</button>
                                        <span class='item-qty' id='qty-<?= $id ?>'><?= $qty ?></span>
                                        <button class='btn btn-outline-secondary btn-sm btn-increase' data-item-id='<?= $id ?>'>+</button>
                                        <input type='hidden' id='price-<?= $id ?>' value='<?= $price ?>'>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                </div>
                <!-- Cart Order Summary -->
                <div class="col-lg-4">
                    <div class="order-summary-container">
                        <h5>Order Summary</h5>
                        <p class="mb-2">Total  <span class="price fw-bold" id="order-total">Rs. 0</span></p>
                        <input type="text" id="tableNumber"  class="custom-form-control mb-3" placeholder="Enter Your Table Number">
                        <button class="payment-btn w-100" id="checkoutBtn">
                            PROCEED TO CHECKOUT
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>




<script>
    // Update Total Price And Qty
    document.addEventListener('DOMContentLoaded', function () {
        const itemCheckboxes = document.querySelectorAll('.item-select');
        const orderTotalElement = document.getElementById('order-total');
        const checkoutBtn = document.getElementById('checkout-btn');
        const selectAllCheckbox = document.getElementById('select-all');

        // ✅ Automatically check all items on page load
        itemCheckboxes.forEach(cb => cb.checked = true);
        if (selectAllCheckbox) selectAllCheckbox.checked = true;

        // ✅ Update total function
        function updateOrderTotal() {
            let total = 0;
            let allSelected = true;
            let anySelected = false;

            itemCheckboxes.forEach(checkbox => {
                if (checkbox.checked) {
                    anySelected = true;
                    const itemId = checkbox.getAttribute('data-item-id');
                    const qty = parseInt(document.getElementById(`qty-${itemId}`).textContent);
                    const price = parseFloat(document.getElementById(`price-${itemId}`).value);
                    total += qty * price;
                } else {
                    allSelected = false;
                }
            });

            orderTotalElement.textContent = `Rs. ${total.toFixed(2)}`;
            checkoutBtn.disabled = !anySelected;

            // ✅ Sync "select all" checkbox state
            if (selectAllCheckbox) {
                selectAllCheckbox.checked = allSelected;
            }
        }

        // ✅ Select All checkbox toggle
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function () {
                const isChecked = this.checked;
                itemCheckboxes.forEach(cb => cb.checked = isChecked);
                updateOrderTotal();
            });
        }

        // ✅ Bind individual item checkbox changes
        itemCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', updateOrderTotal);
        });

        // ✅ Quantity buttons
        document.querySelectorAll('.btn-increase, .btn-decrease').forEach(btn => {
            btn.addEventListener('click', function () {
                const itemId = this.getAttribute('data-item-id');
                const qtyElement = document.getElementById(`qty-${itemId}`);
                const priceElement = document.getElementById(`price-${itemId}`);
                const totalElement = document.getElementById(`total-${itemId}`);

                let qty = parseInt(qtyElement.textContent);
                const price = parseFloat(priceElement.value);

                if (this.classList.contains('btn-increase')) {
                    qty += 1;
                } else {
                    qty = Math.max(1, qty - 1);
                }

                qtyElement.textContent = qty;
                totalElement.textContent = `Rs. ${(qty * price).toFixed(2)}`;

                // Auto-check item
                const checkbox = document.querySelector(`.item-select[data-item-id="${itemId}"]`);
                checkbox.checked = true;

                updateOrderTotal();
            });
        });

        // ✅ Initial total calculation
        updateOrderTotal();
    });





    // Delete Cart Items
    document.addEventListener('DOMContentLoaded', function () {
        // DELETE button click handler
        document.querySelector('.btn-delete').addEventListener('click', function () {
            const selectedCheckboxes = document.querySelectorAll('.item-select:checked');
            if (selectedCheckboxes.length === 0) {
                alert("Please select at least one item to delete.");
                return;
            }

            // Collect selected item IDs
            const itemIds = Array.from(selectedCheckboxes).map(cb => cb.getAttribute('data-item-id'));

            // Send AJAX request
            fetch('delete_cart_items.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ item_ids: itemIds })
            })
                .then(response => response.json())
                .then(data => {
                    console.log('Delete response:', data); // Add this line for debugging
                    if (data.success) {
                        // Remove deleted items from DOM
                        itemIds.forEach(id => {
                            const itemContainer = document.querySelector(`.cart-item[data-item-id='${id}']`);
                            if (itemContainer) itemContainer.closest('.cart-items-container').remove();
                        });
                        updateOrderSummary();
                    } else {
                        alert('Failed to delete items: ' + (data.error || 'Unknown error'));
                    }
                })

                    .catch(err => {
                    console.error('Error:', err);
                    alert('Error deleting items.');
                });
        });

        // Recalculate total
        function updateOrderSummary() {
            let total = 0;
            document.querySelectorAll('.item-select:checked').forEach(cb => {
                const id = cb.getAttribute('data-item-id');
                const qty = parseInt(document.getElementById(`qty-${id}`).textContent);
                const price = parseFloat(document.getElementById(`price-${id}`).value);
                total += qty * price;
            });
            document.getElementById('order-total').textContent = `Rs. ${total.toFixed(2)}`;
        }

        // Update total on checkbox change
        document.querySelectorAll('.item-select').forEach(cb => {
            cb.addEventListener('change', updateOrderSummary);
        });
    });




    // Checkout Orders
    // ✅ Checkout Orders (UPDATED)
    document.getElementById('checkoutBtn').addEventListener('click', () => {
        const tableNumber = document.getElementById('tableNumber').value.trim();
        const userId = 1; // Replace with session user ID if needed

        const checkedBoxes = document.querySelectorAll('.item-select:checked');
        if (checkedBoxes.length === 0) {
            alert('Select items to checkout');
            return;
        }

        if (!tableNumber) {
            alert('Enter table number');
            return;
        }

        // Prepare items with updated quantity and prices
        const cartItems = Array.from(checkedBoxes).map(cb => {
            const itemId = cb.getAttribute('data-item-id');
            const qty = parseInt(document.getElementById(`qty-${itemId}`).textContent);
            const price = parseFloat(document.getElementById(`price-${itemId}`).value);
            return { cart_item_id: itemId, quantity: qty, price: price };
        });

        const payload = {
            user_id: userId,
            table_number: tableNumber,
            cart_items: cartItems
        };

        fetch('process_checkout.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(payload)
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert('Checkout successful! Order ID: ' + data.order_id);
                    location.reload();
                } else {
                    alert('Checkout failed: ' + data.message);
                }
            })
            .catch(err => {
                console.error('Error:', err);
                alert('An error occurred');
            });
    });

</script>


<script src="../Frontend/js/script.js"></script>

</body>
</html>

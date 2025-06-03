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
            'image_url' => $row['image_url'] ?? './assets/images/Menu/default.jpg',
        ];
    }
}

$stmt->close();
$conn->close();
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Cart</title>
    <!-- Google Fonts, Bootstrap, Icons etc. -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700&family=Roboto:wght@300;400;500&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined&display=swap" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
    <link rel="stylesheet" href="../Frontend/css/styles.css" />
</head>

<body class="common-page" id="cart-page">
<div>
    <div class="navbar">
        <div class="navbar-container">
            <div class="navbar-brand">
                <a class="navbar-brand logo-wiggle" href="index.php">TASTENOW</a>
            </div>
            <div class="d-flex align-items-center ms-3">
                <a class="nav-link" href="#"><?= htmlspecialchars($user_name) ?></a>
            </div>
        </div>
    </div>

    <div class="container">
        <div>
            <div class="row">
                <div class="col-lg-8">
                    <div class="mb-3 d-flex justify-content-between align-items-center">
                        <div>
                            <input type="checkbox" id="select-all" class="form-check-input me-2" />
                            <label for="select-all">SELECT ALL (<?= count($cart_items) ?> ITEM(S))</label>
                        </div>
                        <button class="btn btn-sm btn-delete">DELETE</button>
                    </div>

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
                                <input type='checkbox' class='mt-2 item-select' value="1" data-item-id='<?= $id ?>' />
                                <img src='<?= $img ?>' alt='Product' width='80' height='80' />
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
                                        <input type='hidden' id='price-<?= $id ?>' value='<?= $price ?>' />
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="col-lg-4">
                    <div class="order-summary-container">
                        <h5>Order Summary</h5>
                        <p class="mb-2">Total  <span class="price fw-bold" id="order-total">Rs. 0</span></p>
                        <input type="text" id="tableNumber"  class="custom-form-control mb-3" placeholder="Enter Your Table Number" />
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
    document.addEventListener('DOMContentLoaded', function () {
        const container = document.querySelector('.container');
        const orderTotalElement = document.getElementById('order-total');
        const checkoutBtn = document.getElementById('checkoutBtn');
        const selectAllCheckbox = document.getElementById('select-all');

        // Auto-check all items on load
        function getItemCheckboxes() {
            return container.querySelectorAll('.item-select');
        }
        getItemCheckboxes().forEach(cb => cb.checked = true);
        if (selectAllCheckbox) selectAllCheckbox.checked = true;

        function updateOrderTotal() {
            let total = 0;
            let allSelected = true;
            let anySelected = false;
            getItemCheckboxes().forEach(checkbox => {
                if (checkbox.checked) {
                    anySelected = true;
                    const itemId = checkbox.dataset.itemId;
                    const qty = parseInt(document.getElementById(`qty-${itemId}`).textContent.trim(), 10);
                    const price = parseFloat(document.getElementById(`price-${itemId}`).value);
                    total += qty * price;
                } else {
                    allSelected = false;
                }
            });

            orderTotalElement.textContent = `Rs. ${total.toFixed(2)}`;
            checkoutBtn.disabled = !anySelected;
            if (selectAllCheckbox) selectAllCheckbox.checked = allSelected;
        }

        // Handle select all
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function () {
                const isChecked = this.checked;
                getItemCheckboxes().forEach(cb => cb.checked = isChecked);
                updateOrderTotal();
            });
        }

        container.addEventListener('change', function(e) {
            if (e.target.classList.contains('item-select')) {
                updateOrderTotal();
            }
        });

        // Event delegation for quantity buttons
        container.addEventListener('click', function(e) {
            if (e.target.classList.contains('btn-increase') || e.target.classList.contains('btn-decrease')) {
                const btn = e.target;
                const itemId = btn.dataset.itemId;
                const qtyElement = document.getElementById(`qty-${itemId}`);
                const price = parseFloat(document.getElementById(`price-${itemId}`).value);
                const totalElement = document.getElementById(`total-${itemId}`);
                let qty = parseInt(qtyElement.textContent.trim(), 10);

                if (btn.classList.contains('btn-increase')) {
                    qty++;
                } else {
                    qty = Math.max(1, qty - 1);
                }

                qtyElement.textContent = qty;
                totalElement.textContent = `Rs. ${(qty * price).toFixed(2)}`;

                // Auto-check item
                const itemCheckbox = container.querySelector(`.item-select[data-item-id="${itemId}"]`);
                if (itemCheckbox) itemCheckbox.checked = true;

                updateOrderTotal();
            }
        });

        // DELETE selected cart items
        container.querySelector('.btn-delete')?.addEventListener('click', function () {
            const selected = Array.from(getItemCheckboxes()).filter(cb => cb.checked);
            if (selected.length === 0) {
                return alert("Please select at least one item to delete.");
            }

            const itemIds = selected.map(cb => cb.dataset.itemId);

            fetch('delete_cart_items.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ item_ids: itemIds })
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        itemIds.forEach(id => {
                            const item = container.querySelector(`.cart-item[data-item-id="${id}"]`);
                            if (item) item.closest('.cart-items-container')?.remove();
                        });
                        updateOrderTotal();
                    } else {
                        alert('Failed to delete: ' + (data.error || 'Unknown error'));
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('Error deleting items.');
                });
        });

        // Checkout
        checkoutBtn?.addEventListener('click', () => {
            const tableNumber = document.getElementById('tableNumber').value.trim();
            if (!tableNumber) {
                alert('Please enter your table number.');
                return;
            }

            const selectedItems = Array.from(getItemCheckboxes())
                .filter(cb => cb.checked)
                .map(cb => {
                    const itemId = cb.dataset.itemId;
                    return {
                        cart_item_id: itemId,
                        quantity: parseInt(document.getElementById(`qty-${itemId}`).textContent.trim(), 10)
                    };
                });

            if (selectedItems.length === 0) {
                alert('Please select items to checkout.');
                return;
            }

            // Post order details to backend (you need to implement checkout logic)
            fetch('checkout.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ tableNumber, items: selectedItems })
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert('Order placed successfully!');
                        location.reload();
                    } else {
                        alert('Failed to place order: ' + (data.error || 'Unknown error'));
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('Checkout error.');
                });
        });

        updateOrderTotal();
    });
</script>
</body>
</html>

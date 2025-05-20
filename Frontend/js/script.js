











//Cart
document.addEventListener('DOMContentLoaded', function () {
    // Function to update the total price of the cart
    function updateOrderTotal() {
        let orderTotal = 0;
        let selectedItemsCount = 0;

        document.querySelectorAll('.cart-item').forEach(function (item) {
            const checkbox = item.querySelector('.item-select');
            if (checkbox.checked) {
                selectedItemsCount++;
                const qty = parseInt(item.querySelector('.item-qty').textContent);
                const basePrice = parseFloat(item.querySelector('#price-' + item.dataset.itemId).value);
                const addonTotal = parseFloat(item.querySelector('#addons-total-' + item.dataset.itemId).value);
                orderTotal += (basePrice + addonTotal) * qty;
            }
        });

        // Update the order total in the summary
        document.getElementById('order-total').textContent = 'Rs. ' + orderTotal.toFixed(2);

        // Update the SELECT ALL text with the number of selected items
        const selectAllLabel = document.querySelector('label');
        selectAllLabel.textContent = `SELECT ALL (${selectedItemsCount} ITEM(S))`;
    }

    // Handle the "Select All" checkbox
    const selectAllCheckbox = document.querySelector('input[type="checkbox"]');
    selectAllCheckbox.addEventListener('change', function () {
        const isChecked = selectAllCheckbox.checked;
        document.querySelectorAll('.item-select').forEach(function (checkbox) {
            checkbox.checked = isChecked;
        });
        updateOrderTotal();
    });

    // Handle item selection checkbox change
    document.querySelectorAll('.item-select').forEach(function (checkbox) {
        checkbox.addEventListener('change', function () {
            updateOrderTotal();
        });
    });

    // Handle the quantity increase and decrease
    document.querySelectorAll('.cart-item').forEach(function (item) {
        const increaseBtn = item.querySelector('.btn-increase');
        const decreaseBtn = item.querySelector('.btn-decrease');
        const qtySpan = item.querySelector('.item-qty');
        const totalSpan = item.querySelector('.item-price .price');
        const cartItemId = item.dataset.itemId;

        increaseBtn.addEventListener('click', function () {
            let qty = parseInt(qtySpan.textContent);
            qty++;
            qtySpan.textContent = qty;
            const basePrice = parseFloat(document.getElementById('price-' + cartItemId).value);
            const addonTotal = parseFloat(document.getElementById('addons-total-' + cartItemId).value);
            const newTotal = (basePrice + addonTotal) * qty;
            totalSpan.textContent = 'Rs. ' + newTotal.toFixed(2);
            updateOrderTotal();
        });

        decreaseBtn.addEventListener('click', function () {
            let qty = parseInt(qtySpan.textContent);
            if (qty > 1) {
                qty--;
                qtySpan.textContent = qty;
                const basePrice = parseFloat(document.getElementById('price-' + cartItemId).value);
                const addonTotal = parseFloat(document.getElementById('addons-total-' + cartItemId).value);
                const newTotal = (basePrice + addonTotal) * qty;
                totalSpan.textContent = 'Rs. ' + newTotal.toFixed(2);
                updateOrderTotal();
            }
        });
    });

    // Handle "Delete Selected" button click
    document.querySelector('.btn-delete').addEventListener('click', function () {
        const selectedItems = [];
        document.querySelectorAll('.item-select:checked').forEach(function (checkbox) {
            const cartItemId = checkbox.dataset.itemId;
            selectedItems.push(cartItemId);
        });

        if (selectedItems.length > 0) {
            // Perform an AJAX request to delete the selected items
            const formData = new FormData();
            formData.append('action', 'delete_cart_items');
            formData.append('cart_item_ids', JSON.stringify(selectedItems));

            fetch('../Backend/delete_cart_items.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    console.log(data); // Debugging step
                    if (data.success) {
                        // Loop through each selected item and remove its corresponding element
                        selectedItems.forEach(function (itemId) {
                            const itemElement = document.querySelector(`[data-item-id="${itemId}"]`);
                            if (itemElement) {
                                itemElement.remove();  // Remove the item from the DOM immediately
                            }
                        });
                        updateOrderTotal();  // Update the order total after removing items
                    } else {
                        // Display error message if deletion failed
                        alert('Failed to delete selected items: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to delete items. Please try again.');
                });
        } else {
            alert('No items selected to delete.');
        }
    });
});

document.getElementById('checkout-btn').addEventListener('click', function () {
    // Get the table number input value
    const tableNumber = document.querySelector('input[placeholder="Enter Your Table Number"]').value;

    if (!tableNumber) {
        alert("Please enter a table number.");
        return;
    }

    // Get the selected items
    const selectedItems = [];
    document.querySelectorAll('.item-select:checked').forEach(function (checkbox) {
        const cartItemId = checkbox.dataset.itemId;
        const qty = parseInt(checkbox.closest('.cart-item').querySelector('.item-qty').textContent);
        const basePrice = parseFloat(document.getElementById('price-' + cartItemId).value);
        const addonTotal = parseFloat(document.getElementById('addons-total-' + cartItemId).value);

        // Add item details to the array
        selectedItems.push({
            cart_item_id: cartItemId,
            quantity: qty,
            base_price: basePrice,
            addon_total: addonTotal
        });
    });

    if (selectedItems.length === 0) {
        alert("Please select at least one item.");
        return;
    }

    // Send the data via AJAX
    const formData = new FormData();
    formData.append('table_number', tableNumber);
    formData.append('selected_items', JSON.stringify(selectedItems));

    fetch('../Backend/process_order.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Order processed successfully!');
                // Redirect to a success page or update the UI as needed
            } else {
                alert('Failed to process order: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to process the order. Please try again.');
        });
});



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
                                    <small class="text-muted">Ordered on: ${new Date(order.order_date).toLocaleString()}</small>
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



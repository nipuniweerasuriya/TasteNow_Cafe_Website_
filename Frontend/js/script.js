/* Navbar Fixed Top */
window.addEventListener('scroll', function () {
    const navbar = document.getElementById('navbar');
    const topBarHeight = document.querySelector('.top-bar')?.offsetHeight || 0;

    if (window.scrollY > topBarHeight) {
        navbar?.classList.add('fixed-top', 'navbar-scrolled');
        document.body.classList.add('fixed-nav-padding');
    } else {
        navbar?.classList.remove('fixed-top', 'navbar-scrolled');
        document.body.classList.remove('fixed-nav-padding');
    }
});

/* Menu See More Btn */
document.addEventListener("DOMContentLoaded", function () {
    const menuItems = document.querySelectorAll(".menu-item");
    const seeMoreBtn = document.getElementById("see-more-btn");

    const initiallyVisible = 8;

    menuItems.forEach((item, index) => {
        if (index < initiallyVisible) {
            item.classList.add("visible");
        }
    });

    seeMoreBtn?.addEventListener("click", function () {
        menuItems.forEach(item => item.classList.add("visible"));
        seeMoreBtn.style.display = "none";
    });
});



//* Menu Display Logic */
document.addEventListener('DOMContentLoaded', function () {
    const menuItems = document.querySelectorAll('.menu-item');
    const seeMoreBtn = document.getElementById('see-more-btn');

    // Hide all items after the first 4
    menuItems.forEach((item, index) => {
        if (index >= 4) {
            item.style.display = 'none';
        }
    });

    // Show all when See More is clicked
    seeMoreBtn.addEventListener('click', () => {
        menuItems.forEach(item => {
            item.style.display = 'block';
        });
        seeMoreBtn.style.display = 'none'; // Hide button after clicked
    });
});


document.addEventListener('DOMContentLoaded', () => {
    // CATEGORY FILTERING
    const buttons = document.querySelectorAll('.category-button');

    buttons.forEach(button => {
        button.addEventListener('click', () => {
            const category = button.getAttribute('data-category').toLowerCase();
            const items = document.querySelectorAll('.menu-item');

            items.forEach(item => {
                const itemCategory = item.getAttribute('data-category').toLowerCase();
                item.style.display = (category === 'all' || itemCategory === category) ? 'block' : 'none';
            });

            // Highlight active button
            buttons.forEach(btn => btn.classList.remove('active'));
            button.classList.add('active');
        });
    });

    // ADD TO CART BUTTONS
    const addToCartButtons = document.querySelectorAll('.add-to-cart-btn');

    addToCartButtons.forEach(button => {
        button.addEventListener('click', function () {
            const itemId = this.getAttribute('data-id');
            const itemDiv = this.closest('.menu-item');
            const variants = JSON.parse(itemDiv.getAttribute('data-variants'));
            const addOns = JSON.parse(itemDiv.getAttribute('data-addons'));

            openModal(itemId, variants, addOns);
        });
    });

    // ADD ITEM TO CART WITH OPTIONS
    document.getElementById('addToCartWithOptions').addEventListener('click', () => {
        const modal = document.getElementById("menu-options-modal");

        // Get all selected variants
        const variantOptions = document.querySelectorAll('#variantsDropdown option:checked');
        const variantIds = Array.from(variantOptions).map(opt => parseInt(opt.value));

        if (variantIds.length === 0) {
            alert("Please select at least one variant.");
            return;
        }

        const addOnCheckboxes = document.querySelectorAll('#addOnsContainer input[type="checkbox"]:checked');
        const addOnIds = Array.from(addOnCheckboxes).map(cb => parseInt(cb.value));

        const itemId = parseInt(modal.getAttribute('data-item-id'));

        const cartData = {
            itemId: itemId,
            variantIds: variantIds,
            addOnIds: addOnIds
        };

        // Send to server
        fetch('../Backend/add_to_cart.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(cartData)
        })
            .then(async response => {
                const text = await response.text();
                try {
                    const data = JSON.parse(text);
                    if (data.status === 'success') {
                        alert('Item added to cart!');
                        modal.style.display = "none";
                    } else {
                        alert(data.message || 'Failed to add to cart.');
                    }
                } catch (e) {
                    console.error('Non-JSON response from server:', text);
                    alert('Unexpected server response. Check console.');
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                alert('Server error. Check console for details.');
            });
    });
});

// MODAL HANDLING
function openModal(itemId, variants, addOns) {
    const modal = document.getElementById("menu-options-modal");
    modal.style.display = "block";
    modal.setAttribute('data-item-id', itemId);

    // Populate variant dropdown (multi-select)
    const variantsDropdown = document.getElementById('variantsDropdown');
    variantsDropdown.innerHTML = '';
    variantsDropdown.multiple = true; // ensure it's multiple

    variants.forEach(variant => {
        let option = document.createElement('option');
        option.value = variant.id;
        option.textContent = `${variant.variant_name} - Rs. ${variant.price}`;
        variantsDropdown.appendChild(option);
    });

    // Populate add-ons
    const addOnsContainer = document.getElementById('addOnsContainer');
    addOnsContainer.innerHTML = '';
    addOns.forEach(addOn => {
        let checkbox = document.createElement('input');
        checkbox.type = 'checkbox';
        checkbox.id = `addon-${addOn.id}`;
        checkbox.value = addOn.id;

        let label = document.createElement('label');
        label.setAttribute('for', `addon-${addOn.id}`);
        label.textContent = `${addOn.addon_name} - Rs. ${addOn.addon_price}`;

        addOnsContainer.appendChild(checkbox);
        addOnsContainer.appendChild(label);
        addOnsContainer.appendChild(document.createElement('br'));
    });

    // Close modal
    document.getElementById('close-modal').onclick = () => {
        modal.style.display = "none";
    };
}


// Sign In and Sign Up Toggle Logic
const signinBtn = document.getElementById('signing-btn');
const dropdown = document.getElementById('form-dropdown');
const signinForm = document.getElementById('signing-form');
const signupForm = document.getElementById('signup-form');
const switchToSignup = document.getElementById('switch-to-signup');
const switchToSignin = document.getElementById('switch-to-signin');

// Toggle dropdown visibility
signinBtn?.addEventListener('click', (e) => {
    e.stopPropagation(); // Prevent event bubbling
    const isVisible = dropdown.style.display === "block";
    dropdown.style.display = isVisible ? "none" : "block";
    signinForm.style.display = "block";
    signupForm.style.display = "none";
});

// Switch to the SignUp form
switchToSignup?.addEventListener('click', (e) => {
    e.preventDefault();
    signinForm.style.display = "none";
    signupForm.style.display = "block";
});

// Switch to Sign In form
switchToSignin?.addEventListener('click', (e) => {
    e.preventDefault();
    signupForm.style.display = "none";
    signinForm.style.display = "block";
});

// Optional: Close dropdown if clicked outside
window.addEventListener('click', (e) => {
    if (!dropdown.contains(e.target) && e.target !== signinBtn) {
        dropdown.style.display = "none";
    }
});





//ADMIN PAGE DROPDOWN LOGIC
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



document.addEventListener("DOMContentLoaded", function () {
    window.showAddMenuForm = function () {
        const formContainer = document.getElementById('form-container');
        formContainer.style.display = 'block';
        formContainer.scrollIntoView({ behavior: "smooth" });

        if (formContainer.innerHTML.trim() !== '') return;

        formContainer.innerHTML = `
            <div class="add-menu-container">
                <h2 class="form-heading">-----Add New Menu Item-----</h2>
                <form class="menu-form" action="../Backend/add_menu_item.php/" method="POST" enctype="multipart/form-data" onsubmit="return handleFormSubmit(event)">
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



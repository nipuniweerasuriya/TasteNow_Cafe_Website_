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
    // Show the form
    window.showAddMenuForm = function () {
        const formContainer = document.getElementById('form-container');
        formContainer.style.display = 'block';

        // Avoid duplicate rendering
        if (formContainer.innerHTML.trim() !== '') return;

        formContainer.innerHTML = `
            <div class="add-menu-container">
                <h2 class="form-heading">-----Add New Menu Item-----</h2>
                <form class="menu-form" action="../Backend/add_menu_item.php" method="POST" enctype="multipart/form-data" onsubmit="return handleFormSubmit(event)">
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
                </form>
            </div>
        `;
    };

    // Handle form submission
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
            })
            .catch(error => {
                console.error('Error:', error);
                alert("There was an error adding the menu item.");
            });

        return false;
    };

    // Add new variant input row
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

    // Add new add-on input row
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

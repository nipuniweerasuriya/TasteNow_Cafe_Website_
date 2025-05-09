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

/* Menu Adds To Cart And Cart */
let cart = [];
let currentItem = null;

document.querySelectorAll('.add-to-cart-btn').forEach((btn) => {
    btn.addEventListener('click', () => {
        const itemEl = btn.parentElement;
        const name = itemEl.querySelector('h6').innerText;
        const price = parseFloat(itemEl.querySelector('p').innerText.replace('Rs.', ''));

        currentItem = {name, price};
        document.getElementById('modal-item-name').innerText = name;
        document.getElementById('modal-item-price').value = price;
        document.getElementById('modal-qty').value = 1;
        document.getElementById('modal-request').value = '';
        document.querySelectorAll('#customization-modal input[type="checkbox"]').forEach(cb => cb.checked = false);
        document.getElementById('customization-modal').style.display = 'flex';
    });
});

function closeModal() {
    document.getElementById('customization-modal').style.display = 'none';
}

function addToCart() {
    const variantSelect = document.getElementById('modal-variant');
    const variant = variantSelect.value;
    const variantExtra = parseFloat(variantSelect.selectedOptions[0].dataset.extra);

    const checkboxes = document.querySelectorAll('#customization-modal input[type="checkbox"]:checked');
    const addOns = Array.from(checkboxes).map(cb => ({
        name: cb.value,
        price: parseFloat(cb.dataset.price)
    }));

    const specialRequest = document.getElementById('modal-request').value;
    const quantity = parseInt(document.getElementById('modal-qty').value);
    const basePrice = parseFloat(document.getElementById('modal-item-price').value);

    const item = {
        ...currentItem,
        variant,
        variantExtra,
        addOns,
        specialRequest,
        quantity,
        basePrice
    };

    cart.push(item);
    closeModal();
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

<?php
session_start();
?>
<!doctype html>
<html lang="en">


<head>
    <!-- Section: Head -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TasteNow</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600;700&family=Roboto:wght@300;400;500&display=swap"
          rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&display=swap"
          rel="stylesheet">

    <!-- Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="../Frontend/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>


<body id="home_page">

<!-- Section: Topbar -->
<div class="container-fluid">
    <div class="top-bar">
        <div class="top-bar-left">
            <span class="material-symbols-outlined icon-mail">mail</span><span>support@tastenow.com</span>
            <span class="material-symbols-outlined icon-call">call</span><span>+94 76 123 4567</span>
        </div>
        <div class="top-bar-right">
            <span class="material-symbols-outlined icon-time">schedule</span><span>Mon-Sun 8.00am to 9.00pm</span>
        </div>
    </div>


    <!-- Section: Navbar -->
    <nav class="navbar navbar-expand-lg bg-body-tertiary" id="navbar">
        <div class="container navbar-container">
            <a class="navbar-brand logo-wiggle" href="index.php">TASTENOW</a>

            <!-- Toggler Button -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavAltMarkup"
                    aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
                <div class="navbar-nav ms-auto align-items-lg-center d-flex gap-2">
                    <a class="nav-link active" href="#">Home</a>
                    <a class="nav-link" href="#">Menu</a>
                    <a class="nav-link" href="#">About</a>
                    <a class="nav-link" href="#">Contact</a>
                    <a class="nav-link" href="#">Feedback</a>


                    <!-- Section: Profile Direction-->
                    <?php
                    if (isset($_SESSION['user_name']) && isset($_SESSION['role'])):
                        switch ($_SESSION['role']) {
                            case 'admin':
                                $profileLink = "../Frontend/admin_dashboard.php";
                                break;
                            case 'kitchen':
                                $profileLink = "../Backend/kitchen.php";
                                break;
                            case 'cashier':
                                $profileLink = "../Backend/cashier.php";
                                break;
                            default:
                                $profileLink = "../Frontend/profile.php";
                        }
                        ?>
                        <a class="nav-link"
                           href="<?= $profileLink ?>"><?= htmlspecialchars($_SESSION['user_name']) ?></a>
                    <?php else: ?>
                        <a class="nav-link" href="#" id="signing-btn">Sign In</a>
                    <?php endif; ?>
                </div>


                <!-- Section: Sign In/Sign Up Form -->
                <div id="form-dropdown" class="form-container" style="display: none;">
                    <!-- Sign In Form -->
                    <form action="../Backend/signin.php" method="post">
                        <div id="signing-form">
                            <h2>Sign In</h2>
                            <label>Email</label>
                            <input type="email" name="email" required placeholder="Enter Your Email">
                            <label>Password</label>
                            <input type="password" name="password" required placeholder="Enter Your Password">
                            <label>Role</label>
                            <select name="role" required>
                                <option value="user">User</option>
                                <option value="admin">Admin</option>
                                <option value="kitchen">Kitchen</option>
                                <option value="cashier">Cashier</option>
                            </select>
                            <button type="submit">SIGN IN</button>
                            <p>Don't have an account? <a href="#" id="switch-to-signup">Register</a></p>
                        </div>
                    </form>

                    <!-- Sign Up Form -->
                    <form action="../Backend/signup.php" method="post">
                        <div id="signup-form" style="display:none;">
                            <h2>Sign Up</h2>
                            <label>Username</label>
                            <input type="text" name="name" required placeholder="Enter Your Name">
                            <label>Email</label>
                            <input type="email" name="email" required placeholder="Enter Your Email">
                            <label>Password</label>
                            <input type="password" name="password" required placeholder="Enter Your Password">
                            <label>Confirm Password</label>
                            <input type="password" name="confirm-password" required placeholder="Confirm Your Password">
                            <label>Role</label>
                            <select name="role" required>
                                <option value="admin">Admin</option>
                                <option value="kitchen">Kitchen</option>
                                <option value="cashier">Cashier</option>
                                <option value="user">User</option>
                            </select>
                            <button type="submit">SIGN UP</button>
                            <p>Already have an account? <a href="#" id="switch-to-signin">Sign In</a></p>
                        </div>
                    </form>
                </div>

                <!-- Section: Cart Icon -->
                <div class="d-flex align-items-center ms-3">
                    <?php if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'kitchen' && $_SESSION['role'] !== 'cashier')): ?>
                        <a href="cart.php">
                            <span class="material-symbols-outlined icon-cart me-3">shopping_cart</span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>


    <!-- Section: Welcome Message -->
    <div class="container h1-container">
        <h1 class="h1-heading">Welcome to <span>TasteNow</span></h1>
        <p class="p-0">Serving delicious moments for over 18 years!</p>
        <button class="btn btn-menu">OUR MENU</button>
        <button class="btn btn-booking">BOOK A TABLE</button>
    </div>
</div>


<!-- Section: About -->
<div class="about-us">
    <div class="images-grid">
        <div class="image-box"><img src="assets/images/gallery/shop-1.webp" alt="Image 1"></div>
        <div class="image-box"><img src="assets/images/gallery/events-1.webp" alt="Image 2"></div>
        <div class="image-box"><img src="assets/images/gallery/shop-2.webp" alt="Image 3"></div>
        <div class="image-box"><img src="assets/images/gallery/events-2.webp" alt="Image 4"></div>
    </div>
    <div class="about-text">
        <h2 class="section-title">----- About Us -----</h2>
        <h2>Welcome to <strong>TASTENOW</strong></h2>
        <p>Welcome to TasteNow, your cozy corner for great coffee, delicious food,
            and warm conversations. We’re passionate about creating a relaxed,
            welcoming atmosphere where everyone feels at home—whether you're
            stopping by for a quick bite, meeting friends, or just enjoying a
            quiet moment to yourself. Every item on
            our menu is made with care, using fresh, locally sourced ingredients
            to bring out the best in every flavor.
            At TasteNow, we believe that a café is more than just a place to
            eat—it’s a space to connect, unwind, and enjoy the little things.
            From our handcrafted beverages to our tasty pastries and meals,
            we’re here to make your day a little brighter. We invite you to come in,
            take a seat, and experience the comfort and quality that make TasteNow a
            local favorite.</p>
    </div>
</div>


<!-- Section: Menu -->
<section id="menu">
    <div class="menu-heading-container">
        <h2>-----Our Menu-----</h2>
        <span id="filter-toggle" class="material-symbols-outlined" onclick="toggleCategories()">filter_list</span>
    </div>

    <div id="category-container">
        <button class="category-button" data-category="all">All</button>
        <button class="category-button" data-category="coffee">Coffee</button>
        <button class="category-button" data-category="tea">Tea</button>
        <button class="category-button" data-category="smoothies">Smoothies</button>
        <button class="category-button" data-category="snacks-&-pastries">Snacks & Pastries</button>
        <button class="category-button" data-category="desserts">Desserts</button>
        <button class="category-button" data-category="drinks">Drinks</button>
    </div>

    <div id="menu-container">
        <!-- Section: Get Menu Items In The Backend And Display Dynamically -->
        <?php include '../Backend/get_menu_items.php'; ?>
    </div>

    <button class="see-more-btn" id="see-more-btn">See More</button>
</section>

<!-- Section: Modal For Variants and Add-ons -->
<div id="menu-options-modal" class="modal">
    <div class="modal-content">
        <span id="close-modal" class="close">&times;</span>
        <h6>Select Variant:</h6>
        <select id="variantsDropdown">
            <option value="">Select Variant</option>
        </select>
        <h6>Add-ons:</h6>
        <div id="addOnsContainer"></div>
        <button id="addToCartWithOptions">Add</button>
    </div>
</div>


<!-- Section: Table Booking -->
<div class="booking-container">
    <h2 class="form-heading">-----Book Your Table-----</h2>
    <form class="booking-form" action="../Backend/table_booking.php" method="POST">
        <div class="form-row">
            <input type="text" name="name" placeholder="Your Name" required/>
            <input type="tel" name="phone" placeholder="Your Phone Number" required/>
            <input type="email" name="email" placeholder="Your Email" required/>
        </div>
        <div class="form-row">
            <input type="number" name="number_of_people" placeholder="Number of People" required/>
            <input type="date" name="booking_date" required/>
            <input type="time" name="booking_time" required/>
        </div>
        <textarea name="special_request" placeholder="Special Request" rows="4"></textarea>
        <button type="submit">Book Now</button>
    </form>
</div>


<!-- Section: Footer -->
<footer class="footer text-white pt-5 pb-3">
    <div class="container">
        <div class="row">
            <!-- Section: About -->
            <div class="col-md-3 mb-4">
                <a class="text-uppercase footer-brand">Tastenow</a>
                <p>Delicious moments delivered.</p>
                <ul class="list-payment-icon">
                    <li><img src="assets/images/master.png" alt="Mastercard" class="payment-icon"/></li>
                    <li><img src="assets/images/visa.png" alt="Visa" class="payment-icon"/></li>
                    <li><img src="assets/images/cash-payment.png" alt="Cash" class="payment-icon"/></li>
                </ul>
            </div>

            <!--Section: Quick Links -->
            <div class="col-md-3 mb-4">
                <h6 class="footer-sub-headings text-uppercase">Quick Links</h6>
                <ul class="list-unstyled">
                    <li><a href="#">Home</a></li>
                    <li><a href="#">Menu</a></li>
                    <li><a href="#">Book a Table</a></li>
                    <li><a href="#">Contact</a></li>
                </ul>
            </div>

            <!-- Section: Contact -->
            <div class="col-md-3 mb-4">
                <h5 class="footer-sub-headings text-uppercase">Contact Us</h5>
                <p><i class="bi bi-envelope me-2"></i>support@tastehub.com</p>
                <p><i class="bi bi-telephone me-2"></i>+94 76 123 4567</p>
                <p><i class="bi bi-geo-alt me-2"></i>Colombo, Sri Lanka</p>
                <div class="mt-3">
                    <a href="#" class="social-icon me-3"><i class="bi bi-facebook fs-4"></i></a>
                    <a href="#" class="social-icon me-3"><i class="bi bi-instagram fs-4"></i></a>
                    <a href="#" class="social-icon me-3"><i class="bi bi-tiktok fs-4"></i></a>
                    <a href="#" class="social-icon me-3"><i class="bi bi-whatsapp fs-4"></i></a>
                </div>
            </div>

            <!-- Section: Feedback -->
            <div class="col-md-3 mb-4">
                <h5 class="footer-sub-headings text-uppercase">Feedback</h5>
                <form>
                    <textarea class="feedback-form" rows="3" placeholder="Your feedback..."></textarea>
                    <button type="submit" class="submit-btn btn-light mt-2">Submit</button>
                </form>
            </div>
        </div>
    </div>
    <div class="footer-bottom-bar text-center py-2 mt-3">
        © 2025 Tastenow. All rights reserved.
    </div>
</footer>


<!-- Section: Js Link-->
<script>
    // Navbar Fixed Top
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


    // Menu See More Btn
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

    //Menu Display Logic
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


    // Category Filtering
    document.addEventListener('DOMContentLoaded', () => {
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

        // Add To Cart Button
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

        // Add Item To Cart With Variants
        document.getElementById('addToCartWithOptions').addEventListener('click', () => {
            const modal = document.getElementById("menu-options-modal");

            // Get All Selected Variants
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

            // Send to Server
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

    // Modal Handling
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




    /*Category Toggler */
    function toggleCategories() {
        const categoryContainer = document.getElementById('category-container');
        const isVisible = categoryContainer.classList.toggle('show');

        // Lock or unlock page scroll
        if (isVisible) {
            document.body.classList.add('category-open');
        } else {
            document.body.classList.remove('category-open');
        }
    }


</script>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script src="../Frontend/js/script.js"></script>

</body>
</html>

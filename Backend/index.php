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
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">


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
                    <a class="nav-link" href="#menu">Menu</a>
                    <a class="nav-link" href="#about">About</a>
                    <a class="nav-link" href="#footer">Contact</a>
                    <a class="nav-link" href="#footer">Feedback</a>


                    <!-- Section: Profile Direction-->
                    <?php
                    if (isset($_SESSION['user_name']) && isset($_SESSION['role'])):
                        switch ($_SESSION['role']) {
                            case 'admin':
                                $profileLink = "../Backend/admin_dashboard.php";
                                break;
                            case 'kitchen':
                                $profileLink = "../Backend/kitchen.php";
                                break;
                            case 'cashier':
                                $profileLink = "../Backend/cashier.php";
                                break;
                            default:
                                $profileLink = "../Backend/profile.php";
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
                    <form action="signin.php" method="post">
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
                    <form action="signup.php" method="post">
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
                <!-- Section: Cart Icon -->
                <div class="d-flex align-items-center ms-3">
                    <?php if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'kitchen' && $_SESSION['role'] !== 'cashier')): ?>
                        <a href="cart.php" style="position: relative; display: inline-block;">
                            <span class="material-symbols-outlined icon-cart me-3">shopping_cart</span>
                            <span id="cart-qty-badge" class="cart-badge">0</span>
                        </a>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </nav>


    <!-- Section: Welcome Message -->
    <div class="main-container h1-container" id="main-section">
        <h1 class="h1-heading">Welcome to <span>TasteNow</span></h1>
        <p class="p-0">Serving delicious moments for over 18 years!</p>
        <a href="#menu">
            <button class="btn btn-menu">OUR MENU</button>
        </a>
        <a href="#table_booking">
            <button class="btn btn-booking">BOOK A TABLE</button>
        </a>
    </div>
</div>


<!-- Section: About -->
<div class="about-us" id="about">
    <div class="images-grid">
        <div class="image-box"><img src="../Frontend/assets/images/gallery/shop-1.webp" alt="Image 1"></div>
        <div class="image-box"><img src="../Frontend/assets/images/gallery/events-1.webp" alt="Image 2"></div>
        <div class="image-box"><img src="../Frontend/assets/images/gallery/shop-2.webp" alt="Image 3"></div>
        <div class="image-box"><img src="../Frontend/assets/images/gallery/events-2.webp" alt="Image 4"></div>
    </div>
    <div class="about-text">
        <h2 class="section-title">About Us</h2>
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
        <h2 class="menu-heading">Our Menu</h2>
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
        <?php include '../Backend/get_menu_items.php'; ?>
    </div>

    <button class="see-more-btn" id="see-more-btn">See More</button>
</section>


<!-- Section: Table Booking -->
<div class="booking-container" id="table_booking">
    <h2 class="form-heading">Book Your Table</h2>
    <form class="booking-form" action="table_booking.php" method="POST">
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
        <div class="form-row">
            <select name="duration" required>
                <option value="" disabled selected>Duration (hours)</option>
                <option value="1">1 Hour</option>
                <option value="2">2 Hour</option>
                <option value="3">3 Hour</option>
                <option value="4">4 Hour</option>
            </select>
        </div>
        <textarea name="special_request" placeholder="Special Request" rows="4"></textarea>
        <button type="submit">Book Now</button>
    </form>
</div>


<!-- Section: Feedback display -->
<div class="feedback-container">
    <?php include 'display_feedback.php'; ?>
</div>


<!-- Section: Location -->
<section class="location-section">
    <div class="map-container">
        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d15831.5771470398!2d80.32347147620074!3d7.252871027803606!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3ae317ff06856f21%3A0x2bfab7cb42395899!2sWOW%20Cafe%20Kegalle!5e0!3m2!1ssi!2slk!4v1748712708946!5m2!1ssi!2slk">
        </iframe>
    </div>
</section>


<!-- Section: Footer -->
<footer class="footer text-white pt-5 pb-3" id="footer">
    <div class="container">
        <div class="row">
            <!-- Section: About -->
            <div class="col-md-3 mb-4">
                <a class="text-uppercase footer-brand">Tastenow</a>
                <p>Delicious moments delivered.</p>
                <ul class="list-payment-icon">
                    <li><img src="../Frontend/assets/images/master.png" alt="Mastercard" class="payment-icon"/></li>
                    <li><img src="../Frontend/assets/images/visa.png" alt="Visa" class="payment-icon"/></li>
                    <li><img src="../Frontend/assets/images/cash-payment.png" alt="Cash" class="payment-icon"/></li>
                </ul>
            </div>

            <!--Section: Quick Links -->
            <div class="col-md-3 mb-4">
                <h6 class="footer-sub-headings text-uppercase">Quick Links</h6>
                <ul class="list-unstyled">
                    <li><a href="#home_page">Home</a></li>
                    <li><a href="#menu">Menu</a></li>
                    <li><a href="#table_booking">Book a Table</a></li>
                    <li><a href="#footer">Contact</a></li>
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
                <form action="feedback.php" method="POST">
                    <textarea class="feedback-form" name="message" rows="3" placeholder="Your feedback..."
                              required></textarea>
                    <button type="submit" class="submit-btn btn-light mt-2">Submit</button>
                    <div id="feedbackMessage" class="mt-2 text-success"></div>
                </form>
            </div>
        </div>
    </div>
    <a href="#" class="back-to-top" id="backToTopBtn">
        <span class="material-icons">arrow_upward</span>
    </a>

    <div class="footer-bottom-bar text-center py-2 mt-3">
        © 2025 Tastenow. All rights reserved.
    </div>
</footer>


<!-- Section: Js Link-->
<script>
    // Navbar Fixed Top
    document.addEventListener("DOMContentLoaded", function () {
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

        // Sign In and Sign Up Toggle Logic
        const signinBtn = document.getElementById('signing-btn');
        const dropdown = document.getElementById('form-dropdown');
        const signinForm = document.getElementById('signing-form');
        const signupForm = document.getElementById('signup-form');
        const switchToSignup = document.getElementById('switch-to-signup');
        const switchToSignin = document.getElementById('switch-to-signin');

        signinBtn?.addEventListener('click', (e) => {
            e.stopPropagation();
            const isVisible = dropdown.style.display === "block";
            dropdown.style.display = isVisible ? "none" : "block";
            signinForm.style.display = "block";
            signupForm.style.display = "none";
        });

        switchToSignup?.addEventListener('click', (e) => {
            e.preventDefault();
            signinForm.style.display = "none";
            signupForm.style.display = "block";
        });

        switchToSignin?.addEventListener('click', (e) => {
            e.preventDefault();
            signupForm.style.display = "none";
            signinForm.style.display = "block";
        });

        window.addEventListener('click', (e) => {
            if (!dropdown.contains(e.target) && e.target !== signinBtn) {
                dropdown.style.display = "none";
            }
        });

        // Menu See More Btn
        const menuItems = document.querySelectorAll(".menu-item");
        const seeMoreBtn = document.getElementById("see-more-btn");
        const initiallyVisible = 8;

        menuItems.forEach((item, index) => {
            if (index >= initiallyVisible) {
                item.style.display = 'none';
            }
        });

        seeMoreBtn?.addEventListener("click", function () {
            menuItems.forEach(item => item.style.display = "block");
            seeMoreBtn.style.display = "none";
        });

        // Category Filtering
        const buttons = document.querySelectorAll('.category-button');
        buttons.forEach(button => {
            button.addEventListener('click', () => {
                const category = button.getAttribute('data-category').toLowerCase();
                menuItems.forEach(item => {
                    const itemCategory = item.getAttribute('data-category').toLowerCase();
                    item.style.display = (category === 'all' || itemCategory === category) ? 'block' : 'none';
                });
                buttons.forEach(btn => btn.classList.remove('active'));
                button.classList.add('active');
            });
        });

        // Add Item to Cart
        const addToCartButtons = document.querySelectorAll('.add-to-cart-btn');
        addToCartButtons.forEach(button => {
            button.addEventListener('click', function () {
                const itemId = this.getAttribute('data-id');
                const itemName = this.getAttribute('data-name');
                const itemPrice = this.getAttribute('data-price');
                const itemImage = this.getAttribute('data-image');

                const cartData = {
                    itemId: parseInt(itemId),
                    itemName: itemName,
                    price: parseFloat(itemPrice),
                    image: itemImage
                };

                fetch('add_to_cart.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify(cartData)
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            alert('Item added to cart!');
                        } else {
                            alert(data.message || 'Failed to add item.');
                        }
                    })
                    .catch(error => {
                        console.error('Error adding to cart:', error);
                        alert('Something went wrong.');
                    });
            });
        });

        // Booking Section
        // Set minimum date to today
        const dateInput = document.querySelector('input[name="booking_date"]');
        const timeInput = document.querySelector('input[name="booking_time"]');

        function setMinDateTime() {
            const now = new Date();
            const today = now.toISOString().split('T')[0];
            dateInput.min = today;

            dateInput.addEventListener('change', () => {
                const isToday = dateInput.value === today;
                if (isToday) {
                    const currentTime = now.toTimeString().slice(0, 5);
                    timeInput.min = currentTime;
                } else {
                    timeInput.removeAttribute('min');
                }
            });
        }

        if (dateInput && timeInput) setMinDateTime();
    });

    // Back to top btn
    const backToTopBtn = document.getElementById("backToTopBtn");

    window.addEventListener("scroll", () => {
        if (document.documentElement.scrollTop > 500) {
            backToTopBtn.style.display = "block";
        } else {
            backToTopBtn.style.display = "none";
        }
    });

    backToTopBtn.addEventListener("click", function (e) {
        e.preventDefault();
        window.scrollTo({
            top: 0,
            behavior: "smooth"
        });
    });


    function updateCartQuantity() {
        fetch('get_cart_qty.php')
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    const qty = data.totalQuantity;
                    const cartBadge = document.getElementById('cart-qty-badge');
                    if (qty > 0) {
                        cartBadge.textContent = qty;
                        cartBadge.style.display = 'inline-block';
                    } else {
                        cartBadge.style.display = 'none';
                    }
                }
            })
            .catch(() => {
                const cartBadge = document.getElementById('cart-qty-badge');
                cartBadge.style.display = 'none';
            });
    }

    // Optionally call on page load
    document.addEventListener('DOMContentLoaded', updateCartQuantity);


</script>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script src="../Frontend/js/script.js"></script>

</body>
</html>

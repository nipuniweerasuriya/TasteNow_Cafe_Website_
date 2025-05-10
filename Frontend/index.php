<?php
session_start();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TasteNow</title>

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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <!--css link-->
    <link rel="stylesheet" href="../Frontend/css/styles.css"/>
</head>

<body>

<!--top bar-->
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

    <!-- navbar -->
    <nav class="navbar navbar-expand-lg bg-body-tertiary" id="navbar">
        <div class="container navbar-container">
            <!-- Logo at the start -->
            <a class="navbar-brand logo-wiggle" href="index.php">TASTENOW</a>

            <!-- Toggler for mobile view -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavAltMarkup"
                    aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Nav links -->
            <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
                <div class="navbar-nav ms-auto align-items-lg-center d-flex gap-2">
                    <a class="nav-link active" aria-current="page" href="#">Home</a>
                    <a class="nav-link" href="#">Menu</a>
                    <a class="nav-link" href="#">About</a>
                    <a class="nav-link" href="#">Contact</a>
                    <a class="nav-link" href="#">Feedback</a>

                    <?php if (isset($_SESSION['user_name'])): ?>
                        <a class="nav-link" href="../Frontend/profile.html"><?= htmlspecialchars($_SESSION['user_name']) ?></a>
                    <?php else: ?>
                        <a class="nav-link" href="#" id="signing-btn">Sign In</a>
                    <?php endif; ?>
                </div>


                <!-- Sign In / Sign Up -->
                <!-- Dropdown container -->
                <div id="form-dropdown" class="form-container" style="display: none;">

                    <!-- Sign In Form -->
                    <form action="../Backend/signin.php" method="post">
                        <div id="signing-form">
                            <h2>Sign In</h2>
                            <label for="signin-email">Email</label>
                            <input type="email" id="signin-email" name="email" required placeholder="Enter Your Email">

                            <label for="signin-password">Password</label>
                            <input type="password" id="signin-password" name="password" required placeholder="Enter Your Password">

                            <label for="signin-role">Role</label>
                            <select id="signin-role" name="role" required>
                                <option value="admin">Admin</option>
                                <option value="kitchen">Kitchen</option>
                                <option value="cashier">Cashier</option>
                                <option value="user">User</option>
                            </select>

                            <button type="submit">SIGN IN</button>
                            <p>Don't have an account? <a href="#" id="switch-to-signup">Register</a></p>
                        </div>
                    </form>

                    <!-- Signup Form -->
                    <form action="../Backend/signup.php" method="post">
                        <div id="signup-form">
                            <h2>Sign Up</h2>

                            <label for="name">Username</label>
                            <input type="text" id="name" name="name" required placeholder="Enter Your Name">

                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" required placeholder="Enter Your Email">

                            <label for="password">Password</label>
                            <input type="password" id="password" name="password" required placeholder="Enter Your Password">

                            <label for="confirm-password">Confirm Password</label>
                            <input type="password" id="confirm-password" name="confirm-password" required placeholder="Confirm Your Password">

                            <label for="role">Role</label>
                            <select id="role" name="role" required>
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


                <!-- Account & Cart Icons -->
                <div class="d-flex align-items-center ms-3">
                    <!-- Cart Icon -->
                    <a href="cart.php">
                        <span class="material-symbols-outlined icon-cart me-3">shopping_cart</span>
                    </a>
                </div>

            </div>
        </div>
    </nav>

    <!--welcome msg section-->
    <div class="container h1-container">
        <h1 class="h1-heading">Welcome to <span>TasteNow</span></h1>
        <p class="p-0">Serving delicious moments for over 18 years!</p>
        <button type="button" class="btn btn-menu">OUR MENU</button>
        <button type="button" class="btn btn-booking">BOOK A TABLE</button>
    </div>
</div>

<!--About Us-->
<div class="about-us">
    <div class="images-grid">
        <div class="image-box">
            <img src="assets/images/gallery/shop-1.webp" alt="Image 1">
        </div>
        <div class="image-box">
            <img src="assets/images/gallery/events-1.webp" alt="Image 2">
        </div>
        <div class="image-box">
            <img src="assets/images/gallery/shop-2.webp" alt="Image 3">
        </div>
        <div class="image-box">
            <img src="assets/images/gallery/events-2.webp" alt="Image 4">
        </div>
    </div>
    <div class="about-text">
        <h2 class="section-title">----- About Us -----</h2>
        <h2>Welcome to <strong>TASTENOW</strong></h2>
        <p>
            Welcome to TasteNow – where flavor meets passion! At TasteNow, we believe that
            every meal should be a delightful experience. Our restaurant brings together a
            fusion of fresh ingredients, creative recipes, and warm hospitality to serve you
            unforgettable dishes. Whether you're here for a quick bite, a family dinner, or a
            special occasion, we’re dedicated to making every moment enjoyable. With our
            easy-to-use online ordering and table booking system, great food is just a click away.
            Join us and discover the taste that keeps everyone coming back!
        </p>
        <a href="#" class="read-more">Read More</a>
    </div>
</div>

<!-- Menu Section -->
<section id="menu">
    <div class="menu-heading-container">
        <h2>-----Our Menu-----</h2>
    </div>

    <!-- Category Buttons -->
    <div id="category-container">
        <button class="category-button" data-category="all">All</button>
        <button class="category-button" data-category="coffee">Coffee</button>
        <button class="category-button" data-category="tea">Tea</button>
        <button class="category-button" data-category="smoothies">Smoothies</button>
        <button class="category-button" data-category="snacks-&-pastries">Snacks & Pastries</button>
        <button class="category-button" data-category="desserts">Desserts</button>
        <button class="category-button" data-category="drinks">Drinks</button>
    </div>

    <!-- Menu Items (loaded from DB) -->
    <div id="menu-container">
        <?php include '../Backend/get_menu_items.php'; ?>
    </div>

    <button class="see-more-btn" id="see-more-btn">See More</button>
</section>



<!-- Modal for Variants and Add-ons -->
<div id="menu-options-modal" class="modal">
    <div class="modal-content">
        <span id="close-modal" class="close">&times;</span>
        <h3>Select Variants and Add-ons</h3>

        <!-- Variant selection -->

        <label for="variantsDropdown" multiple >Select Variant:</label>
        <select id="variantsDropdown">
            <option value="">Select Variant</option>
            <!-- Options will be dynamically added -->
        </select>

        <!-- Add-ons selection -->
        <h4>Add-ons:</h4>
        <div id="addOnsContainer">
            <!-- Add-on checkboxes will be dynamically added -->
        </div>

        <!-- Submit Button -->
        <button id="addToCartWithOptions">Add</button>

    </div>
</div>







<!-- Table Booking Section -->
<div class="booking-container">
    <h2 class="form-heading">-----Book Your Table-----</h2>
    <form class="booking-form">
        <div class="form-row">
            <label>
                <input type="text" placeholder="Your Name" required/>
            </label>
            <label>
                <input type="tel" placeholder="Your Phone Number" required/>
            </label>
            <label>
                <input type="email" placeholder="Your Email" required/>
            </label>
        </div>
        <div class="form-row">
            <label>
                <input type="number" placeholder="Number of People" required/>
            </label>
            <label>
                <input type="date" required/>
            </label>
            <label>
                <input type="time" required/>
            </label>
        </div>
        <div class="form-row textarea-row">
            <label>
                <textarea placeholder="Special Request" rows="4"></textarea>
            </label>
        </div>
        <div class="form-row">
            <button type="submit">Book Now</button>
        </div>
    </form>
</div>

<!--footer-->
<footer class="footer text-white pt-5 pb-3">
    <div class="container">
        <div class="row">
            <!-- About Section -->
            <div class="col-md-3 mb-4">
                <a class="text-uppercase footer-brand">Tastenow</a>
                <p>Delicious moments delivered. Order online, book tables, and enjoy your food in comfort.</p>

                <!-- Payment Methods-->
                <div class="payment-icon col-md-3 mb-4 ">
                    <ul class="list-payment-icon">
                        <li><img src="assets/images/master.png" alt="Visa" class="payment-icon"/></li>
                        <li><img src="assets/images/paypal.png" alt="Mastercard" class="payment-icon"/></li>
                        <li><img src="assets/images/visa.png" alt="PayPal" class="payment-icon"/></li>
                        <li><img src="assets/images/cash-payment.png" alt="Cash on Delivery" class="payment-icon"/></li>
                    </ul>

                </div>
            </div>

            <!-- Quick Links -->
            <div class="col-md-3 mb-4">
                <h6 class="footer-sub-headings text-uppercase">Quick Links</h6>
                <ul class="list-unstyled">
                    <li><a href="#" class="">Home</a></li>
                    <li><a href="#" class="">Menu</a></li>
                    <li><a href="#" class="">Book a Table</a></li>
                    <li><a href="#" class="">Contact</a></li>
                </ul>
            </div>

            <!-- Contact Info & Social Icons -->
            <div class="col-md-3 mb-4">
                <h5 class="footer-sub-headings text-uppercase">Contact Us</h5>
                <p><i class="bi bi-envelope me-2"></i>support@tastehub.com</p>
                <p><i class="bi bi-telephone me-2"></i>+94 76 123 4567</p>
                <p><i class="bi bi-geo-alt me-2"></i>Colombo, Sri Lanka</p>

                <!-- Social Media Icons -->
                <div class="mt-3">
                    <a href="#" class="text-white me-3"><i class="social-icon bi bi-facebook fs-4"></i></a>
                    <a href="#" class="text-white me-3"><i class="social-icon bi bi-instagram fs-4"></i></a>
                    <a href="#" class="text-white me-3"><i class="social-icon bi bi-tiktok fs-4"></i></a>
                    <a href="#" class="text-white"><i class="social-icon bi bi-whatsapp fs-4"></i></a>
                </div>
            </div>

            <!-- Feedback Form -->
            <div class="col-md-3 mb-4">
                <h5 class="footer-sub-headings text-uppercase">Feedback</h5>
                <form>
                    <div class="mb-2">
                        <label>
                            <textarea class="form-control" rows="3" placeholder="Your feedback..."></textarea>
                        </label>
                    </div>
                    <button type="submit" class="form-control-btn">Send</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Bottom Bar -->
    <div class="footer-bottom-bar text-center py-2 mt-4">
        © 2025 TasteNow. All rights reserved.
    </div>
</footer>


<!--js link-->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script src="../Frontend/js/script.js"></script>

</body>
</html>

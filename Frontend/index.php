<?php
session_start();
?>
<!doctype html>
<html lang="en">


<head>
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

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="../Frontend/css/styles.css">
</head>


<body id="home_page">

<!-- Top Bar -->
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

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg bg-body-tertiary" id="navbar">
        <div class="container navbar-container">
            <a class="navbar-brand logo-wiggle" href="index.php">TASTENOW</a>
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

                <!-- Form Dropdown -->
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

                <!-- Cart Icon -->
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

    <!-- Welcome Section -->
    <div class="container h1-container">
        <h1 class="h1-heading">Welcome to <span>TasteNow</span></h1>
        <p class="p-0">Serving delicious moments for over 18 years!</p>
        <button class="btn btn-menu">OUR MENU</button>
        <button class="btn btn-booking">BOOK A TABLE</button>
    </div>
</div>

<!-- About Section -->
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

<!-- Menu Section -->
<section id="menu">
    <div class="menu-heading-container">
        <h2>-----Our Menu-----</h2>
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

<!-- Modal for Variants and Add-ons -->
<div id="menu-options-modal" class="modal">
    <div class="modal-content">
        <span id="close-modal" class="close">&times;</span>
        <h3>Select Variants and Add-ons</h3>
        <label>Select Variant:</label>
        <select id="variantsDropdown">
            <option value="">Select Variant</option>
        </select>
        <h4>Add-ons:</h4>
        <div id="addOnsContainer"></div>
        <button id="addToCartWithOptions">Add</button>
    </div>
</div>

<!-- Table Booking -->
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


<footer class="footer text-white pt-5 pb-3">
    <div class="container">
        <div class="row">
            <!-- About -->
            <div class="col-md-3 mb-4">
                <a class="text-uppercase footer-brand">Tastenow</a>
                <p>Delicious moments delivered.</p>
                <ul class="list-payment-icon">
                    <li><img src="assets/images/master.png" alt="Mastercard" class="payment-icon"/></li>
                    <li><img src="assets/images/paypal.png" alt="Paypal" class="payment-icon"/></li>
                    <li><img src="assets/images/visa.png" alt="Visa" class="payment-icon"/></li>
                    <li><img src="assets/images/cash-payment.png" alt="Cash" class="payment-icon"/></li>
                </ul>
            </div>

            <!-- Quick Links -->
            <div class="col-md-3 mb-4">
                <h6 class="footer-sub-headings text-uppercase">Quick Links</h6>
                <ul class="list-unstyled">
                    <li><a href="#">Home</a></li>
                    <li><a href="#">Menu</a></li>
                    <li><a href="#">Book a Table</a></li>
                    <li><a href="#">Contact</a></li>
                </ul>
            </div>

            <!-- Contact -->
            <div class="col-md-3 mb-4">
                <h5 class="footer-sub-headings text-uppercase">Contact Us</h5>
                <p><i class="bi bi-envelope me-2"></i>support@tastehub.com</p>
                <p><i class="bi bi-telephone me-2"></i>+94 76 123 4567</p>
                <p><i class="bi bi-geo-alt me-2"></i>Colombo, Sri Lanka</p>
                <div class="mt-3">
                    <a href="#" class="text-white me-3"><i class="bi bi-facebook fs-4"></i></a>
                    <a href="#" class="text-white me-3"><i class="bi bi-instagram fs-4"></i></a>
                    <a href="#" class="text-white me-3"><i class="bi bi-tiktok fs-4"></i></a>
                    <a href="#" class="text-white"><i class="bi bi-whatsapp fs-4"></i></a>
                </div>
            </div>

            <!-- Feedback -->
            <div class="col-md-3 mb-4">
                <h5 class="footer-sub-headings text-uppercase">Feedback</h5>
                <form>
                    <textarea class="form-control" rows="3" placeholder="Your feedback..."></textarea>
                    <button type="submit" class="btn btn-light mt-2">Submit</button>
                </form>
            </div>
        </div>
    </div>
</footer>


<!--js link-->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script src="../Frontend/js/script.js"></script>

</body>
</html>

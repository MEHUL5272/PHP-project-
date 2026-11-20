<?php

session_start();

$isLoggedIn = isset($_SESSION["user_id"]);

$userName = $_SESSION["user_name"] ?? "";

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Shree Shyam Coffee</title>


    <style>
        * {

            margin: 0;

            padding: 0;

            box-sizing: border-box;

        }


        body {

            font-family: Arial, sans-serif;

            background: #0b0b0b;

            color: white;

        }


        /* ===============================
           NAVBAR
        ================================ */

        .navbar {

            min-height: 75px;

            background: #111;

            border-bottom: 1px solid #333;

            display: flex;

            align-items: center;

            justify-content: space-between;

            padding: 0 7%;

            gap: 20px;

        }


        .logo {

            color: #e7a33a;

            font-size: 25px;

            font-weight: bold;

            white-space: nowrap;

        }


        .nav-links {

            display: flex;

            gap: 25px;

            align-items: center;

            flex-wrap: wrap;

            justify-content: center;

        }


        .nav-links a {

            color: white;

            text-decoration: none;

            font-size: 15px;

        }


        .nav-links a:hover {

            color: #e7a33a;

        }


        .login-btn {

            background: #e7a33a;

            color: #111 !important;

            padding: 10px 18px;

            border-radius: 6px;

            font-weight: bold;

        }


        .signup-btn {

            border: 1px solid #e7a33a;

            color: #e7a33a !important;

            padding: 9px 16px;

            border-radius: 6px;

            font-weight: bold;

        }


        .logout-btn {

            border: 1px solid #ff7777;

            color: #ff7777 !important;

            padding: 8px 14px;

            border-radius: 6px;

        }


        .logout-btn:hover {

            background: #ff7777;

            color: #111 !important;

        }


        .user-name {

            color: #ddd;

            font-weight: bold;

        }


        /* ===============================
           HERO
        ================================ */

        .hero {

            min-height: 600px;

            display: flex;

            align-items: center;

            padding: 60px 8%;

            background:

                radial-gradient(circle at right,
                    #4b2b0e,
                    #0b0b0b 55%);

        }


        .hero-content {

            max-width: 650px;

        }


        .small-title {

            color: #e7a33a;

            font-size: 18px;

            margin-bottom: 15px;

        }


        .hero h1 {

            font-size: 65px;

            line-height: 1.05;

            margin-bottom: 20px;

        }


        .hero h1 span {

            color: #e7a33a;

        }


        .hero p {

            color: #ccc;

            font-size: 18px;

            line-height: 1.7;

            margin-bottom: 30px;

        }


        .hero-buttons {

            display: flex;

            gap: 15px;

            flex-wrap: wrap;

        }


        .btn {

            padding: 14px 25px;

            border-radius: 7px;

            text-decoration: none;

            font-weight: bold;

        }


        .primary {

            background: #e7a33a;

            color: #111;

        }


        .secondary {

            border: 1px solid #e7a33a;

            color: #e7a33a;

        }


        .primary:hover {

            background: #f5b84c;

        }


        .secondary:hover {

            background: #e7a33a;

            color: #111;

        }


        /* ===============================
           PRODUCTS
        ================================ */

        .products-section {

            padding: 70px 8%;

            text-align: center;

        }


        .section-title {

            color: #e7a33a;

            font-size: 17px;

            margin-bottom: 10px;

        }


        .products-section h2 {

            font-size: 38px;

            margin-bottom: 45px;

        }


        .product-grid {

            display: grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap: 25px;

        }


        .product-card {

            background: #161616;

            border: 1px solid #333;

            border-radius: 15px;

            padding: 30px;

            transition: 0.3s;

        }


        .product-card:hover {

            transform: translateY(-7px);

            border-color: #e7a33a;

        }


        .product-icon {

            font-size: 55px;

            margin-bottom: 15px;

        }


        .product-card h3 {

            margin-bottom: 10px;

        }


        .product-card p {

            color: #aaa;

        }


        /* ===============================
           FOOTER
        ================================ */
        /* =========================
   FOOTER
========================= */

        .coffee-footer {
            background:
                radial-gradient(circle at top left,
                    #3a230f,
                    #090909 55%);

            color: #ddd;
            margin-top: 60px;
            border-top: 1px solid #3d2814;
        }


        /* CONTAINER */

        .footer-container {
            width: 90%;
            max-width: 1250px;
            margin: auto;

            display: grid;
            grid-template-columns:
                1.5fr 1.2fr 1fr 1fr;

            gap: 45px;

            padding: 55px 0 40px;
        }


        /* BOX */

        .footer-box {
            min-width: 0;
        }


        /* LOGO */

        .footer-logo {
            color: #e7a33a;
            font-size: 25px;
            font-weight: bold;
            margin-bottom: 18px;
        }


        /* TEXT */

        .footer-box p {
            color: #aaa;
            line-height: 1.8;
            font-size: 14px;
        }


        /* HEADINGS */

        .footer-box h3 {
            color: #e7a33a;
            font-size: 18px;
            margin: 0 0 18px;

            position: relative;
            padding-bottom: 10px;
        }

        .footer-box h3::after {
            content: "";
            position: absolute;

            left: 0;
            bottom: 0;

            width: 45px;
            height: 2px;

            background: #e7a33a;
        }


        /* QUICK LINKS */

        .footer-box>a {
            display: block;

            color: #aaa;
            text-decoration: none;

            margin-bottom: 11px;

            transition: 0.3s;
        }

        .footer-box>a:hover {
            color: #e7a33a;
            transform: translateX(5px);
        }


        /* BADGE */

        .footer-badge {
            display: inline-block;

            margin-top: 12px;

            padding: 9px 14px;

            border: 1px solid #6b451c;
            border-radius: 20px;

            color: #e7a33a;
            font-size: 13px;
        }


        /* MAP LINK */

        .map-link {
            display: inline-block !important;

            background: #e7a33a !important;
            color: #111 !important;

            padding: 10px 15px;

            border-radius: 6px;

            font-weight: bold;

            margin-top: 8px;
        }

        .map-link:hover {
            background: #f5b84c !important;
            transform: translateY(-2px) !important;
        }


        /* TIME */

        .time {
            color: #e7a33a !important;
            font-size: 18px !important;
            font-weight: bold;
        }


        /* SMALL TEXT */

        .small-text {
            font-size: 13px !important;
        }


        /* MAP */

        .footer-map {
            width: 90%;
            max-width: 1250px;

            margin: 0 auto 35px;

            border: 1px solid #4b321b;
            border-radius: 12px;

            overflow: hidden;

            box-shadow:
                0 10px 30px rgba(0, 0, 0, 0.4);
        }

        .footer-map iframe {
            display: block;
            filter: grayscale(15%);
        }


        /* BOTTOM */

        .footer-bottom {
            border-top: 1px solid #292929;

            width: 90%;
            max-width: 1250px;

            margin: auto;

            padding: 20px 0;

            display: flex;
            justify-content: space-between;
            align-items: center;

            gap: 20px;
        }

        .footer-bottom p {
            margin: 0;

            color: #777;

            font-size: 13px;
        }

        .footer-bottom strong {
            color: #e7a33a;
        }


        /* =========================
   MOBILE
========================= */

        @media (max-width: 900px) {

            .footer-container {
                grid-template-columns: 1fr 1fr;
                gap: 35px;
            }

        }


        @media (max-width: 600px) {

            .footer-container {
                grid-template-columns: 1fr;

                padding: 40px 0 25px;

                gap: 30px;
            }

            .footer-map {
                width: 92%;
            }

            .footer-map iframe {
                height: 230px;
            }

            .footer-bottom {
                flex-direction: column;

                text-align: center;

                padding: 18px 0;
            }

        }

        /* ===============================
           MOBILE
        ================================ */

        @media(max-width: 900px) {

            .navbar {

                flex-direction: column;

                padding: 20px 5%;

            }


            .nav-links {

                gap: 15px;

            }


            .hero h1 {

                font-size: 50px;

            }


            .product-grid {

                grid-template-columns:
                    repeat(2, 1fr);

            }

        }


        @media(max-width: 600px) {

            .nav-links {

                gap: 10px;

            }


            .nav-links a {

                font-size: 14px;

            }


            .hero {

                min-height: 500px;

                padding: 50px 7%;

            }


            .hero h1 {

                font-size: 42px;

            }


            .hero p {

                font-size: 16px;

            }


            .product-grid {

                grid-template-columns: 1fr;

            }


            .products-section h2 {

                font-size: 30px;

            }

        }
    </style>

</head>


<body>


    <!-- ===============================
     NAVBAR
================================ -->

    <nav class="navbar">


        <div class="logo">

            ☕ Shree Shyam Coffee

        </div>


        <div class="nav-links">


            <!-- HOME -->

            <a href="index.php">

                Home

            </a>


            <!-- PRODUCTS -->

            <a href="user/products.php">

                Products

            </a>


            <?php if ($isLoggedIn): ?>


                <!-- MY ORDERS -->

                <a href="user/my_orders.php">

                    My Orders

                </a>


                <!-- CART -->

                <a href="user/cart.php">

                    🛒 Cart

                </a>


                <!-- USER NAME -->

                <span class="user-name">

                    Hi,
                    <?= htmlspecialchars($userName) ?>

                </span>


                <!-- LOGOUT -->

                <a href="user/logout.php" class="logout-btn">

                    Logout

                </a>


            <?php else: ?>


                <!-- CART -->

                <a href="user/cart.php">

                    🛒 Cart

                </a>


                <!-- LOGIN -->

                <a href="user/login.php" class="login-btn">

                    Login

                </a>


                <!-- SIGNUP -->

                <a href="user/signup.php" class="signup-btn">

                    Signup

                </a>


            <?php endif; ?>


        </div>

    </nav>



    <!-- ===============================
     HERO
================================ -->

    <section class="hero">


        <div class="hero-content">


            <div class="small-title">

                WELCOME TO SHREE SHYAM COFFEE

            </div>


            <h1>

                Fresh Coffee.

                <br>

                <span>
                    Good Vibes.
                </span>

            </h1>


            <p>

                Enjoy delicious cold coffee,
                creamy shakes and refreshing
                ice tea made especially for you.

            </p>


            <div class="hero-buttons">


                <a href="user/products.php" class="btn primary">

                    Explore Menu

                </a>


                <a href="#about" class="btn secondary">

                    About Us

                </a>


            </div>


        </div>


    </section>



    <!-- ===============================
     PRODUCTS
================================ -->

    <section class="products-section">


        <div class="section-title">

            OUR SPECIALS

        </div>


        <h2>

            Customer Favorites ☕

        </h2>


        <div class="product-grid">


            <div class="product-card">

                <div class="product-icon">
                    ☕
                </div>

                <h3>
                    Cold Coffee
                </h3>

                <p>
                    Classic creamy cold coffee
                </p>

            </div>



            <div class="product-card">

                <div class="product-icon">
                    🍫
                </div>

                <h3>
                    Chocolate Shake
                </h3>

                <p>
                    Rich and delicious chocolate shake
                </p>

            </div>



            <div class="product-card">

                <div class="product-icon">
                    🍪
                </div>

                <h3>
                    Oreo Shake
                </h3>

                <p>
                    Creamy Oreo shake for cookie lovers
                </p>

            </div>



            <div class="product-card">

                <div class="product-icon">
                    🍪
                </div>

                <h3>
                    Cookies & Cream
                </h3>

                <p>
                    Smooth cookies and cream shake
                </p>

            </div>



            <div class="product-card">

                <div class="product-icon">
                    🧋
                </div>

                <h3>
                    KitKat Shake
                </h3>

                <p>
                    Delicious KitKat flavored shake
                </p>

            </div>



            <div class="product-card">

                <div class="product-icon">
                    🍋
                </div>

                <h3>
                    Lemon Ice Tea
                </h3>

                <p>
                    Refreshing lemon ice tea
                </p>

            </div>


        </div>

    </section>



    <!-- =========================
     SHREE SHYAM COFFEE FOOTER
========================= -->

    <footer class="coffee-footer">

        <div class="footer-container">

            <!-- BRAND -->
            <div class="footer-box brand-box">
                <div class="footer-logo">☕ Shree Shyam Coffee</div>

                <p>
                    Fresh Coffee, Shakes & Ice Tea
                    made with love and quality ingredients.
                </p>

                <div class="footer-badge">
                    ☕ Fresh • Cold • Delicious
                </div>
            </div>


            <!-- ADDRESS -->
            <div class="footer-box">
                <h3>📍 Visit Us</h3>

                <p>
                    D - MART FOOD STREET,<br>
                    NIKOL - NARODA ROAD,<br>
                    AHMEDABAD, GUJARAT - 382350
                </p>

                <a href="https://www.google.com/maps/search/?api=1&query=D-MART+FOOD+STREET+Nikol+Naroda+Road+Ahmedabad+Gujarat+382350"
                    target="_blank" class="map-link">
                    🗺️ Get Directions
                </a>
            </div>


            <!-- OPENING HOURS -->
            <div class="footer-box">
                <h3>🕘 Opening Hours</h3>

                <p>
                    <strong>Every Day</strong>
                </p>

                <p class="time">
                    9:00 PM – 2:00 AM
                </p>

                <p class="small-text">
                    Come & enjoy your favourite coffee!
                </p>
            </div>


            <!-- QUICK LINKS -->
            <div class="footer-box">
                <h3>🔗 Quick Links</h3>

                <a href="index.php">Home</a>
                <a href="user/products.php">Products</a>
                <a href="user/cart.php">Cart</a>
                <a href="user/my_orders.php">My Orders</a>
            </div>

        </div>


        <!-- MAP -->
        <div class="footer-map">

            <iframe
                src="https://www.google.com/maps?q=D-MART%20FOOD%20STREET,%20NIKOL%20-%20NARODA%20ROAD,%20AHMEDABAD,%20GUJARAT%20382350&output=embed"
                width="100%" height="280" style="border:0;" allowfullscreen="" loading="lazy"
                referrerpolicy="no-referrer-when-downgrade">
            </iframe>

        </div>


        <!-- BOTTOM -->
        <div class="footer-bottom">

            <p>
                © <?= date("Y") ?>
                <strong>Shree Shyam Coffee</strong>.
                All Rights Reserved.
            </p>

            <p>
                Made with ☕ & ❤️
            </p>

        </div>

    </footer>


</body>

</html>
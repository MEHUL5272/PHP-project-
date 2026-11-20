<?php

session_start();

require_once __DIR__ . '/../vendor/autoload.php';

use MongoDB\Client;

try {

    $client = new Client("mongodb://127.0.0.1:27017");

    $db = $client->selectDatabase("shree_shyam_coffee");

    $productsCollection = $db->products;

    $products = $productsCollection->find(
        [],
        [
            "sort" => [
                "category" => 1,
                "name" => 1
            ]
        ]
    );

} catch (Exception $e) {

    die("MongoDB Error: " . $e->getMessage());

}


/* CART COUNT */

$cartCount = 0;

if (isset($_SESSION["cart"])) {

    foreach ($_SESSION["cart"] as $item) {

        $cartCount += $item["quantity"];

    }

}


/* SEARCH + CATEGORY */

$search = trim($_GET["search"] ?? "");

$categoryFilter = $_GET["category"] ?? "All";

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Products - Shree Shyam Coffee</title>

<style>

* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

body {

    font-family: Arial, sans-serif;

    background: #090909;

    color: white;
}


/* NAVBAR */

.navbar {

    min-height: 75px;

    background: #111;

    border-bottom: 1px solid #333;

    display: flex;

    align-items: center;

    justify-content: space-between;

    padding: 0 7%;
}

.logo {

    color: #e7a33a;

    font-size: 25px;

    font-weight: bold;
}

.nav-links {

    display: flex;

    align-items: center;

    gap: 25px;
}

.nav-links a {

    color: white;

    text-decoration: none;
}

.nav-links a:hover {

    color: #e7a33a;
}

.cart {

    background: #e7a33a;

    color: #111 !important;

    padding: 9px 15px;

    border-radius: 6px;

    font-weight: bold;
}


/* HEADER */

.page-header {

    text-align: center;

    padding: 60px 20px 40px;

    background:
        radial-gradient(
            circle,
            #38220d,
            #090909 65%
        );
}

.page-header h1 {

    font-size: 45px;

    color: #e7a33a;

    margin-bottom: 10px;
}

.page-header p {

    color: #aaa;

    font-size: 17px;
}


/* SEARCH SECTION */

.filter-box {

    width: 86%;

    max-width: 1200px;

    margin: 35px auto 0;

    background: #161616;

    border: 1px solid #333;

    border-radius: 14px;

    padding: 20px;
}

.filter-form {

    display: flex;

    gap: 12px;

    align-items: center;
}

.search-input {

    flex: 1;

    padding: 14px 16px;

    background: #222;

    border: 1px solid #444;

    border-radius: 8px;

    color: white;

    outline: none;

    font-size: 15px;
}

.search-input:focus {

    border-color: #e7a33a;
}

.category-select {

    padding: 14px 16px;

    background: #222;

    border: 1px solid #444;

    border-radius: 8px;

    color: white;

    outline: none;

    font-size: 15px;

    cursor: pointer;
}

.category-select:focus {

    border-color: #e7a33a;
}

.search-btn {

    padding: 14px 22px;

    background: #e7a33a;

    border: none;

    border-radius: 8px;

    color: #111;

    font-weight: bold;

    cursor: pointer;

    font-size: 15px;
}

.search-btn:hover {

    background: #f5b84c;
}

.clear-btn {

    padding: 14px 18px;

    background: #333;

    border-radius: 8px;

    color: white;

    text-decoration: none;

    font-weight: bold;

    font-size: 15px;
}

.clear-btn:hover {

    background: #444;
}


/* PRODUCTS */

.products-container {

    width: 86%;

    max-width: 1200px;

    margin: auto;

    padding: 35px 0 80px;
}

.category {

    margin-bottom: 55px;
}

.category-title {

    color: #e7a33a;

    font-size: 27px;

    margin-bottom: 25px;

    border-left: 4px solid #e7a33a;

    padding-left: 12px;
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

    padding: 25px;

    transition: 0.3s;
}

.product-card:hover {

    transform: translateY(-6px);

    border-color: #e7a33a;

    box-shadow:
        0 10px 25px rgba(0,0,0,0.4);
}

.product-image {

    height: 150px;

    border-radius: 12px;

    background:
        linear-gradient(
            135deg,
            #4a2b0d,
            #1a1a1a
        );

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 70px;

    margin-bottom: 20px;

    overflow: hidden;
}

.product-image img {

    width: 100%;

    height: 100%;

    object-fit: cover;
}

.product-card h3 {

    font-size: 21px;

    margin-bottom: 18px;
}

.size-row {

    display: flex;

    gap: 10px;

    margin-bottom: 18px;
}

.size {

    flex: 1;

    background: #222;

    border: 1px solid #444;

    border-radius: 8px;

    padding: 10px;

    text-align: center;

    color: #ddd;
}

.size strong {

    display: block;

    color: #e7a33a;

    margin-top: 4px;
}

.add-cart {

    width: 100%;

    border: none;

    border-radius: 8px;

    padding: 12px;

    background: #e7a33a;

    color: #111;

    font-size: 15px;

    font-weight: bold;

    cursor: pointer;
}

.add-cart:hover {

    background: #f5b84c;
}


/* NO PRODUCTS */

.no-products {

    text-align: center;

    padding: 80px 20px;

    color: #aaa;
}

.no-products-icon {

    font-size: 60px;

    margin-bottom: 15px;
}

.no-products h2 {

    color: #e7a33a;

    margin-bottom: 10px;
}


/* FOOTER */

footer {

    text-align: center;

    padding: 30px;

    background: #111;

    color: #888;

    border-top: 1px solid #333;
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


/* MOBILE */

@media(max-width: 850px) {

    .product-grid {

        grid-template-columns: 1fr 1fr;
    }

}

@media(max-width: 650px) {

    .navbar {

        flex-direction: column;

        gap: 15px;

        padding: 18px 4%;
    }

    .nav-links {

        gap: 10px;

        font-size: 13px;
    }

    .logo {

        font-size: 20px;
    }

    .filter-form {

        flex-direction: column;

        align-items: stretch;
    }

    .search-input,
    .category-select,
    .search-btn,
    .clear-btn {

        width: 100%;
    }

}

@media(max-width: 550px) {

    .product-grid {

        grid-template-columns: 1fr;
    }

    .page-header h1 {

        font-size: 35px;
    }

}

</style>

</head>

<body>


<!-- NAVBAR -->

<nav class="navbar">

    <div class="logo">

        ☕ Shree Shyam Coffee

    </div>


    <div class="nav-links">

        <a href="../index.php">
            Home
        </a>

        <a href="products.php">
            Products
        </a>

        <a href="cart.php" class="cart">

            🛒 Cart (<?= $cartCount ?>)

        </a>

    </div>

</nav>


<!-- HEADER -->

<section class="page-header">

    <h1>
        Our Menu ☕
    </h1>

    <p>
        Fresh coffee, creamy shakes & refreshing drinks
    </p>

</section>


<!-- SEARCH + FILTER -->

<div class="filter-box">

    <form
        method="GET"
        action="products.php"
        class="filter-form"
    >

        <input
            type="text"
            name="search"
            class="search-input"
            placeholder="🔍 Search coffee, shake..."
            value="<?= htmlspecialchars($search) ?>"
        >


        <select
            name="category"
            class="category-select"
        >

            <option
                value="All"
                <?= $categoryFilter === "All" ? "selected" : "" ?>
            >
                All Categories
            </option>

            <option
                value="Cold Coffee"
                <?= $categoryFilter === "Cold Coffee" ? "selected" : "" ?>
            >
                ☕ Cold Coffee
            </option>

            <option
                value="Shake"
                <?= $categoryFilter === "Shake" ? "selected" : "" ?>
            >
                🥤 Shake
            </option>

            <option
                value="Ice Tea"
                <?= $categoryFilter === "Ice Tea" ? "selected" : "" ?>
            >
                🍋 Ice Tea
            </option>

        </select>


        <button
            type="submit"
            class="search-btn"
        >
            🔍 Search
        </button>


        <?php if ($search !== "" || $categoryFilter !== "All"): ?>

            <a
                href="products.php"
                class="clear-btn"
            >
                ✕ Clear
            </a>

        <?php endif; ?>

    </form>

</div>


<!-- PRODUCTS -->

<div class="products-container">

<?php

$currentCategory = "";

$foundProducts = false;

foreach ($products as $product):

    /* SEARCH FILTER */

    $productName = strtolower(
        (string)$product["name"]
    );

    $searchText = strtolower($search);

    $searchMatch = (
        $search === "" ||
        strpos($productName, $searchText) !== false
    );


    /* CATEGORY FILTER */

    $categoryMatch = (
        $categoryFilter === "All" ||
        $product["category"] === $categoryFilter
    );


    /* SKIP PRODUCT */

    if (!$searchMatch || !$categoryMatch) {

        continue;

    }


    $foundProducts = true;


    /* CATEGORY TITLE */

    if ($currentCategory !== $product["category"]):

        if ($currentCategory !== ""):

            echo "</div></section>";

        endif;

        $currentCategory = $product["category"];

?>

<section class="category">

    <h2 class="category-title">

        <?= htmlspecialchars(
            $currentCategory
        ) ?>

    </h2>

    <div class="product-grid">

<?php endif; ?>


<!-- PRODUCT CARD -->

<div class="product-card">


    <!-- IMAGE -->

    <div class="product-image">

        <?php

        /*
         * If your MongoDB product has image field,
         * it will display here.
         */

        if (
            isset($product["image"]) &&
            $product["image"] !== ""
        ):

        ?>

            <img
                src="../uploads/products/<?= htmlspecialchars($product["image"]) ?>"
                alt="<?= htmlspecialchars($product["name"]) ?>"
            >

        <?php else: ?>

            <?php

            if ($product["category"] === "Cold Coffee") {

                echo "☕";

            } elseif ($product["category"] === "Shake") {

                echo "🥤";

            } else {

                echo "🍋";

            }

            ?>

        <?php endif; ?>

    </div>


    <!-- PRODUCT NAME -->

    <h3>

        <?= htmlspecialchars(
            $product["name"]
        ) ?>

    </h3>


    <!-- SIZE -->

    <div class="size-row">


        <div class="size">

            250ml

            <strong>

                ₹<?= $product["prices"]["250ml"] ?>

            </strong>

        </div>


        <div class="size">

            350ml

            <strong>

                ₹<?= $product["prices"]["350ml"] ?>

            </strong>

        </div>

    </div>


    <!-- 250ML -->

    <form
        action="cart.php"
        method="POST"
    >

        <input
            type="hidden"
            name="product_id"
            value="<?= $product["_id"] ?>"
        >

        <input
            type="hidden"
            name="product_name"
            value="<?= htmlspecialchars($product["name"]) ?>"
        >

        <input
            type="hidden"
            name="category"
            value="<?= htmlspecialchars($product["category"]) ?>"
        >

        <input
            type="hidden"
            name="size"
            value="250ml"
        >

        <input
            type="hidden"
            name="price"
            value="<?= $product["prices"]["250ml"] ?>"
        >

        <input
            type="hidden"
            name="action"
            value="add"
        >

        <button
            class="add-cart"
            type="submit"
        >

            🛒 Add 250ml to Cart

        </button>

    </form>


    <br>


    <!-- 350ML -->

    <form
        action="cart.php"
        method="POST"
    >

        <input
            type="hidden"
            name="product_id"
            value="<?= $product["_id"] ?>"
        >

        <input
            type="hidden"
            name="product_name"
            value="<?= htmlspecialchars($product["name"]) ?>"
        >

        <input
            type="hidden"
            name="category"
            value="<?= htmlspecialchars($product["category"]) ?>"
        >

        <input
            type="hidden"
            name="size"
            value="350ml"
        >

        <input
            type="hidden"
            name="price"
            value="<?= $product["prices"]["350ml"] ?>"
        >

        <input
            type="hidden"
            name="action"
            value="add"
        >

        <button
            class="add-cart"
            type="submit"
        >

            🛒 Add 350ml to Cart

        </button>

    </form>


</div>


<?php endforeach; ?>


<?php

if ($currentCategory !== "") {

    echo "</div></section>";

}

?>


<!-- NO RESULT -->

<?php if (!$foundProducts): ?>

    <div class="no-products">

        <div class="no-products-icon">
            🔍
        </div>

        <h2>
            No Products Found
        </h2>

        <p>
            Try another product name or category.
        </p>

        <br>

        <a
            href="products.php"
            class="clear-btn"
        >
            View All Products
        </a>

    </div>

<?php endif; ?>


</div>


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
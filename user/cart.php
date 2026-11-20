<?php

session_start();


// ===============================
// ADD TO CART
// ===============================

if (
    isset($_POST["action"]) &&
    $_POST["action"] === "add"
) {

    $productId = $_POST["product_id"];
    $productName = $_POST["product_name"];
    $category = $_POST["category"];
    $size = $_POST["size"];
    $price = (float) $_POST["price"];


    // Unique cart key
    $cartKey = $productId . "_" . $size;


    if (!isset($_SESSION["cart"])) {

        $_SESSION["cart"] = [];

    }


    if (isset($_SESSION["cart"][$cartKey])) {

        $_SESSION["cart"][$cartKey]["quantity"]++;

    } else {

        $_SESSION["cart"][$cartKey] = [

            "product_id" => $productId,

            "name" => $productName,

            "category" => $category,

            "size" => $size,

            "price" => $price,

            "quantity" => 1

        ];

    }


    header("Location: cart.php");
    exit;
}


// ===============================
// INCREASE QUANTITY
// ===============================

if (
    isset($_POST["action"]) &&
    $_POST["action"] === "increase"
) {

    $cartKey = $_POST["cart_key"];

    if (isset($_SESSION["cart"][$cartKey])) {

        $_SESSION["cart"][$cartKey]["quantity"]++;

    }

    header("Location: cart.php");
    exit;
}


// ===============================
// DECREASE QUANTITY
// ===============================

if (
    isset($_POST["action"]) &&
    $_POST["action"] === "decrease"
) {

    $cartKey = $_POST["cart_key"];

    if (isset($_SESSION["cart"][$cartKey])) {

        $_SESSION["cart"][$cartKey]["quantity"]--;

        if ($_SESSION["cart"][$cartKey]["quantity"] <= 0) {

            unset($_SESSION["cart"][$cartKey]);

        }

    }

    header("Location: cart.php");
    exit;
}


// ===============================
// REMOVE ITEM
// ===============================

if (
    isset($_POST["action"]) &&
    $_POST["action"] === "remove"
) {

    $cartKey = $_POST["cart_key"];

    unset($_SESSION["cart"][$cartKey]);

    header("Location: cart.php");
    exit;
}


// ===============================
// CLEAR CART
// ===============================

if (
    isset($_POST["action"]) &&
    $_POST["action"] === "clear"
) {

    $_SESSION["cart"] = [];

    header("Location: cart.php");
    exit;
}


// ===============================
// CART DATA
// ===============================

$cart = $_SESSION["cart"] ?? [];

$total = 0;

$cartCount = 0;


foreach ($cart as $item) {

    $total +=
        $item["price"] *
        $item["quantity"];

    $cartCount += $item["quantity"];

}

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Cart - Shree Shyam Coffee</title>

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

    height: 75px;

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

    gap: 25px;
}

.nav-links a {

    color: white;

    text-decoration: none;
}

.nav-links a:hover {

    color: #e7a33a;
}


/* HEADER */

.header {

    text-align: center;

    padding: 55px 20px 35px;

    background:
        radial-gradient(
            circle,
            #38220d,
            #090909 65%
        );
}

.header h1 {

    color: #e7a33a;

    font-size: 42px;

    margin-bottom: 10px;
}

.header p {

    color: #aaa;
}


/* CART */

.cart-container {

    width: 85%;

    max-width: 1100px;

    margin: auto;

    padding: 40px 0 80px;
}

.cart-item {

    background: #161616;

    border: 1px solid #333;

    border-radius: 12px;

    padding: 22px;

    margin-bottom: 18px;

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 20px;
}

.item-info h3 {

    color: #e7a33a;

    margin-bottom: 8px;
}

.item-info p {

    color: #aaa;

    margin-bottom: 5px;
}

.price {

    color: white;

    font-weight: bold;
}


/* QUANTITY */

.quantity {

    display: flex;

    align-items: center;

    gap: 8px;
}

.quantity button {

    width: 35px;

    height: 35px;

    border: none;

    border-radius: 6px;

    background: #e7a33a;

    color: #111;

    font-size: 18px;

    font-weight: bold;

    cursor: pointer;
}

.quantity span {

    min-width: 30px;

    text-align: center;

    font-weight: bold;
}


/* REMOVE */

.remove {

    background: #3a1515 !important;

    color: #ff7777 !important;

    padding: 9px 12px;

    width: auto !important;

    height: auto !important;

    font-size: 13px !important;
}


/* SUMMARY */

.summary {

    margin-top: 30px;

    background: #161616;

    border: 1px solid #e7a33a;

    border-radius: 12px;

    padding: 25px;

    text-align: right;
}

.summary h2 {

    color: #e7a33a;

    margin-bottom: 20px;
}

.total {

    font-size: 28px;

    font-weight: bold;

    margin-bottom: 20px;
}

.btn {

    border: none;

    padding: 12px 22px;

    border-radius: 7px;

    font-weight: bold;

    cursor: pointer;

    text-decoration: none;

    display: inline-block;
}

.clear {

    background: #333;

    color: white;

    margin-right: 10px;
}

.checkout {

    background: #e7a33a;

    color: #111;
}


/* EMPTY CART */

.empty {

    text-align: center;

    padding: 80px 20px;
}

.empty-icon {

    font-size: 70px;

    margin-bottom: 20px;
}

.empty h2 {

    margin-bottom: 12px;
}

.empty p {

    color: #aaa;

    margin-bottom: 25px;
}

.shop-btn {

    display: inline-block;

    background: #e7a33a;

    color: #111;

    padding: 13px 25px;

    border-radius: 7px;

    text-decoration: none;

    font-weight: bold;
}


/* MOBILE */

@media(max-width: 700px) {

    .cart-item {

        flex-direction: column;

        align-items: flex-start;
    }

    .summary {

        text-align: center;
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

    </div>

</nav>


<!-- HEADER -->

<section class="header">

    <h1>
        Your Cart 🛒
    </h1>

    <p>
        <?= $cartCount ?> item(s) in your cart
    </p>

</section>


<div class="cart-container">


<?php if (empty($cart)): ?>


    <!-- EMPTY CART -->

    <div class="empty">

        <div class="empty-icon">
            🛒
        </div>

        <h2>
            Your cart is empty
        </h2>

        <p>
            Add your favourite coffee or shake!
        </p>

        <a href="products.php" class="shop-btn">
            Explore Menu
        </a>

    </div>


<?php else: ?>


    <!-- CART ITEMS -->

    <?php foreach ($cart as $cartKey => $item): ?>

        <div class="cart-item">


            <div class="item-info">

                <h3>
                    <?= htmlspecialchars($item["name"]) ?>
                </h3>

                <p>
                    Category:
                    <?= htmlspecialchars($item["category"]) ?>
                </p>

                <p>
                    Size:
                    <?= htmlspecialchars($item["size"]) ?>
                </p>

                <p class="price">
                    ₹<?= number_format($item["price"], 2) ?>

                    ×

                    <?= $item["quantity"] ?>
                </p>

            </div>


            <!-- QUANTITY -->

            <div class="quantity">


                <form method="POST">

                    <input
                        type="hidden"
                        name="cart_key"
                        value="<?= htmlspecialchars($cartKey) ?>"
                    >

                    <input
                        type="hidden"
                        name="action"
                        value="decrease"
                    >

                    <button type="submit">
                        −
                    </button>

                </form>


                <span>
                    <?= $item["quantity"] ?>
                </span>


                <form method="POST">

                    <input
                        type="hidden"
                        name="cart_key"
                        value="<?= htmlspecialchars($cartKey) ?>"
                    >

                    <input
                        type="hidden"
                        name="action"
                        value="increase"
                    >

                    <button type="submit">
                        +
                    </button>

                </form>


            </div>


            <!-- REMOVE -->

            <form method="POST">

                <input
                    type="hidden"
                    name="cart_key"
                    value="<?= htmlspecialchars($cartKey) ?>"
                >

                <input
                    type="hidden"
                    name="action"
                    value="remove"
                >

                <button
                    type="submit"
                    class="quantity button remove"
                >
                    Remove
                </button>

            </form>


        </div>

    <?php endforeach; ?>


    <!-- SUMMARY -->

    <div class="summary">

        <h2>
            Order Summary
        </h2>

        <div class="total">

            Total:
            ₹<?= number_format($total, 2) ?>

        </div>


        <form method="POST" style="display:inline;">

            <input
                type="hidden"
                name="action"
                value="clear"
            >

            <button
                type="submit"
                class="btn clear"
            >
                Clear Cart
            </button>

        </form>


        <a
            href="checkout.php"
            class="btn checkout"
        >
            Checkout
        </a>

    </div>


<?php endif; ?>


</div>


</body>

</html>
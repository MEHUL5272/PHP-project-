<?php

session_start();

require_once __DIR__ . '/../vendor/autoload.php';

use MongoDB\Client;


// ===============================
// LOGIN CHECK
// ===============================

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}


// ===============================
// CART CHECK
// ===============================

$cart = $_SESSION["cart"] ?? [];

if (empty($cart)) {
    header("Location: products.php");
    exit;
}


// ===============================
// CALCULATE TOTAL
// ===============================

$total = 0;

foreach ($cart as $item) {

    $total +=
        $item["price"] *
        $item["quantity"];
}


// ===============================
// USER DETAILS
// ===============================

$userName = $_SESSION["user_name"] ?? "";
$userEmail = $_SESSION["user_email"] ?? "";

$message = "";


// ===============================
// PLACE ORDER
// ===============================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"] ?? "");
    $mobile = trim($_POST["mobile"] ?? "");
    $address = trim($_POST["address"] ?? "");

    if (
        $name === "" ||
        $mobile === "" ||
        $address === ""
    ) {

        $message = "Please fill all details.";

    } elseif (!preg_match("/^[0-9]{10}$/", $mobile)) {

        $message = "Please enter a valid 10 digit mobile number.";

    } else {

        try {

            $client = new Client(
                "mongodb://127.0.0.1:27017"
            );

            $db = $client->selectDatabase(
                "shree_shyam_coffee"
            );

            $orders = $db->orders;


            // ===============================
            // CREATE ORDER ITEMS
            // ===============================

            $orderItems = [];

            foreach ($cart as $item) {

                $orderItems[] = [

                    "product_id" =>
                        $item["product_id"],

                    "name" =>
                        $item["name"],

                    "category" =>
                        $item["category"],

                    "size" =>
                        $item["size"],

                    "price" =>
                        $item["price"],

                    "quantity" =>
                        $item["quantity"],

                    "subtotal" =>
                        $item["price"] *
                        $item["quantity"]
                ];
            }


            // ===============================
            // INSERT ORDER
            // ===============================

            $result = $orders->insertOne([

                "user_id" =>
                    $_SESSION["user_id"],

                "customer_name" =>
                    $name,

                "email" =>
                    $userEmail,

                "mobile" =>
                    $mobile,

                "address" =>
                    $address,

                "items" =>
                    $orderItems,

                "total" =>
                    $total,

                "status" =>
                    "Pending",

                "payment_status" =>
                    "Pending",

                "created_at" =>
                    new MongoDB\BSON\UTCDateTime()

            ]);


            // ===============================
            // ORDER ID
            // ===============================

            $orderId =
                (string)$result->getInsertedId();


            // ===============================
            // CLEAR CART
            // ===============================

            $_SESSION["cart"] = [];


            // ===============================
            // SUCCESS PAGE
            // ===============================

            ?>

            <!DOCTYPE html>

            <html>

            <head>

                <meta charset="UTF-8">

                <meta
                    name="viewport"
                    content="width=device-width, initial-scale=1.0"
                >

                <title>Order Successful</title>

                <style>

                    * {
                        box-sizing: border-box;
                    }

                    body {

                        margin: 0;

                        min-height: 100vh;

                        background: #090909;

                        color: white;

                        font-family: Arial, sans-serif;

                        display: flex;

                        justify-content: center;

                        align-items: center;

                        padding: 20px;
                    }

                    .success-box {

                        width: 450px;

                        max-width: 100%;

                        background: #151515;

                        border: 1px solid #e7a33a;

                        border-radius: 18px;

                        padding: 40px;

                        text-align: center;

                        box-shadow:
                            0 0 30px
                            rgba(218,155,45,0.25);
                    }

                    .icon {

                        font-size: 70px;

                        margin-bottom: 15px;
                    }

                    h1 {

                        color: #e7a33a;

                        margin-bottom: 15px;
                    }

                    p {

                        color: #bbb;

                        line-height: 1.7;
                    }

                    .order-id {

                        margin: 20px 0;

                        padding: 15px;

                        background: #222;

                        border-radius: 8px;

                        color: #e7a33a;

                        word-break: break-all;
                    }

                    .btn {

                        display: inline-block;

                        margin-top: 15px;

                        padding: 13px 22px;

                        background: #e7a33a;

                        color: #111;

                        text-decoration: none;

                        border-radius: 7px;

                        font-weight: bold;
                    }

                </style>

            </head>

            <body>

                <div class="success-box">

                    <div class="icon">
                        ☕🎉
                    </div>

                    <h1>
                        Order Placed Successfully!
                    </h1>

                    <p>
                        Thank you for ordering from
                        <strong>Shree Shyam Coffee</strong>.
                    </p>

                    <div class="order-id">

                        Order ID:<br>

                        <strong>
                            <?= htmlspecialchars($orderId) ?>
                        </strong>

                    </div>

                    <p>
                        Order Status:
                        <strong>Pending</strong>
                    </p>

                    <a
                        href="../index.php"
                        class="btn"
                    >
                        Back to Home
                    </a>

                </div>

            </body>

            </html>

            <?php

            exit;


        } catch (Exception $e) {

            $message =
                "Order Error: " .
                $e->getMessage();
        }
    }
}

?>


<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Checkout - Shree Shyam Coffee</title>

<style>

* {
    box-sizing: border-box;
}

body {

    margin: 0;

    background: #090909;

    color: white;

    font-family: Arial, sans-serif;
}

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

    font-size: 24px;

    font-weight: bold;
}

.navbar a {

    color: white;

    text-decoration: none;
}

.header {

    text-align: center;

    padding: 45px 20px;

    background:
        radial-gradient(
            circle,
            #38220d,
            #090909 65%
        );
}

.header h1 {

    color: #e7a33a;

    font-size: 40px;

    margin-bottom: 10px;
}

.header p {

    color: #aaa;
}

.container {

    width: 85%;

    max-width: 1100px;

    margin: auto;

    padding: 45px 0 80px;

    display: grid;

    grid-template-columns:
        1.2fr 0.8fr;

    gap: 30px;
}

.box {

    background: #161616;

    border: 1px solid #333;

    border-radius: 14px;

    padding: 28px;
}

.box h2 {

    color: #e7a33a;

    margin-bottom: 25px;
}

label {

    display: block;

    color: #ddd;

    margin-bottom: 7px;

    font-weight: bold;
}

input,
textarea {

    width: 100%;

    padding: 13px;

    margin-bottom: 18px;

    background: #222;

    color: white;

    border: 1px solid #444;

    border-radius: 7px;

    outline: none;

    font-size: 15px;
}

input:focus,
textarea:focus {

    border-color: #e7a33a;
}

textarea {

    min-height: 120px;

    resize: vertical;
}

.message {

    background: #2b1717;

    color: #ff7777;

    padding: 12px;

    border-radius: 7px;

    margin-bottom: 18px;
}

.order-item {

    padding: 15px 0;

    border-bottom: 1px solid #333;
}

.order-item h3 {

    color: white;

    margin-bottom: 7px;
}

.order-item p {

    color: #aaa;

    margin: 5px 0;
}

.item-price {

    color: #e7a33a;

    font-weight: bold;
}

.total {

    display: flex;

    justify-content: space-between;

    margin-top: 20px;

    font-size: 24px;

    font-weight: bold;
}

.total span:last-child {

    color: #e7a33a;
}

.place-order {

    width: 100%;

    margin-top: 25px;

    padding: 14px;

    border: none;

    border-radius: 8px;

    background: #e7a33a;

    color: #111;

    font-size: 16px;

    font-weight: bold;

    cursor: pointer;
}

.place-order:hover {

    background: #f5b84c;
}

@media(max-width: 800px) {

    .container {

        grid-template-columns: 1fr;
    }

}

</style>

</head>

<body>


<nav class="navbar">

    <div class="logo">
        ☕ Shree Shyam Coffee
    </div>

    <a href="cart.php">
        ← Back to Cart
    </a>

</nav>


<section class="header">

    <h1>
        Checkout
    </h1>

    <p>
        Complete your order
    </p>

</section>


<div class="container">


    <!-- CUSTOMER DETAILS -->

    <div class="box">

        <h2>
            Customer Details
        </h2>

        <?php if ($message !== ""): ?>

            <div class="message">
                <?= htmlspecialchars($message) ?>
            </div>

        <?php endif; ?>


        <form method="POST">

            <label>
                Full Name
            </label>

            <input
                type="text"
                name="name"
                value="<?= htmlspecialchars($userName) ?>"
                placeholder="Enter your full name"
                required
            >


            <label>
                Mobile Number
            </label>

            <input
                type="tel"
                name="mobile"
                maxlength="10"
                pattern="[0-9]{10}"
                placeholder="Enter 10 digit mobile number"
                required
            >


            <label>
                Delivery Address
            </label>

            <textarea
                name="address"
                placeholder="Enter your complete delivery address"
                required
            ></textarea>


            <button
                type="submit"
                class="place-order"
            >
                🛒 Place Order
            </button>

        </form>

    </div>


    <!-- ORDER SUMMARY -->

    <div class="box">

        <h2>
            Order Summary
        </h2>


        <?php foreach ($cart as $item): ?>

            <div class="order-item">

                <h3>
                    <?= htmlspecialchars($item["name"]) ?>
                </h3>

                <p>
                    <?= htmlspecialchars($item["size"]) ?>
                    ×
                    <?= $item["quantity"] ?>
                </p>

                <p class="item-price">

                    ₹<?= number_format(
                        $item["price"] *
                        $item["quantity"],
                        2
                    ) ?>

                </p>

            </div>

        <?php endforeach; ?>


        <div class="total">

            <span>
                Total
            </span>

            <span>
                ₹<?= number_format($total, 2) ?>
            </span>

        </div>

    </div>


</div>

</body>

</html>
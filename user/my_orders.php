<?php

session_start();

require_once __DIR__ . '/../vendor/autoload.php';

use MongoDB\Client;

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

try {

    $client = new Client("mongodb://127.0.0.1:27017");

    $db = $client->selectDatabase("shree_shyam_coffee");

    $ordersCollection = $db->orders;

    $userId = $_SESSION["user_id"];

    $orders = $ordersCollection->find(
        [
            "user_id" => $userId
        ],
        [
            "sort" => [
                "created_at" => -1
            ]
        ]
    );

} catch (Exception $e) {

    die("MongoDB Error: " . $e->getMessage());

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

<title>My Orders - Shree Shyam Coffee</title>


<style>

* {
    box-sizing: border-box;
}

body {

    margin: 0;

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

    font-size: 24px;

    font-weight: bold;
}

.navbar a {

    color: white;

    text-decoration: none;

    margin-left: 20px;
}

.navbar a:hover {

    color: #e7a33a;
}


/* HEADER */

.header {

    text-align: center;

    padding: 50px 20px;

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


/* CONTAINER */

.container {

    width: 90%;

    max-width: 1150px;

    margin: auto;

    padding: 40px 0 80px;
}


/* ORDER CARD */

.order-card {

    background: #161616;

    border: 1px solid #333;

    border-radius: 14px;

    padding: 25px;

    margin-bottom: 30px;
}


/* HEADER */

.order-header {

    display: flex;

    justify-content: space-between;

    align-items: center;

    border-bottom: 1px solid #333;

    padding-bottom: 18px;

    margin-bottom: 15px;

    gap: 20px;
}

.order-id {

    color: #e7a33a;

    font-weight: bold;

    word-break: break-all;
}

.status {

    padding: 7px 14px;

    border-radius: 20px;

    background: #33260f;

    color: #e7a33a;

    font-size: 13px;

    font-weight: bold;
}

.order-date {

    color: #888;

    font-size: 14px;

    margin-bottom: 25px;
}


/* TRACKING */

.tracking-title {

    color: #e7a33a;

    font-size: 18px;

    font-weight: bold;

    margin-bottom: 20px;
}

.tracking {

    display: flex;

    justify-content: space-between;

    align-items: flex-start;

    margin: 25px 0 35px;

    position: relative;
}

.tracking-line {

    position: absolute;

    top: 15px;

    left: 8%;

    right: 8%;

    height: 3px;

    background: #333;

    z-index: 0;
}

.step {

    position: relative;

    z-index: 1;

    width: 20%;

    text-align: center;
}

.step-circle {

    width: 30px;

    height: 30px;

    border-radius: 50%;

    background: #333;

    border: 3px solid #090909;

    margin: auto;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 13px;

    font-weight: bold;
}

.step.active .step-circle {

    background: #e7a33a;

    color: #111;
}

.step.completed .step-circle {

    background: #4caf50;

    color: white;
}

.step-name {

    margin-top: 10px;

    font-size: 12px;

    color: #777;
}

.step.active .step-name {

    color: #e7a33a;

    font-weight: bold;
}

.step.completed .step-name {

    color: #4caf50;

    font-weight: bold;
}


/* ITEMS */

.items-title {

    color: #e7a33a;

    font-size: 18px;

    font-weight: bold;

    margin-bottom: 12px;
}

.item {

    display: flex;

    justify-content: space-between;

    align-items: center;

    padding: 15px 0;

    border-bottom: 1px solid #252525;
}

.item-name {

    color: white;

    font-weight: bold;
}

.item-details {

    color: #999;

    font-size: 14px;

    margin-top: 5px;
}

.item-price {

    color: #e7a33a;

    font-weight: bold;
}


/* TOTAL */

.order-total {

    text-align: right;

    font-size: 24px;

    font-weight: bold;

    color: #e7a33a;

    margin-top: 20px;
}


/* ACTIONS */

.actions {

    display: flex;

    flex-wrap: wrap;

    gap: 10px;

    margin-top: 25px;

    padding-top: 20px;

    border-top: 1px solid #333;
}

.btn {

    display: inline-block;

    padding: 11px 17px;

    border-radius: 7px;

    text-decoration: none;

    font-weight: bold;

    transition: 0.3s;
}

.edit-btn {

    background: #e7a33a;

    color: #111;
}

.edit-btn:hover {

    background: #ffc766;
}

.add-btn {

    background: #286f42;

    color: white;
}

.add-btn:hover {

    background: #379358;
}

.update-btn {

    background: #365f91;

    color: white;
}

.update-btn:hover {

    background: #477ab8;
}

.cancel-btn {

    background: #8e2929;

    color: white;
}

.cancel-btn:hover {

    background: #b43a3a;
}


/* EMPTY */

.empty {

    text-align: center;

    padding: 80px 20px;
}

.empty-icon {

    font-size: 70px;

    margin-bottom: 20px;
}

.empty p {

    color: #aaa;

    margin-bottom: 25px;
}

.shop-btn {

    display: inline-block;

    padding: 13px 25px;

    background: #e7a33a;

    color: #111;

    text-decoration: none;

    border-radius: 7px;

    font-weight: bold;
}


/* MOBILE */

@media(max-width: 750px) {

    .navbar {

        flex-direction: column;

        gap: 15px;

        padding: 18px;
    }

    .order-header {

        flex-direction: column;

        align-items: flex-start;
    }

    .item {

        flex-direction: column;

        align-items: flex-start;

        gap: 8px;
    }

    .tracking {

        overflow-x: auto;

        padding-bottom: 10px;
    }

    .step {

        min-width: 95px;
    }

    .tracking-line {

        left: 48px;

        right: 48px;
    }

    .actions {

        flex-direction: column;
    }

    .btn {

        width: 100%;

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

    <div>

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
        My Orders 📦
    </h1>

    <p>
        Track and manage your coffee orders
    </p>

</section>


<div class="container">


<?php

$hasOrders = false;

foreach ($orders as $order):

    $hasOrders = true;

    $currentStatus =
        $order["status"] ?? "Pending";


    $statuses = [

        "Pending",
        "Confirmed",
        "Preparing",
        "Ready",
        "Completed"

    ];


    $currentIndex =
        array_search(
            $currentStatus,
            $statuses
        );


    if ($currentIndex === false) {

        $currentIndex = 0;

    }


    $canManage =
        $currentStatus === "Pending";

?>


<div class="order-card">


    <!-- ORDER HEADER -->

    <div class="order-header">

        <div>

            <div>
                Order ID
            </div>

            <div class="order-id">

                <?= htmlspecialchars(
                    (string)$order["_id"]
                ) ?>

            </div>

        </div>


        <div class="status">

            <?= htmlspecialchars(
                $currentStatus
            ) ?>

        </div>

    </div>


    <!-- DATE -->

    <div class="order-date">

        Order Date:

        <?php

        if (
            isset($order["created_at"]) &&
            $order["created_at"]
                instanceof MongoDB\BSON\UTCDateTime
        ) {

            echo $order["created_at"]
                ->toDateTime()
                ->format("d M Y, h:i A");

        } else {

            echo "-";

        }

        ?>

    </div>


    <!-- TRACKING -->

    <?php if ($currentStatus !== "Cancelled"): ?>

        <div class="tracking-title">
            Order Tracking 🚚
        </div>


        <div class="tracking">

            <div class="tracking-line"></div>


            <?php foreach (
                $statuses as $index => $status
            ): ?>

                <?php

                if ($index < $currentIndex) {

                    $class = "completed";

                } elseif ($index == $currentIndex) {

                    $class = "active";

                } else {

                    $class = "";

                }

                ?>


                <div class="step <?= $class ?>">

                    <div class="step-circle">

                        <?php if (
                            $index < $currentIndex
                        ): ?>

                            ✓

                        <?php else: ?>

                            <?= $index + 1 ?>

                        <?php endif; ?>

                    </div>


                    <div class="step-name">

                        <?= htmlspecialchars(
                            $status
                        ) ?>

                    </div>

                </div>


            <?php endforeach; ?>

        </div>

    <?php endif; ?>


    <!-- ITEMS -->

    <div class="items-title">

        ☕ Ordered Items

    </div>


    <?php foreach (
        ($order["items"] ?? []) as $item
    ): ?>


        <div class="item">

            <div>

                <div class="item-name">

                    <?= htmlspecialchars(
                        $item["name"] ?? "-"
                    ) ?>

                </div>


                <div class="item-details">

                    <?= htmlspecialchars(
                        $item["size"] ?? "-"
                    ) ?>

                    ×

                    <?= (int)(
                        $item["quantity"] ?? 0
                    ) ?>

                    &nbsp; | &nbsp;

                    ₹<?= number_format(
                        (float)(
                            $item["price"] ?? 0
                        ),
                        2
                    ) ?>

                    each

                </div>

            </div>


            <div class="item-price">

                ₹<?= number_format(
                    (float)(
                        $item["subtotal"] ?? 0
                    ),
                    2
                ) ?>

            </div>

        </div>


    <?php endforeach; ?>


    <!-- TOTAL -->

    <div class="order-total">

        Total:

        ₹<?= number_format(
            (float)(
                $order["total"] ?? 0
            ),
            2
        ) ?>

    </div>


    <!-- ACTIONS -->

    <?php if ($canManage): ?>

        <div class="actions">


            <!-- EDIT -->

            <a
                href="edit_order.php?order_id=<?= urlencode(
                    (string)$order["_id"]
                ) ?>"
                class="btn edit-btn"
            >
                ✏️ Edit Order
            </a>


            <!-- ADD -->

            <a
                href="add_to_order.php?order_id=<?= urlencode(
                    (string)$order["_id"]
                ) ?>"
                class="btn add-btn"
            >
                ➕ Add Item
            </a>


            <!-- CANCEL -->

            <a
                href="cancel_order.php?order_id=<?= urlencode(
                    (string)$order["_id"]
                ) ?>"
                class="btn cancel-btn"
                onclick="return confirm(
                    'Are you sure you want to cancel this order?'
                );"
            >
                ❌ Cancel Order
            </a>


        </div>

    <?php endif; ?>


</div>


<?php endforeach; ?>


<?php if (!$hasOrders): ?>


    <div class="empty">

        <div class="empty-icon">
            📦
        </div>

        <h2>
            No Orders Yet
        </h2>

        <p>
            You haven't placed any orders yet.
        </p>

        <a
            href="products.php"
            class="shop-btn"
        >
            Start Shopping
        </a>

    </div>


<?php endif; ?>


</div>


</body>

</html>
<?php

session_start();

require_once __DIR__ . '/../vendor/autoload.php';

use MongoDB\Client;

if (!isset($_SESSION["admin_id"])) {
    header("Location: admin_login.php");
    exit;
}

try {

    $client = new Client(
        "mongodb://127.0.0.1:27017"
    );

    $db = $client->selectDatabase(
        "shree_shyam_coffee"
    );

    $usersCollection = $db->users;
    $productsCollection = $db->products;
    $ordersCollection = $db->orders;


    /* ===============================
       BASIC COUNTS
    =============================== */

    $totalUsers =
        $usersCollection->countDocuments();

    $totalProducts =
        $productsCollection->countDocuments();

    $totalOrders =
        $ordersCollection->countDocuments();


    /* ===============================
       ORDER STATUS COUNTS
    =============================== */

    $pendingOrders =
        $ordersCollection->countDocuments([
            "status" => "Pending"
        ]);

    $completedOrders =
        $ordersCollection->countDocuments([
            "status" => "Completed"
        ]);


    /* ===============================
       TOTAL SALES
    =============================== */

    $totalSales = 0;

    $salesCursor = $ordersCollection->find(
        [],
        [
            "projection" => [
                "total" => 1
            ]
        ]
    );

    foreach ($salesCursor as $order) {

        $totalSales +=
            (float)($order["total"] ?? 0);

    }


    /* ===============================
       TODAY DATE RANGE
    =============================== */

    $startOfToday = new DateTime(
        "today",
        new DateTimeZone("Asia/Kolkata")
    );

    $startOfTomorrow = clone $startOfToday;

    $startOfTomorrow->modify("+1 day");


    /* ===============================
       TODAY'S ORDERS
    =============================== */

    $todayOrders = $ordersCollection->countDocuments([
        "created_at" => [
            '$gte' =>
                new MongoDB\BSON\UTCDateTime(
                    $startOfToday->getTimestamp() * 1000
                ),

            '$lt' =>
                new MongoDB\BSON\UTCDateTime(
                    $startOfTomorrow->getTimestamp() * 1000
                )
        ]
    ]);


    /* ===============================
       TODAY'S SALES
    =============================== */

    $todaySales = 0;

    $todaySalesCursor = $ordersCollection->find(
        [
            "created_at" => [
                '$gte' =>
                    new MongoDB\BSON\UTCDateTime(
                        $startOfToday->getTimestamp() * 1000
                    ),

                '$lt' =>
                    new MongoDB\BSON\UTCDateTime(
                        $startOfTomorrow->getTimestamp() * 1000
                    )
            ]
        ],
        [
            "projection" => [
                "total" => 1
            ]
        ]
    );

    foreach ($todaySalesCursor as $order) {

        $todaySales +=
            (float)($order["total"] ?? 0);

    }


    /* ===============================
       RECENT ORDERS
    =============================== */

    $recentOrders =
        $ordersCollection->find(
            [],
            [
                "sort" => [
                    "created_at" => -1
                ],
                "limit" => 5
            ]
        );


} catch (Exception $e) {

    die(
        "MongoDB Error: " .
        $e->getMessage()
    );

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

<title>
    Admin Dashboard - Shree Shyam Coffee
</title>

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

    padding: 15px 6%;

    gap: 20px;

}

.logo {

    color: #e7a33a;

    font-size: 24px;

    font-weight: bold;

}

.nav-right {

    display: flex;

    align-items: center;

    gap: 20px;

}

.admin-name {

    color: #ccc;

}

.logout {

    color: #ff7777;

    text-decoration: none;

    border: 1px solid #ff7777;

    padding: 8px 14px;

    border-radius: 6px;

}

.logout:hover {

    background: #ff7777;

    color: #111;

}


/* ===============================
   HEADER
================================ */

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

    margin: 0 0 10px;

}

.header p {

    color: #aaa;

    margin: 0;

}


/* ===============================
   CONTAINER
================================ */

.container {

    width: 90%;

    max-width: 1250px;

    margin: auto;

    padding: 40px 0 70px;

}


/* ===============================
   STATISTICS
================================ */

.stats {

    display: grid;

    grid-template-columns:
        repeat(4, 1fr);

    gap: 20px;

    margin-bottom: 40px;

}

.stat-card {

    background: #161616;

    border: 1px solid #333;

    border-radius: 14px;

    padding: 25px;

    text-align: center;

    transition: 0.2s;

}

.stat-card:hover {

    border-color: #e7a33a;

    transform: translateY(-3px);

}

.stat-icon {

    font-size: 40px;

    margin-bottom: 10px;

}

.stat-title {

    color: #aaa;

    font-size: 14px;

    margin-bottom: 10px;

}

.stat-value {

    color: #e7a33a;

    font-size: 28px;

    font-weight: bold;

}


/* ===============================
   SECTION TITLE
================================ */

.section-title {

    color: #e7a33a;

    font-size: 25px;

    margin-bottom: 20px;

}


/* ===============================
   QUICK ACTIONS
================================ */

.actions {

    display: grid;

    grid-template-columns:
        repeat(3, 1fr);

    gap: 20px;

    margin-bottom: 45px;

}

.action-card {

    background: #161616;

    border: 1px solid #333;

    border-radius: 12px;

    padding: 22px;

    text-decoration: none;

    color: white;

    transition: 0.2s;

}

.action-card:hover {

    border-color: #e7a33a;

    transform: translateY(-3px);

}

.action-icon {

    font-size: 35px;

    margin-bottom: 10px;

}

.action-title {

    font-size: 18px;

    font-weight: bold;

    margin-bottom: 6px;

}

.action-text {

    color: #999;

    font-size: 14px;

}


/* ===============================
   RECENT ORDERS
================================ */

.orders-box {

    background: #161616;

    border: 1px solid #333;

    border-radius: 14px;

    overflow: hidden;

}

.order-row {

    display: grid;

    grid-template-columns:
        1.5fr 1fr 1fr 1fr;

    gap: 15px;

    padding: 18px 22px;

    border-bottom: 1px solid #292929;

    align-items: center;

}

.order-row:last-child {

    border-bottom: none;

}

.order-heading {

    color: #e7a33a;

    font-weight: bold;

    background: #111;

}

.order-id {

    color: #ddd;

    word-break: break-all;

    font-size: 13px;

}

.customer {

    color: white;

}

.order-total {

    color: #e7a33a;

    font-weight: bold;

}

.status {

    display: inline-block;

    background: #33260f;

    color: #e7a33a;

    padding: 6px 10px;

    border-radius: 15px;

    font-size: 12px;

    font-weight: bold;

}

.no-orders {

    text-align: center;

    color: #888;

    padding: 35px;

}


/* ===============================
   RESPONSIVE
================================ */

@media(max-width: 1000px) {

    .stats {

        grid-template-columns:
            repeat(2, 1fr);

    }

}

@media(max-width: 900px) {

    .actions {

        grid-template-columns:
            repeat(2, 1fr);

    }

}

@media(max-width: 650px) {

    .navbar {

        height: auto;

        padding: 18px 5%;

        gap: 15px;

        flex-direction: column;

    }

    .nav-right {

        flex-wrap: wrap;

        justify-content: center;

    }

    .stats {

        grid-template-columns: 1fr;

    }

    .actions {

        grid-template-columns: 1fr;

    }

    .order-row {

        grid-template-columns: 1fr;

        gap: 8px;

    }

    .order-heading {

        display: none;

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


    <div class="nav-right">

        <span class="admin-name">

            👤

            <?= htmlspecialchars(
                $_SESSION["admin_name"] ?? "Admin"
            ) ?>

        </span>


        <a
            href="admin_logout.php"
            class="logout"
        >
            Logout
        </a>

    </div>

</nav>


<!-- ===============================
     HEADER
================================ -->

<section class="header">

    <h1>
        Admin Dashboard 📊
    </h1>

    <p>
        Manage Shree Shyam Coffee
    </p>

</section>


<div class="container">


<!-- ===============================
     STATISTICS
================================ -->

<div class="stats">


    <!-- TOTAL USERS -->

    <div class="stat-card">

        <div class="stat-icon">
            👥
        </div>

        <div class="stat-title">
            Total Users
        </div>

        <div class="stat-value">
            <?= $totalUsers ?>
        </div>

    </div>


    <!-- TOTAL PRODUCTS -->

    <div class="stat-card">

        <div class="stat-icon">
            ☕
        </div>

        <div class="stat-title">
            Total Products
        </div>

        <div class="stat-value">
            <?= $totalProducts ?>
        </div>

    </div>


    <!-- TOTAL ORDERS -->

    <div class="stat-card">

        <div class="stat-icon">
            📦
        </div>

        <div class="stat-title">
            Total Orders
        </div>

        <div class="stat-value">
            <?= $totalOrders ?>
        </div>

    </div>


    <!-- TOTAL SALES -->

    <div class="stat-card">

        <div class="stat-icon">
            💰
        </div>

        <div class="stat-title">
            Total Sales
        </div>

        <div class="stat-value">
            ₹<?= number_format(
                $totalSales,
                2
            ) ?>
        </div>

    </div>


    <!-- PENDING ORDERS -->

    <div class="stat-card">

        <div class="stat-icon">
            ⏳
        </div>

        <div class="stat-title">
            Pending Orders
        </div>

        <div class="stat-value">
            <?= $pendingOrders ?>
        </div>

    </div>


    <!-- COMPLETED ORDERS -->

    <div class="stat-card">

        <div class="stat-icon">
            ✅
        </div>

        <div class="stat-title">
            Completed Orders
        </div>

        <div class="stat-value">
            <?= $completedOrders ?>
        </div>

    </div>


    <!-- TODAY ORDERS -->

    <div class="stat-card">

        <div class="stat-icon">
            📅
        </div>

        <div class="stat-title">
            Today's Orders
        </div>

        <div class="stat-value">
            <?= $todayOrders ?>
        </div>

    </div>


    <!-- TODAY SALES -->

    <div class="stat-card">

        <div class="stat-icon">
            💵
        </div>

        <div class="stat-title">
            Today's Sales
        </div>

        <div class="stat-value">

            ₹<?= number_format(
                $todaySales,
                2
            ) ?>

        </div>

    </div>


</div>


<!-- ===============================
     QUICK ACTIONS
================================ -->

<div class="section-title">

    Quick Actions

</div>


<div class="actions">


    <a
        href="users.php"
        class="action-card"
    >

        <div class="action-icon">
            👥
        </div>

        <div class="action-title">
            Manage Users
        </div>

        <div class="action-text">
            View and manage registered users.
        </div>

    </a>


    <a
        href="products.php"
        class="action-card"
    >

        <div class="action-icon">
            ☕
        </div>

        <div class="action-title">
            Manage Products
        </div>

        <div class="action-text">
            Add, edit and delete coffee products.
        </div>

    </a>


    <a
        href="orders.php"
        class="action-card"
    >

        <div class="action-icon">
            📦
        </div>

        <div class="action-title">
            Manage Orders
        </div>

        <div class="action-text">
            View orders and update order status.
        </div>

    </a>


</div>


<!-- ===============================
     RECENT ORDERS
================================ -->

<div class="section-title">

    Recent Orders

</div>


<div class="orders-box">


    <div class="order-row order-heading">

        <div>
            Order ID
        </div>

        <div>
            Customer
        </div>

        <div>
            Total
        </div>

        <div>
            Status
        </div>

    </div>


<?php

$hasOrders = false;

foreach ($recentOrders as $order):

    $hasOrders = true;

?>

    <div class="order-row">


        <div class="order-id">

            <?= htmlspecialchars(
                (string)$order["_id"]
            ) ?>

        </div>


        <div class="customer">

            <?= htmlspecialchars(
                $order["customer_name"]
                ?? "Unknown"
            ) ?>

        </div>


        <div class="order-total">

            ₹<?= number_format(
                (float)(
                    $order["total"] ?? 0
                ),
                2
            ) ?>

        </div>


        <div>

            <span class="status">

                <?= htmlspecialchars(
                    $order["status"]
                    ?? "Pending"
                ) ?>

            </span>

        </div>


    </div>

<?php endforeach; ?>


<?php if (!$hasOrders): ?>

    <div class="no-orders">

        No orders found.

    </div>

<?php endif; ?>


</div>


</div>


</body>

</html>
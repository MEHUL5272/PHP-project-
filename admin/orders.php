<?php

session_start();

require_once __DIR__ . '/../vendor/autoload.php';

use MongoDB\Client;
use MongoDB\BSON\ObjectId;


// ===============================
// ADMIN LOGIN CHECK
// ===============================

if (!isset($_SESSION["admin_id"])) {

    header("Location: admin_login.php");
    exit;
}


// ===============================
// MONGODB CONNECTION
// ===============================

try {

    $client = new Client(
        "mongodb://127.0.0.1:27017"
    );

    $db = $client->selectDatabase(
        "shree_shyam_coffee"
    );

    $ordersCollection = $db->orders;

    $message = "";


    // ===============================
// UPDATE ORDER STATUS
// ===============================

    if (
        $_SERVER["REQUEST_METHOD"] === "POST" &&
        isset($_POST["update_status"])
    ) {

        $orderId = $_POST["order_id"] ?? "";

        $status = $_POST["status"] ?? "";

        $allowedStatuses = [
            "Pending",
            "Confirmed",
            "Preparing",
            "Ready",
            "Completed"
        ];


        if (
            $orderId !== "" &&
            in_array($status, $allowedStatuses, true)
        ) {

            try {

                $ordersCollection->updateOne(

                    [
                        "_id" => new ObjectId($orderId)
                    ],

                    [
                        '$set' => [
                            "status" => $status
                        ]
                    ]

                );

                $message =
                    "Order status updated successfully.";

            } catch (Exception $e) {

                $message =
                    "Unable to update order status.";
            }
        }
    }


    // ===============================
// UPDATE PAYMENT STATUS
// ===============================

    if (
        $_SERVER["REQUEST_METHOD"] === "POST" &&
        isset($_POST["update_payment"])
    ) {

        $orderId =
            $_POST["order_id"] ?? "";

        $paymentStatus =
            $_POST["payment_status"] ?? "";

        $allowedPaymentStatuses = [
            "Pending",
            "Paid",
            "Failed"
        ];


        if (
            $orderId !== "" &&
            in_array(
                $paymentStatus,
                $allowedPaymentStatuses,
                true
            )
        ) {

            try {

                $ordersCollection->updateOne(

                    [
                        "_id" =>
                            new ObjectId($orderId)
                    ],

                    [
                        '$set' => [
                            "payment_status" =>
                                $paymentStatus
                        ]
                    ]

                );

                $message =
                    "Payment status updated successfully.";

            } catch (Exception $e) {

                $message =
                    "Unable to update payment status.";
            }
        }
    }


    // ===============================
// GET ALL ORDERS
// ===============================

    $orders = $ordersCollection->find(

        [],

        [
            "sort" => [
                "created_at" => -1
            ]
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

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Manage Orders - Shree Shyam Coffee</title>


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

        .nav-links {

            display: flex;

            align-items: center;

            gap: 20px;
        }

        .nav-links a {

            color: white;

            text-decoration: none;
        }

        .nav-links a:hover {

            color: #e7a33a;
        }


        /* ===============================
   HEADER
================================ */

        .header {

            text-align: center;

            padding: 45px 20px;

            background:
                radial-gradient(circle,
                    #38220d,
                    #090909 65%);
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

            width: 92%;

            max-width: 1250px;

            margin: auto;

            padding: 40px 0 70px;
        }


        /* ===============================
   MESSAGE
================================ */

        .message {

            background: #16351f;

            color: #72e69a;

            border: 1px solid #2c7041;

            padding: 14px 18px;

            border-radius: 8px;

            margin-bottom: 25px;

            font-weight: bold;
        }


        /* ===============================
   ORDER CARD
================================ */

        .order-card {

            background: #161616;

            border: 1px solid #333;

            border-radius: 14px;

            padding: 25px;

            margin-bottom: 25px;
        }


        /* ===============================
   ORDER HEADER
================================ */

        .order-header {

            display: flex;

            justify-content: space-between;

            align-items: flex-start;

            gap: 20px;

            padding-bottom: 18px;

            border-bottom: 1px solid #333;

            margin-bottom: 18px;
        }

        .order-id-label {

            color: #999;

            font-size: 13px;

            margin-bottom: 5px;
        }

        .order-id {

            color: #e7a33a;

            font-weight: bold;

            word-break: break-all;
        }

        .order-date {

            color: #888;

            font-size: 14px;

            margin-top: 8px;
        }


        /* ===============================
   CUSTOMER
================================ */

        .customer-box {

            background: #111;

            border-radius: 10px;

            padding: 18px;

            margin-bottom: 20px;
        }

        .customer-title {

            color: #e7a33a;

            font-size: 18px;

            font-weight: bold;

            margin-bottom: 12px;
        }

        .customer-info {

            display: grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap: 12px;
        }

        .info-item {

            color: #ccc;

            font-size: 14px;

            line-height: 1.6;
        }

        .info-label {

            color: #888;

            font-size: 12px;
        }


        /* ===============================
   ITEMS
================================ */

        .items-title {

            color: #e7a33a;

            font-weight: bold;

            margin-bottom: 10px;
        }

        .item {

            display: flex;

            justify-content: space-between;

            align-items: center;

            padding: 13px 0;

            border-bottom: 1px solid #292929;
        }

        .item:last-child {

            border-bottom: none;
        }

        .item-name {

            font-weight: bold;

            color: white;
        }

        .item-details {

            color: #999;

            font-size: 14px;

            margin-top: 4px;
        }

        .item-price {

            color: #e7a33a;

            font-weight: bold;
        }


        /* ===============================
   BOTTOM
================================ */

        .order-bottom {

            display: flex;

            justify-content: space-between;

            align-items: flex-end;

            gap: 25px;

            margin-top: 22px;

            padding-top: 18px;

            border-top: 1px solid #333;
        }

        .total {

            color: #e7a33a;

            font-size: 24px;

            font-weight: bold;
        }


        /* ===============================
   STATUS CONTROLS
================================ */

        .controls {

            display: flex;

            gap: 12px;

            flex-wrap: wrap;

            justify-content: flex-end;
        }

        .control-box {

            display: flex;

            flex-direction: column;

            gap: 6px;
        }

        .control-box label {

            color: #888;

            font-size: 12px;
        }

        select {

            min-width: 150px;

            padding: 10px;

            background: #222;

            color: white;

            border: 1px solid #444;

            border-radius: 6px;

            outline: none;
        }

        select:focus {

            border-color: #e7a33a;
        }

        .update-btn {

            padding: 10px 14px;

            border: none;

            border-radius: 6px;

            background: #e7a33a;

            color: #111;

            font-weight: bold;

            cursor: pointer;
        }

        .update-btn:hover {

            background: #f5b84c;
        }


        /* ===============================
   EMPTY
================================ */

        .empty {

            text-align: center;

            padding: 80px 20px;

            background: #161616;

            border: 1px solid #333;

            border-radius: 14px;

            color: #888;
        }

        .empty-icon {

            font-size: 65px;

            margin-bottom: 15px;
        }


        /* ===============================
   RESPONSIVE
================================ */

        @media(max-width: 850px) {

            .navbar {

                flex-direction: column;

                justify-content: center;
            }

            .nav-links {

                flex-wrap: wrap;

                justify-content: center;
            }

            .customer-info {

                grid-template-columns: 1fr;
            }

            .order-bottom {

                flex-direction: column;

                align-items: stretch;
            }

            .controls {

                justify-content: flex-start;
            }

        }

        @media(max-width: 600px) {

            .order-header {

                flex-direction: column;
            }

            .item {

                flex-direction: column;

                align-items: flex-start;

                gap: 8px;
            }

            .controls {

                flex-direction: column;
            }

            .control-box,
            select,
            .update-btn {

                width: 100%;
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

            <a href="dashboard.php">
                Dashboard
            </a>

            <a href="users.php">
                Users
            </a>

            <a href="products.php">
                Products
            </a>

            <a href="admin_logout.php">
                Logout
            </a>

        </div>

    </nav>


    <!-- ===============================
     HEADER
================================ -->

    <section class="header">

        <h1>
            Manage Orders 📦
        </h1>

        <p>
            View orders and update their status
        </p>

    </section>


    <div class="container">


        <?php if ($message !== ""): ?>

            <div class="message">

                <?= htmlspecialchars($message) ?>

            </div>

        <?php endif; ?>


        <?php

        $hasOrders = false;

        foreach ($orders as $order):

            $hasOrders = true;

            ?>


            <div class="order-card">


                <!-- ===============================
         ORDER HEADER
    ================================ -->

                <div class="order-header">

                    <div>

                        <div class="order-id-label">
                            Order ID
                        </div>

                        <div class="order-id">

                            <?= htmlspecialchars(
                                (string) $order["_id"]
                            ) ?>

                        </div>

                        <div class="order-date">

                            <?php

                            if (
                                isset($order["created_at"]) &&
                                $order["created_at"]
                                instanceof MongoDB\BSON\UTCDateTime
                            ) {

                                echo $order["created_at"]
                                    ->toDateTime()
                                    ->format(
                                        "d M Y, h:i A"
                                    );

                            } else {

                                echo "-";

                            }

                            ?>

                        </div>

                    </div>


                    <div>

                        <strong>
                            Status:
                        </strong>

                        <span style="
                    color:#e7a33a;
                    font-weight:bold;
                ">

                            <?= htmlspecialchars(
                                $order["status"] ?? "Pending"
                            ) ?>

                        </span>

                    </div>

                </div>


                <!-- ===============================
         CUSTOMER DETAILS
    ================================ -->

                <div class="customer-box">

                    <div class="customer-title">
                        👤 Customer Details
                    </div>


                    <div class="customer-info">


                        <div class="info-item">

                            <div class="info-label">
                                Name
                            </div>

                            <?= htmlspecialchars(
                                $order["customer_name"] ?? "-"
                            ) ?>

                        </div>


                        <div class="info-item">

                            <div class="info-label">
                                Email
                            </div>

                            <?= htmlspecialchars(
                                $order["email"] ?? "-"
                            ) ?>

                        </div>


                        <div class="info-item">

                            <div class="info-label">
                                Mobile
                            </div>

                            <?= htmlspecialchars(
                                $order["mobile"] ?? "-"
                            ) ?>

                        </div>


                        <div class="info-item" style="grid-column:1/-1;">

                            <div class="info-label">
                                Delivery Address
                            </div>

                            <?= htmlspecialchars(
                                $order["address"] ?? "-"
                            ) ?>

                        </div>


                    </div>

                </div>


                <!-- ===============================
         ORDER ITEMS
    ================================ -->

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

                                <?= (int) (
                                    $item["quantity"] ?? 0
                                ) ?>

                                &nbsp; | &nbsp;

                                ₹<?= number_format(
                                    (float) (
                                        $item["price"] ?? 0
                                    ),
                                    2
                                ) ?>

                                each

                            </div>

                        </div>


                        <div class="item-price">

                            ₹<?= number_format(
                                (float) (
                                    $item["subtotal"] ?? 0
                                ),
                                2
                            ) ?>

                        </div>


                    </div>


                <?php endforeach; ?>


                <!-- ===============================
         TOTAL + CONTROLS
    ================================ -->

                <div class="order-bottom">


                    <div>

                        <div class="total">

                            Total:
                            ₹<?= number_format(
                                (float) (
                                    $order["total"] ?? 0
                                ),
                                2
                            ) ?>

                        </div>

                    </div>


                    <div class="controls">

                        <!-- INVOICE -->
                        <a href="invoice.php?order_id=<?= urlencode((string) $order["_id"]) ?>" class="update-btn" style="
            text-decoration:none;
            display:inline-block;
            text-align:center;
        ">
                            🧾 Invoice
                        </a>

                        <!-- ORDER STATUS -->

                        <form method="POST" class="control-box">

                            <label>
                                Order Status
                            </label>


                            <input type="hidden" name="order_id" value="<?= htmlspecialchars(
                                (string) $order["_id"]
                            ) ?>">


                            <div style="
                        display:flex;
                        gap:7px;
                    ">

                                <select name="status">

                                    <?php

                                    $currentStatus =
                                        $order["status"]
                                        ?? "Pending";

                                    $statuses = [

                                        "Pending",

                                        "Confirmed",

                                        "Preparing",

                                        "Ready",

                                        "Completed"

                                    ];

                                    foreach (
                                        $statuses as $status
                                    ):

                                        ?>

                                        <option value="<?= $status ?>" <?= $currentStatus === $status
                                              ? "selected"
                                              : "" ?>>
                                            <?= $status ?>
                                        </option>

                                    <?php endforeach; ?>

                                </select>


                                <button type="submit" name="update_status" class="update-btn">
                                    Update
                                </button>

                            </div>

                        </form>


                        <!-- PAYMENT STATUS -->

                        <form method="POST" class="control-box">

                            <label>
                                Payment Status
                            </label>


                            <input type="hidden" name="order_id" value="<?= htmlspecialchars(
                                (string) $order["_id"]
                            ) ?>">


                            <div style="
                        display:flex;
                        gap:7px;
                    ">

                                <?php

                                $currentPayment =
                                    $order["payment_status"]
                                    ?? "Pending";

                                ?>

                                <select name="payment_status">

                                    <option value="Pending" <?= $currentPayment === "Pending"
                                        ? "selected"
                                        : "" ?>>
                                        Pending
                                    </option>

                                    <option value="Paid" <?= $currentPayment === "Paid"
                                        ? "selected"
                                        : "" ?>>
                                        Paid
                                    </option>

                                    <option value="Failed" <?= $currentPayment === "Failed"
                                        ? "selected"
                                        : "" ?>>
                                        Failed
                                    </option>

                                </select>


                                <button type="submit" name="update_payment" class="update-btn">
                                    Update
                                </button>

                            </div>

                        </form>


                    </div>

                </div>


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
                    Customer orders will appear here.
                </p>

            </div>

        <?php endif; ?>


    </div>


</body>

</html>